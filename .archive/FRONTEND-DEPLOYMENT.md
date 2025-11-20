# 🚀 SnakkaZ Frontend Deployment Guide

**Status:** Ready to Deploy ✅  
**Frontend URL:** https://snakkaz.com  
**Backend API:** https://snakkaz.com/api  
**Last Updated:** November 19, 2025

---

## ✅ Pre-Deployment Checklist

### Frontend Status
- ✅ React 19 + TypeScript
- ✅ All components implemented
- ✅ Auth flow (Login/Register)
- ✅ Chat window with real-time
- ✅ Message list with styling
- ✅ Room sidebar
- ✅ API integration
- ✅ WebSocket ready
- ✅ Responsive design (Telegram-inspired)
- ✅ CSS styling complete
- ✅ No build errors
- ✅ Dependencies installed

### Backend Status
- ✅ API live at https://snakkaz.com/api
- ✅ Database configured
- ✅ 8 endpoints working
- ✅ Token authentication
- ✅ HTTPS enabled

---

## 🔧 Quick Deploy (Automatic)

```bash
# From workspace root
./deploy-frontend.sh
```

This will:
1. Build production bundle
2. Upload to snakkaz.com via FTP
3. Verify deployment

**Estimated time:** 2-3 minutes

---

## 📦 Manual Deployment

### Step 1: Build Production

```bash
cd /workspaces/SnakkaZ/frontend
npm run build
```

**Output:** `dist/` folder with optimized files

### Step 2: Upload via FTP

**Using FileZilla:**
```
Host: ftp.snakkaz.com
User: admin@snakkaz.com
Pass: SnakkaZ123!!
Port: 21

Upload: frontend/dist/* → /public_html/
```

**Using lftp (command line):**
```bash
cd /workspaces/SnakkaZ/frontend/dist
lftp -c "
  open -u admin@snakkaz.com,SnakkaZ123!! ftp.snakkaz.com
  mirror --reverse --verbose ./
  bye
"
```

### Step 3: Verify Deployment

```bash
# Check homepage
curl -I https://snakkaz.com

# Should return: 200 OK
```

Open in browser:
- https://snakkaz.com
- https://snakkaz.com/login
- https://snakkaz.com/register

---

## 🧪 Testing After Deployment

### 1. Test Authentication

**Register New User:**
1. Go to https://snakkaz.com/register
2. Fill form:
   - Username: testuser
   - Email: test@example.com
   - Password: Test123456
3. Click "Create Account"
4. Should redirect to `/chat`

**Login:**
1. Go to https://snakkaz.com/login
2. Enter credentials
3. Should see chat interface

### 2. Test Chat Functionality

**Send Message:**
1. Click on a room (or create new)
2. Type message
3. Press Enter or click Send
4. Message should appear in chat

**Real-time Updates:**
- Open same chat in two browser windows
- Send message from one
- Should appear in both (if WebSocket working)

### 3. Test Responsive Design

- Desktop: ✅ Sidebar + Chat window
- Tablet: ✅ Collapsible sidebar
- Mobile: ✅ Full-screen chat

---

## 📁 Build Output Structure

```
dist/
├── index.html              # Main entry point
├── assets/
│   ├── index-[hash].js     # Main JS bundle
│   ├── index-[hash].css    # Compiled CSS
│   └── [other-assets]      # Images, fonts, etc.
└── vite.svg               # Favicon
```

**Total size:** ~500KB (minified + gzipped)

---

## 🔧 Configuration Files

### Environment Variables (.env)
```env
VITE_API_URL=https://snakkaz.com/api
VITE_WS_URL=wss://snakkaz.com
VITE_MCP_URL=https://mcp.snakkaz.com
VITE_ENV=production
```

### Vite Config (vite.config.ts)
```typescript
export default defineConfig({
  plugins: [react()],
  base: '/',
  build: {
    outDir: 'dist',
    sourcemap: false,
    minify: 'esbuild',
  },
})
```

---

## 🐛 Troubleshooting

### Build Errors

**Error:** `Module not found`
```bash
# Clear cache and reinstall
rm -rf node_modules package-lock.json
npm install
npm run build
```

**Error:** TypeScript errors
```bash
# Check types
npm run build -- --mode development
# Fix errors in source files
```

### Deployment Errors

**403 Forbidden:**
- Check FTP credentials
- Verify file permissions on server

**404 Not Found:**
- Check .htaccess is uploaded
- Verify files are in correct directory

**API Errors:**
- Check backend is running: https://snakkaz.com/api/health.php
- Verify CORS headers
- Check browser console for errors

### Runtime Errors

**Can't login:**
- Check API endpoint: https://snakkaz.com/api/auth/login.php
- Verify database credentials
- Check browser console

**Messages not sending:**
- Check WebSocket connection
- Verify token in localStorage
- Check network tab in DevTools

**Styling issues:**
- Hard refresh: Ctrl+Shift+R
- Clear browser cache
- Check CSS files loaded in Network tab

---

## 📊 Performance Metrics

### Target Metrics
- **Load Time:** < 2 seconds
- **First Contentful Paint:** < 1 second
- **Time to Interactive:** < 3 seconds
- **Lighthouse Score:** > 90

### Optimization Features
- ✅ Code splitting
- ✅ Tree shaking
- ✅ CSS minification
- ✅ Asset optimization
- ✅ Gzip compression (server)
- ✅ Browser caching

---

## 🔄 Update Workflow

### For Code Changes:
```bash
1. Edit files in src/
2. Test locally: npm run dev
3. Build: npm run build
4. Deploy: ./deploy-frontend.sh
```

### For Emergency Rollback:
```bash
# Keep previous dist/ as backup
mv dist dist.backup
git checkout HEAD~1
npm run build
./deploy-frontend.sh
```

---

## 🌐 URLs Reference

| Service | URL |
|---------|-----|
| Frontend | https://snakkaz.com |
| Login | https://snakkaz.com/login |
| Register | https://snakkaz.com/register |
| Chat | https://snakkaz.com/chat |
| API | https://snakkaz.com/api |
| Health | https://snakkaz.com/api/health.php |
| MCP | https://mcp.snakkaz.com |

---

## 🔐 Security Notes

### Production Checklist:
- ✅ HTTPS enforced
- ✅ API tokens stored securely (localStorage)
- ✅ XSS protection (React auto-escaping)
- ✅ CSRF tokens (API handles)
- ✅ Input validation (client + server)
- ✅ Secure password requirements

### Best Practices:
- Never commit `.env` with secrets
- Rotate JWT secret regularly
- Monitor error logs
- Keep dependencies updated

---

## 📞 Support

### If Deployment Fails:

1. **Check logs:**
   - Build errors in terminal
   - Browser console errors
   - Server error logs

2. **Verify backend:**
   ```bash
   curl https://snakkaz.com/api/health.php
   ```

3. **Test locally first:**
   ```bash
   npm run dev
   # Open http://localhost:5174
   ```

4. **Common Issues:**
   - Wrong FTP credentials
   - Missing .htaccess
   - CORS errors
   - Database connection

---

## ✅ Success Criteria

After deployment, verify:
- [ ] Homepage loads at https://snakkaz.com
- [ ] Login page works
- [ ] Registration creates user
- [ ] Chat interface appears after login
- [ ] Can send/receive messages
- [ ] Responsive on mobile
- [ ] No console errors
- [ ] API calls succeed

---

**Ready to deploy? Run:** `./deploy-frontend.sh` 🚀

**Questions?** Check the logs and docs above!
