# Docker Local Deployment Guide

## Prerequisites
1. Install Docker Desktop for Windows
   - Download: https://www.docker.com/products/docker-desktop
   - Install and restart your computer
   - Make sure Docker is running (check system tray)

---

## Step 1: Build Docker Image (One-time, 2-3 minutes)

Open PowerShell in your project directory and run:

```powershell
cd "c:\Users\User\Documents\SEM 7\INDUSTRIAL THINGS\NES SOLUTION AND NETWORK SDN BHD\payroll system"

docker build -t mines-payroll .
```

**What this does:**
- Reads your `Dockerfile`
- Installs PHP, Apache, PostgreSQL extensions
- Installs Composer dependencies
- Creates a ready-to-run image

---

## Step 2: Run Docker Container

```powershell
docker run -d -p 8080:80 --name payroll-app mines-payroll
```

**What this does:**
- `-d` = Run in background
- `-p 8080:80` = Map port 8080 on your computer to port 80 in container
- `--name payroll-app` = Give it a friendly name
- `mines-payroll` = Use the image we built

---

## Step 3: Access Your App

Open your browser and go to:
```
http://localhost:8080
```

**That's it!** Your app is running in Docker.

---

## Useful Docker Commands

### View running containers:
```powershell
docker ps
```

### View logs (for debugging):
```powershell
docker logs payroll-app
```

### Stop the container:
```powershell
docker stop payroll-app
```

### Start it again:
```powershell
docker start payroll-app
```

### Remove container (to rebuild):
```powershell
docker stop payroll-app
docker rm payroll-app
```

### Rebuild after code changes:
```powershell
# Stop and remove old container
docker stop payroll-app
docker rm payroll-app

# Rebuild image
docker build -t mines-payroll .

# Run new container
docker run -d -p 8080:80 --name payroll-app mines-payroll
```

---

## Bonus: Make it Accessible from Other Devices

### Option 1: Use ngrok (Easiest)
1. Download ngrok: https://ngrok.com/download
2. Extract and run:
   ```powershell
   ngrok http 8080
   ```
3. You'll get a public URL like: `https://abc123.ngrok.io`
4. Share this URL for demos (temporary, free)

### Option 2: Use Your Local IP
1. Find your local IP:
   ```powershell
   ipconfig
   ```
   Look for "IPv4 Address" (e.g., 192.168.1.100)

2. Access from other devices on same network:
   ```
   http://192.168.1.100:8080
   ```

---

## Troubleshooting

### Issue: "Docker is not running"
**Solution:** 
1. Open Docker Desktop
2. Wait for it to start (whale icon in system tray)
3. Try command again

### Issue: "Port 8080 is already in use"
**Solution:** Use a different port:
```powershell
docker run -d -p 8081:80 --name payroll-app mines-payroll
```
Then access at `http://localhost:8081`

### Issue: "Database connection failed"
**Solution:** Make sure `.env` file has correct Supabase credentials:
```env
DB_HOST=db.aahaznqptohmkdiqpjnx.supabase.co
DB_PORT=5432
DB_USER=postgres
DB_PASS=ZGdRerSZQfxtoHKs
```

### Issue: Build fails with "composer install" error
**Solution:** 
1. Make sure `composer.json` and `composer.lock` are in your project
2. Check internet connection (Docker needs to download packages)

---

## Advantages of Docker Local

| Advantage | Description |
|-----------|-------------|
| ✅ **No deployment** | Runs on your machine |
| ✅ **Fast** | No network latency |
| ✅ **Free** | No hosting costs |
| ✅ **Full control** | No platform limitations |
| ✅ **Easy demos** | Use ngrok for temporary public access |
| ✅ **Professional** | Same setup as production |

---

## For Presentations/Demos

### Method 1: Screen Share
- Just share your screen in Zoom/Teams/Google Meet
- Show `http://localhost:8080`

### Method 2: Ngrok (Temporary Public URL)
```powershell
# Install ngrok
# Download from: https://ngrok.com/download

# Run ngrok
ngrok http 8080

# Share the https URL it gives you
# Example: https://abc123.ngrok-free.app
```

### Method 3: Record Video
- Use OBS Studio or Windows Game Bar
- Record a demo video
- Share the video

---

## Production Deployment (When Ready)

When you're ready to deploy for real:
1. Your Docker setup is already production-ready
2. Push to GitHub
3. Deploy to any Docker-supporting platform:
   - Railway (easiest)
   - Fly.io
   - DigitalOcean App Platform
   - AWS ECS
   - Google Cloud Run

Your `Dockerfile` works everywhere!

---

## Summary

**For now: Run Docker locally**
```powershell
docker build -t mines-payroll .
docker run -d -p 8080:80 --name payroll-app mines-payroll
```

**For demos: Use ngrok**
```powershell
ngrok http 8080
```

**For production: Deploy later when needed**

This is the **simplest, most reliable solution** for your situation.
