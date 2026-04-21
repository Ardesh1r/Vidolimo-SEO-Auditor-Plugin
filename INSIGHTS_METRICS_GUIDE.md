# GitHub Insights Metrics Guide

## What You Can Now Track

Your GitHub Actions workflow now automatically fetches and displays:

### **GitHub Insights (Last 14 Days)**
- 📥 **Clones**: Total number of times people cloned your repo
- 👤 **Unique Cloners**: How many different people cloned it
- 👀 **Total Views**: Total repository page views
- 👥 **Unique Visitors**: How many different people visited

### **GitHub Stats (Overall)**
- ⭐ **Stars**: Total stars on the repo
- 🍴 **Forks**: Total forks
- 👁️ **Watchers**: Total watchers

### **WordPress.org Stats**
- 📥 **Total Downloads**: Downloads from WordPress.org
- 🚀 **Active Installs**: Currently active installations
- ⭐ **Rating**: Plugin rating (0-100)
- 📊 **Number of Ratings**: How many people rated it

---

## Where to See These Metrics

### **Option 1: STATS.md (Updated Daily)**
File: `STATS.md` in your repository

**Example:**
```markdown
# Repository Statistics

Last updated: 2026-04-21 00:00:00 UTC

## GitHub Stats (Overall)
- ⭐ Stars: 42
- 🍴 Forks: 8
- 👁️ Watchers: 12

## GitHub Insights (Last 14 Days)
- 📥 Clones: 109
- 👤 Unique Cloners: 30
- 👀 Total Views: 156
- 👥 Unique Visitors: 45

## WordPress.org Stats
- 📥 Total Downloads: 156
- 🚀 Active Installs: 45
- ⭐ Rating: 4.8/100
- 📊 Number of Ratings: 5
```

### **Option 2: GitHub Insights Tab (Manual)**
Go to: https://github.com/Ardesh1r/Vidolimo-SEO-Auditor-Plugin/insights

See live graphs and detailed breakdowns.

### **Option 3: GitHub Actions Logs**
Go to: https://github.com/Ardesh1r/Vidolimo-SEO-Auditor-Plugin/actions

Click "Update Repository Stats" → Latest run → See all metrics in the logs.

---

## How It Works

### **Automatic Daily Updates**
1. Workflow runs every day at 00:00 UTC (midnight)
2. Fetches all metrics from GitHub API and WordPress.org API
3. Creates/updates STATS.md with latest numbers
4. Commits and pushes automatically

### **Manual Trigger**
1. Go to: https://github.com/Ardesh1r/Vidolimo-SEO-Auditor-Plugin/actions
2. Click "Update Repository Stats"
3. Click "Run workflow"
4. Wait 1-2 minutes for completion

---

## Understanding the Metrics

### **Clones vs Downloads**
- **Clones**: Developers using `git clone` (includes forks, testing, local development)
- **WordPress.org Downloads**: Users downloading from WordPress.org plugin directory

**Example:**
- 109 clones in 14 days = developer interest
- 45 active installs = actual usage on WordPress sites

### **Unique Cloners vs Unique Visitors**
- **Unique Cloners**: Different people who cloned the repo
- **Unique Visitors**: Different people who visited the repo page

**Example:**
- 30 unique cloners = 30 different developers
- 45 unique visitors = 45 different people viewed the page

---

## Tracking Growth

### **Weekly Comparison**
Check STATS.md weekly to see growth:

| Week | Clones | Unique Cloners | Views | Active Installs |
|------|--------|----------------|-------|-----------------|
| Week 1 | 50 | 15 | 80 | 20 |
| Week 2 | 109 | 30 | 156 | 45 |
| Growth | +118% | +100% | +95% | +125% |

### **Monthly Trends**
Track month-over-month growth to identify patterns and adjust promotion strategy.

---

## API Endpoints Used

### **GitHub Clones (Last 14 Days)**
```bash
curl -H "Authorization: token YOUR_TOKEN" \
  https://api.github.com/repos/Ardesh1r/Vidolimo-SEO-Auditor-Plugin/traffic/clones
```

**Response:**
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

### **GitHub Views (Last 14 Days)**
```bash
curl -H "Authorization: token YOUR_TOKEN" \
  https://api.github.com/repos/Ardesh1r/Vidolimo-SEO-Auditor-Plugin/traffic/views
```

**Response:**
```json
{
  "count": 156,
  "uniques": 45,
  "views": [
    {
      "timestamp": "2026-04-21T00:00:00Z",
      "count": 10,
      "uniques": 5
    }
  ]
}
```

### **WordPress.org Plugin Stats**
```bash
curl https://api.wordpress.org/plugins/info/1.0/vidolimo-seo-auditor.json
```

---

## Important Notes

### **14-Day Limitation**
GitHub Insights API only returns data for the last 14 days. Older data is not available.

### **Authentication Required**
Clones and Views data require authentication (GitHub token). The workflow uses `${{ secrets.GITHUB_TOKEN }}` automatically.

### **Update Frequency**
- **Automatic**: Daily at 00:00 UTC
- **Manual**: Anytime via GitHub Actions "Run workflow" button

### **Data Accuracy**
- Metrics are accurate as of the last workflow run
- If you need real-time data, check GitHub Insights tab directly

---

## Useful Commands

### **Check Latest Stats**
```bash
# View STATS.md
cat STATS.md

# Or visit GitHub
https://github.com/Ardesh1r/Vidolimo-SEO-Auditor-Plugin/blob/main/STATS.md
```

### **View Workflow Logs**
```bash
# Go to Actions tab
https://github.com/Ardesh1r/Vidolimo-SEO-Auditor-Plugin/actions

# Click "Update Repository Stats"
# Click latest run
# See all metrics in logs
```

### **Manual Workflow Trigger**
```bash
# Via GitHub UI
1. Go to Actions tab
2. Click "Update Repository Stats"
3. Click "Run workflow"

# Via GitHub CLI (if installed)
gh workflow run update-stats.yml
```

---

## Summary

| Metric | Source | Frequency | Where to See |
|--------|--------|-----------|--------------|
| Clones (14d) | GitHub API | Daily | STATS.md |
| Unique Cloners (14d) | GitHub API | Daily | STATS.md |
| Views (14d) | GitHub API | Daily | STATS.md |
| Unique Visitors (14d) | GitHub API | Daily | STATS.md |
| Stars | GitHub API | Daily | STATS.md, README |
| Forks | GitHub API | Daily | STATS.md, README |
| Downloads | WordPress.org API | Daily | STATS.md, README |
| Active Installs | WordPress.org API | Daily | STATS.md, README |

---

## Next Steps

1. **Check STATS.md** — See your current metrics
2. **Monitor weekly** — Track growth trends
3. **Adjust strategy** — Based on what's working
4. **Celebrate milestones** — Share growth with community

---

**Your metrics are now being tracked automatically! 📊🚀**
