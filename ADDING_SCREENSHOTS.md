# How to Add Screenshots to WordPress.org Plugin Page

## Where to Upload Screenshots

Screenshots go in the `.wordpress-org/` folder with specific naming conventions:

```
.wordpress-org/
├── screenshot-1.png
├── screenshot-2.png
├── screenshot-3.png
├── screenshot-4.png
└── screenshot-5.png
```

## Screenshot Requirements

- **Format**: PNG, JPG, or GIF
- **Dimensions**: 1200x900px (recommended) or 1544x1024px
- **Maximum**: 5 screenshots
- **File naming**: `screenshot-1.png`, `screenshot-2.png`, etc. (sequential numbers)
- **File size**: Keep under 500KB each for faster loading

## How to Create Screenshots

### Option 1: Using Your Plugin
1. Install your plugin on a test WordPress site
2. Take screenshots of:
   - Dashboard view
   - Page analysis results
   - SEO recommendations
   - Settings page
   - Mobile view (optional)
3. Use Mac's built-in screenshot tool: `Cmd + Shift + 4`

### Option 2: Using Online Tools
- **Figma**: Create mockups of the plugin interface
- **Adobe XD**: Design plugin screenshots
- **Canva**: Create professional-looking screenshots

### Option 3: Using Automation
- **Playwright**: Automated screenshot testing
- **Puppeteer**: Headless browser screenshots
- **Selenium**: Browser automation

## Step-by-Step: Adding Screenshots

### Step 1: Create/Prepare Screenshots
Take 3-5 screenshots showing:
1. **Dashboard** - Main SEO analysis dashboard
2. **Page Analysis** - Detailed analysis results
3. **Recommendations** - SEO recommendations
4. **Settings** - Plugin settings page
5. **Mobile View** - Mobile-responsive interface

### Step 2: Resize to 1200x900px
Using ImageMagick:
```bash
magick screenshot.png -resize 1200x900 screenshot-1.png
```

Or using Preview on Mac:
1. Open screenshot in Preview
2. Tools → Adjust Size
3. Set to 1200x900px
4. Export as PNG

### Step 3: Add Captions (Optional)
Add helpful captions to explain each screenshot:
```bash
magick screenshot-1.png -pointsize 30 -fill white -gravity South \
  -annotate +0+20 "Dashboard - View overall SEO scores" \
  screenshot-1-captioned.png
```

### Step 4: Place in .wordpress-org Folder
```bash
cp screenshot-1.png /path/to/vidolimo-seo-auditor/.wordpress-org/
cp screenshot-2.png /path/to/vidolimo-seo-auditor/.wordpress-org/
cp screenshot-3.png /path/to/vidolimo-seo-auditor/.wordpress-org/
cp screenshot-4.png /path/to/vidolimo-seo-auditor/.wordpress-org/
cp screenshot-5.png /path/to/vidolimo-seo-auditor/.wordpress-org/
```

### Step 5: Upload to SVN
```bash
cd /path/to/svn/repo
svn add .wordpress-org/screenshot-*.png
svn commit -m "Add plugin screenshots"
```

Or use the deployment script:
```bash
./DEPLOY_TO_WORDPRESS.sh
```

## Example Directory Structure

```
vidolimo-seo-auditor/
├── .wordpress-org/
│   ├── icon-128x128.png
│   ├── icon.svg
│   ├── banner-1544x500.png
│   ├── banner-772x250.png
│   ├── screenshot-1.png  ← Dashboard
│   ├── screenshot-2.png  ← Analysis Results
│   ├── screenshot-3.png  ← Recommendations
│   ├── screenshot-4.png  ← Settings
│   └── screenshot-5.png  ← Mobile View
├── trunk/
├── tags/
└── branches/
```

## What to Show in Screenshots

### Screenshot 1: Dashboard
- Overall SEO score
- Category breakdown (Content, Technical, Performance, etc.)
- Quick stats

### Screenshot 2: Page Analysis
- Detailed analysis results
- Issues found
- Severity indicators

### Screenshot 3: Recommendations
- Actionable recommendations
- How to fix issues
- Priority levels

### Screenshot 4: Settings
- Plugin configuration options
- Customization features
- Admin interface

### Screenshot 5: Mobile View
- Responsive design
- Mobile-friendly interface
- Accessibility features

## Tips for Better Screenshots

1. **Use a clean WordPress theme** - Avoid cluttered backgrounds
2. **Show real data** - Use actual plugin output, not mockups
3. **Highlight key features** - Use arrows or boxes to point out important elements
4. **Keep consistent styling** - Use same color scheme across all screenshots
5. **Add captions** - Explain what each screenshot shows
6. **Optimize file size** - Compress images without losing quality
7. **Test on mobile** - Ensure screenshots look good on all devices

## Batch Processing Screenshots

Create a script to resize all screenshots:

```bash
#!/bin/bash
for file in screenshot-*.png; do
    magick "$file" -resize 1200x900 ".wordpress-org/$file"
done
```

## Updating Screenshots

To update existing screenshots:
1. Replace the PNG files in `.wordpress-org/`
2. Commit to SVN
3. WordPress.org will update within 5-10 minutes

## Useful Commands

**Resize image:**
```bash
magick input.png -resize 1200x900 output.png
```

**Compress image:**
```bash
magick input.png -quality 85 output.png
```

**Add text caption:**
```bash
magick input.png -pointsize 24 -fill white -gravity South \
  -annotate +0+10 "Your caption here" output.png
```

**Batch resize:**
```bash
for file in *.png; do
    magick "$file" -resize 1200x900 "resized-$file"
done
```

## After Uploading

1. **Verify on WordPress.org** - Check plugin page after 5-10 minutes
2. **Test on mobile** - Ensure screenshots display correctly
3. **Update readme.txt** - Reference screenshots in your documentation
4. **Announce changes** - Let users know about updated screenshots

## Support

- **WordPress Plugin Assets Guide**: https://developer.wordpress.org/plugins/wordpress-org/plugin-assets/
- **Screenshot Guidelines**: https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/
- **ImageMagick Docs**: https://imagemagick.org/

---

**Ready to add screenshots?** Follow the steps above and your plugin page will look professional and informative!
