#!/bin/bash

# GitHub organization
ORG_NAME="plottcreative"

# Get the current directory name as the repository name
REPO_NAME=$(basename "$PWD")

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