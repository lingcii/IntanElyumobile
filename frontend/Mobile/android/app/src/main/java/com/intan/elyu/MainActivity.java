package com.intan.elyu;

import android.Manifest;
import android.content.Context;
import android.content.Intent;
import android.content.pm.PackageManager;
import android.net.ConnectivityManager;
import android.net.Network;
import android.net.NetworkCapabilities;
import android.net.NetworkInfo;
import android.net.Uri;
import android.os.Build;
import android.os.Bundle;
import android.webkit.GeolocationPermissions;
import android.webkit.WebResourceError;
import android.webkit.WebResourceRequest;
import android.webkit.WebResourceResponse;
import android.webkit.WebSettings;
import android.webkit.WebView;
import androidx.core.app.ActivityCompat;
import androidx.core.content.ContextCompat;
import com.getcapacitor.BridgeActivity;
import com.getcapacitor.BridgeWebChromeClient;
import com.getcapacitor.BridgeWebViewClient;
import java.io.BufferedReader;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.nio.charset.StandardCharsets;

public class MainActivity extends BridgeActivity {
    private static final int LOCATION_PERMISSION_REQUEST_CODE = 1001;

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        
        // Fresh install or re-installation after uninstall detection:
        android.content.SharedPreferences installPrefs = getSharedPreferences("intan_elyu_install_state", Context.MODE_PRIVATE);
        boolean isInitialized = installPrefs.getBoolean("app_install_initialized", false);
        final boolean isFreshInstall = !isInitialized;

        if (isFreshInstall) {
            // Fresh install or re-install detected: wipe cloud-restored cookies & web storage so user is never logged in directly
            try {
                android.webkit.CookieManager cookieManager = android.webkit.CookieManager.getInstance();
                cookieManager.removeAllCookies(null);
                cookieManager.flush();
            } catch (Exception ignored) {}

            try {
                android.webkit.WebStorage.getInstance().deleteAllData();
            } catch (Exception ignored) {}

            installPrefs.edit().putBoolean("app_install_initialized", true).apply();
        }

        // Request runtime Android location permissions on app startup
        requestLocationPermissions();

