#!/bin/bash

# GitHub organization
ORG_NAME="plottcreative"

# Get the current directory name as the repository name
REPO_NAME=$(basename "$PWD")

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
sleep 2

if aws s3api head-bucket --bucket "$bucket_name" 2>/dev/null; then
    echo -e "${RED}Bucket ${bucket_name} already exists! Exiting...${NC}"
    exit 1
fi

echo "Making your bucket for ya lad, bear with me"
sleep 2
aws s3api create-bucket --bucket "$bucket_name" --region "$aws_region" --create-bucket-configuration LocationConstraint="$aws_region"
sleep 2
if [ $? -eq 0 ]; then
    echo -e "${GRN}Alright then!${NC} We're good to go with the bucket, that's been created"
else
    echo -e "${RED}Ahh shit!${NC} We couldn't create that bucket, I wonder if the bucket has already been created"
    exit 1
fi

iam_user_name="${REPO_NAME}-s3-user"
sleep 2
echo "Right so your bucket has been made, let's make a user to access the bucket"

aws iam create-user --user-name "$iam_user_name"

if [ $? -eq 0 ]; then
    echo -e "${GRN}Sound mate${NC}, your user has been made"
else
    echo -e "${RED}Oops${NC}, I couldn't create the user, are you sure they're not currently a user?"
    exit 1
fi

policy_arn="arn:aws:iam::aws:policy/AmazonS3FullAccess"
sleep 2
echo "Cool your user has been created, let's give it the right permissions"
aws iam attach-user-policy --user-name "$iam_user_name" --policy-arn "$policy_arn"

if [ $? -eq 0 ]; then
    echo -e "${GRN}We can access the bucket now${NC}, how cool is that??"
else 
    echo -e "${RED}You shall not pass!${NC} The policy hasn't been attached to the user"
    exit 1
fi

sleep 2
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

# Clean up
rm iam_user_creds.json

# Notify completion
GIF_URL="https://media.giphy.com/media/26ufdipQqU2lhNA4g/giphy.gif"
open "$GIF_URL"