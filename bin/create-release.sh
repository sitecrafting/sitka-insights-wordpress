#!/usr/bin/env bash

# Exit on error
set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored messages
error() {
    echo -e "${RED}ERROR: $1${NC}" >&2
    exit 1
}

success() {
    echo -e "${GREEN}✓ $1${NC}"
}

info() {
    echo -e "${YELLOW}→ $1${NC}"
}

# Check if required commands exist
command -v git >/dev/null 2>&1 || error "git is required but not installed"
command -v gh >/dev/null 2>&1 || error "GitHub CLI (gh) is required but not installed.  Install from https://cli.github.com/"
command -v jq >/dev/null 2>&1 || error "jq is required but not installed"
command -v zip >/dev/null 2>&1 || error "zip is required but not installed"
command -v tar >/dev/null 2>&1 || error "tar is required but not installed"
command -v composer >/dev/null 2>&1 || error "composer is required but not installed"

# Check if we're in a git repository
if !  git rev-parse --git-dir > /dev/null 2>&1; then
    error "Not in a git repository"
fi

# Check if working directory is clean
if ! git diff-index --quiet HEAD --; then
    error "Working directory is not clean.  Please commit or stash your changes first."
fi

# Get repository info
REPO_ROOT=$(git rev-parse --show-toplevel)
cd "$REPO_ROOT"

# Check if required files exist
[[ -f "sitka-insights.php" ]] || error "sitka-insights.php not found"
[[ -f "composer.json" ]] || error "composer. json not found"

# Request new version number
echo ""
info "Current version information:"
grep -E "Version:|\"version\":" sitka-insights.php composer.json 2>/dev/null || true
echo ""

read -p "Enter new version number (e.g., 1.2.3): " VERSION

# Validate version format (basic semver check)
SEMVER_REGEX='^(0|[1-9][0-9]*)\.(0|[1-9][0-9]*)\.(0|[1-9][0-9]*)(-((0|[1-9][0-9]*|[0-9]*[a-zA-Z-][0-9a-zA-Z-]*)(\.(0|[1-9][0-9]*|[0-9]*[a-zA-Z-][0-9a-zA-Z-]*))*))?(\+([0-9a-zA-Z-]+(\.[0-9a-zA-Z-]+)*))?$'

if ! [[ "$VERSION" =~ $SEMVER_REGEX ]]; then
    error "Invalid version format. Please use semantic versioning (e.g., 1.2.3)"
fi

info "Preparing release for version $VERSION"

# Confirm with user
read -p "Are you sure you want to create release $VERSION? (y/N): " -n 1 -r
echo
if [[ !  $REPLY =~ ^[Yy]$ ]]; then
    error "Release cancelled by user"
fi

# Update version in sitka-insights.php
# Must work with BSD/macOS sed, which takes the -i backup suffix as a SEPARATE
# argument (so "sed -i -E" eats -E as the suffix and dies on \1) and has no \s
# shorthand (so \s silently matches nothing). Writing to a temp file avoids -i
# altogether and mirrors the composer.json step below; POSIX classes work in both
# BSD and GNU sed.
info "Updating version in sitka-insights.php..."
sed -E "s/(^[[:space:]]*\*[[:space:]]*Version:)[[:space:]]*.*/\1 $VERSION/" \
    sitka-insights.php > sitka-insights.php.tmp && mv sitka-insights.php.tmp sitka-insights.php

# Confirm the header actually changed. A silently non-matching regex would leave
# the old version in place and the script would go on to commit, tag and publish
# a release whose plugin header disagrees with its own tag.
grep -qE "^[[:space:]]*\*[[:space:]]*Version:[[:space:]]*$VERSION[[:space:]]*$" sitka-insights.php \
    || error "Version header in sitka-insights.php was not updated - check the plugin header format"
success "Updated sitka-insights.php"

# Update version and dist.url in composer.json
info "Updating composer.json..."
DIST_URL="https://github.com/sitecrafting/sitka-insights-wordpress/releases/download/v$VERSION/sitka-insights-$VERSION.zip"

# Use jq to update composer.json
jq --arg version "$VERSION" \
   --arg url "$DIST_URL" \
   '.version = $version | .dist.url = $url' \
   composer.json > composer.json.tmp && mv composer.json.tmp composer.json

success "Updated composer.json"

# Commit changes
info "Committing changes..."
git add sitka-insights.php composer.json
git commit -m "Bump version to $VERSION"
success "Changes committed"

# Create and push tag
info "Creating git tag v$VERSION..."
git tag -a "v$VERSION" -m "Release version $VERSION"
success "Tag created"

info "Pushing changes and tag to remote..."
git push origin HEAD
git push origin "v$VERSION"
success "Pushed to remote"

# Create releases directory if it doesn't exist
mkdir -p releases

# The files that make up the distributable plugin, relative to the repo root.
# One list, used for both archives: they previously differed only by a redundant
# explicit vendor/autoload.php, which vendor/ already covers.
PAYLOAD=(
    sitka-insights.php
    wp-api.php
    src
    cli
    js
    css
    vendor
    views
    LICENSE.txt
    README.md
)

# Both archives must unpack to a single sitka-insights/ directory, since that is
# the folder name WordPress expects the plugin to install under. We get that
# prefix by staging a copy in a temp dir rather than by creating anything inside
# the repo. The previous approach symlinked ./sitka-insights -> . and deleted it
# only on the success path, so any failure left the link behind - and once that
# leftover became a real directory, "ln -sfn . sitka-insights" failed outright
# ("ln: sitka-insights/.: Operation not permitted") and broke every later run.
STAGE=$(mktemp -d)
trap 'rm -rf "$STAGE"' EXIT

info "Staging plugin files..."
mkdir -p "$STAGE/sitka-insights"
cp -R "${PAYLOAD[@]}" "$STAGE/sitka-insights/"

# Create ZIP archive
info "Creating ZIP archive..."
ZIP_FILE="releases/sitka-insights-$VERSION.zip"
rm -f "$ZIP_FILE"  # zip adds to an existing archive rather than replacing it
( cd "$STAGE" && zip -r "$REPO_ROOT/$ZIP_FILE" sitka-insights )
success "Created $ZIP_FILE"

# Create TAR.GZ archive
info "Creating TAR.GZ archive..."
TAR_FILE="releases/sitka-insights-$VERSION.tar.gz"
tar -cvzf "$REPO_ROOT/$TAR_FILE" -C "$STAGE" sitka-insights
success "Created $TAR_FILE"

# Ask if this is a pre-release
read -p "Is this a pre-release? (y/N): " -n 1 -r
echo
PRERELEASE_FLAG=""
if [[ $REPLY =~ ^[Yy]$ ]]; then
    PRERELEASE_FLAG="--prerelease"
    info "This will be marked as a pre-release"
fi


# Create GitHub release
info "Creating GitHub release..."
gh release create "v$VERSION" \
    --title "Version v$VERSION" \
    --generate-notes \
    $PRERELEASE_FLAG \
    "$REPO_ROOT/$ZIP_FILE" \
    "$REPO_ROOT/$TAR_FILE"

success "GitHub release created successfully!"

echo ""
echo -e "${GREEN}════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}Release $VERSION completed successfully!${NC}"
echo -e "${GREEN}════════════════════════════════════════════════════${NC}"
echo ""
info "Archives created:"
echo "  - $ZIP_FILE"
echo "  - $TAR_FILE"
echo ""
info "Next steps:"
echo "  - Review the release at:  https://github.com/sitecrafting/sitka-insights-wordpress/releases/tag/v$VERSION"
echo "  - Update any documentation if needed"
echo ""