        try {
            if (this.bridge != null && this.bridge.getWebView() != null) {
                final WebView webView = this.bridge.getWebView();

                if (isFreshInstall) {
                    try {
                        webView.clearCache(true);
                        webView.clearFormData();
                        webView.clearHistory();
                    } catch (Exception ignored) {}
                }

                WebSettings settings = webView.getSettings();
                
                // Enable Geolocation, Storage, and Local Asset File Access in WebView
                settings.setGeolocationEnabled(true);
                settings.setGeolocationDatabasePath(getFilesDir().getPath());
                settings.setDomStorageEnabled(true);
                settings.setDatabaseEnabled(true);
                settings.setCacheMode(WebSettings.LOAD_CACHE_ELSE_NETWORK);
                settings.setJavaScriptEnabled(true);
                settings.setJavaScriptCanOpenWindowsAutomatically(true);
                settings.setAllowFileAccess(true);
                settings.setAllowContentAccess(true);
                settings.setAllowFileAccessFromFileURLs(true);
                settings.setAllowUniversalAccessFromFileURLs(true);

                // Add native JavascriptInterface for clean and instant application exit
                webView.addJavascriptInterface(new Object() {
                    @android.webkit.JavascriptInterface
                    public void exitApp() {
                        runOnUiThread(() -> finishAffinity());
                    }
                }, "IntanElyuNative");

                String ua = settings.getUserAgentString();
                if (ua != null) {
                    ua = ua.replace("; wv", "").replace("Version/4.0 ", "");
                    if (!ua.contains("IntanElyuAPK")) {
                        ua = ua + " IntanElyuAPK/1.0";
                    }
                    settings.setUserAgentString(ua);
                }

                // Automatically grant Geolocation permission prompt to WebView origin
                webView.setWebChromeClient(new BridgeWebChromeClient(this.bridge) {
                    @Override
                    public void onGeolocationPermissionsShowPrompt(String origin, GeolocationPermissions.Callback callback) {
                        callback.invoke(origin, true, false);
                    }
                });

                // Custom BridgeWebViewClient: Guaranteed Zero-Failure offline mode & custom URI handler
                webView.setWebViewClient(new BridgeWebViewClient(this.bridge) {
                    private boolean freshCleanDone = false;

                    @Override
                    public void onPageStarted(WebView view, String url, android.graphics.Bitmap favicon) {
                        super.onPageStarted(view, url, favicon);
                        if (isFreshInstall && !freshCleanDone) {
                            freshCleanDone = true;
                            view.evaluateJavascript(
                                "(function() {" +
                                "  try {" +
                                "    localStorage.removeItem('intan_elyu_token');" +
                                "    localStorage.removeItem('Intan_Elyu_Token');" +
                                "    localStorage.removeItem('api_token');" +
                                "    localStorage.removeItem('auth_user');" +
                                "    localStorage.removeItem('intan_elyu_user');" +
                                "    localStorage.removeItem('intan_elyu_guest');" +
                                "    sessionStorage.clear();" +
                                "  } catch(e) {}" +
                                "})()",
                                null
                            );
                        }
                    }
                    private boolean handleCustomUriOrNavigation(WebView view, String url) {
                        if (url == null) return false;

                        // Handle mailto: links natively without crashing WebView with ERR_UNKNOWN_URL_SCHEME
                        if (url.startsWith("mailto:")) {
                            try {
                                Intent mailIntent = new Intent(Intent.ACTION_SENDTO);
                                mailIntent.setData(Uri.parse(url));
                                startActivity(mailIntent);
                            } catch (Exception e) {
                                try {
                                    Intent chooser = Intent.createChooser(new Intent(Intent.ACTION_VIEW, Uri.parse(url)), "Send Email");
                                    startActivity(chooser);
                                } catch (Exception ignored) {}
                            }
                            return true;
                        }

                        // Handle telephone, sms, and map geo links natively
                        if (url.startsWith("tel:") || url.startsWith("sms:") || url.startsWith("geo:")) {
                            try {
                                Intent intent = new Intent(Intent.ACTION_VIEW, Uri.parse(url));
                                startActivity(intent);
                            } catch (Exception ignored) {}
                            return true;
                        }

                        // Explicit offline navigation trigger
                        if (url.contains("offline=true") || url.contains("local-portal")) {
                            loadLocalOfflinePortal(view);
                            return true;
                        }

                        // Open Google OAuth URLs in external system browser to prevent WebView Error 403: disallowed_useragent
                        if (url.contains("accounts.google.com") || url.contains("google.com/o/oauth2")) {
                            try {
                                Intent browserIntent = new Intent(Intent.ACTION_VIEW, Uri.parse(url));
                                startActivity(browserIntent);
                                return true;
                            } catch (Exception ignored) {}
                        }

                        return false;
                    }

                    @Override
                    public boolean shouldOverrideUrlLoading(WebView view, String url) {
                        if (handleCustomUriOrNavigation(view, url)) {
                            return true;
                        }
                        return super.shouldOverrideUrlLoading(view, url);
                    }

                    @Override
                    public boolean shouldOverrideUrlLoading(WebView view, WebResourceRequest request) {
                        if (request != null && request.getUrl() != null) {
                            String url = request.getUrl().toString();
                            if (handleCustomUriOrNavigation(view, url)) {
                                return true;
                            }
                        }
                        return super.shouldOverrideUrlLoading(view, request);
                    }

                    // Network Interceptor: Serves local assets directly with 0 network calls when offline
                    @Override
                    public WebResourceResponse shouldInterceptRequest(WebView view, WebResourceRequest request) {
                        if (request != null && request.getUrl() != null) {
                            Uri uri = request.getUrl();
                            String path = uri.getPath();

                            // 1. Intercept Main Frame navigation when device has no internet
                            if (request.isForMainFrame()) {
                                String urlStr = uri.toString();
                                if (!isNetworkAvailable() || urlStr.contains("offline") || urlStr.contains("local-portal")) {
                                    try {
                                        InputStream is = getAssets().open("public/index.html");
                                        return new WebResourceResponse("text/html", "UTF-8", is);
                                    } catch (Exception ignored) {}
                                }
                            }

                            // 2. Intercept local APK asset bundle (/assets/...) so css/js/images render instantly offline
                            if (path != null && path.contains("/assets/")) {
                                try {
                                    String subPath = path.substring(path.indexOf("/assets/"));
                                    InputStream is = getAssets().open("public" + subPath);
                                    String mimeType = getMimeType(subPath);
                                    return new WebResourceResponse(mimeType, "UTF-8", is);
                                } catch (Exception ignored) {}
                            }
                        }
                        return super.shouldInterceptRequest(view, request);
                    }

                    @Override
                    public WebResourceResponse shouldInterceptRequest(WebView view, String url) {
                        if (url != null) {
                            if (!isNetworkAvailable() || url.contains("offline") || url.contains("local-portal")) {
                                try {
                                    InputStream is = getAssets().open("public/index.html");
                                    return new WebResourceResponse("text/html", "UTF-8", is);
                                } catch (Exception ignored) {}
                            }
                            if (url.contains("/assets/")) {
                                try {
                                    String subPath = url.substring(url.indexOf("/assets/"));
                                    if (subPath.contains("?")) {
                                        subPath = subPath.substring(0, subPath.indexOf("?"));
                                    }
                                    InputStream is = getAssets().open("public" + subPath);
                                    String mimeType = getMimeType(subPath);
                                    return new WebResourceResponse(mimeType, "UTF-8", is);
                                } catch (Exception ignored) {}
                            }
                        }
                        return super.shouldInterceptRequest(view, url);
                    }

                    // Error Suppression: Never display Android "Webpage not available net::ERR_INTERNET_DISCONNECTED"
                    @Override
                    public void onReceivedError(WebView view, int errorCode, String description, String failingUrl) {
                        loadLocalOfflinePortal(view);
                    }

                    @Override
                    public void onReceivedError(WebView view, WebResourceRequest request, WebResourceError error) {
                        if (request != null && request.isForMainFrame()) {
                            loadLocalOfflinePortal(view);
                            return;
                        }
                        super.onReceivedError(view, request, error);
                    }
                });

                // Cold Boot Offline Protection: If launched without internet, load offline catalog bundle immediately
                if (!isNetworkAvailable()) {
                    loadLocalOfflinePortal(webView);
                }

                // Register network monitor to smoothly transition to live cloud app when internet returns
                registerNetworkCallback(webView);
            }
        } catch (Exception e) {
            e.printStackTrace();
        }

