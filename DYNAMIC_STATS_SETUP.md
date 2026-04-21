# Dynamic Stats Setup Guide

## Option 1: Shields.io Dynamic Badges (Recommended - No Setup Needed)

Shields.io automatically pulls live data from GitHub API and WordPress.org API. The badges update in real-time!

### Current Setup (Already in README)

```markdown
**📊 Stats:**
[![GitHub Stars](https://img.shields.io/github/stars/Ardesh1r/Vidolimo-SEO-Auditor-Plugin?style=flat&color=yellow)](https://github.com/Ardesh1r/Vidolimo-SEO-Auditor-Plugin/stargazers)
[![GitHub Forks](https://img.shields.io/github/forks/Ardesh1r/Vidolimo-SEO-Auditor-Plugin?style=flat&color=blue)](https://github.com/Ardesh1r/Vidolimo-SEO-Auditor-Plugin/network/members)
[![WordPress.org Downloads](https://img.shields.io/badge/dynamic/json?label=WP.org%20Downloads&query=downloaded&url=https://api.wordpress.org/plugins/info/1.0/vidolimo-seo-auditor.json&color=green)](https://wordpress.org/plugins/vidolimo-seo-auditor/)
[![WordPress.org Active Installs](https://img.shields.io/badge/dynamic/json?label=Active%20Installs&query=active_installations&url=https://api.wordpress.org/plugins/info/1.0/vidolimo-seo-auditor.json&color=brightgreen)](https://wordpress.org/plugins/vidolimo-seo-auditor/)
```

**Pros:**
- ✅ Real-time updates
- ✅ No setup required
- ✅ No GitHub Actions needed
- ✅ Works immediately

**Cons:**
- Shields.io API has rate limits (but generous for public repos)

---

## Option 2: GitHub Actions Workflow (Automatic Daily Updates)

I've created `.github/workflows/update-stats.yml` that:
1. Runs daily at midnight UTC
2. Fetches GitHub Insights data
3. Fetches WordPress.org plugin stats
4. Updates README with actual numbers
5. Creates a STATS.md file with all metrics

### Setup

1. **Ensure GitHub Token is available** (it is by default in GitHub Actions)
2. **Commit the workflow file**:
   ```bash
   git add .github/workflows/update-stats.yml
   git commit -m "Add automatic stats update workflow"
   git push origin main
   ```

3. **First run** (manual trigger):
   - Go to: https://github.com/Ardesh1r/Vidolimo-SEO-Auditor-Plugin/actions
   - Click "Update Repository Stats"
   - Click "Run workflow"

4. **Automatic runs**: Every day at 00:00 UTC

### What It Does

- Fetches from GitHub API:
  - Stars count
  - Forks count
  - Watchers count

- Fetches from WordPress.org API:
  - Total downloads
  - Active installations
  - Rating (0-100)
  - Number of ratings

- Updates README.md with actual numbers
- Creates STATS.md with detailed breakdown
- Commits and pushes automatically

### Example Output

**README.md badges will show:**
```
⭐ GitHub Stars: 42
🍴 GitHub Forks: 8
📥 WP.org Downloads: 156
🚀 Active Installs: 45
```

**STATS.md will contain:**
```markdown
# Repository Statistics

Last updated: 2026-04-21 00:00:00 UTC

## GitHub Stats
- ⭐ Stars: 42
- 🍴 Forks: 8
- 👁️ Watchers: 12

## WordPress.org Stats
- 📥 Total Downloads: 156
- 🚀 Active Installs: 45
- ⭐ Rating: 4.8/100
- 📊 Number of Ratings: 5
```

---

## Option 3: Custom HTML/JavaScript (Advanced)

If you want a custom dashboard on your GitHub page:

