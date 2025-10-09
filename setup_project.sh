#!/usr/bin/env bash
#
# Project Initialization Script
#
# This script automates the setup of a new project, including:
# 1. Configuring DDEV and cloning the starter theme.
# 2. Creating a private GitHub repository and pushing initial branches.
# 3. Provisioning AWS resources:
#    - A private S3 bucket.
#    - A dedicated IAM user with least-privilege permissions.
#    - A secure CloudFront distribution using Origin Access Control (OAC).
# 4. Setting up required GitHub Actions secrets.
# 5. Creating a local .env file.
# 6. Installing PHP and Node.js dependencies.
#

# Exit immediately if a command exits with a non-zero status.
# Treat unset variables as an error.
# Pipelines return the exit code of the last command to exit with a non-zero status.
set -euo pipefail

# --- Configuration ---
readonly ORG_NAME="plottcreative"
readonly AWS_REGION="eu-west-1"
readonly THEME_REPO_URL="https://github.com/plottcreative/plott-os"
readonly THEME_DIR="web/app/themes/plott-os"
# --- End Configuration ---

# Dynamic variables derived from the current environment
readonly REPO_NAME=$(basename "$PWD")
readonly BUCKET_NAME="${REPO_NAME}-s3-bucket"
readonly IAM_USER_NAME="${REPO_NAME}-s3-user"
readonly CREDENTIALS_FILE="iam_user_creds.json"

# --- Helper Functions for Logging ---
info() { printf "\n\033[1;34m%s\033[0m\n" "$*"; }
success() { printf "\033[1;32m✓ %s\033[0m\n" "$*"; }
error() { printf "\033[1;31m✗ %s\033[0m\n" "$*"; exit 1; }
warning() { printf "\033[1;33m⚠️ %s\033[0m\n" "$*"; }

# --- Script Functions ---

# Function to check for required command-line tools
check_dependencies() {
    info "Checking for required tools..."
    local missing_deps=0
    for cmd in ddev git gh aws jq npm composer; do
        if ! command -v "$cmd" &>/dev/null; then
            warning "'$cmd' is not installed or not in your PATH."
            missing_deps=1
        fi
    done
    [[ "$missing_deps" -eq 0 ]] && success "All dependencies found." || error "Please install missing dependencies and try again."
}

# Function to set up the DDEV project
setup_ddev() {
    info "Configuring DDEV project..."
    ddev config --project-name "$REPO_NAME" --project-type=php --docroot=web
    ddev add-on get ddev/ddev-adminer
    ddev start
    success "DDEV project '$REPO_NAME' started."
}

# Function to initialize the Git repository and push to GitHub
setup_git_repo() {
    info "Setting up Git and GitHub repository..."
    if [ -d ".git" ]; then
        error "A Git repository already exists in this directory. Please run this script in a new project directory."
    fi

    info "Cloning starter theme from $THEME_REPO_URL..."
    git clone "$THEME_REPO_URL" "$THEME_DIR"
    rm -rf "${THEME_DIR}/.git"
    success "Theme cloned and its .git folder removed."

    info "Creating private GitHub repository: $ORG_NAME/$REPO_NAME"
    gh repo create "$ORG_NAME/$REPO_NAME" --private --confirm
    
    git init -b main
    git add .
    git commit -m "Initial commit"
    git remote add origin "https://github.com/$ORG_NAME/$REPO_NAME.git"
    git push -u origin main
    success "Pushed 'main' branch to origin."

    git checkout -b stage
    git push -u origin stage
    success "Created and pushed 'stage' branch."

    git checkout -b develop
    git push -u origin develop
    success "Created and pushed 'develop' branch."
}

# Function to configure AWS CLI if not already set up
configure_aws_cli() {
    info "Checking AWS CLI configuration..."
    if ! aws configure list-profiles | grep -q "default"; then
        warning "Default AWS profile not found. Let's configure it."
        read -rp "Enter your AWS Access Key ID: " aws_key_id
        read -rsp "Enter your AWS Secret Access Key: " aws_key_secret
        echo "" # Newline after secret input
        aws configure set aws_access_key_id "$aws_key_id"
        aws configure set aws_secret_access_key "$aws_key_secret"
        aws configure set default.region "$AWS_REGION"
        success "AWS CLI default profile configured."
    else
        success "AWS CLI is already configured."
    fi
}

