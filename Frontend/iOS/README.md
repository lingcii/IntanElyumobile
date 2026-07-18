# Intan Elyu — iOS App

Native iOS wrapper for the Intan Elyu tourism app.

## Requirements

- macOS 14+ (Sonoma)
- Xcode 15+
- [Homebrew](https://brew.sh)
- [XcodeGen](https://github.com/yonaskolb/XcodeGen)

---

## Setup (one-time)

```bash
cd Frontend/iOS

# 1. Install XcodeGen
brew install xcodegen

# 2. Generate the Xcode project
xcodegen generate

# 3. Open in Xcode
open IntanElyu.xcodeproj
```

Or use the Makefile:

```bash
make setup    # install XcodeGen + generate project
make open     # generate + open in Xcode
```

## Configuration

Edit `IntanElyu/ContentView.swift` to change the backend/frontend URLs:

```swift
@AppStorage("backend_url") private var backendURL = "http://127.0.0.1:8000"
@AppStorage("frontend_url") private var frontendURL = "http://127.0.0.1:3000"
```

## Running

1. Open the project in Xcode
2. Select a simulator or connected device
3. Press `Cmd+R` to build and run

## Building for App Store

1. Change `bundleIdPrefix` in `project.yml` if needed
2. Set `DEVELOPMENT_TEAM` in Xcode → Signing & Capabilities
3. Product → Archive
4. Distribute App
