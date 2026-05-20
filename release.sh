#!/bin/bash
set -euo pipefail
IFS=$'\n\t'

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
METADATA=$(python3 "$SCRIPT_DIR/scripts/get-wordpress-matrix.py" --metadata)

TESTED_UP_TO=$(echo "$METADATA" | jq -r '.tested_up_to')
REQUIRES_AT_LEAST=$(echo "$METADATA" | jq -r '.requires_at_least')
REQUIRES_PHP=$(echo "$METADATA" | jq -r '.requires_php')

CONFIG_TESTED_UP_TO=$(jq -r '.TESTED_UP_TO' ./config.json)
CONFIG_REQUIRES_AT_LEAST=$(jq -r '.REQUIRES_AT_LEAST // empty' ./config.json)
CONFIG_REQUIRES_PHP=$(jq -r '.REQUIRES_PHP // empty' ./config.json)

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

# Determine if tested compatibility bounds changed (same source as CI matrix)
COMPAT_CHANGED=false
if [[ "$TESTED_UP_TO" != "$CONFIG_TESTED_UP_TO" ]] \
    || [[ "$REQUIRES_AT_LEAST" != "$CONFIG_REQUIRES_AT_LEAST" ]] \
    || [[ "$REQUIRES_PHP" != "$CONFIG_REQUIRES_PHP" ]]; then
    COMPAT_CHANGED=true
fi

# Exit early if no code changes and compatibility metadata hasn't changed
if [[ "$CODE_CHANGED" == false && "$COMPAT_CHANGED" == false ]]; then
    echo "### :no_good_woman: Didn't update versions :no_good:" >> "$GITHUB_STEP_SUMMARY"
    echo "" >> "$GITHUB_STEP_SUMMARY"
    echo "Stopped because there are no changes since the last release." >> "$GITHUB_STEP_SUMMARY"
    echo "No changes detected. Exiting..."
    exit 0
fi

# Increment the STABLE_TAG value
STABLE_TAG=$(echo "$PREVIOUS_STABLE_TAG" | awk -F. '{$NF+=1} 1' OFS='.')

echo "### :rocket: Updated versions :rocket:" >> "$GITHUB_STEP_SUMMARY"
echo "" >> "$GITHUB_STEP_SUMMARY"
echo "- New stable tag: $STABLE_TAG (was $PREVIOUS_STABLE_TAG)" >> "$GITHUB_STEP_SUMMARY"
echo "- Tested up to: $TESTED_UP_TO (was $CONFIG_TESTED_UP_TO)" >> "$GITHUB_STEP_SUMMARY"
echo "- Requires at least: $REQUIRES_AT_LEAST (was $CONFIG_REQUIRES_AT_LEAST)" >> "$GITHUB_STEP_SUMMARY"
echo "- Requires PHP: $REQUIRES_PHP (was $CONFIG_REQUIRES_PHP)" >> "$GITHUB_STEP_SUMMARY"

# Use sed to replace the version lines in some files
sed -i -e "s/^Tested up to: [0-9.]*$/Tested up to: $TESTED_UP_TO/" \
       -e "s/^Requires at least: [0-9.]*$/Requires at least: $REQUIRES_AT_LEAST/" \
       -e "s/^Requires PHP: [0-9.]*$/Requires PHP: $REQUIRES_PHP/" \
       -e "s/^Stable tag: [0-9.]*$/Stable tag: $STABLE_TAG/" ./readme.txt

if ! grep -q 'Requires PHP:' ./simple-analytics.php; then
    sed -i "/\* Requires at least:/a\\ * Requires PHP: $REQUIRES_PHP" ./simple-analytics.php
fi

sed -i -e "s/^ \* Tested up to: [0-9.]*/ * Tested up to: $TESTED_UP_TO/" \
       -e "s/^ \* Requires at least: [0-9.]*/ * Requires at least: $REQUIRES_AT_LEAST/" \
       -e "s/^ \* Requires PHP: [0-9.]*/ * Requires PHP: $REQUIRES_PHP/" \
       -e "s/^ \* Version: [0-9.]*/ * Version: $STABLE_TAG/" ./simple-analytics.php