        handleIntent(getIntent());
    }

    private String lastHandledUri = null;
    private long lastHandledUriTime = 0;

    @Override
    protected void onNewIntent(Intent intent) {
        super.onNewIntent(intent);
        setIntent(intent);
        handleIntent(intent);
    }

    private void handleIntent(Intent intent) {
        if (intent == null || intent.getData() == null) return;
        Uri data = intent.getData();
        String uriStr = data.toString();
        
        long now = System.currentTimeMillis();
        if (uriStr.equals(lastHandledUri) && (now - lastHandledUriTime) < 3000) {
            return;
        }
        lastHandledUri = uriStr;
        lastHandledUriTime = now;
        
        try {
            if (this.bridge != null && this.bridge.getWebView() != null) {
                WebView webView = this.bridge.getWebView();
                
                if (uriStr.startsWith("intanelyu://") || uriStr.startsWith("com.intan.elyu://")) {
                    String queryOrPath = "";
                    if (uriStr.contains("?")) {
                        queryOrPath = uriStr.substring(uriStr.indexOf("?"));
                    } else if (uriStr.contains("#")) {
                        queryOrPath = uriStr.substring(uriStr.indexOf("#"));
                    }
                    final String targetUrl = "https://app.intan-elyu.online/index.php" + queryOrPath;
                    final String safeUri = uriStr.replace("'", "\\'");
                    
                    webView.post(() -> {
                        webView.evaluateJavascript(
                            "try {" +
                            "  if (window.Capacitor && window.Capacitor.Plugins && window.Capacitor.Plugins.Browser) {" +
                            "    window.Capacitor.Plugins.Browser.close().catch(function(){});" +
                            "  }" +
                            "  if (typeof window.initGoogleOAuthHandler === 'function') {" +
                            "    window.initGoogleOAuthHandler('" + safeUri + "');" +
                            "  } else {" +
                            "    window.location.href = '" + targetUrl + "';" +
                            "  }" +
                            "} catch(e) {" +
                            "  window.location.href = '" + targetUrl + "';" +
                            "}",
                            null
                        );
                    });
                } else if (uriStr.contains("app.intan-elyu.online")) {
                    final String targetUrl = uriStr;
                    final String safeUri = uriStr.replace("'", "\\'");
                    webView.post(() -> {
                        if (safeUri.contains("access_token=") || safeUri.contains("id_token=")) {
                            webView.evaluateJavascript(
                                "try {" +
                                "  if (window.Capacitor && window.Capacitor.Plugins && window.Capacitor.Plugins.Browser) {" +
                                "    window.Capacitor.Plugins.Browser.close().catch(function(){});" +
                                "  }" +
                                "  if (typeof window.initGoogleOAuthHandler === 'function') {" +
                                "    window.initGoogleOAuthHandler('" + safeUri + "');" +
                                "  } else {" +
                                "    window.location.href = '" + targetUrl + "';" +
                                "  }" +
                                "} catch(e) {" +
                                "  window.location.href = '" + targetUrl + "';" +
                                "}",
                                null
                            );
                        } else {
                            webView.loadUrl(targetUrl);
                        }
                    });
                }
            }
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    private void requestLocationPermissions() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M) {
            boolean fineLocationNotGranted = ContextCompat.checkSelfPermission(this, Manifest.permission.ACCESS_FINE_LOCATION) != PackageManager.PERMISSION_GRANTED;
            boolean coarseLocationNotGranted = ContextCompat.checkSelfPermission(this, Manifest.permission.ACCESS_COARSE_LOCATION) != PackageManager.PERMISSION_GRANTED;

            if (fineLocationNotGranted || coarseLocationNotGranted) {
                ActivityCompat.requestPermissions(
                    this,
                    new String[]{
                        Manifest.permission.ACCESS_FINE_LOCATION,
                        Manifest.permission.ACCESS_COARSE_LOCATION
                    },
                    LOCATION_PERMISSION_REQUEST_CODE
                );
            }
        }
    }

    private long lastBackPressTime = 0;

    @Override
    public void onBackPressed() {
        try {
            if (this.bridge != null && this.bridge.getWebView() != null) {
                final WebView webView = this.bridge.getWebView();
                String currentUrl = webView.getUrl();
                if (currentUrl != null && (currentUrl.contains("accounts.google.com") || currentUrl.contains("google.com"))) {
                    if (webView.canGoBack()) {
                        webView.goBack();
                        return;
                    } else {
                        webView.loadUrl("https://app.intan-elyu.online/index.php?view=auth");
                        return;
                    }
                }

                // Delegate to JavaScript's universal back handler (handles modals, sheets, and view navigation)
                webView.evaluateJavascript(
                    "(function() {" +
                    "  try {" +
                    "    if (typeof window.handleAppBack === 'function') {" +
                    "      return window.handleAppBack() ? 'HANDLED' : 'EXIT';" +
                    "    } else if (typeof window.navigateBack === 'function') {" +
                    "      window.navigateBack();" +
                    "      return 'HANDLED';" +
                    "    }" +
                    "  } catch(e) {}" +
                    "  return 'EXIT';" +
                    "})()",
                    value -> {
                        String res = value != null ? value.replace("\"", "").trim() : "EXIT";
                        if ("EXIT".equalsIgnoreCase(res)) {
                            long now = System.currentTimeMillis();
                            if (now - lastBackPressTime < 2000) {
                                finishAffinity();
                            } else {
                                lastBackPressTime = now;
                                android.widget.Toast.makeText(MainActivity.this, "Press back again to exit", android.widget.Toast.LENGTH_SHORT).show();
                            }
                        }
                    }
                );
                return;
            }
        } catch (Exception e) {
            e.printStackTrace();
        }

        // Native double-back fallback
        long now = System.currentTimeMillis();
        if (now - lastBackPressTime < 2000) {
            finishAffinity();
        } else {
            lastBackPressTime = now;
            android.widget.Toast.makeText(this, "Press back again to exit", android.widget.Toast.LENGTH_SHORT).show();
        }
    }

    /**
     * Guaranteed Zero-Failure Local Asset Loader: Reads public/index.html directly from APK assets
     * and injects it into WebView via loadDataWithBaseURL with zero network / DNS overhead.
     */
    private void loadLocalOfflinePortal(final WebView webView) {
        if (webView == null) return;
        runOnUiThread(() -> {
            try {
                InputStream is = getAssets().open("public/index.html");
                BufferedReader reader = new BufferedReader(new InputStreamReader(is, StandardCharsets.UTF_8));
                StringBuilder sb = new StringBuilder();
                String line;
                while ((line = reader.readLine()) != null) {
                    sb.append(line).append("\n");
                }
                reader.close();
                is.close();
                String html = sb.toString();
                webView.loadDataWithBaseURL("https://app.intan-elyu.online/", html, "text/html", "UTF-8", "https://app.intan-elyu.online/?offline=true");
            } catch (Exception e) {
                try {
                    webView.loadUrl("file:///android_asset/public/index.html");
                } catch (Exception ignored) {}
            }
        });
    }

    private String getMimeType(String path) {
        if (path == null) return "text/plain";
        String lower = path.toLowerCase();
        if (lower.endsWith(".html") || lower.endsWith(".htm")) return "text/html";
        if (lower.endsWith(".css")) return "text/css";
        if (lower.endsWith(".js")) return "application/javascript";
        if (lower.endsWith(".png")) return "image/png";
        if (lower.endsWith(".jpg") || lower.endsWith(".jpeg")) return "image/jpeg";
        if (lower.endsWith(".webp")) return "image/webp";
        if (lower.endsWith(".svg")) return "image/svg+xml";
        if (lower.endsWith(".json")) return "application/json";
        if (lower.endsWith(".woff")) return "font/woff";
        if (lower.endsWith(".woff2")) return "font/woff2";
        if (lower.endsWith(".ttf")) return "font/ttf";
        return "application/octet-stream";
    }

    private boolean isNetworkAvailable() {
        try {
            ConnectivityManager cm = (ConnectivityManager) getSystemService(Context.CONNECTIVITY_SERVICE);
            if (cm == null) return false;
            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M) {
                Network net = cm.getActiveNetwork();
                if (net == null) return false;
                NetworkCapabilities caps = cm.getNetworkCapabilities(net);
                return caps != null && caps.hasCapability(NetworkCapabilities.NET_CAPABILITY_INTERNET);
            } else {
                NetworkInfo info = cm.getActiveNetworkInfo();
                return info != null && info.isConnected();
            }
        } catch (Exception e) {
            return false;
        }
    }

    private void registerNetworkCallback(final WebView webView) {
        try {
            ConnectivityManager cm = (ConnectivityManager) getSystemService(Context.CONNECTIVITY_SERVICE);
            if (cm != null && Build.VERSION.SDK_INT >= Build.VERSION_CODES.N) {
                cm.registerDefaultNetworkCallback(new ConnectivityManager.NetworkCallback() {
                    @Override
                    public void onAvailable(Network network) {
                        runOnUiThread(() -> {
                            try {
                                if (webView != null) {
                                    String curUrl = webView.getUrl();
                                    if (curUrl != null && (curUrl.contains("offline") || curUrl.contains("data:text/html") || curUrl.startsWith("file:///"))) {
                                        webView.getSettings().setCacheMode(WebSettings.LOAD_DEFAULT);
                                        webView.loadUrl("https://app.intan-elyu.online?app=true");
                                    }
                                }
                            } catch (Exception ignored) {}
                        });
                    }
                });
            }
        } catch (Exception ignored) {}
    }
}
