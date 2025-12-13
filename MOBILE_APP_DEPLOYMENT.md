# MI-NES Payroll System - Mobile App Deployment Guide

## Overview
This guide covers deploying the MI-NES Payroll System to:
- **Apple App Store** (iOS)
- **Google Play Store** (Android)
- **Huawei AppGallery** (HarmonyOS/Android)

---

## Option 1: Capacitor (Recommended)

Capacitor wraps your web app in a native container, allowing deployment to app stores.

### Prerequisites
- Node.js 18+
- For iOS: macOS with Xcode 15+
- For Android: Android Studio with SDK 33+
- For Huawei: HMS Core SDK

### Step 1: Install Capacitor

```bash
npm install @capacitor/core @capacitor/cli
npm install @capacitor/android @capacitor/ios
npx cap init "MI-NES Payroll" "com.mines.payroll"
```

### Step 2: Configure capacitor.config.ts

```typescript
import { CapacitorConfig } from '@capacitor/cli';

const config: CapacitorConfig = {
  appId: 'com.mines.payroll',
  appName: 'MI-NES Payroll',
  webDir: 'out',
  server: {
    androidScheme: 'https'
  },
  plugins: {
    SplashScreen: {
      launchShowDuration: 2000,
      backgroundColor: "#4F46E5",
      showSpinner: false
    },
    PushNotifications: {
      presentationOptions: ["badge", "sound", "alert"]
    }
  }
};

export default config;
```

### Step 3: Build and Export Next.js

Add to `next.config.mjs`:
```javascript
const nextConfig = {
  output: 'export',
  trailingSlash: true,
  images: {
    unoptimized: true
  }
};
```

Build:
```bash
npm run build
npx cap add android
npx cap add ios
npx cap sync
```

### Step 4: Add Native Platforms

```bash
# Android
npx cap open android

# iOS (requires macOS)
npx cap open ios
```

---

## App Store Requirements

### Apple App Store (iOS)
1. **Apple Developer Account** - $99/year
2. **App Store Connect** account
3. **Certificates & Provisioning Profiles**
4. **App Icons** (1024x1024 and various sizes)
5. **Screenshots** for different device sizes
6. **Privacy Policy URL**
7. **App Review Guidelines compliance**

Required Info:
- Bundle ID: `com.mines.payroll`
- App Name: MI-NES Payroll
- Category: Business
- Age Rating: 4+

### Google Play Store (Android)
1. **Google Play Console** account - $25 one-time
2. **Signed APK/AAB** (Android App Bundle)
3. **App Icons** (512x512)
4. **Feature Graphic** (1024x500)
5. **Screenshots** (min 2, max 8)
6. **Privacy Policy URL**
7. **Content Rating questionnaire**

Required Info:
- Package Name: `com.mines.payroll`
- App Name: MI-NES Payroll
- Category: Business
- Target API Level: 33+

### Huawei AppGallery
1. **Huawei Developer Account** - Free
2. **HMS Core SDK** integration
3. **App Icons & Screenshots**
4. **Privacy Policy**
5. **Additional review for financial apps**

Required Info:
- Package Name: `com.mines.payroll`
- Support for HMS (Huawei Mobile Services)

---

## Native Features to Add

### 1. Push Notifications
```bash
npm install @capacitor/push-notifications
```

Use cases:
- Payroll generated notification
- Leave request approved/rejected
- Clock-in reminder

### 2. Biometric Authentication
```bash
npm install @capacitor-community/biometric-auth
```

Use cases:
- Login with fingerprint/Face ID
- Approve sensitive actions

### 3. Geolocation (for Clock-in)
```bash
npm install @capacitor/geolocation
```

Already implemented in web version - works natively.

### 4. Camera (for Document Upload)
```bash
npm install @capacitor/camera
```

Use cases:
- Upload MC documents
- Profile photo

### 5. Local Notifications
```bash
npm install @capacitor/local-notifications
```

Use cases:
- Remind to clock out
- Leave balance alerts

---

## App Icons & Splash Screen

### Required Sizes

**iOS App Icons:**
- 1024x1024 (App Store)
- 180x180 (iPhone @3x)
- 120x120 (iPhone @2x)
- 167x167 (iPad Pro)
- 152x152 (iPad @2x)

**Android App Icons:**
- 512x512 (Play Store)
- 192x192 (xxxhdpi)
- 144x144 (xxhdpi)
- 96x96 (xhdpi)
- 72x72 (hdpi)
- 48x48 (mdpi)

**Splash Screen:**
- 2732x2732 (universal)
- Background: #4F46E5 (Indigo)
- Logo: MI-NES logo centered

---

## Build Commands

### Development
```bash
# Build web app
npm run build

# Sync with native projects
npx cap sync

# Run on Android
npx cap run android

# Run on iOS
npx cap run ios
```

### Production Build

**Android (AAB for Play Store):**
```bash
cd android
./gradlew bundleRelease
```
Output: `android/app/build/outputs/bundle/release/app-release.aab`

**iOS (Archive for App Store):**
1. Open in Xcode: `npx cap open ios`
2. Product → Archive
3. Distribute App → App Store Connect

**Huawei (APK with HMS):**
```bash
cd android
./gradlew assembleRelease
```
Then integrate HMS SDK and submit to AppGallery.

---

## Testing

### Android Testing
1. **Internal Testing** - Up to 100 testers
2. **Closed Testing** - Selected groups
3. **Open Testing** - Public beta
4. **Production** - Full release

### iOS Testing
1. **TestFlight** - Up to 10,000 testers
2. **App Store Review** - 24-48 hours typical

### Huawei Testing
1. **Open Testing** - Beta release
2. **AppGallery Review** - 3-5 business days

---

## Privacy Policy Requirements

All app stores require a privacy policy. Include:

1. **Data Collection**
   - Personal info (name, email)
   - Location data (for attendance)
   - Employment information

2. **Data Usage**
   - Payroll processing
   - Attendance tracking
   - Leave management

3. **Data Storage**
   - Stored in Supabase (cloud)
   - Encrypted in transit (HTTPS)

4. **User Rights**
   - Access their data
   - Request deletion
   - Export data

---

## Estimated Timeline

| Phase | Duration | Tasks |
|-------|----------|-------|
| Setup | 1 week | Capacitor setup, native projects |
| Development | 2 weeks | Native features, testing |
| Assets | 1 week | Icons, screenshots, descriptions |
| iOS Review | 1-2 weeks | App Store submission |
| Android Review | 1 week | Play Store submission |
| Huawei Review | 1-2 weeks | AppGallery submission |

**Total: 6-8 weeks** to launch on all three platforms

---

## Costs

| Item | Cost |
|------|------|
| Apple Developer Program | $99/year |
| Google Play Console | $25 one-time |
| Huawei Developer Account | Free |
| **Total First Year** | **~$124** |

---

## Contact for App Store Accounts

Set up developer accounts at:
- Apple: https://developer.apple.com/programs/
- Google: https://play.google.com/console/
- Huawei: https://developer.huawei.com/consumer/en/appgallery/
