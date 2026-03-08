<?php
/**
 * Google Auth Deep Link Proxy for Mobile Devices
 * This script catches the Google OAuth callback and executes a frontend JS redirect
 * to force the mobile Expo browser (WebBrowser.openAuthSessionAsync) to snap securely shut,
 * returning the Google tokens to the React Native app.
 */

// We don't need strict headers because we are just returning an HTML redirect page.

// Note: Google redirects back using the Anchor Hash Fragment (#id_token=...&access_token=...) 
// because we use response_type=id_token. PHP does not have access to window.location.hash on the server-side, 
// so the redirect must be executed purely via client-side JavaScript.

$appScheme = isset($_GET['scheme']) ? preg_replace('/[^a-zA-Z0-9]/', '', $_GET['scheme']) : 'leilife';
$appRedirect = $appScheme . "://google-login"; // fallback deep link

?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Logging in...</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; text-align: center; padding: 40px; color: #3E2723; }
        .loader { border: 4px solid #f3f3f3; border-top: 4px solid #3E2723; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 20px auto; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <h2>Authorizing...</h2>
    <div class="loader"></div>
    <p>Please wait, you are being redirected back to the app.</p>

    <script>
        // Extract the Google OAuth parameters from the URL hash
        var hash = window.location.hash; // e.g., #state=...&id_token=...
        var search = window.location.search; // e.g., ?state=... (fallback)
        
        // Try to see if the React Native app passed a dynamic deep link inside the state param
        // state would contain base64 encoded app_redirect
        var params = new URLSearchParams(hash.substring(1) || search.substring(1));
        var state = params.get('state');
        
        var baseRedirect = "<?= $appRedirect ?>";
        
        if (state) {
            try {
                // If the app passed a specific return URL (like exp://... in Expo Go)
                // it would be in the state variable encoded.
                var decodedState = decodeURIComponent(state);
                if (decodedState.startsWith("exp://") || decodedState.startsWith("leilife://")) {
                    baseRedirect = decodedState;
                }
            } catch (e) {
                console.error("Failed to decode state URL");
            }
        }

        // Combine the deep link scheme directly with the Google query params so React Native can parse it
        var finalDeepLink = baseRedirect;
        
        // Ensure there is a question mark between the base url and parameters
        if (hash) {
           var paramString = hash.startsWith('#') ? hash.substring(1) : hash;
           finalDeepLink += (finalDeepLink.indexOf('?') === -1 ? '?' : '&') + paramString;
        } else if (search) {
           var paramString = search.startsWith('?') ? search.substring(1) : search;
           finalDeepLink += (finalDeepLink.indexOf('?') === -1 ? '?' : '&') + paramString;
        }

        console.log("Redirecting to: " + finalDeepLink);
        
        // Push the browser explicitly to the mobile deep link scheme to force the window shut
        window.location.href = finalDeepLink;

        // Fallback if browser blocks automatic JS redirect
        setTimeout(function() {
            document.body.innerHTML += '<p>If it does not load automatically, <a href="' + finalDeepLink + '">Click Here</a></p>';
        }, 2000);
    </script>
</body>
</html>
