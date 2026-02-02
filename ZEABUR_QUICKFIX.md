## ⚡ QUICK FIX: Zeabur Environment Variables

Copy these exact variables to your Zeabur dashboard → Variables tab:

### Required Variables

```
DB_HOST=db.aahaznqptohmkdiqpjnx.supabase.co
DB_PORT=6543
DB_NAME=postgres
DB_USER=postgres
DB_PASS=<GET_FROM_SUPABASE_DASHBOARD>
```

### Optional Variables (Recommended)

```
APP_ENV=production
APP_DEBUG=false
SESSION_LIFETIME=7200
SESSION_SECURE=true
SESSION_HTTPONLY=true
```

---

## 🔑 How to Get DB_PASS:

1. Visit: https://supabase.com/dashboard/project/aahaznqptohmkdiqpjnx/settings/database
2. Click "Reset database password"
3. Copy the password
4. Paste it as DB_PASS in Zeabur

---

## ✅ After Adding Variables:

1. Click **Save** in Zeabur
2. Go to **Deployments** tab
3. Click **Redeploy**
4. Wait 1-2 minutes
5. Visit: https://mi-nes.zeabur.app/diagnostics.php
6. Verify all checks pass ✓
7. Access login: https://mi-nes.zeabur.app/auth/login.php

---

## 🚨 Still Getting 502?

Visit diagnostics page for detailed error info:
https://mi-nes.zeabur.app/diagnostics.php
