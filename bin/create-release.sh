#!/bin/bash

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
if ! [[ "$VERSION" =~ ^[0-9]+\.[0-9]+\.[0-9]+[a-z]?$ ]]; then
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
info "Updating version in sitka-insights.php..."
sed -i -E "s/(^\s*\* Version:)\s*[^\r\n]*/\1 $VERSION/" sitka-insights.php
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
ln -sfn . sitka-insights

# Create ZIP archive
info "Creating ZIP archive..."
ZIP_FILE="releases/sitka-insights-$VERSION.zip"
zip -r "$ZIP_FILE" \
    sitka-insights/sitka-insights.php \
    sitka-insights/wp-api.php \
    sitka-insights/src \
    sitka-insights/cli \
    sitka-insights/js \
    sitka-insights/css \
    sitka-insights/vendor \
    sitka-insights/views \
    sitka-insights/LICENSE.txt \
    sitka-insights/README.md

success "Created $ZIP_FILE"

# Create TAR.GZ archive
info "Creating TAR.GZ archive..."
TAR_FILE="releases/sitka-insights-$VERSION.tar.gz"
tar -cvzf "$TAR_FILE" \
    sitka-insights/vendor/autoload.php \
    sitka-insights/sitka-insights.php \
    sitka-insights/wp-api.php \
    sitka-insights/src \
    sitka-insights/cli \
    sitka-insights/js \
    sitka-insights/css \
    sitka-insights/vendor \
    sitka-insights/views \
    sitka-insights/LICENSE.txt \
    sitka-insights/README.md

success "Created $TAR_FILE"

# Remove hackish symlink
rm ./sitka-insights

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
