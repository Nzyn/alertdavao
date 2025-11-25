# ⚠️ IMPORTANT: Documentation Policy

## DO NOT Create New .md Files

All documentation must be added to **PROJECT_SUMMARY.md**

### Why?
- Prevents clutter and duplication
- Single source of truth
- Easier to search and maintain
- Chronological tracking of all changes

### How to Add Documentation

1. Open `PROJECT_SUMMARY.md`
2. Update the "Update History" section at the top with:
   - Current date and time
   - Description of changes
   - Files modified
3. Add your detailed content in a new section or update existing section
4. Save and commit

### If You Need to Compile Existing .md Files

Run: `.\compile-docs.ps1`

This will merge all .md files into PROJECT_SUMMARY.md chronologically.

### Files Exempt from This Rule
- `README.md` - Project overview and quick start
- `PROJECT_SUMMARY.md` - Complete documentation

---

**Last Policy Update**: November 23, 2025
