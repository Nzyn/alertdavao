# 📝 How to Add New Documentation

## ⚠️ IMPORTANT: Do NOT create new .md files!

All documentation must be added to **PROJECT_SUMMARY.md**

---

## Steps to Add Documentation

### 1. Open PROJECT_SUMMARY.md
```bash
code PROJECT_SUMMARY.md
# or
notepad PROJECT_SUMMARY.md
```

### 2. Update the "Update History" Section
Add your entry at the top of the Update History section:

```markdown
### [Current Date] - [Time] - [Brief Title]
- ✅ [What you fixed/added]
- ✅ [Files modified]
- ✅ [Impact/result]
```

### 3. Add Detailed Content
Scroll to the end or appropriate section and add:

```markdown
---

## 📄 [Your Topic Title]
**Date**: [Current Date and Time]
**Category**: [Fix/Feature/Update/etc.]

[Your detailed content here...]

---
```

### 4. Save and Commit
```bash
git add PROJECT_SUMMARY.md
git commit -m "docs: [brief description]"
```

---

## Why Only One File?

✅ **Single source of truth** - No searching through multiple files  
✅ **Chronological tracking** - See the evolution of the project  
✅ **Easier maintenance** - Update one file instead of many  
✅ **Better searchability** - Ctrl+F finds everything  
✅ **Reduced clutter** - Clean repository structure  

---

## If Someone Creates a New .md File

Run the compilation script to merge it:

```bash
scripts\compile-docs.ps1
```

This will automatically:
1. Read all .md files (except README.md and PROJECT_SUMMARY.md)
2. Sort them chronologically
3. Compile them into PROJECT_SUMMARY.md
4. You can then delete the individual files

---

## Current Documentation Structure

```
alertdavao2.0.new/
├── README.md                    ← Quick start guide (keep)
├── PROJECT_SUMMARY.md           ← ALL documentation (update this!)
│
├── docs/                        ← Documentation guides
│   ├── HOW_TO_ADD_DOCS.md      ← This file
│   └── DOCUMENTATION_POLICY.md  ← Documentation policy
│
├── scripts/                     ← All automation scripts
│   ├── compile-docs.ps1        ← Recompile script
│   ├── restart-all.bat         ← Restart all services
│   └── ... (19 scripts total)
│
└── sql/                         ← Database scripts
    ├── seed_police_stations.sql
    └── ... (7 SQL files)
```

---

**Last Updated**: November 23, 2025 02:30 AM
