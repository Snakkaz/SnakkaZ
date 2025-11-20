# 🧹 SnakkaZ Cleanup & Reorganization Plan

**Dato:** 20. November 2025  
**Status:** Planning  
**Mål:** Clean, organized, production-ready workspace

---

## 📊 CURRENT PROBLEMS

### 1. Database Issues ❌
- LiteSpeed caching PHP files (database.php still uses old credentials in cache)
- Connection fails despite correct credentials in code
- Need permanent cache-busting solution

### 2. Auth Persistence ❌
- User logged out on page refresh (SHOULD persist!)
- localStorage exists but not used properly
- Need proper token validation flow

### 3. Too Many Duplicate Files ❌
**Markdown Documentation (24 files):**
- 6x Deployment docs
- 5x Status/Progress docs  
- 4x Plan/Roadmap docs
- Multiple duplicates with same content

**Test Files (17 files):**
- Debug HTML files
- Test PHP scripts  
- Temporary upload scripts

**Deploy Scripts (15+ files):**
- Multiple Python deploy scripts
- Shell scripts
- Duplicate functionality

---

## 🎯 CLEANUP STRATEGY

### Phase 1: Fix Critical Issues
1. ✅ Permanent database cache fix
2. ✅ Fix auth persistence (localStorage → auto-login)
3. ✅ Test complete flow

### Phase 2: Delete Duplicates & Temp Files
**Keep ONLY:**
- `README.md` - Main project docs
- `AGENT-HANDOVER.md` - For next agent
- `MASTER-PLAN-COMPLETE.md` - Roadmap
- `docs/API.md` - API reference
- `docs/DEPLOYMENT.md` - Deploy guide

**DELETE:**
- All other markdown files (consolidate content first)
- All test-*.html files
- All test-*.php files (in /server/api/)
- All upload-*.py scripts
- Old deploy scripts (keep only deploy-full.py)

### Phase 3: Reorganize Structure
```
/workspaces/SnakkaZ/
├── README.md                    # Project overview
├── AGENT-HANDOVER.md            # Handover docs
├── MASTER-PLAN.md               # Consolidated roadmap
├── deploy.py                    # Single deploy script
├── .gitignore                   # Ignore temp files
│
├── frontend/                    # React app (clean)
├── server/                      # PHP backend (clean)
├── database/                    # SQL schemas only
│   ├── schema.sql
│   ├── seed-demo-data.sql
│   └── migrations/
│
├── docs/                        # Documentation
│   ├── API.md
│   ├── DEPLOYMENT.md
│   └── TROUBLESHOOTING.md
│
└── .archive/                    # Old files (for reference)
    └── [all old markdown/scripts]
```

---

## 🔧 FIXES TO IMPLEMENT

### 1. Database Connection (PRIORITY 1)
**Problem:** LiteSpeed caches database.php with wrong credentials  
**Solution:**
- Add version query parameter to force reload
- Use opcache_reset() in PHP
- Add timestamp to config file
- Clear LiteSpeed cache via .htaccess

### 2. Auth Persistence (PRIORITY 1)  
**Problem:** User logged out on refresh  
**Current:** localStorage has token but not validated on load  
**Solution:**
- Frontend: Check localStorage on mount
- Call /api/auth/validate.php to verify token
- Auto-login if valid
- Standard web app behavior

### 3. File Organization (PRIORITY 2)
- Move old files to .archive/
- Delete temp/test files
- Consolidate documentation
- Update .gitignore

---

## ✅ SUCCESS CRITERIA

1. **Database:** Stable connection, no cache issues
2. **Auth:** User stays logged in after refresh (standard behavior)
3. **Structure:** Clean, organized, professional
4. **Documentation:** Single source of truth for each topic
5. **Deploy:** One simple command deploys everything

---

## 🚀 IMPLEMENTATION ORDER

1. Fix database cache issue (30 min)
2. Implement proper auth persistence (20 min)
3. Test complete user flow (10 min)
4. Clean up files (20 min)
5. Update documentation (10 min)
6. Final deploy & test (10 min)

**Total Time:** ~2 timer

---

**Next Step:** Start with Phase 1 - Fix critical issues
