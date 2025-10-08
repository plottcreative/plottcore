#!/bin/bash
start_time=$(date +%s)
# GitHub organization
ORG_NAME="plottcreative"
# Get the current directory name as the repository name
REPO_NAME=$(basename "$PWD")

# Sort out ddev
ddev config --project-name "$REPO_NAME" --project-type=php --docroot=web \
  && ddev add-on get ddev/ddev-adminer \
  && ddev start

# Check if Git exists
if [ -d .git ]; then
    echo "Git repo is already setup in this directory"
    exit 1
fi

# Clone PLOTT OS and remove the .git folder
git clone https://github.com/plottcreative/plott-os web/app/themes/plott-os

if [ $? -eq 0 ]; then
    echo "plott-os repo cloned successfully into web/app/themes."
else
    echo "Failed to clone the PLOTT OS"
    exit 1
fi

sleep 2

echo "Removing .git for plott-os"

cd web/app/themes/plott-os
rm -rf .git

cd ~/Projects/$REPO_NAME

# Create a new private GitHub repo under the PLOTT org using the GitHub CLI
gh repo create $ORG_NAME/$REPO_NAME --private --confirm

# Check if the repo was succesfully created
if [ $? -ne 0 ]; then
    echo "Failed to create GitHub Repo"
    exit 1;
fi

# Init Git Repo
git init

# Add all files and commit
git add .
git commit -m "Initial commit"

# Create main branch and push
git branch -M main
git remote add origin https://github.com/$ORG_NAME/$REPO_NAME.git
git push -u origin main

# Create stage branch and push
git checkout -b stage
git push -u origin stage

# Create develop branch and push
git checkout -b develop
git push -u origin develop

# AWS Config
aws_region="eu-west-1"
bucket_name="${REPO_NAME}-s3-bucket"

if ! aws configure list-profiles | grep -q default; then
    echo "Ahh Snap you've not got AWS configured"
    echo "Enter your AWS Key ID:"
    read aws_key_id
    echo "Enter your AWS Key Secret:"
    read -s aws_key_secret

    aws configure set aws_access_key_id $aws_key_id
    aws configure set aws_secret_access_key $aws_key_secret
    aws configure set default.region $aws_region
else
    echo "AWS already configured"
    echo "GOOD BOY"
    sleep 2
fi

echo "Let's see if this bucket already exists"
sleep 1

if aws s3api head-bucket --bucket "$bucket_name" 2>/dev/null; then
    echo -e "Bucket already in existance! Exiting..."
    exit 1
    sleep 1
fi

echo "Making your bucket now"
sleep 1

aws s3api create-bucket --bucket "$bucket_name" --region "$aws_region" --create-bucket-configuration LocationConstraint="$aws_region"
sleep 1

if [ $? -eq 0 ]; then
    echo -e "Bucket has been created"
else
    echo -e "Ahh, the bucket can't be created, this will have to be done manually"
fi

iam_user_name="${REPO_NAME}-s3-user"
sleep 1
echo "Creating Bucket Access"

aws iam create-user --user-name "$iam_user_name"


if [ $? -eq 0 ]; then 
    echo -e "AWS IAM User created"
else 
    echo -e "User creation failed, this will have to be done manually"
    exit 1
fi

policy_arn="arn:aws:iam::aws:policy/AmazonS3FullAccess"
sleep 1

echo "Assigning the correct permissions"

aws iam attach-user-policy --user-name "$iam_user_name" --policy-arn "$policy_arn"

if [ $? -eq 0 ]; then
    echo -e "Policy attached to the the bucket"
else 
    echo -e "Policy not attached, this will have to be done manually"
    exit 1
fi

sleep 1
echo "Generating Access Keys"

aws iam create-access-key --user-name "$iam_user_name" > iam_user_creds.json

if [ $? -eq 0 ]; then
    echo "Access keys created and save to iam_user_creds.json"
else
    echo "Access keys failed to generate, this will have to be done manually"
    exit 1
fi

aws_access_key_id=$(jq -r '.AccessKey.AccessKeyId' iam_user_creds.json)
aws_secret_access_key=$(jq -r '.AccessKey.SecretAccessKey' iam_user_creds.json)

sleep 1
echo "Adding AWS Keys to GitHub"

gh secret set AWS_ACCESS_KEY_ID --body "$aws_access_key_id" --repo "$ORG_NAME/$REPO_NAME"
if [ $? -eq 0 ]; then
    echo "GitHub secret AWS_ACCESS_KEY_ID set successfully"
else
    echp "Failed to add AWS_ACCESS_KEY_ID secret into Git Hub"
    exit 1
fi

