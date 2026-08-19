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
import android.webkit.WebResourceRequest;
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
                
                // Enable Geolocation & Storage in WebView
                settings.setGeolocationEnabled(true);
                settings.setGeolocationDatabasePath(getFilesDir().getPath());
                settings.setDomStorageEnabled(true);
                settings.setDatabaseEnabled(true);
                settings.setJavaScriptEnabled(true);
                settings.setJavaScriptCanOpenWindowsAutomatically(true);

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

                // Keep all navigations (auth, google, app) strictly inside this APK WebView
                webView.setWebViewClient(new BridgeWebViewClient(this.bridge) {
                    @Override
                    public boolean shouldOverrideUrlLoading(WebView view, String url) {
                        if (url != null && (
                            url.contains("intan-elyu.online") || 
                            url.contains("accounts.google.com") || 
                            url.contains("google.com") || 
                            url.contains("googleapis.com") ||
                            url.contains("localhost")
                        )) {
                            view.loadUrl(url);
                            return true;
                        }
                        return super.shouldOverrideUrlLoading(view, url);
                    }

                    @Override
                    public boolean shouldOverrideUrlLoading(WebView view, WebResourceRequest request) {
                        if (request != null && request.getUrl() != null) {
                            String url = request.getUrl().toString();
                            if (url.contains("intan-elyu.online") || 
                                url.contains("accounts.google.com") || 
                                url.contains("google.com") || 
                                url.contains("googleapis.com") ||
                                url.contains("localhost")) {
                                view.loadUrl(url);
                                return true;
                            }
                        }
                        return super.shouldOverrideUrlLoading(view, request);
                    }
                });
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
}