```html
<!-- Add to README.md as HTML -->
<div id="stats-dashboard">
  <h2>📊 Live Statistics</h2>
  <div id="github-stats"></div>
  <div id="wordpress-stats"></div>
</div>

<script>
  // Fetch GitHub stats
  fetch('https://api.github.com/repos/Ardesh1r/Vidolimo-SEO-Auditor-Plugin')
    .then(r => r.json())
    .then(data => {
      document.getElementById('github-stats').innerHTML = `
        <h3>GitHub</h3>
        <p>⭐ Stars: ${data.stargazers_count}</p>
        <p>🍴 Forks: ${data.forks_count}</p>
        <p>👁️ Watchers: ${data.watchers_count}</p>
      `;
    });

  // Fetch WordPress.org stats
  fetch('https://api.wordpress.org/plugins/info/1.0/vidolimo-seo-auditor.json')
    .then(r => r.json())
    .then(data => {
      document.getElementById('wordpress-stats').innerHTML = `
        <h3>WordPress.org</h3>
        <p>📥 Downloads: ${data.downloaded}</p>
        <p>🚀 Active Installs: ${data.active_installations}</p>
        <p>⭐ Rating: ${data.rating}/100</p>
        <p>📊 Ratings: ${data.num_ratings}</p>
      `;
    });
</script>
```

**Note:** GitHub README doesn't execute JavaScript, so this won't work in README.md directly.

---

## Option 4: GitHub Insights API (Advanced)

GitHub Insights data (clones, views) requires authentication and is only available to repo owners.

### Get Insights Data Programmatically

```bash
# Get clone data (requires authentication)
curl -H "Authorization: token YOUR_GITHUB_TOKEN" \
  https://api.github.com/repos/Ardesh1r/Vidolimo-SEO-Auditor-Plugin/traffic/clones

# Get views data (requires authentication)
curl -H "Authorization: token YOUR_GITHUB_TOKEN" \
  https://api.github.com/repos/Ardesh1r/Vidolimo-SEO-Auditor-Plugin/traffic/views
```

### Response Example

```json
{
  "count": 109,
  "uniques": 30,
  "clones": [
    {
      "timestamp": "2026-04-21T00:00:00Z",
      "count": 5,
      "uniques": 2
    }
  ]
}
```

---

## Recommended Setup

**Use Option 1 (Shields.io) + Option 2 (GitHub Actions)**

1. **Shields.io badges** (already in README) — Real-time, no setup
2. **GitHub Actions workflow** — Daily updates to README with exact numbers

This gives you:
- ✅ Real-time badges that update instantly
- ✅ Daily snapshots of exact numbers
- ✅ Historical tracking in STATS.md
- ✅ Automatic commits showing growth

---

## How to Enable GitHub Actions Workflow

### Step 1: Commit the workflow file

```bash
cd /Users/Ardi/Documents/OpenSource/vidolimo-seo-auditor
git add .github/workflows/update-stats.yml
git commit -m "Add automatic stats update workflow"
git push origin main
```

### Step 2: Verify workflow is enabled

1. Go to: https://github.com/Ardesh1r/Vidolimo-SEO-Auditor-Plugin/actions
2. You should see "Update Repository Stats" workflow
3. Click it → "Run workflow" to test

### Step 3: Check results

- Workflow will run and update README.md
- Creates STATS.md with detailed stats
- Commits automatically

---

## Monitoring Your Stats

### Daily
- Check README badges (real-time via Shields.io)

### Weekly
- Check GitHub Insights tab for trends
- Review STATS.md for historical data

### Monthly
- Analyze growth patterns
- Adjust promotion strategy based on data

---

## Useful Links

- **Shields.io**: https://shields.io/
- **GitHub API**: https://docs.github.com/en/rest
- **WordPress.org API**: https://developer.wordpress.org/plugins/wordpress-org/how-to-use-the-wordpress-org-plugin-api/
- **GitHub Actions**: https://docs.github.com/en/actions

---

## Summary

| Option | Real-time | Setup | Effort | Best For |
|--------|-----------|-------|--------|----------|
| **Shields.io** | ✅ Yes | None | 0 min | Quick display |
| **GitHub Actions** | ⏰ Daily | Easy | 5 min | Tracking growth |
| **Custom HTML** | ✅ Yes | Medium | 30 min | Custom dashboard |
| **Insights API** | ✅ Yes | Hard | 1 hour | Advanced tracking |

**Recommendation:** Use Shields.io + GitHub Actions for best results!

---

**Ready to set up? Follow the "How to Enable GitHub Actions Workflow" section above!** 🚀
