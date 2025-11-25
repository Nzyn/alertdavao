# Android SDK Setup Guide

## Problem
You're seeing this error:
```
SDK location not found. Define a valid SDK location with an ANDROID_HOME environment variable 
or by setting the sdk.dir path in your project's local properties file
```

## Solution

### Option 1: Install Android Studio (Recommended)

1. **Download Android Studio**
   - Visit https://developer.android.com/studio
   - Download and install Android Studio

2. **Install Android SDK**
   - Open Android Studio
   - Go to: `File > Settings > Appearance & Behavior > System Settings > Android SDK`
   - Install the latest Android SDK (recommended: Android 13/14)
   - Note the SDK location path (e.g., `C:\Users\YOUR_USERNAME\AppData\Local\Android\Sdk`)

3. **Configure local.properties**
   - Open `UserSide/android/local.properties`
   - Uncomment the `sdk.dir` line
   - Replace with your actual SDK path:
     ```
     sdk.dir=C:\\Users\\YOUR_USERNAME\\AppData\\Local\\Android\\Sdk
     ```
   - **Note:** Use double backslashes `\\` on Windows

4. **Set Environment Variable (Optional but recommended)**
   ```powershell
   # Run in PowerShell as Administrator
   [System.Environment]::SetEnvironmentVariable('ANDROID_HOME', 'C:\Users\YOUR_USERNAME\AppData\Local\Android\Sdk', 'User')
   [System.Environment]::SetEnvironmentVariable('ANDROID_SDK_ROOT', 'C:\Users\YOUR_USERNAME\AppData\Local\Android\Sdk', 'User')
   ```

### Option 2: Command Line Tools Only

If you don't want to install Android Studio:

1. Download command line tools from: https://developer.android.com/studio#command-tools
2. Extract to a folder (e.g., `C:\Android\cmdline-tools`)
3. Install SDK using sdkmanager
4. Set the path in `local.properties`

## After Setup

1. Restart your terminal/IDE
2. Run your build command again
3. The project should now build successfully

## Common SDK Locations

- **Windows**: `C:\Users\USERNAME\AppData\Local\Android\Sdk`
- **macOS**: `/Users/USERNAME/Library/Android/sdk`
- **Linux**: `/home/USERNAME/Android/Sdk`

## Verification

To verify your setup, run:
```powershell
# Check environment variables
$env:ANDROID_HOME
$env:ANDROID_SDK_ROOT

# Check if SDK tools exist
Get-ChildItem "$env:ANDROID_HOME\platform-tools"
```

## Important Notes

- `local.properties` is machine-specific and should NOT be committed to git
- It's already added to `.gitignore` to prevent accidental commits
- Each developer needs to set up their own `local.properties` file