# Function to create the S3 bucket
create_s3_bucket() {
    info "Checking for S3 bucket '$BUCKET_NAME'..."
    if aws s3api head-bucket --bucket "$BUCKET_NAME" 2>/dev/null; then
        error "S3 bucket '$BUCKET_NAME' already exists. Exiting."
    fi

    info "Creating S3 bucket '$BUCKET_NAME' in $AWS_REGION..."
    aws s3api create-bucket \
        --bucket "$BUCKET_NAME" \
        --region "$AWS_REGION" \
        --create-bucket-configuration LocationConstraint="$AWS_REGION"
    
    info "Blocking all public access to the bucket..."
    aws s3api put-public-access-block \
        --bucket "$BUCKET_NAME" \
        --public-access-block-configuration "BlockPublicAcls=true,IgnorePublicAcls=true,BlockPublicPolicy=true,RestrictPublicBuckets=true"
        
    success "S3 bucket '$BUCKET_NAME' created and secured."
}

# Function to create an IAM user and a least-privilege policy
create_iam_user_and_keys() {
    info "Creating IAM user '$IAM_USER_NAME'..."
    if aws iam get-user --user-name "$IAM_USER_NAME" &>/dev/null; then
        warning "IAM user '$IAM_USER_NAME' already exists. Skipping creation."
    else
        aws iam create-user --user-name "$IAM_USER_NAME"
        success "IAM user created."
    fi

    info "Creating a least-privilege IAM policy..."
    local policy_json
    policy_json=$(cat <<EOF
{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Effect": "Allow",
            "Action": [
                "s3:GetObject",
                "s3:PutObject",
                "s3:DeleteObject",
                "s3:ListBucket"
            ],
            "Resource": [
                "arn:aws:s3:::${BUCKET_NAME}",
                "arn:aws:s3:::${BUCKET_NAME}/*"
            ]
        }
    ]
}
EOF
)
    aws iam put-user-policy \
        --user-name "$IAM_USER_NAME" \
        --policy-name "${REPO_NAME}-S3-Access-Policy" \
        --policy-document "$policy_json"
    success "IAM policy attached to user."

    info "Generating access keys for IAM user..."
    aws iam create-access-key --user-name "$IAM_USER_NAME" > "$CREDENTIALS_FILE"
    success "Access keys created and saved temporarily to $CREDENTIALS_FILE."
}

# Function to set secrets in the GitHub repository
set_github_secrets() {
    info "Setting GitHub Actions secrets..."
    local aws_access_key_id aws_secret_access_key
    aws_access_key_id=$(jq -r '.AccessKey.AccessKeyId' "$CREDENTIALS_FILE")
    aws_secret_access_key=$(jq -r '.AccessKey.SecretAccessKey' "$CREDENTIALS_FILE")

    gh secret set AWS_ACCESS_KEY_ID --body "$aws_access_key_id" --repo "$ORG_NAME/$REPO_NAME"
    gh secret set AWS_SECRET_ACCESS_KEY --body "$aws_secret_access_key" --repo "$ORG_NAME/$REPO_NAME"
    success "Set AWS credentials as GitHub secrets."

    # Set placeholder database credentials
    gh secret set DB_NAME_PROD --body "production_db_name" --repo "$ORG_NAME/$REPO_NAME"
    gh secret set DB_USER_PROD --body "production_db_user" --repo "$ORG_NAME/$REPO_NAME"
    gh secret set DB_PASS_PROD --body "change_me_in_github_secrets" --repo "$ORG_NAME/$REPO_NAME"
    gh secret set DB_NAME_STAGE --body "staging_db_name" --repo "$ORG_NAME/$REPO_NAME"
    success "Set placeholder database secrets."
}