# Get the current date in the specified format
DATE=$(date +"%Y-%m-%d")

# Prepare the changelog entry
CHANGELOG_ENTRY=$(printf "= %s =\n* %s" "$STABLE_TAG" "$DATE")

# Add compatibility update to changelog if tested bounds changed
if [[ "$COMPAT_CHANGED" == true ]]; then
    CHANGELOG_ENTRY=$(printf "%s\n* Tested up to WordPress %s" "$CHANGELOG_ENTRY" "$TESTED_UP_TO")
    CHANGELOG_ENTRY=$(printf "%s\n* Requires WordPress %s" "$CHANGELOG_ENTRY" "$REQUIRES_AT_LEAST")
    CHANGELOG_ENTRY=$(printf "%s\n* Requires PHP %s" "$CHANGELOG_ENTRY" "$REQUIRES_PHP")
fi

# Add commit messages to changelog if there are code changes
if [[ "$CODE_CHANGED" == true ]]; then
    CHANGELOG_ENTRY=$(printf "%s\n* Changes:" "$CHANGELOG_ENTRY")
    CHANGELOG_ENTRY=$(printf "%s\n%s" "$CHANGELOG_ENTRY" "$COMMITS_SINCE_TAG")
fi

# Insert the new changelog entry below the line "== Changelog =="
awk -v changelog="$CHANGELOG_ENTRY" '
    /== Changelog ==/ {
        print $0 "\n"
        print changelog "\n"
        next
    }
    { print }' readme.txt > readme.txt.tmp && mv readme.txt.tmp readme.txt

# Update the config.json file
echo "{
  \"TESTED_UP_TO\": \"$TESTED_UP_TO\",
  \"REQUIRES_AT_LEAST\": \"$REQUIRES_AT_LEAST\",
  \"REQUIRES_PHP\": \"$REQUIRES_PHP\",
  \"STABLE_TAG\": \"$STABLE_TAG\"
}" > config.json

# Prepare release name and body
if [[ "$COMPAT_CHANGED" == true && "$CODE_CHANGED" == true ]]; then
    RELEASE_NAME="Release $STABLE_TAG: Code updates and tested on WordPress $REQUIRES_AT_LEAST–$TESTED_UP_TO"
elif [[ "$COMPAT_CHANGED" == true ]]; then
    RELEASE_NAME="Release $STABLE_TAG: Tested on WordPress $REQUIRES_AT_LEAST–$TESTED_UP_TO"
elif [[ "$CODE_CHANGED" == true ]]; then
    RELEASE_NAME="Release $STABLE_TAG: Code updates"
else
    RELEASE_NAME="Release $STABLE_TAG"
fi

RELEASE_BODY="$CHANGELOG_ENTRY"

# Output to GitHub Actions using the multiline syntax
echo "tested-up-to=$TESTED_UP_TO" >> "$GITHUB_OUTPUT"
echo "stable-tag=$STABLE_TAG" >> "$GITHUB_OUTPUT"
echo "has-changed=true" >> "$GITHUB_OUTPUT"
echo "code-changed=$CODE_CHANGED" >> "$GITHUB_OUTPUT"
echo "compat-changed=$COMPAT_CHANGED" >> "$GITHUB_OUTPUT"

# Output RELEASE_NAME (in case it contains special characters)
echo "release-name=$RELEASE_NAME" >> "$GITHUB_OUTPUT"

# Output RELEASE_BODY using the multiline syntax
echo "release-body<<EOF" >> "$GITHUB_OUTPUT"
echo "$RELEASE_BODY" >> "$GITHUB_OUTPUT"
echo "EOF" >> "$GITHUB_OUTPUT"
