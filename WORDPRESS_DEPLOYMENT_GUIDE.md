# WordPress.org Plugin Deployment Guide

## Current Status

✅ **Completed:**
- Phase 1: SVN credentials set up
- Phase 2: Plugin structure prepared
  - Created `readme.txt` (WordPress.org compliant)
  - All plugin files ready
  - Version: 1.0.2

⏳ **Pending:**
- Phase 3: SVN installation
- Phase 4: Upload to WordPress.org

---

## What You Need to Do

### Step 1: Install SVN

SVN (Subversion) is required to upload your plugin to WordPress.org.

#### Option A: Install via Homebrew (Recommended)

1. First, install Homebrew if you don't have it:
```bash
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
```

2. Then install SVN:
```bash
brew install subversion
```

3. Verify installation:
```bash
svn --version
```

#### Option B: Download Pre-compiled Binary

Visit: https://subversion.apache.org/packages.html

Select the macOS package and follow the installation instructions.

#### Option C: Build from Source

If you prefer to build from source:
```bash
# Install dependencies first
brew install apr apr-util sqlite3

# Download and build SVN
curl -O https://archive.apache.org/dist/subversion/subversion-1.14.3.tar.gz
tar xzf subversion-1.14.3.tar.gz
cd subversion-1.14.3
./configure
make
sudo make install
```

---

### Step 2: Deploy Your Plugin

Once SVN is installed, you have two options:

#### Option A: Use the Automated Script (Easiest)

```bash
cd /Users/Ardi/Documents/OpenSource/vidolimo-seo-auditor
chmod +x DEPLOY_TO_WORDPRESS.sh
./DEPLOY_TO_WORDPRESS.sh
```

The script will:
1. Check out the WordPress.org SVN repository
2. Copy your plugin files to `/trunk`
3. Commit the changes
4. Create a version tag

#### Option B: Manual Deployment

If you prefer to do it manually:

```bash
# 1. Check out the repository
svn checkout https://plugins.svn.wordpress.org/vidolimo-seo-auditor /tmp/vidolimo-seo-auditor-svn

# 2. Copy your plugin files to trunk
cp -r /Users/Ardi/Documents/OpenSource/vidolimo-seo-auditor/* /tmp/vidolimo-seo-auditor-svn/trunk/

# 3. Remove unnecessary files
rm -f /tmp/vidolimo-seo-auditor-svn/trunk/.git
rm -f /tmp/vidolimo-seo-auditor-svn/trunk/.gitignore
rm -f /tmp/vidolimo-seo-auditor-svn/trunk/README.md
rm -f /tmp/vidolimo-seo-auditor-svn/trunk/DEPLOY_TO_WORDPRESS.sh
rm -f /tmp/vidolimo-seo-auditor-svn/trunk/WORDPRESS_DEPLOYMENT_GUIDE.md

# 4. Add files to SVN
cd /tmp/vidolimo-seo-auditor-svn
svn add trunk/* --force

# 5. Commit to trunk
svn commit trunk -m "Version 1.0.2 release - Vidolimo SEO Auditor"

# 6. Create a version tag
svn copy trunk tags/1.0.2 -m "Tag version 1.0.2"
```

---

## What Happens During Deployment

1. **SVN Checkout**: Downloads the WordPress.org repository for your plugin
2. **File Copy**: Copies all your plugin files to the `/trunk` directory
3. **SVN Commit**: Uploads your files to WordPress.org
4. **Tag Creation**: Creates a version tag (1.0.2) for release tracking

---

## After Deployment

### Verify Your Plugin

1. Visit: https://wordpress.org/plugins/vidolimo-seo-auditor/
2. Check that your plugin page is live
3. Verify the description, version, and features are correct

### Important Notes

- ⏱️ **Plugin page updates**: May take a few minutes
- 🔍 **Search results**: May take up to 72 hours to fully update
- 📝 **readme.txt**: Already created and validated
- 🏷️ **Version tag**: Will be created automatically during deployment

---

## Troubleshooting

### SVN Authentication Failed
- Verify your SVN username: `ardesh1r` (case-sensitive)
- Check your SVN password in WordPress.org Account & Security
- Make sure you're using the SVN password, not your WordPress.org password

### Files Not Uploading
- Ensure all files are in the `/trunk` directory
- Check that `.svn` directories are preserved (don't delete them)
- Verify you have write permissions

### Plugin Page Not Updating
- Wait a few minutes and refresh
- Check the SVN repository directly: https://plugins.svn.wordpress.org/vidolimo-seo-auditor/trunk/
- If files are there but page isn't updating, contact WordPress.org support

---

## Support

If you encounter issues:

1. **WordPress Plugin Directory FAQ**: https://developer.wordpress.org/plugins/wordpress-org/plugin-developer-faq/
2. **SVN Documentation**: https://developer.wordpress.org/plugins/wordpress-org/how-to-use-subversion/
3. **Contact WordPress.org**: plugins@wordpress.org

---

## Files Included

- `readme.txt` - WordPress.org compliant plugin description
- `DEPLOY_TO_WORDPRESS.sh` - Automated deployment script
- `WORDPRESS_DEPLOYMENT_GUIDE.md` - This guide

Good luck with your plugin launch! 🚀
