package com.intan.elyu;

import android.Manifest;
import android.content.Intent;
import android.content.pm.PackageManager;
import android.net.Uri;
import android.os.Build;
import android.os.Bundle;
import android.webkit.GeolocationPermissions;
import android.webkit.WebSettings;
import android.webkit.WebView;
import android.webkit.WebResourceError;
import android.webkit.WebResourceRequest;
import android.content.Context;
import android.net.ConnectivityManager;
import android.net.Network;
import android.net.NetworkCapabilities;
import android.net.NetworkInfo;
import androidx.core.app.ActivityCompat;
import androidx.core.content.ContextCompat;
import com.getcapacitor.BridgeActivity;
import com.getcapacitor.BridgeWebChromeClient;
import com.getcapacitor.BridgeWebViewClient;

public class MainActivity extends BridgeActivity {
    private static final int LOCATION_PERMISSION_REQUEST_CODE = 1001;

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        
        // Request runtime Android location permissions on app startup
        requestLocationPermissions();

        try {
            if (this.bridge != null && this.bridge.getWebView() != null) {
                WebView webView = this.bridge.getWebView();
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

                // Keep all navigations (auth, google, app, offline portal) strictly inside this APK WebView
                webView.setWebViewClient(new BridgeWebViewClient(this.bridge) {
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

                        // Keep in-app web pages and offline portal inside the WebView
                        if (url.startsWith("http://") || url.startsWith("https://") || url.startsWith("file:///")) {
                            if (url.contains("intan-elyu.online") || 
                                url.contains("accounts.google.com") || 
                                url.contains("google.com") || 
                                url.contains("googleapis.com") ||
                                url.contains("localhost") ||
                                url.startsWith("file:///")) {
                                view.loadUrl(url);
                                return true;
                            }
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

                    @Override
                    public void onReceivedError(WebView view, int errorCode, String description, String failingUrl) {
                        if (failingUrl != null && (failingUrl.contains("intan-elyu.online") || failingUrl.startsWith("http"))) {
                            // Suppress system error page and seamlessly load local offline app
                            final String offlinePortal = getOfflinePortalUrl();
                            view.post(() -> view.loadUrl(offlinePortal));
                            return;
                        }
                        super.onReceivedError(view, errorCode, description, failingUrl);
                    }

                    @Override
                    public void onReceivedError(WebView view, WebResourceRequest request, WebResourceError error) {
                        if (request != null && request.isForMainFrame()) {
                            String failingUrl = request.getUrl() != null ? request.getUrl().toString() : "";
                            if (failingUrl.contains("intan-elyu.online") || failingUrl.startsWith("http")) {
                                // Suppress system error page and seamlessly load local offline app
                                final String offlinePortal = getOfflinePortalUrl();
                                view.post(() -> view.loadUrl(offlinePortal));
                                return;
                            }
                        }
                        super.onReceivedError(view, request, error);
                    }
                });

                // Cold Boot Offline Protection: If launched without internet, load local offline bundle immediately
                if (!isNetworkAvailable()) {
                    final String offlinePortal = getOfflinePortalUrl();
                    webView.post(() -> webView.loadUrl(offlinePortal));
                }

                // Register network monitor to smoothly transition to live cloud app when internet returns
                registerNetworkCallback(webView);
            }
        } catch (Exception e) {
            e.printStackTrace();
        }

        handleIntent(getIntent());
    }

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
                    webView.post(() -> webView.loadUrl(targetUrl));
                } else if (uriStr.contains("app.intan-elyu.online")) {
                    final String targetUrl = uriStr;
                    webView.post(() -> webView.loadUrl(targetUrl));
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

    @Override
    public void onBackPressed() {
        try {
            if (this.bridge != null && this.bridge.getWebView() != null) {
                WebView webView = this.bridge.getWebView();
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
            }
        } catch (Exception e) {
            e.printStackTrace();
        }
        super.onBackPressed();
    }

    private String getOfflinePortalUrl() {
        if (this.bridge != null && this.bridge.getLocalUrl() != null) {
            String local = this.bridge.getLocalUrl();
            if (local.endsWith("/")) {
                return local + "index.html";
            }
            return local + "/index.html";
        }
        return "https://localhost/index.html";
    }

    private boolean isNetworkAvailable() {
        try {
            ConnectivityManager cm = (ConnectivityManager) getSystemService(Context.CONNECTIVITY_SERVICE);
            if (cm == null) return false;
            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M) {
                Network net = cm.getActiveNetwork();
                if (net == null) return false;
                NetworkCapabilities caps = cm.getNetworkCapabilities(net);
                return caps != null && (caps.hasCapability(NetworkCapabilities.NET_CAPABILITY_INTERNET) &&
                                        caps.hasCapability(NetworkCapabilities.NET_CAPABILITY_VALIDATED));
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
                                    if (curUrl != null && (curUrl.contains("localhost") || curUrl.startsWith("file:///"))) {
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