# Function to create a secure CloudFront distribution
create_cloudfront_distribution() {
    info "Creating CloudFront Origin Access Control (OAC)..."
    local oac_id
    oac_id=$(aws cloudfront create-origin-access-control \
        --origin-access-control-config "Name=${REPO_NAME}-OAC,OriginAccessControlOriginType=s3,SigningBehavior=always,SigningProtocol=sigv4" \
        | jq -r '.OriginAccessControl.Id')
    success "OAC created with ID: $oac_id"

    info "Creating CloudFront distribution..."
    local bucket_endpoint="${BUCKET_NAME}.s3.${AWS_REGION}.amazonaws.com"
    local distribution_config
    distribution_config=$(cat <<EOF
{
    "CallerReference": "${REPO_NAME}-$(date +%s)",
    "Comment": "Distribution for ${REPO_NAME}",
    "Enabled": true,
    "Origins": {
        "Quantity": 1,
        "Items": [{
            "Id": "${REPO_NAME}-S3-Origin",
            "DomainName": "${bucket_endpoint}",
            "OriginAccessControlId": "${oac_id}",
            "S3OriginConfig": { "OriginAccessIdentity": "" }
        }]
    },
    "DefaultCacheBehavior": {
        "TargetOriginId": "${REPO_NAME}-S3-Origin",
        "ViewerProtocolPolicy": "redirect-to-https",
        "AllowedMethods": {
            "Quantity": 2, "Items": ["GET", "HEAD"],
            "CachedMethods": { "Quantity": 2, "Items": ["GET", "HEAD"] }
        },
        "Compress": true,
        "ForwardedValues": { "QueryString": false, "Cookies": { "Forward": "none" } },
        "MinTTL": 0, "DefaultTTL": 86400, "MaxTTL": 31536000
    },
    "ViewerCertificate": { "CloudFrontDefaultCertificate": true, "MinimumProtocolVersion": "TLSv1.2_2021" },
    "HttpVersion": "http2",
    "IsIPV6Enabled": true
}
EOF
)
    local distribution_json
    distribution_json=$(aws cloudfront create-distribution --distribution-config "$distribution_config")
    local distribution_id
    distribution_id=$(echo "$distribution_json" | jq -r '.Distribution.Id')
    local distribution_arn
    distribution_arn=$(echo "$distribution_json" | jq -r '.Distribution.ARN')
    success "CloudFront distribution created with ID: $distribution_id"

    info "Updating S3 bucket policy to grant access to CloudFront..."
    local bucket_policy
    bucket_policy=$(cat <<EOF
{
    "Version": "2012-10-17",
    "Statement": [{
        "Effect": "Allow",
        "Principal": { "Service": "cloudfront.amazonaws.com" },
        "Action": "s3:GetObject",
        "Resource": "arn:aws:s3:::${BUCKET_NAME}/*",
        "Condition": {
            "StringEquals": {
                "AWS:SourceArn": "${distribution_arn}"
            }
        }
    }]
}
EOF
)
    aws s3api put-bucket-policy --bucket "$BUCKET_NAME" --policy "$bucket_policy"
    success "S3 bucket policy updated for CloudFront access."
}

# Function to create the local .env file
create_env_file() {
    info "Creating local .env file..."
    if [ -f ".env" ]; then
        cp ".env" ".env.bak"
        warning "Existing .env file backed up as .env.bak"
    fi

    cat > ".env" <<EOL
# --- DDEV Environment Variables ---
WP_ENV=development
WP_HOME=https://${REPO_NAME}.ddev.site
WP_SITEURL=https://${REPO_NAME}.ddev.site/wp
DB_NAME=db
DB_USER=db
DB_PASSWORD=db
DB_HOST=db
EOL
    success ".env file created successfully."
}

# Function to install Composer and NPM dependencies
install_dependencies() {
    info "Installing Composer dependencies..."
    composer install --quiet
    success "Composer dependencies installed."

    info "Installing theme NPM dependencies..."
    if [ -f "${THEME_DIR}/package.json" ]; then
        (cd "$THEME_DIR" && npm install)
        success "NPM dependencies installed."
        info "Running initial asset build..."
        (cd "$THEME_DIR" && npm run prod)
        success "Asset build complete."
    else
        warning "package.json not found in ${THEME_DIR}. Skipping NPM steps."
    fi

    info "Installing theme Composer dependencies..."
    if [ -f "${THEME_DIR}/composer.jso" ]; then
        (cd "${THEME_DIR}" && composer install)
        success "Composer dependencies installed"
    else
        warning "composer.json not found in ${THEME_DIR}. Skipping Composer steps."
    fi
}


# --- Main Execution ---
main() {
    local start_time
    start_time=$(date +%s)

    check_dependencies
    setup_ddev
    setup_git_repo
    configure_aws_cli
    create_s3_bucket
    create_iam_user_and_keys
    set_github_secrets
    create_cloudfront_distribution
    create_env_file
    install_dependencies

    local end_time duration
    end_time=$(date +%s)
    duration=$((end_time - start_time))

    success "All tasks completed in ${duration} seconds."
    info "Your new project '$REPO_NAME' is ready!"
}

# Run the main function
main