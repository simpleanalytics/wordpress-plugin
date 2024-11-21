#!/bin/bash
set -euo pipefail
IFS=$'\n\t'

# Fetch the JSON data from the specified URL
JSON_DATA=$(curl -s https://api.wordpress.org/core/stable-check/1.0/)

# Use jq to parse the JSON data and extract the version marked as "latest"
TESTED_UP_TO=$(echo "$JSON_DATA" | jq -r 'to_entries[] | select(.value == "latest") | .key')

# Read the TESTED_UP_TO value from config.json
CONFIG_TESTED_UP_TO=$(jq -r '.TESTED_UP_TO' ./config.json)

# Fetch the current STABLE_TAG value from config.json
PREVIOUS_STABLE_TAG=$(jq -r '.STABLE_TAG' config.json)

# Get the latest git tag
LATEST_TAG=$(git describe --tags --abbrev=0 || echo "")

# Check if there are commits since the latest tag
if [ -n "$LATEST_TAG" ]; then
    COMMITS_SINCE_TAG=$(git log "$LATEST_TAG"..HEAD --pretty=format:"* %s")
else
    COMMITS_SINCE_TAG=$(git log --pretty=format:"* %s")
fi

# Determine if there are code changes since the last tag
CODE_CHANGED=false
if [[ -n "$COMMITS_SINCE_TAG" ]]; then
    CODE_CHANGED=true
fi

# Determine if the WordPress version has changed
WP_VERSION_CHANGED=false
if [[ "$TESTED_UP_TO" != "$CONFIG_TESTED_UP_TO" ]]; then
    WP_VERSION_CHANGED=true
fi

# Exit early if no code changes and WordPress version hasn't changed
if [[ "$CODE_CHANGED" == false && "$WP_VERSION_CHANGED" == false ]]; then
    echo "### :no_good_woman: Didn't update versions :no_good:" >> $GITHUB_STEP_SUMMARY
    echo "" >> $GITHUB_STEP_SUMMARY
    echo "Stopped because there are no changes since the last release." >> $GITHUB_STEP_SUMMARY
    echo "No changes detected. Exiting..."
    exit 0
fi

# Increment the STABLE_TAG value
STABLE_TAG=$(echo "$PREVIOUS_STABLE_TAG" | awk -F. '{$NF+=1} 1' OFS='.')

echo "### :rocket: Updated versions :rocket:" >> $GITHUB_STEP_SUMMARY
echo "" >> $GITHUB_STEP_SUMMARY
echo "- New stable tag: $STABLE_TAG (was $PREVIOUS_STABLE_TAG)" >> $GITHUB_STEP_SUMMARY
echo "- New WordPress version: $TESTED_UP_TO (was $CONFIG_TESTED_UP_TO)" >> $GITHUB_STEP_SUMMARY

# Use sed to replace the version lines in some files
sed -i -e "s/^Tested up to: [0-9.]*$/Tested up to: $TESTED_UP_TO/" \
       -e "s/^Stable tag: [0-9.]*$/Stable tag: $STABLE_TAG/" ./readme.txt

sed -i -e "s/^Tested up to: [0-9.]*$/Tested up to: $TESTED_UP_TO/" \
       -e "s/^Version: [0-9.]*$/Version: $STABLE_TAG/" ./simple-analytics.php

# Get the current date in the specified format
DATE=$(date +"%Y-%m-%d")

# Prepare the changelog entry
CHANGELOG_ENTRY=$(printf "= %s =\n* %s" "$STABLE_TAG" "$DATE")

# Add WordPress version update to changelog if it has changed
if [[ "$WP_VERSION_CHANGED" == true ]]; then
    CHANGELOG_ENTRY=$(printf "%s\n* Tested up to WordPress %s" "$CHANGELOG_ENTRY" "$TESTED_UP_TO")
fi

# Add commit messages to changelog if there are code changes
if [[ "$CODE_CHANGED" == true ]]; then
    CHANGELOG_ENTRY=$(printf "%s\n* Changes:" "$CHANGELOG_ENTRY")
    while IFS= read -r line; do
        CHANGELOG_ENTRY=$(printf "%s\n%s" "$CHANGELOG_ENTRY" "$line")
    done < <(echo "$COMMITS_SINCE_TAG")
fi

# Insert the new changelog entry below the line "== Changelog =="
awk '
/== Changelog ==/ {
    print $0
    print ""
    printf "%s\n", ENVIRON["CHANGELOG_ENTRY"]
    next
}
{ print }' readme.txt > readme.txt.tmp && mv readme.txt.tmp readme.txt

# Update the config.json file
echo "{
  \"TESTED_UP_TO\": \"$TESTED_UP_TO\",
  \"STABLE_TAG\": \"$STABLE_TAG\"
}" > config.json

# Prepare release name and body
if [[ "$WP_VERSION_CHANGED" == true && "$CODE_CHANGED" == true ]]; then
    RELEASE_NAME="Release $STABLE_TAG: Code updates and support for WordPress $TESTED_UP_TO"
elif [[ "$WP_VERSION_CHANGED" == true ]]; then
    RELEASE_NAME="Release $STABLE_TAG: Support for WordPress $TESTED_UP_TO"
elif [[ "$CODE_CHANGED" == true ]]; then
    RELEASE_NAME="Release $STABLE_TAG: Code updates"
else
    RELEASE_NAME="Release $STABLE_TAG"
fi

RELEASE_BODY="$CHANGELOG_ENTRY"

# Output to GitHub Actions
echo "tested-up-to=$TESTED_UP_TO" >> $GITHUB_OUTPUT
echo "stable-tag=$STABLE_TAG" >> $GITHUB_OUTPUT
echo "has-changed=true" >> $GITHUB_OUTPUT
echo "code-changed=$CODE_CHANGED" >> $GITHUB_OUTPUT
echo "wp-version-changed=$WP_VERSION_CHANGED" >> $GITHUB_OUTPUT
echo "release-name=$RELEASE_NAME" >> $GITHUB_OUTPUT
echo "release-body=$RELEASE_BODY" >> $GITHUB_OUTPUT
