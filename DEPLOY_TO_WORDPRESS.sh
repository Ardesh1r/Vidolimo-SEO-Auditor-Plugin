#!/bin/bash

# Vidolimo SEO Auditor - WordPress.org Deployment Script
# This script deploys your plugin to WordPress.org using SVN

set -e

# Configuration
SVN_URL="https://plugins.svn.wordpress.org/vidolimo-seo-auditor"
SVN_USER="ardesh1r"
SVN_REPO_DIR="/tmp/vidolimo-seo-auditor-svn-deploy"
PLUGIN_SOURCE_DIR="/Users/Ardi/Documents/OpenSource/vidolimo-seo-auditor"
VERSION="1.0.2"

echo "=========================================="
echo "Vidolimo SEO Auditor - WordPress Deployment"
echo "=========================================="
echo ""

# Check if SVN is installed
if ! command -v svn &> /dev/null; then
    echo "❌ ERROR: SVN is not installed!"
    echo ""
    echo "To install SVN on macOS, you can:"
    echo "1. Install Homebrew: /bin/bash -c \"\$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)\""
    echo "2. Then install SVN: brew install subversion"
    echo ""
    echo "Or download from: https://subversion.apache.org/packages.html"
    exit 1
fi

echo "✓ SVN is installed: $(svn --version | head -1)"
echo ""

# Step 1: Check out the repository
echo "Step 1: Checking out WordPress.org SVN repository..."
if [ -d "$SVN_REPO_DIR" ]; then
    echo "  Repository already exists, updating..."
    cd "$SVN_REPO_DIR"
    svn update
else
    echo "  Checking out fresh copy..."
    svn checkout "$SVN_URL" "$SVN_REPO_DIR"
fi

echo "✓ Repository ready at: $SVN_REPO_DIR"
echo ""

# Step 2: Copy plugin files to trunk
echo "Step 2: Copying plugin files to trunk..."
cd "$SVN_REPO_DIR"

# Remove old trunk content (except .svn)
find trunk -mindepth 1 -not -path "trunk/.svn*" -delete

# Copy new files
cp -r "$PLUGIN_SOURCE_DIR"/* trunk/

# Remove unnecessary files
rm -f trunk/.git trunk/.gitignore trunk/DEPLOY_TO_WORDPRESS.sh trunk/README.md

echo "✓ Plugin files copied to trunk"
echo ""

# Step 3: Add new files to SVN
echo "Step 3: Adding files to SVN..."
svn add trunk/* --force 2>/dev/null || true

echo "✓ Files staged for commit"
echo ""

# Step 4: Commit to trunk
echo "Step 4: Committing to WordPress.org..."
echo "  (You will be prompted for your SVN password)"
echo ""

svn commit trunk -m "Version $VERSION release - Vidolimo SEO Auditor"

echo ""
echo "✓ Committed to trunk"
echo ""

# Step 5: Create tag
echo "Step 5: Creating version tag..."
svn copy trunk tags/$VERSION -m "Tag version $VERSION"

echo "✓ Tag created"
echo ""

# Step 6: Verify
echo "Step 6: Verifying deployment..."
echo ""
echo "Your plugin is now deployed to WordPress.org!"
echo ""
echo "Plugin URL: https://wordpress.org/plugins/vidolimo-seo-auditor/"
echo "SVN URL: $SVN_URL"
echo ""
echo "Note: It may take a few minutes for the plugin page to update."
echo "Search results may take up to 72 hours to fully update."
echo ""

echo "=========================================="
echo "✓ Deployment Complete!"
echo "=========================================="
