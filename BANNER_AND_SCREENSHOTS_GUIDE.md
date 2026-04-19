# Banner and Screenshots Guide

## Current Status

✅ **Banners Uploaded**
- Large banner (1544x500px) - PNG format
- Small banner (772x250px) - PNG format
- Both uploaded to WordPress.org SVN (Revision 3509931)

⏳ **Screenshots** - Ready to add

---

## Banner Images

Your plugin now has professional banner images:

### Large Banner (1544x500px)
- **File**: `.wordpress-org/banner-1544x500.png`
- **Used**: Plugin directory header
- **Status**: ✅ Uploaded to WordPress.org

### Small Banner (772x250px)
- **File**: `.wordpress-org/banner-772x250.png`
- **Used**: Search results and mobile views
- **Status**: ✅ Uploaded to WordPress.org

**Expected Update**: Banner should appear on your plugin page within 5-10 minutes.

---

## How to Add Screenshots

### Step 1: Create Screenshots
Take 3-5 screenshots of your plugin showing:

1. **Dashboard** - Main SEO analysis interface
2. **Page Analysis** - Detailed analysis results
3. **Recommendations** - SEO improvement suggestions
4. **Settings** - Plugin configuration page
5. **Mobile View** (optional) - Mobile-responsive design

### Step 2: Prepare Screenshots
Resize to 1200x900px using ImageMagick:

```bash
magick screenshot.png -resize 1200x900 screenshot-1.png
```

Or use Mac Preview:
1. Open screenshot
2. Tools → Adjust Size
3. Set to 1200x900px
4. Export as PNG

### Step 3: Place in .wordpress-org Folder

```bash
cp screenshot-1.png /Users/Ardi/Documents/OpenSource/vidolimo-seo-auditor/.wordpress-org/
cp screenshot-2.png /Users/Ardi/Documents/OpenSource/vidolimo-seo-auditor/.wordpress-org/
cp screenshot-3.png /Users/Ardi/Documents/OpenSource/vidolimo-seo-auditor/.wordpress-org/
cp screenshot-4.png /Users/Ardi/Documents/OpenSource/vidolimo-seo-auditor/.wordpress-org/
cp screenshot-5.png /Users/Ardi/Documents/OpenSource/vidolimo-seo-auditor/.wordpress-org/
```

### Step 4: Upload to WordPress.org

**Option A: Using Deployment Script**
```bash
cd /Users/Ardi/Documents/OpenSource/vidolimo-seo-auditor
./DEPLOY_TO_WORDPRESS.sh
```

**Option B: Manual SVN Upload**
```bash
cd /tmp/vidolimo-seo-auditor-svn-deploy
cp /Users/Ardi/Documents/OpenSource/vidolimo-seo-auditor/.wordpress-org/screenshot-*.png .wordpress-org/
svn add .wordpress-org/screenshot-*.png
svn commit -m "Add plugin screenshots"
```

**Option C: Using Git + SVN**
```bash
# Commit to GitHub
cd /Users/Ardi/Documents/OpenSource/vidolimo-seo-auditor
git add .wordpress-org/screenshot-*.png
git commit -m "Add plugin screenshots"
git push origin main

# Then upload to SVN (same as Option B)
```

---

## File Structure

Your `.wordpress-org/` folder should look like this:

```
.wordpress-org/
├── icon-128x128.png          ✅ Uploaded
├── icon.svg                  ✅ Uploaded
├── banner-1544x500.png       ✅ Uploaded
├── banner-1544x500.svg       ✅ Uploaded
├── banner-772x250.png        ✅ Uploaded
├── banner-772x250.svg        ✅ Uploaded
├── screenshot-1.png          ⏳ Ready to add
├── screenshot-2.png          ⏳ Ready to add
├── screenshot-3.png          ⏳ Ready to add
├── screenshot-4.png          ⏳ Ready to add
└── screenshot-5.png          ⏳ Ready to add
```

---

## Screenshot Best Practices

### What to Show
- ✅ Real plugin interface (not mockups)
- ✅ Key features and functionality
- ✅ User-friendly design
- ✅ Clear, readable text
- ✅ Professional appearance

### What to Avoid
- ❌ Blurry or low-quality images
- ❌ Personal information or sensitive data
- ❌ Outdated WordPress versions
- ❌ Cluttered or confusing layouts
- ❌ Excessive branding or watermarks

### Image Optimization
```bash
# Resize to 1200x900px
magick input.png -resize 1200x900 output.png

# Compress without quality loss
magick input.png -quality 85 output.png

# Batch process multiple files
for file in *.png; do
    magick "$file" -resize 1200x900 "resized-$file"
done
```

---

## Verification Checklist

- [ ] Banner images created (1544x500 and 772x250)
- [ ] Banner images in PNG format
- [ ] Screenshots captured (3-5 images)
- [ ] Screenshots resized to 1200x900px
- [ ] Screenshots named screenshot-1.png through screenshot-5.png
- [ ] All files placed in `.wordpress-org/` folder
- [ ] Files uploaded to WordPress.org SVN
- [ ] Plugin page updated (5-10 minutes)
- [ ] Screenshots display correctly on plugin page

---

## After Upload

1. **Wait 5-10 minutes** for WordPress.org to process
2. **Visit your plugin page**: https://wordpress.org/plugins/vidolimo-seo-auditor/
3. **Verify banners appear** in the header
4. **Check screenshots** display correctly
5. **Test on mobile** to ensure responsive design

---

## Useful Commands

**Check what's in .wordpress-org folder:**
```bash
ls -la /Users/Ardi/Documents/OpenSource/vidolimo-seo-auditor/.wordpress-org/
```

**View SVN assets:**
```bash
svn list https://plugins.svn.wordpress.org/vidolimo-seo-auditor/.wordpress-org/
```

**Get SVN log:**
```bash
svn log https://plugins.svn.wordpress.org/vidolimo-seo-auditor -l 5
```

---

## Support

- **WordPress Plugin Assets Guide**: https://developer.wordpress.org/plugins/wordpress-org/plugin-assets/
- **ImageMagick Documentation**: https://imagemagick.org/
- **WordPress.org Support**: https://wordpress.org/support/plugin/vidolimo-seo-auditor/

---

## Next Steps

1. ✅ Banners are uploaded - they'll appear soon
2. ⏳ Create and upload screenshots (follow Step 1-4 above)
3. ✅ Your plugin page will be complete with all assets!

**Your plugin is almost ready to showcase!** 🚀