gh secret set AWS_SECRET_ACCESS_KEY --body "$aws_secret_access_key" --repo "$ORG_NAME/$REPO_NAME"
if [ $? -eq 0 ]; then
    echo "GitHub secret AWS_SECRET_ACCESS_KEY set successfully"
else
    echp "Failed to add AWS_SECRET_ACCESS_KEY secret into Git Hub"
    exit 1
fi

# Create default secrets
gh secret set DB_NAME_PROD --body "a" --repo "$ORG_NAME/$REPO_NAME"
if [ $? -eq 0 ]; then   
    echo -e "DB_NAME_PROD secret added successfully"
else
    echo -e "DB_NAME_PROD secret added unsuccessfully"
    exit 1
fi
gh secret set DB_USER_PROD --body "a" --repo "$ORG_NAME/$REPO_NAME"
if [ $? -eq 0 ]; then   
    echo -e "DB_USER_PROD secret added successfully"
else
    echo -e "DB_USER_PROD secret added unsuccessfully"
    exit 1
fi
gh secret set DB_PASS_PROD --body "a" --repo "$ORG_NAME/$REPO_NAME"
if [ $? -eq 0 ]; then   
    echo -e "DB_PASS_PROD secret added successfully"
else
    echo -e "DB_PASS_PROD secret added unsuccessfully"
    exit 1
fi
gh secret set DB_NAME_STAGE --body "a" --repo "$ORG_NAME/$REPO_NAME"
if [ $? -eq 0 ]; then   
    echo -e "DB_NAME_STAGE secret added successfully"
else
    echo -e "DB_NAME_STAGE secret added unsuccessfully"
    exit 1
fi

sleep 1

# Create a CloudFront distribution
distribution_config=$(cat <<EOF
{
"CallerReference": "${REPO_NAME}-$(date +%s)",
  "Comment": "${REPO_NAME} distribution",
  "Enabled": true,
  "Origins": {
    "Quantity": 1,
    "Items": [
      {
        "Id": "${REPO_NAME}}",
        "DomainName": "${REPO_NAME}.s3.${aws_region}.amazonaws.com",
        "S3OriginConfig": { "OriginAccessIdentity": "" }
      }
    ]
  },
  "DefaultCacheBehavior": {
    "TargetOriginId": "${REPO_NAME}",
    "ViewerProtocolPolicy": "redirect-to-https",
    "AllowedMethods": {
      "Quantity": 2,
      "Items": ["GET","HEAD"],
      "CachedMethods": { "Quantity": 2, "Items": ["GET","HEAD"] }
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

distribution_id=$(aws cloudfront create-distribution --distribution-config "$distribution_config" | jq -r '.Distributuin.Id')

if [ -n "$distribution_id" ]; then
    echo "CloudFront Distribution $distribution_id has been created successfully"
else 
    echo "Failed to create a CloudFront distribution"
    exit 1
fi

sleep 1

echo "Setting up the .env"

ENV_FILE=".env"

if [ -f "$ENV_FILE" ]; then
    cp "$ENV_FILE" "${ENV_FILE}.bak"
    echo "Existing .env file backed up as .env.bak"
fi

cat > "$ENV_FILE" <<EOL
# --- Project Environment Variables ---
APP_NAME=$REPO_NAME
DB_NAME=$DB_NAME
AWS_ACCESS_KEY_ID=$aws_access_key_id
AWS_SECRET_ACCESS_KEY=$aws_secret_access_key
WP_ENV=development
WP_HOME=http://${REPO_NAME}.ddev.site
WP_SITEURL=http://${REPO_NAME}.ddev.site/wp
EOL

echo ".env file has been created/updated successfully."

sleep 1

composer update --quiet
composer install --quiet

if [ $? -eq 0 ]; then
    echo "Composer updated and dependecies installed"
else 
    echo "Failed to update/install dependecies"
fi

sleep 1

echo "Setting up the theme"

cd "web/app/themes/plott-os"
sleep 1

echo "Checking if there is a package.json"

if [ ! -f package.json ]; then
    echo "package.json is missing"
    exit 1;
else 
    echo "package.json found"
    echo "installing NPM packages"
    npm i
    if [ $? -eq 0 ]; then
        echo "NPM packages successfully installed"
        echo "Running a npm run prod to check"
        npm run prod
    else
        echo "Failed to install/update npm"
        exit 1
    fi
fi

sleep 1

echo "Checking if composer.json is there"

if [ ! -f composer.json ]; then
    echo "composer.json is missing"
else
    echo "composer.json found"
    echo "installing composer packages"
    composer update
    composer install
    if[ $? -eq 0 ]; then
        echo "Composer installed completely"
    else
        echo "Failed to installed composer dependieces"
    fi
fi

end_time=$(date +%s)

duration=$((end_time-start_time))

echo "Setup completed time: ${duration}s"

