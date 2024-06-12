#!/bin/bash

project_name=$(basename "$PWD")
aws_region="eu-west-1"

RED='\033[0;31m'
GRN='\033[0;32m'
NC='\033[0m'

if ! aws configure list-profiles | grep -q default; then
    echo "Oh no you've not configured your AWS creds"
    echo "Enter your AWS Key ID:"
    read aws_key_id
    echo "Enter your AWS Key Secret"
    read aws_key_secret

    aws configure set aws_access_key_id $aws_key_id
    aws configure set aws_secret_access_key $aws_key_secret
    aws configure set default.region $aws_region
else
    echo "Good lad you've already got AWS setup"
    echo "I'll use your current AWS Config"
fi

bucket_name="${project_name}-s3-bucket"
echo "Making your bucket for ya lad, bear with me"
sleep 2
aws s3api create-bucket --bucket "$bucket_name" --region "$aws_region" --create-bucket-configuration LocationConstraint="$aws_region"
sleep 2
if [ $? -eq 0 ]; then
    echo "${GRN}Alright then!${NC} We're good to go with the bucket, that's been created"
else
    echo "${RED}Ahh shit!${NC} We couldn't create that bucket, I wonder if the bucket has already been created"
    exit 1
fi

iam_user_name="${project_name}-s3-user"
sleep 2
echo "Right so you're bucket has been made, let's make a user to access the bucket"

aws iam create-user --user-name "$iam_user_name"

if [ $? -eq 0 ]; then
    echo "${GRN}Sound mate${NC}, your user has been made"
else
    echo "${RED}Oops${NC}, I couldn't create the user, are you sure they're not currently as user?"
    exit 1
fi

policy_arn="arn:aws:iam::aws:policy/AmazonS3FullAccess"
sleep 2
echo "Cool you're user has been create, let's give it the right permissions"
aws iam attach-user-policy --user-name "$iam_user_name" --policy-arn "$policy_arn"

if [ $? -eq 0 ]; then
    echo "${GRN}We can access the bucket now${NC}, how cool is that??"
else 
    echo "${RED}You shall not pass!${NC} The policy hasn't been attached to the user"
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

echo "Here is your Access Key ID: ${aws_access_key_id}"
echo "Here is your Secret Access Key: ${aws_secret_access_key}"

rm iam_user_creds.json

sleep 2
echo "Now let's make the distribution and take over the world!"

distribution_config=$(cat <<EOF
{
    "CallerReference": "$bucket_name-$(date +%s)",
    "Comment": "$bucket_name distribution",
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
    echo "${GRN}We have liftoff!${NC} Your distribution has been created."
else
    echo "${RED}Failed${NC} to create CloudFront distribution."
    exit 1
fi

GIF_URL="https://media.giphy.com/media/26ufdipQqU2lhNA4g/giphy.gif"
open "$GIF_URL"