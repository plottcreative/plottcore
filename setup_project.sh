#!/bin/bash
start_time=$(date +%s)
# GitHub organization
ORG_NAME="plottcreative"

# Get the current directory name as the repository name
REPO_NAME=$(basename "$PWD")

#Get the Valet Env File
VALET_ENV_FILE="../.valet-env.php"

# Check if the current directory is already a git repository
if [ -d .git ]; then
    echo "Git repository is already set up in this directory."
    exit 1
fi

# Create a new private GitHub repository under the specified organization using the GitHub CLI
gh repo create $ORG_NAME/$REPO_NAME --private --confirm

# Check if the repository was created successfully
if [ $? -ne 0 ]; then
    echo "Failed to create GitHub repository"
    exit 1
fi

# Initialize git repository
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

# Switch back to main
git checkout main

# Clone the plott-os repository into web/app/themes
git clone https://github.com/plottcreative/plott-os web/app/themes/plott-os

# Check if the cloning was successful
if [ $? -eq 0 ]; then
    echo "plott-os repository cloned successfully into web/app/themes."
else
    echo "Failed to clone plott-os repository."
    exit 1
fi

# AWS configuration
aws_region="eu-west-1"
bucket_name="${REPO_NAME}-s3-bucket"

# Check if AWS is configured
if ! aws configure list-profiles | grep -q default; then
    echo "Oh no you've not configured your AWS creds"
    echo "Enter your AWS Key ID:"
    read aws_key_id
    echo "Enter your AWS Key Secret:"
    read -s aws_key_secret

    aws configure set aws_access_key_id $aws_key_id
    aws configure set aws_secret_access_key $aws_key_secret
    aws configure set default.region $aws_region
else
    echo "Good lad you've already got AWS setup"
    echo "I'll use your current AWS Config"
fi

echo "Checking if the bucket already exists..."
sleep 1

if aws s3api head-bucket --bucket "$bucket_name" 2>/dev/null; then
    echo -e "${RED}Bucket ${bucket_name} already exists! Exiting...${NC}"
    exit 1
fi

echo "Making your bucket for ya lad, bear with me"
sleep 1
aws s3api create-bucket --bucket "$bucket_name" --region "$aws_region" --create-bucket-configuration LocationConstraint="$aws_region"
sleep 1
if [ $? -eq 0 ]; then
    echo -e "${GRN}Alright then!${NC} We're good to go with the bucket, that's been created"
else
    echo -e "${RED}Ahh shit!${NC} We couldn't create that bucket, I wonder if the bucket has already been created"
    exit 1
fi

iam_user_name="${REPO_NAME}-s3-user"
sleep 1
echo "Right so your bucket has been made, let's make a user to access the bucket"

aws iam create-user --user-name "$iam_user_name"

if [ $? -eq 0 ]; then
    echo -e "${GRN}Sound mate${NC}, your user has been made"
else
    echo -e "${RED}Oops${NC}, I couldn't create the user, are you sure they're not currently a user?"
    exit 1
fi

policy_arn="arn:aws:iam::aws:policy/AmazonS3FullAccess"
sleep 1
echo "Cool your user has been created, let's give it the right permissions"
aws iam attach-user-policy --user-name "$iam_user_name" --policy-arn "$policy_arn"

if [ $? -eq 0 ]; then
    echo -e "${GRN}We can access the bucket now${NC}, how cool is that??"
else 
    echo -e "${RED}You shall not pass!${NC} The policy hasn't been attached to the user"
    exit 1
fi

sleep 1
echo "Let's get some keys to unlock your door"

aws iam create-access-key --user-name "$iam_user_name" > iam_user_creds.json

if [ $? -eq 0 ]; then
    echo "We've got the keys, we've got the secret"
else
    echo "Oops, you've been locked out!"
    exit 1
fi

aws_access_key_id=$(jq -r '.AccessKey.AccessKeyId' iam_user_creds.json)
aws_secret_access_key=$(jq -r '.AccessKey.SecretAccessKey' iam_user_creds.json)

# Upload GitHub secrets
gh secret set AWS_ACCESS_KEY_ID --body "$aws_access_key_id" --repo "$ORG_NAME/$REPO_NAME"
if [ $? -eq 0 ]; then
    echo -e "${GRN}GitHub secret AWS_ACCESS_KEY_ID set successfully.${NC}"
else
    echo -e "${RED}Failed to set GitHub secret AWS_ACCESS_KEY_ID.${NC}"
    exit 1
fi

gh secret set AWS_SECRET_ACCESS_KEY --body "$aws_secret_access_key" --repo "$ORG_NAME/$REPO_NAME"
if [ $? -eq 0 ]; then
    echo -e "${GRN}GitHub secret AWS_SECRET_ACCESS_KEY set successfully.${NC}"
else
    echo -e "${RED}Failed to set GitHub secret AWS_SECRET_ACCESS_KEY.${NC}"
    exit 1
fi

sleep 1

# Create a CloudFront distribution
distribution_config=$(cat <<EOF
{
    "CallerReference": "$theme_name-$(date +%s)",
    "Comment": "$theme_name distribution",
    "Enabled": true,
    "Origins": {
        "Items": [
            {
                "Id": "$bucket_name",
                "DomainName": "$bucket_name.s3.amazonaws.com",
                "S3OriginConfig": {
                    "OriginAccessIdentity": ""
                }
            }
        ],
        "Quantity": 1
    },
    "DefaultCacheBehavior": {
        "TargetOriginId": "$bucket_name",
        "ViewerProtocolPolicy": "redirect-to-https",
        "AllowedMethods": {
            "Items": ["GET", "HEAD"],
            "Quantity": 2,
            "CachedMethods": {
                "Items": ["GET", "HEAD"],
                "Quantity": 2
            }
        },
        "Compress": true,
        "ForwardedValues": {
            "QueryString": false,
            "Cookies": {
                "Forward": "none"
            }
        },
        "MinTTL": 0,
        "DefaultTTL": 86400,
        "MaxTTL": 31536000
    },
    "ViewerCertificate": {
        "CloudFrontDefaultCertificate": true,
        "MinimumProtocolVersion": "TLSv1.2_2021"
    },
    "HttpVersion": "http2",
    "IsIPV6Enabled": true
}
EOF
)

distribution_id=$(aws cloudfront create-distribution --distribution-config "$distribution_config" | jq -r '.Distribution.Id')

if [ -n "$distribution_id" ]; then
    echo "CloudFront distribution $distribution_id has been created successfully."
else
    echo "Failed to create CloudFront distribution."
    exit 1
fi

sleep 1

echo "I'm just setting up your valet enviroment, hold on for sec"

if [ -f "$VALET_ENV_FILE" ]; then

    cp "$VALET_ENV_FILE" "${VALET_ENV_FILE}.bak"

    new_entry=" '$REPO_NAME' => [\n 'DB_NAME' => '$REPO_NAME', \n 'AWS_ACCESS_KEY_ID' =>  '$aws_access_key_id', \n 'AWS_SECRET_ACCESS_KEY' => '$aws_secret_access_key', \n 'WP_ENV' => 'development', \n 'WP_HOME'=>'http://${REPO_NAME}.test', \n 'WP_SITEURL' =>'http://${REPO_NAME}.test/wp'\n],"

    awk -v new_entry="$new_entry" '
    /#SITE SPECIFIC/ {print; print new_entry; next}
    {print}
    ' "$VALET_ENV_FILE" > "${VALET_ENV_FILE}.tmp" && mv "${VALET_ENV_FILE}.tmp" "$VALET_ENV_FILE"

    echo "Project config has been added to the root valet-env file"
else
    echo "${RED}Project not added to the valet-env file"
    exit 1
fi

sleep 1

echo "Making your DB, need somewhere to store your porn"

sleep 1

mysql -u root -e "CREATE DATABASE `$REPO_NAME`;"
if [ $? -eq 0 ]; then
    echo "MySQL database $REPO_NAME has been created successfully."
else
    echo "Failed to create MySQL database $REPO_NAME."
    exit 1
fi

echo "Just checking your project is fully up-to-date"

if [ ! -f composer.json ]; then
    echo "Error: composer.json not found"
    exit 1
fi

composer update --quiet
composer install --quiet

if [ $? -eq 0 ]; then
    echo "Composer has been successfully updated and dependices installed"
else
    echo "Failed to update/install Composer dependecies."
    exit 1;
fi

sleep 1

echo "I'm going into the plott-os folder now to set it up"

cd "web/app/themes/plott-os"

sleep 1

echo "I'll now run npm scripts to install the project theme dependices"
echo "Fist let's make sure you've got a package.json in there"

if [ ! -f package.json ]; then
    echo "Well, well, well, you're missing package.json"
else 
    echo "WooHoo!, I've found your package.json file"
    echo "Running NPM Install now"
    npm install
    if [ $? -eq 0 ]; then
        echo "NPM Packages have been fully installed"
        echo "I'll now run npm run production to finish it off"
        echo "Thats what she said!"
        npm run production
    else   
        echo "Failed to install/update npm"
        exit 1;
    fi
fi

end_time=$(date +%s)

# Calculate duration
duration=$((end_time - start_time))

# Log duration
echo "Script execution time: ${duration} seconds"