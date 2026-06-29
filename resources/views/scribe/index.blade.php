<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="IE=edge,chrome=1" http-equiv="X-UA-Compatible">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>Laravel API Documentation</title>

    <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset("/vendor/scribe/css/theme-default.style.css") }}" media="screen">
    <link rel="stylesheet" href="{{ asset("/vendor/scribe/css/theme-default.print.css") }}" media="print">

    <script src="https://cdn.jsdelivr.net/npm/lodash@4.17.10/lodash.min.js"></script>

    <link rel="stylesheet"
          href="https://unpkg.com/@highlightjs/cdn-assets@11.6.0/styles/obsidian.min.css">
    <script src="https://unpkg.com/@highlightjs/cdn-assets@11.6.0/highlight.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jets/0.14.1/jets.min.js"></script>

    <style id="language-style">
        /* starts out as display none and is replaced with js later  */
                    body .content .bash-example code { display: none; }
                    body .content .javascript-example code { display: none; }
                    body .content .php-example code { display: none; }
            </style>

    <script>
        var tryItOutBaseUrl = "http://localhost:8000";
        var useCsrf = Boolean();
        var csrfUrl = "/sanctum/csrf-cookie";
    </script>
    <script src="{{ asset("/vendor/scribe/js/tryitout-5.8.0.js") }}"></script>

    <script src="{{ asset("/vendor/scribe/js/theme-default-5.8.0.js") }}"></script>

</head>

<body data-languages="[&quot;bash&quot;,&quot;javascript&quot;,&quot;php&quot;]">

<a href="#" id="nav-button">
    <span>
        MENU
        <img src="{{ asset("/vendor/scribe/images/navbar.png") }}" alt="navbar-image"/>
    </span>
</a>
<div class="tocify-wrapper">
    
            <div class="lang-selector">
                                            <button type="button" class="lang-button" data-language-name="bash">bash</button>
                                            <button type="button" class="lang-button" data-language-name="javascript">javascript</button>
                                            <button type="button" class="lang-button" data-language-name="php">php</button>
                    </div>
    
    <div class="search">
        <input type="text" class="search" id="input-search" placeholder="Search">
    </div>

    <div id="toc">
                    <ul id="tocify-header-introduction" class="tocify-header">
                <li class="tocify-item level-1" data-unique="introduction">
                    <a href="#introduction">Introduction</a>
                </li>
                            </ul>
                    <ul id="tocify-header-authenticating-requests" class="tocify-header">
                <li class="tocify-item level-1" data-unique="authenticating-requests">
                    <a href="#authenticating-requests">Authenticating requests</a>
                </li>
                            </ul>
                    <ul id="tocify-header-authentication" class="tocify-header">
                <li class="tocify-item level-1" data-unique="authentication">
                    <a href="#authentication">Authentication</a>
                </li>
                                    <ul id="tocify-subheader-authentication" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="authentication-POSTapi-v1-auth-send-otp">
                                <a href="#authentication-POSTapi-v1-auth-send-otp">Send OTP</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="authentication-POSTapi-v1-auth-verify-otp">
                                <a href="#authentication-POSTapi-v1-auth-verify-otp">Verify OTP</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="authentication-POSTapi-v1-auth-register">
                                <a href="#authentication-POSTapi-v1-auth-register">Complete Registration</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="authentication-POSTapi-v1-auth-admin-login">
                                <a href="#authentication-POSTapi-v1-auth-admin-login">Admin Login</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="authentication-POSTapi-v1-auth-logout">
                                <a href="#authentication-POSTapi-v1-auth-logout">Logout</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-car" class="tocify-header">
                <li class="tocify-item level-1" data-unique="car">
                    <a href="#car">Car</a>
                </li>
                                    <ul id="tocify-subheader-car" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="car-GETapi-v1-vehicles">
                                <a href="#car-GETapi-v1-vehicles">List Vehicles</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="car-POSTapi-v1-vehicles">
                                <a href="#car-POSTapi-v1-vehicles">Store Vehicle</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="car-GETapi-v1-vehicles--id_id-">
                                <a href="#car-GETapi-v1-vehicles--id_id-">Show Vehicle</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="car-PUTapi-v1-vehicles--id_id-">
                                <a href="#car-PUTapi-v1-vehicles--id_id-">Update Vehicle</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="car-DELETEapi-v1-vehicles--id_id-">
                                <a href="#car-DELETEapi-v1-vehicles--id_id-">Delete Vehicle</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="car-car-type">
                                <a href="#car-car-type">Car Type</a>
                            </li>
                                                            <ul id="tocify-subheader-car-car-type" class="tocify-subheader">
                                                                            <li class="tocify-item level-3" data-unique="car-GETapi-v1-vehicle-types">
                                            <a href="#car-GETapi-v1-vehicle-types">List Vehicle Types</a>
                                        </li>
                                                                            <li class="tocify-item level-3" data-unique="car-POSTapi-v1-vehicle-types">
                                            <a href="#car-POSTapi-v1-vehicle-types">Store Vehicle Type</a>
                                        </li>
                                                                            <li class="tocify-item level-3" data-unique="car-GETapi-v1-vehicle-types--id_id-">
                                            <a href="#car-GETapi-v1-vehicle-types--id_id-">Show Vehicle Type</a>
                                        </li>
                                                                            <li class="tocify-item level-3" data-unique="car-PUTapi-v1-vehicle-types--id_id-">
                                            <a href="#car-PUTapi-v1-vehicle-types--id_id-">Update Vehicle Type</a>
                                        </li>
                                                                            <li class="tocify-item level-3" data-unique="car-DELETEapi-v1-vehicle-types--id_id-">
                                            <a href="#car-DELETEapi-v1-vehicle-types--id_id-">Delete Vehicle Type</a>
                                        </li>
                                                                    </ul>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-invoice-management" class="tocify-header">
                <li class="tocify-item level-1" data-unique="invoice-management">
                    <a href="#invoice-management">Invoice management</a>
                </li>
                                    <ul id="tocify-subheader-invoice-management" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="invoice-management-GETapi-v1-invoices">
                                <a href="#invoice-management-GETapi-v1-invoices">List Invoices</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="invoice-management-POSTapi-v1-invoices">
                                <a href="#invoice-management-POSTapi-v1-invoices">Store Invoice</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="invoice-management-GETapi-v1-invoices--id_id-">
                                <a href="#invoice-management-GETapi-v1-invoices--id_id-">Show Invoice</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="invoice-management-DELETEapi-v1-invoices--id_id-">
                                <a href="#invoice-management-DELETEapi-v1-invoices--id_id-">Delete Invoice</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-license-plate-management" class="tocify-header">
                <li class="tocify-item level-1" data-unique="license-plate-management">
                    <a href="#license-plate-management">License Plate management</a>
                </li>
                                    <ul id="tocify-subheader-license-plate-management" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="license-plate-management-GETapi-v1-license-plates">
                                <a href="#license-plate-management-GETapi-v1-license-plates">List License Plates</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="license-plate-management-POSTapi-v1-license-plates">
                                <a href="#license-plate-management-POSTapi-v1-license-plates">Store License Plate</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="license-plate-management-GETapi-v1-license-plates--id_id-">
                                <a href="#license-plate-management-GETapi-v1-license-plates--id_id-">Show License Plate</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="license-plate-management-PUTapi-v1-license-plates--id_id-">
                                <a href="#license-plate-management-PUTapi-v1-license-plates--id_id-">Update License Plate</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="license-plate-management-DELETEapi-v1-license-plates--id_id-">
                                <a href="#license-plate-management-DELETEapi-v1-license-plates--id_id-">Delete License Plate</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-organization-management" class="tocify-header">
                <li class="tocify-item level-1" data-unique="organization-management">
                    <a href="#organization-management">Organization management</a>
                </li>
                                    <ul id="tocify-subheader-organization-management" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="organization-management-GETapi-v1-organizations">
                                <a href="#organization-management-GETapi-v1-organizations">List Organizations</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="organization-management-POSTapi-v1-organizations">
                                <a href="#organization-management-POSTapi-v1-organizations">Store Organization</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="organization-management-GETapi-v1-organizations--id_id-">
                                <a href="#organization-management-GETapi-v1-organizations--id_id-">Show Organization</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="organization-management-PUTapi-v1-organizations--id_id-">
                                <a href="#organization-management-PUTapi-v1-organizations--id_id-">Update Organization</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="organization-management-DELETEapi-v1-organizations--id_id-">
                                <a href="#organization-management-DELETEapi-v1-organizations--id_id-">Delete Organization</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-service-management" class="tocify-header">
                <li class="tocify-item level-1" data-unique="service-management">
                    <a href="#service-management">Service management</a>
                </li>
                                    <ul id="tocify-subheader-service-management" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="service-management-GETapi-v1-services">
                                <a href="#service-management-GETapi-v1-services">List Services</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="service-management-POSTapi-v1-services">
                                <a href="#service-management-POSTapi-v1-services">Store Service</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="service-management-GETapi-v1-services--id_id-">
                                <a href="#service-management-GETapi-v1-services--id_id-">Show Service</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="service-management-PUTapi-v1-services--id_id-">
                                <a href="#service-management-PUTapi-v1-services--id_id-">Update Service</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="service-management-DELETEapi-v1-services--id_id-">
                                <a href="#service-management-DELETEapi-v1-services--id_id-">Delete Service</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-user-management" class="tocify-header">
                <li class="tocify-item level-1" data-unique="user-management">
                    <a href="#user-management">User management</a>
                </li>
                                    <ul id="tocify-subheader-user-management" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="user-management-GETapi-v1-users">
                                <a href="#user-management-GETapi-v1-users">List Users</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="user-management-POSTapi-v1-users">
                                <a href="#user-management-POSTapi-v1-users">Store User</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="user-management-GETapi-v1-users--id_id-">
                                <a href="#user-management-GETapi-v1-users--id_id-">Show User</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="user-management-PUTapi-v1-users--id_id-">
                                <a href="#user-management-PUTapi-v1-users--id_id-">Update User</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="user-management-DELETEapi-v1-users--id_id-">
                                <a href="#user-management-DELETEapi-v1-users--id_id-">Delete User</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-wash-ticket-management" class="tocify-header">
                <li class="tocify-item level-1" data-unique="wash-ticket-management">
                    <a href="#wash-ticket-management">Wash Ticket management</a>
                </li>
                                    <ul id="tocify-subheader-wash-ticket-management" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="wash-ticket-management-GETapi-v1-wash-tickets">
                                <a href="#wash-ticket-management-GETapi-v1-wash-tickets">List Wash Tickets</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="wash-ticket-management-POSTapi-v1-wash-tickets">
                                <a href="#wash-ticket-management-POSTapi-v1-wash-tickets">Store Wash Ticket</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="wash-ticket-management-GETapi-v1-wash-tickets--id_id-">
                                <a href="#wash-ticket-management-GETapi-v1-wash-tickets--id_id-">Show Wash Ticket</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="wash-ticket-management-PUTapi-v1-wash-tickets--id_id-">
                                <a href="#wash-ticket-management-PUTapi-v1-wash-tickets--id_id-">Update Wash Ticket</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="wash-ticket-management-DELETEapi-v1-wash-tickets--id_id-">
                                <a href="#wash-ticket-management-DELETEapi-v1-wash-tickets--id_id-">Delete Wash Ticket</a>
                            </li>
                                                                        </ul>
                            </ul>
            </div>

    <ul class="toc-footer" id="toc-footer">
                    <li style="padding-bottom: 5px;"><a href="{{ route("scribe.postman") }}">View Postman collection</a></li>
                            <li style="padding-bottom: 5px;"><a href="{{ route("scribe.openapi") }}">View OpenAPI spec</a></li>
                <li><a href="http://github.com/knuckleswtf/scribe">Documentation powered by Scribe ✍</a></li>
    </ul>

    <ul class="toc-footer" id="last-updated">
        <li>Last updated: March 6, 2026</li>
    </ul>
</div>

<div class="page-wrapper">
    <div class="dark-box"></div>
    <div class="content">
        <h1 id="introduction">Introduction</h1>
<aside>
    <strong>Base URL</strong>: <code>http://localhost:8000</code>
</aside>
<pre><code>This documentation aims to provide all the information you need to work with our API.

&lt;aside&gt;As you scroll, you'll see code examples for working with the API in different programming languages in the dark area to the right (or as part of the content on mobile).
You can switch the language used with the tabs at the top right (or from the nav menu at the top left on mobile).&lt;/aside&gt;</code></pre>

        <h1 id="authenticating-requests">Authenticating requests</h1>
<p>This API is not authenticated.</p>

        <h1 id="authentication">Authentication</h1>

    <p>Handles user authentication via OTP, registration, admin login, and logout.</p>

                                <h2 id="authentication-POSTapi-v1-auth-send-otp">Send OTP</h2>

<p>
</p>

<p>Generates and sends an OTP code to the provided phone number.
Used for both login and registration flows.</p>

<span id="example-requests-POSTapi-v1-auth-send-otp">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/auth/send-otp" \
    --header "X-Device-Name: mobile" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"phone\": \"+22890123456\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/auth/send-otp"
);

const headers = {
    "X-Device-Name": "mobile",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "phone": "+22890123456"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/auth/send-otp';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'X-Device-Name' =&gt; 'mobile',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'phone' =&gt; '+22890123456',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-auth-send-otp">
            <blockquote>
            <p>Example response (200, OTP sent):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;OTP sent successfully.&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (429, Cooldown active):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Please wait 60 seconds before requesting a new OTP.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-v1-auth-send-otp" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-auth-send-otp"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-auth-send-otp"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-auth-send-otp" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-auth-send-otp">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-auth-send-otp" data-method="POST"
      data-path="api/v1/auth/send-otp"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-auth-send-otp', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-auth-send-otp"
                    onclick="tryItOut('POSTapi-v1-auth-send-otp');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-auth-send-otp"
                    onclick="cancelTryOut('POSTapi-v1-auth-send-otp');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-auth-send-otp"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/auth/send-otp</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-Device-Name</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-Device-Name"                data-endpoint="POSTapi-v1-auth-send-otp"
               value="mobile"
               data-component="header">
    <br>
<p>Example: <code>mobile</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-auth-send-otp"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-auth-send-otp"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>phone</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="phone"                data-endpoint="POSTapi-v1-auth-send-otp"
               value="+22890123456"
               data-component="body">
    <br>
<p>The user phone number in Togolese format. Must match the regex /^+228[0-9]{8}$/. Example: <code>+22890123456</code></p>
        </div>
        </form>

                    <h2 id="authentication-POSTapi-v1-auth-verify-otp">Verify OTP</h2>

<p>
</p>

<p>Verifies the OTP code received by the user.</p>
<ul>
<li>If the phone number is <strong>new</strong> → returns <code>is_new_user: true</code> and the phone
number to redirect the user to the registration flow.</li>
<li>If the phone number is <strong>known</strong> → returns an access token and user data.</li>
</ul>

<span id="example-requests-POSTapi-v1-auth-verify-otp">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/auth/verify-otp" \
    --header "X-Device-Name: mobile" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"phone\": \"+22890123456\",
    \"code\": \"482910\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/auth/verify-otp"
);

const headers = {
    "X-Device-Name": "mobile",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "phone": "+22890123456",
    "code": "482910"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/auth/verify-otp';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'X-Device-Name' =&gt; 'mobile',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'phone' =&gt; '+22890123456',
            'code' =&gt; '482910',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-auth-verify-otp">
            <blockquote>
            <p>Example response (200, Existing user):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;is_new_user&quot;: false,
    &quot;token&quot;: &quot;1|abc123...&quot;,
    &quot;user&quot;: {
        &quot;id&quot;: 1,
        &quot;first_name&quot;: &quot;Simon&quot;,
        &quot;phone&quot;: &quot;+22890000000&quot;
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (200, New user):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;is_new_user&quot;: true,
    &quot;phone&quot;: &quot;+22890000000&quot;,
    &quot;message&quot;: &quot;Phone verified. Please complete your registration.&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (422, Invalid or expired OTP):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Invalid or expired OTP code.&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (422, Wrong OTP code):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Incorrect OTP code. 4 attempt(s) remaining.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-v1-auth-verify-otp" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-auth-verify-otp"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-auth-verify-otp"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-auth-verify-otp" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-auth-verify-otp">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-auth-verify-otp" data-method="POST"
      data-path="api/v1/auth/verify-otp"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-auth-verify-otp', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-auth-verify-otp"
                    onclick="tryItOut('POSTapi-v1-auth-verify-otp');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-auth-verify-otp"
                    onclick="cancelTryOut('POSTapi-v1-auth-verify-otp');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-auth-verify-otp"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/auth/verify-otp</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-Device-Name</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-Device-Name"                data-endpoint="POSTapi-v1-auth-verify-otp"
               value="mobile"
               data-component="header">
    <br>
<p>Example: <code>mobile</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-auth-verify-otp"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-auth-verify-otp"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>phone</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="phone"                data-endpoint="POSTapi-v1-auth-verify-otp"
               value="+22890123456"
               data-component="body">
    <br>
<p>The user phone number in Togolese format. Must match the regex /^+228[0-9]{8}$/. Example: <code>+22890123456</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>code</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="code"                data-endpoint="POSTapi-v1-auth-verify-otp"
               value="482910"
               data-component="body">
    <br>
<p>The 6-digit OTP code received by the user. Must be 6 digits. Example: <code>482910</code></p>
        </div>
        </form>

                    <h2 id="authentication-POSTapi-v1-auth-register">Complete Registration</h2>

<p>
</p>

<p>Finalizes account creation after OTP verification.
The phone number must have been verified within the last 15 minutes.</p>

<span id="example-requests-POSTapi-v1-auth-register">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/auth/register" \
    --header "X-Device-Name: mobile" \
    --header "Content-Type: multipart/form-data" \
    --header "Accept: application/json" \
    --form "phone=+22890123456"\
    --form "first_name=Simon"\
    --form "last_name=Dev"\
    --form "address=Lomé, Togo"\
    --form "birthday=1990-01-01"\
    --form "gender=male"\
    --form "image=@C:\Users\Lenovo\AppData\Local\Temp\php9069.tmp" </code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/auth/register"
);

const headers = {
    "X-Device-Name": "mobile",
    "Content-Type": "multipart/form-data",
    "Accept": "application/json",
};

const body = new FormData();
body.append('phone', '+22890123456');
body.append('first_name', 'Simon');
body.append('last_name', 'Dev');
body.append('address', 'Lomé, Togo');
body.append('birthday', '1990-01-01');
body.append('gender', 'male');
body.append('image', document.querySelector('input[name="image"]').files[0]);

fetch(url, {
    method: "POST",
    headers,
    body,
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/auth/register';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'X-Device-Name' =&gt; 'mobile',
            'Content-Type' =&gt; 'multipart/form-data',
            'Accept' =&gt; 'application/json',
        ],
        'multipart' =&gt; [
            [
                'name' =&gt; 'phone',
                'contents' =&gt; '+22890123456'
            ],
            [
                'name' =&gt; 'first_name',
                'contents' =&gt; 'Simon'
            ],
            [
                'name' =&gt; 'last_name',
                'contents' =&gt; 'Dev'
            ],
            [
                'name' =&gt; 'address',
                'contents' =&gt; 'Lomé, Togo'
            ],
            [
                'name' =&gt; 'birthday',
                'contents' =&gt; '1990-01-01'
            ],
            [
                'name' =&gt; 'gender',
                'contents' =&gt; 'male'
            ],
            [
                'name' =&gt; 'image',
                'contents' =&gt; fopen('C:\Users\Lenovo\AppData\Local\Temp\php9069.tmp', 'r')
            ],
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-auth-register">
            <blockquote>
            <p>Example response (201, Account created):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;token&quot;: &quot;2|xyz789...&quot;,
    &quot;user&quot;: {
        &quot;id&quot;: 2,
        &quot;first_name&quot;: &quot;Simon&quot;,
        &quot;last_name&quot;: &quot;Dev&quot;,
        &quot;phone&quot;: &quot;+22890000000&quot;
    },
    &quot;message&quot;: &quot;Account created successfully.&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (409, Account already exists):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;An account already exists for this phone number.&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (422, OTP not verified or session expired):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Phone number not verified or session has expired.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-v1-auth-register" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-auth-register"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-auth-register"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-auth-register" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-auth-register">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-auth-register" data-method="POST"
      data-path="api/v1/auth/register"
      data-authed="0"
      data-hasfiles="1"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-auth-register', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-auth-register"
                    onclick="tryItOut('POSTapi-v1-auth-register');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-auth-register"
                    onclick="cancelTryOut('POSTapi-v1-auth-register');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-auth-register"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/auth/register</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-Device-Name</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-Device-Name"                data-endpoint="POSTapi-v1-auth-register"
               value="mobile"
               data-component="header">
    <br>
<p>Example: <code>mobile</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-auth-register"
               value="multipart/form-data"
               data-component="header">
    <br>
<p>Example: <code>multipart/form-data</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-auth-register"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>phone</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="phone"                data-endpoint="POSTapi-v1-auth-register"
               value="+22890123456"
               data-component="body">
    <br>
<p>The user phone number in Togolese format. Must match the regex /^+228[0-9]{8}$/. Example: <code>+22890123456</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>first_name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="first_name"                data-endpoint="POSTapi-v1-auth-register"
               value="Simon"
               data-component="body">
    <br>
<p>The user first name. Must not be greater than 100 characters. Example: <code>Simon</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>last_name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="last_name"                data-endpoint="POSTapi-v1-auth-register"
               value="Dev"
               data-component="body">
    <br>
<p>The user last name. Must not be greater than 100 characters. Example: <code>Dev</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>address</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="address"                data-endpoint="POSTapi-v1-auth-register"
               value="Lomé, Togo"
               data-component="body">
    <br>
<p>Optional user address. Must not be greater than 255 characters. Example: <code>Lomé, Togo</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>image</code></b>&nbsp;&nbsp;
<small>file</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="file" style="display: none"
                              name="image"                data-endpoint="POSTapi-v1-auth-register"
               value=""
               data-component="body">
    <br>
<p>The image of the user. Must be an image. Must not be greater than 2048 kilobytes. Example: <code>C:\Users\Lenovo\AppData\Local\Temp\php9069.tmp</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>birthday</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="birthday"                data-endpoint="POSTapi-v1-auth-register"
               value="1990-01-01"
               data-component="body">
    <br>
<p>The birth date of the user. Must be a valid date. Must be a date before <code>today</code>. Example: <code>1990-01-01</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>gender</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="gender"                data-endpoint="POSTapi-v1-auth-register"
               value="male"
               data-component="body">
    <br>
<p>The gender of the user. Example: <code>male</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>male</code></li> <li><code>female</code></li></ul>
        </div>
        </form>

                    <h2 id="authentication-POSTapi-v1-auth-admin-login">Admin Login</h2>

<p>
</p>

<p>Authenticates an administrator using email and password.
Returns an access token along with permission rules.</p>

<span id="example-requests-POSTapi-v1-auth-admin-login">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/auth/admin/login" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"email\": \"admin@agoo.tg\",
    \"password\": \"secret1234\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/auth/admin/login"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "email": "admin@agoo.tg",
    "password": "secret1234"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/auth/admin/login';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'email' =&gt; 'admin@agoo.tg',
            'password' =&gt; 'secret1234',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-auth-admin-login">
            <blockquote>
            <p>Example response (200, Login successful):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;accessToken&quot;: &quot;3|admin123...&quot;,
    &quot;userData&quot;: {
        &quot;id&quot;: 1,
        &quot;first_name&quot;: &quot;Admin&quot;,
        &quot;email&quot;: &quot;admin@agoo.tg&quot;
    },
    &quot;userAbilityRules&quot;: [
        {
            &quot;action&quot;: &quot;manage&quot;,
            &quot;subject&quot;: &quot;all&quot;
        }
    ],
    &quot;message&quot;: &quot;Login successful.&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (401, Invalid credentials):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Invalid credentials.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-v1-auth-admin-login" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-auth-admin-login"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-auth-admin-login"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-auth-admin-login" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-auth-admin-login">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-auth-admin-login" data-method="POST"
      data-path="api/v1/auth/admin/login"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-auth-admin-login', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-auth-admin-login"
                    onclick="tryItOut('POSTapi-v1-auth-admin-login');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-auth-admin-login"
                    onclick="cancelTryOut('POSTapi-v1-auth-admin-login');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-auth-admin-login"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/auth/admin/login</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-auth-admin-login"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-auth-admin-login"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>email</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="email"                data-endpoint="POSTapi-v1-auth-admin-login"
               value="admin@agoo.tg"
               data-component="body">
    <br>
<p>The administrator email address. Must be a valid email address. Example: <code>admin@agoo.tg</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>password</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="password"                data-endpoint="POSTapi-v1-auth-admin-login"
               value="secret1234"
               data-component="body">
    <br>
<p>The administrator password. Example: <code>secret1234</code></p>
        </div>
        </form>

                    <h2 id="authentication-POSTapi-v1-auth-logout">Logout</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Revokes the current access token of the authenticated user.</p>

<span id="example-requests-POSTapi-v1-auth-logout">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/auth/logout" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/auth/logout"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/auth/logout';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-auth-logout">
            <blockquote>
            <p>Example response (200, Logout successful):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Logged out successfully.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-v1-auth-logout" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-auth-logout"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-auth-logout"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-auth-logout" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-auth-logout">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-auth-logout" data-method="POST"
      data-path="api/v1/auth/logout"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-auth-logout', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-auth-logout"
                    onclick="tryItOut('POSTapi-v1-auth-logout');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-auth-logout"
                    onclick="cancelTryOut('POSTapi-v1-auth-logout');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-auth-logout"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/auth/logout</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-auth-logout"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-auth-logout"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                <h1 id="car">Car</h1>

    <p>APIs for Car management</p>

                                <h2 id="car-GETapi-v1-vehicles">List Vehicles</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Get a listing of the vehicles.</p>

<span id="example-requests-GETapi-v1-vehicles">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/vehicles" \
    --header "Accept-Language: en" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/vehicles"
);

const headers = {
    "Accept-Language": "en",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/vehicles';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Accept-Language' =&gt; 'en',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-vehicles">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">[
    {
        &quot;id&quot;: &quot;01kk07x4jm9g167pc6ycry0h6q&quot;,
        &quot;createdAt&quot;: {
            &quot;datetime&quot;: &quot;2026-03-06T00:14:06.000000Z&quot;,
            &quot;humanDiff&quot;: &quot;0 seconds ago&quot;,
            &quot;human&quot;: &quot;Fri, Mar 6, 2026 12:14 AM&quot;
        }
    },
    {
        &quot;id&quot;: &quot;01kk07x4jw6szsy7vzqfxja5nf&quot;,
        &quot;createdAt&quot;: {
            &quot;datetime&quot;: &quot;2026-03-06T00:14:06.000000Z&quot;,
            &quot;humanDiff&quot;: &quot;0 seconds ago&quot;,
            &quot;human&quot;: &quot;Fri, Mar 6, 2026 12:14 AM&quot;
        }
    }
]</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-vehicles" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-vehicles"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-vehicles"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-vehicles" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-vehicles">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-vehicles" data-method="GET"
      data-path="api/v1/vehicles"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-vehicles', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-vehicles"
                    onclick="tryItOut('GETapi-v1-vehicles');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-vehicles"
                    onclick="cancelTryOut('GETapi-v1-vehicles');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-vehicles"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/vehicles</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept-Language</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept-Language"                data-endpoint="GETapi-v1-vehicles"
               value="en"
               data-component="header">
    <br>
<p>Example: <code>en</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-vehicles"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-vehicles"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="car-POSTapi-v1-vehicles">Store Vehicle</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Store a newly created vehicle in storage.</p>

<span id="example-requests-POSTapi-v1-vehicles">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/vehicles" \
    --header "Accept-Language: en" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"vehicle_type_id\": \"01JNXXXXXXXXXXXXXXXXXXXXXX\",
    \"license_plate_id\": \"01JNXXXXXXXXXXXXXXXXXXXXXX\",
    \"organization_id\": \"01JNXXXXXXXXXXXXXXXXXXXXXX\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/vehicles"
);

const headers = {
    "Accept-Language": "en",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "vehicle_type_id": "01JNXXXXXXXXXXXXXXXXXXXXXX",
    "license_plate_id": "01JNXXXXXXXXXXXXXXXXXXXXXX",
    "organization_id": "01JNXXXXXXXXXXXXXXXXXXXXXX"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/vehicles';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Accept-Language' =&gt; 'en',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'vehicle_type_id' =&gt; '01JNXXXXXXXXXXXXXXXXXXXXXX',
            'license_plate_id' =&gt; '01JNXXXXXXXXXXXXXXXXXXXXXX',
            'organization_id' =&gt; '01JNXXXXXXXXXXXXXXXXXXXXXX',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-vehicles">
            <blockquote>
            <p>Example response (201, Created):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Vehicle created successfully&quot;,
    &quot;data&quot;: {
        &quot;id&quot;: &quot;01jkp5zz...&quot;,
        &quot;brand&quot;: &quot;Toyota&quot;,
        &quot;model&quot;: &quot;Corolla&quot;
    }
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-v1-vehicles" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-vehicles"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-vehicles"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-vehicles" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-vehicles">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-vehicles" data-method="POST"
      data-path="api/v1/vehicles"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-vehicles', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-vehicles"
                    onclick="tryItOut('POSTapi-v1-vehicles');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-vehicles"
                    onclick="cancelTryOut('POSTapi-v1-vehicles');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-vehicles"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/vehicles</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept-Language</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept-Language"                data-endpoint="POSTapi-v1-vehicles"
               value="en"
               data-component="header">
    <br>
<p>Example: <code>en</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-vehicles"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-vehicles"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>vehicle_type_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="vehicle_type_id"                data-endpoint="POSTapi-v1-vehicles"
               value="01JNXXXXXXXXXXXXXXXXXXXXXX"
               data-component="body">
    <br>
<p>Identifiant ULID du type d'engin. The <code>id</code> of an existing record in the vehicle_types table. Example: <code>01JNXXXXXXXXXXXXXXXXXXXXXX</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>license_plate_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="license_plate_id"                data-endpoint="POSTapi-v1-vehicles"
               value="01JNXXXXXXXXXXXXXXXXXXXXXX"
               data-component="body">
    <br>
<p>Identifiant ULID de la plaque d'immatriculation. The <code>id</code> of an existing record in the license_plates table. Example: <code>01JNXXXXXXXXXXXXXXXXXXXXXX</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>organization_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="organization_id"                data-endpoint="POSTapi-v1-vehicles"
               value="01JNXXXXXXXXXXXXXXXXXXXXXX"
               data-component="body">
    <br>
<p>Identifiant ULID de l'organisation propriétaire. The <code>id</code> of an existing record in the organizations table. Example: <code>01JNXXXXXXXXXXXXXXXXXXXXXX</code></p>
        </div>
        </form>

                    <h2 id="car-GETapi-v1-vehicles--id_id-">Show Vehicle</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Show the specified vehicle.</p>

<span id="example-requests-GETapi-v1-vehicles--id_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/vehicles/consequatur" \
    --header "Accept-Language: en" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/vehicles/consequatur"
);

const headers = {
    "Accept-Language": "en",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/vehicles/consequatur';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Accept-Language' =&gt; 'en',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-vehicles--id_id-">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;id&quot;: &quot;01kk07x4m3fa4e9mh06ttqh2tk&quot;,
    &quot;createdAt&quot;: {
        &quot;datetime&quot;: &quot;2026-03-06T00:14:06.000000Z&quot;,
        &quot;humanDiff&quot;: &quot;0 seconds ago&quot;,
        &quot;human&quot;: &quot;Fri, Mar 6, 2026 12:14 AM&quot;
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-vehicles--id_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-vehicles--id_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-vehicles--id_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-vehicles--id_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-vehicles--id_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-vehicles--id_id-" data-method="GET"
      data-path="api/v1/vehicles/{id_id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-vehicles--id_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-vehicles--id_id-"
                    onclick="tryItOut('GETapi-v1-vehicles--id_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-vehicles--id_id-"
                    onclick="cancelTryOut('GETapi-v1-vehicles--id_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-vehicles--id_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/vehicles/{id_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept-Language</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept-Language"                data-endpoint="GETapi-v1-vehicles--id_id-"
               value="en"
               data-component="header">
    <br>
<p>Example: <code>en</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-vehicles--id_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-vehicles--id_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="id_id"                data-endpoint="GETapi-v1-vehicles--id_id-"
               value="consequatur"
               data-component="url">
    <br>
<p>The ID of the id. Example: <code>consequatur</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>vehicle</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="vehicle"                data-endpoint="GETapi-v1-vehicles--id_id-"
               value="consequatur"
               data-component="url">
    <br>
<p>The ID of the vehicle (ULID) Example: <code>consequatur</code></p>
            </div>
                    </form>

                    <h2 id="car-PUTapi-v1-vehicles--id_id-">Update Vehicle</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Update the specified vehicle in storage.</p>

<span id="example-requests-PUTapi-v1-vehicles--id_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request PUT \
    "http://localhost:8000/api/v1/vehicles/consequatur" \
    --header "Accept-Language: en" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"vehicle_type_id\": \"01JNXXXXXXXXXXXXXXXXXXXXXX\",
    \"license_plate_id\": \"01JNXXXXXXXXXXXXXXXXXXXXXX\",
    \"organization_id\": \"01JNXXXXXXXXXXXXXXXXXXXXXX\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/vehicles/consequatur"
);

const headers = {
    "Accept-Language": "en",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "vehicle_type_id": "01JNXXXXXXXXXXXXXXXXXXXXXX",
    "license_plate_id": "01JNXXXXXXXXXXXXXXXXXXXXXX",
    "organization_id": "01JNXXXXXXXXXXXXXXXXXXXXXX"
};

fetch(url, {
    method: "PUT",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/vehicles/consequatur';
$response = $client-&gt;put(
    $url,
    [
        'headers' =&gt; [
            'Accept-Language' =&gt; 'en',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'vehicle_type_id' =&gt; '01JNXXXXXXXXXXXXXXXXXXXXXX',
            'license_plate_id' =&gt; '01JNXXXXXXXXXXXXXXXXXXXXXX',
            'organization_id' =&gt; '01JNXXXXXXXXXXXXXXXXXXXXXX',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-PUTapi-v1-vehicles--id_id-">
            <blockquote>
            <p>Example response (200, Updated):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Vehicle updated successfully&quot;,
    &quot;data&quot;: {
        &quot;id&quot;: &quot;01jkp5zz...&quot;,
        &quot;model&quot;: &quot;Camry&quot;
    }
}</code>
 </pre>
    </span>
<span id="execution-results-PUTapi-v1-vehicles--id_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-PUTapi-v1-vehicles--id_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PUTapi-v1-vehicles--id_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PUTapi-v1-vehicles--id_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-PUTapi-v1-vehicles--id_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-PUTapi-v1-vehicles--id_id-" data-method="PUT"
      data-path="api/v1/vehicles/{id_id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PUTapi-v1-vehicles--id_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-PUTapi-v1-vehicles--id_id-"
                    onclick="tryItOut('PUTapi-v1-vehicles--id_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-PUTapi-v1-vehicles--id_id-"
                    onclick="cancelTryOut('PUTapi-v1-vehicles--id_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-PUTapi-v1-vehicles--id_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-darkblue">PUT</small>
            <b><code>api/v1/vehicles/{id_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept-Language</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept-Language"                data-endpoint="PUTapi-v1-vehicles--id_id-"
               value="en"
               data-component="header">
    <br>
<p>Example: <code>en</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PUTapi-v1-vehicles--id_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PUTapi-v1-vehicles--id_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="id_id"                data-endpoint="PUTapi-v1-vehicles--id_id-"
               value="consequatur"
               data-component="url">
    <br>
<p>The ID of the id. Example: <code>consequatur</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>vehicle</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="vehicle"                data-endpoint="PUTapi-v1-vehicles--id_id-"
               value="consequatur"
               data-component="url">
    <br>
<p>The ID of the vehicle (ULID) Example: <code>consequatur</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>vehicle_type_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="vehicle_type_id"                data-endpoint="PUTapi-v1-vehicles--id_id-"
               value="01JNXXXXXXXXXXXXXXXXXXXXXX"
               data-component="body">
    <br>
<p>Identifiant ULID du type d'engin. The <code>id</code> of an existing record in the vehicle_types table. Example: <code>01JNXXXXXXXXXXXXXXXXXXXXXX</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>license_plate_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="license_plate_id"                data-endpoint="PUTapi-v1-vehicles--id_id-"
               value="01JNXXXXXXXXXXXXXXXXXXXXXX"
               data-component="body">
    <br>
<p>Identifiant ULID de la plaque d'immatriculation. The <code>id</code> of an existing record in the license_plates table. Example: <code>01JNXXXXXXXXXXXXXXXXXXXXXX</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>organization_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="organization_id"                data-endpoint="PUTapi-v1-vehicles--id_id-"
               value="01JNXXXXXXXXXXXXXXXXXXXXXX"
               data-component="body">
    <br>
<p>Identifiant ULID de l'organisation propriétaire. The <code>id</code> of an existing record in the organizations table. Example: <code>01JNXXXXXXXXXXXXXXXXXXXXXX</code></p>
        </div>
        </form>

                    <h2 id="car-DELETEapi-v1-vehicles--id_id-">Delete Vehicle</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Delete the specified vehicle from storage.</p>

<span id="example-requests-DELETEapi-v1-vehicles--id_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request DELETE \
    "http://localhost:8000/api/v1/vehicles/consequatur" \
    --header "Accept-Language: en" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/vehicles/consequatur"
);

const headers = {
    "Accept-Language": "en",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/vehicles/consequatur';
$response = $client-&gt;delete(
    $url,
    [
        'headers' =&gt; [
            'Accept-Language' =&gt; 'en',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-DELETEapi-v1-vehicles--id_id-">
            <blockquote>
            <p>Example response (204, Deleted):</p>
        </blockquote>
                <pre>
<code>Empty response</code>
 </pre>
    </span>
<span id="execution-results-DELETEapi-v1-vehicles--id_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-DELETEapi-v1-vehicles--id_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-DELETEapi-v1-vehicles--id_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-DELETEapi-v1-vehicles--id_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-DELETEapi-v1-vehicles--id_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-DELETEapi-v1-vehicles--id_id-" data-method="DELETE"
      data-path="api/v1/vehicles/{id_id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('DELETEapi-v1-vehicles--id_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-DELETEapi-v1-vehicles--id_id-"
                    onclick="tryItOut('DELETEapi-v1-vehicles--id_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-DELETEapi-v1-vehicles--id_id-"
                    onclick="cancelTryOut('DELETEapi-v1-vehicles--id_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-DELETEapi-v1-vehicles--id_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/v1/vehicles/{id_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept-Language</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept-Language"                data-endpoint="DELETEapi-v1-vehicles--id_id-"
               value="en"
               data-component="header">
    <br>
<p>Example: <code>en</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="DELETEapi-v1-vehicles--id_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="DELETEapi-v1-vehicles--id_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="id_id"                data-endpoint="DELETEapi-v1-vehicles--id_id-"
               value="consequatur"
               data-component="url">
    <br>
<p>The ID of the id. Example: <code>consequatur</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>vehicle</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="vehicle"                data-endpoint="DELETEapi-v1-vehicles--id_id-"
               value="consequatur"
               data-component="url">
    <br>
<p>The ID of the vehicle (ULID) Example: <code>consequatur</code></p>
            </div>
                    </form>

                                <h2 id="car-car-type">Car Type</h2>
                                        <p>
                    <p>APIs for Car Type management</p>
                </p>
                                        <h2 id="car-GETapi-v1-vehicle-types">List Vehicle Types</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Get a listing of the vehicle types.</p>

<span id="example-requests-GETapi-v1-vehicle-types">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/vehicle-types" \
    --header "Accept-Language: en" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/vehicle-types"
);

const headers = {
    "Accept-Language": "en",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/vehicle-types';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Accept-Language' =&gt; 'en',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-vehicle-types">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">[
    {
        &quot;id&quot;: &quot;01kk07x4njz1k5kyprws219tz1&quot;,
        &quot;name&quot;: &quot;Moto&quot;,
        &quot;createdAt&quot;: {
            &quot;datetime&quot;: &quot;2026-03-06T00:14:06.000000Z&quot;,
            &quot;humanDiff&quot;: &quot;0 seconds ago&quot;,
            &quot;human&quot;: &quot;Fri, Mar 6, 2026 12:14 AM&quot;
        }
    },
    {
        &quot;id&quot;: &quot;01kk07x4np2w98jwwn0axpv418&quot;,
        &quot;name&quot;: &quot;Voiture&quot;,
        &quot;createdAt&quot;: {
            &quot;datetime&quot;: &quot;2026-03-06T00:14:06.000000Z&quot;,
            &quot;humanDiff&quot;: &quot;0 seconds ago&quot;,
            &quot;human&quot;: &quot;Fri, Mar 6, 2026 12:14 AM&quot;
        }
    }
]</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-vehicle-types" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-vehicle-types"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-vehicle-types"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-vehicle-types" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-vehicle-types">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-vehicle-types" data-method="GET"
      data-path="api/v1/vehicle-types"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-vehicle-types', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-vehicle-types"
                    onclick="tryItOut('GETapi-v1-vehicle-types');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-vehicle-types"
                    onclick="cancelTryOut('GETapi-v1-vehicle-types');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-vehicle-types"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/vehicle-types</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept-Language</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept-Language"                data-endpoint="GETapi-v1-vehicle-types"
               value="en"
               data-component="header">
    <br>
<p>Example: <code>en</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-vehicle-types"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-vehicle-types"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="car-POSTapi-v1-vehicle-types">Store Vehicle Type</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Store a newly created vehicle type in storage.</p>

<span id="example-requests-POSTapi-v1-vehicle-types">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/vehicle-types" \
    --header "Accept-Language: en" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"name\": \"Voiture\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/vehicle-types"
);

const headers = {
    "Accept-Language": "en",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "Voiture"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/vehicle-types';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Accept-Language' =&gt; 'en',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'name' =&gt; 'Voiture',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-vehicle-types">
            <blockquote>
            <p>Example response (201, Created):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Vehicle type created successfully&quot;,
    &quot;data&quot;: {
        &quot;id&quot;: &quot;01jkp5zz...&quot;,
        &quot;name&quot;: &quot;SUV&quot;
    }
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-v1-vehicle-types" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-vehicle-types"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-vehicle-types"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-vehicle-types" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-vehicle-types">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-vehicle-types" data-method="POST"
      data-path="api/v1/vehicle-types"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-vehicle-types', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-vehicle-types"
                    onclick="tryItOut('POSTapi-v1-vehicle-types');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-vehicle-types"
                    onclick="cancelTryOut('POSTapi-v1-vehicle-types');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-vehicle-types"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/vehicle-types</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept-Language</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept-Language"                data-endpoint="POSTapi-v1-vehicle-types"
               value="en"
               data-component="header">
    <br>
<p>Example: <code>en</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-vehicle-types"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-vehicle-types"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="name"                data-endpoint="POSTapi-v1-vehicle-types"
               value="Voiture"
               data-component="body">
    <br>
<p>Nom du type d'engin. Must not be greater than 255 characters. Example: <code>Voiture</code></p>
        </div>
        </form>

                    <h2 id="car-GETapi-v1-vehicle-types--id_id-">Show Vehicle Type</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Show the specified vehicle type.</p>

<span id="example-requests-GETapi-v1-vehicle-types--id_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/vehicle-types/consequatur" \
    --header "Accept-Language: en" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/vehicle-types/consequatur"
);

const headers = {
    "Accept-Language": "en",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/vehicle-types/consequatur';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Accept-Language' =&gt; 'en',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-vehicle-types--id_id-">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;id&quot;: &quot;01kk07x4pjc1ex7wxmz50v4967&quot;,
    &quot;name&quot;: &quot;4x4&quot;,
    &quot;createdAt&quot;: {
        &quot;datetime&quot;: &quot;2026-03-06T00:14:06.000000Z&quot;,
        &quot;humanDiff&quot;: &quot;0 seconds ago&quot;,
        &quot;human&quot;: &quot;Fri, Mar 6, 2026 12:14 AM&quot;
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-vehicle-types--id_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-vehicle-types--id_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-vehicle-types--id_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-vehicle-types--id_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-vehicle-types--id_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-vehicle-types--id_id-" data-method="GET"
      data-path="api/v1/vehicle-types/{id_id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-vehicle-types--id_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-vehicle-types--id_id-"
                    onclick="tryItOut('GETapi-v1-vehicle-types--id_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-vehicle-types--id_id-"
                    onclick="cancelTryOut('GETapi-v1-vehicle-types--id_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-vehicle-types--id_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/vehicle-types/{id_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept-Language</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept-Language"                data-endpoint="GETapi-v1-vehicle-types--id_id-"
               value="en"
               data-component="header">
    <br>
<p>Example: <code>en</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-vehicle-types--id_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-vehicle-types--id_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="id_id"                data-endpoint="GETapi-v1-vehicle-types--id_id-"
               value="consequatur"
               data-component="url">
    <br>
<p>The ID of the id. Example: <code>consequatur</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>vehicleType</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="vehicleType"                data-endpoint="GETapi-v1-vehicle-types--id_id-"
               value="consequatur"
               data-component="url">
    <br>
<p>The ID of the vehicle type (ULID) Example: <code>consequatur</code></p>
            </div>
                    </form>

                    <h2 id="car-PUTapi-v1-vehicle-types--id_id-">Update Vehicle Type</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Update the specified vehicle type in storage.</p>

<span id="example-requests-PUTapi-v1-vehicle-types--id_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request PUT \
    "http://localhost:8000/api/v1/vehicle-types/consequatur" \
    --header "Accept-Language: en" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"name\": \"Camion\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/vehicle-types/consequatur"
);

const headers = {
    "Accept-Language": "en",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "Camion"
};

fetch(url, {
    method: "PUT",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/vehicle-types/consequatur';
$response = $client-&gt;put(
    $url,
    [
        'headers' =&gt; [
            'Accept-Language' =&gt; 'en',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'name' =&gt; 'Camion',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-PUTapi-v1-vehicle-types--id_id-">
            <blockquote>
            <p>Example response (200, Updated):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Vehicle type updated successfully&quot;,
    &quot;data&quot;: {
        &quot;id&quot;: &quot;01jkp5zz...&quot;,
        &quot;name&quot;: &quot;Sedan&quot;
    }
}</code>
 </pre>
    </span>
<span id="execution-results-PUTapi-v1-vehicle-types--id_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-PUTapi-v1-vehicle-types--id_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PUTapi-v1-vehicle-types--id_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PUTapi-v1-vehicle-types--id_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-PUTapi-v1-vehicle-types--id_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-PUTapi-v1-vehicle-types--id_id-" data-method="PUT"
      data-path="api/v1/vehicle-types/{id_id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PUTapi-v1-vehicle-types--id_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-PUTapi-v1-vehicle-types--id_id-"
                    onclick="tryItOut('PUTapi-v1-vehicle-types--id_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-PUTapi-v1-vehicle-types--id_id-"
                    onclick="cancelTryOut('PUTapi-v1-vehicle-types--id_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-PUTapi-v1-vehicle-types--id_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-darkblue">PUT</small>
            <b><code>api/v1/vehicle-types/{id_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept-Language</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept-Language"                data-endpoint="PUTapi-v1-vehicle-types--id_id-"
               value="en"
               data-component="header">
    <br>
<p>Example: <code>en</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PUTapi-v1-vehicle-types--id_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PUTapi-v1-vehicle-types--id_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="id_id"                data-endpoint="PUTapi-v1-vehicle-types--id_id-"
               value="consequatur"
               data-component="url">
    <br>
<p>The ID of the id. Example: <code>consequatur</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>vehicleType</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="vehicleType"                data-endpoint="PUTapi-v1-vehicle-types--id_id-"
               value="consequatur"
               data-component="url">
    <br>
<p>The ID of the vehicle type (ULID) Example: <code>consequatur</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="name"                data-endpoint="PUTapi-v1-vehicle-types--id_id-"
               value="Camion"
               data-component="body">
    <br>
<p>Nom du type d'engin. Must not be greater than 255 characters. Example: <code>Camion</code></p>
        </div>
        </form>

                    <h2 id="car-DELETEapi-v1-vehicle-types--id_id-">Delete Vehicle Type</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Delete the specified vehicle type from storage.</p>

<span id="example-requests-DELETEapi-v1-vehicle-types--id_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request DELETE \
    "http://localhost:8000/api/v1/vehicle-types/consequatur" \
    --header "Accept-Language: en" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/vehicle-types/consequatur"
);

const headers = {
    "Accept-Language": "en",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/vehicle-types/consequatur';
$response = $client-&gt;delete(
    $url,
    [
        'headers' =&gt; [
            'Accept-Language' =&gt; 'en',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-DELETEapi-v1-vehicle-types--id_id-">
            <blockquote>
            <p>Example response (204, Deleted):</p>
        </blockquote>
                <pre>
<code>Empty response</code>
 </pre>
    </span>
<span id="execution-results-DELETEapi-v1-vehicle-types--id_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-DELETEapi-v1-vehicle-types--id_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-DELETEapi-v1-vehicle-types--id_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-DELETEapi-v1-vehicle-types--id_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-DELETEapi-v1-vehicle-types--id_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-DELETEapi-v1-vehicle-types--id_id-" data-method="DELETE"
      data-path="api/v1/vehicle-types/{id_id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('DELETEapi-v1-vehicle-types--id_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-DELETEapi-v1-vehicle-types--id_id-"
                    onclick="tryItOut('DELETEapi-v1-vehicle-types--id_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-DELETEapi-v1-vehicle-types--id_id-"
                    onclick="cancelTryOut('DELETEapi-v1-vehicle-types--id_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-DELETEapi-v1-vehicle-types--id_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/v1/vehicle-types/{id_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept-Language</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept-Language"                data-endpoint="DELETEapi-v1-vehicle-types--id_id-"
               value="en"
               data-component="header">
    <br>
<p>Example: <code>en</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="DELETEapi-v1-vehicle-types--id_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="DELETEapi-v1-vehicle-types--id_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="id_id"                data-endpoint="DELETEapi-v1-vehicle-types--id_id-"
               value="consequatur"
               data-component="url">
    <br>
<p>The ID of the id. Example: <code>consequatur</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>vehicleType</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="vehicleType"                data-endpoint="DELETEapi-v1-vehicle-types--id_id-"
               value="consequatur"
               data-component="url">
    <br>
<p>The ID of the vehicle type (ULID) Example: <code>consequatur</code></p>
            </div>
                    </form>

                <h1 id="invoice-management">Invoice management</h1>

    <p>APIs for managing invoices</p>

                                <h2 id="invoice-management-GETapi-v1-invoices">List Invoices</h2>

<p>
</p>

<p>Get a listing of the invoices.</p>

<span id="example-requests-GETapi-v1-invoices">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/invoices" \
    --header "Accept-Language: en" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/invoices"
);

const headers = {
    "Accept-Language": "en",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/invoices';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Accept-Language' =&gt; 'en',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-invoices">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">[
    {
        &quot;id&quot;: &quot;01kk07x3xfpvtwfdh6kz513517&quot;,
        &quot;invoiceNumber&quot;: &quot;FAC-20260306-5736&quot;,
        &quot;amount&quot;: 4149,
        &quot;formattedAmount&quot;: &quot;4 149 FCFA&quot;,
        &quot;notes&quot;: &quot;Amet iste laborum eius est dolor dolores.&quot;,
        &quot;issuedAt&quot;: &quot;2026-03-06T00:14:05.000000Z&quot;,
        &quot;createdAt&quot;: {
            &quot;datetime&quot;: &quot;2026-03-06T00:14:05.000000Z&quot;,
            &quot;humanDiff&quot;: &quot;0 seconds ago&quot;,
            &quot;human&quot;: &quot;Fri, Mar 6, 2026 12:14 AM&quot;
        }
    },
    {
        &quot;id&quot;: &quot;01kk07x3zbwzrj3tgbj4nss72d&quot;,
        &quot;invoiceNumber&quot;: &quot;FAC-20260306-8612&quot;,
        &quot;amount&quot;: 1682,
        &quot;formattedAmount&quot;: &quot;1 682 FCFA&quot;,
        &quot;notes&quot;: &quot;Sit labore quos ea rerum repudiandae est.&quot;,
        &quot;issuedAt&quot;: &quot;2026-03-06T00:14:05.000000Z&quot;,
        &quot;createdAt&quot;: {
            &quot;datetime&quot;: &quot;2026-03-06T00:14:05.000000Z&quot;,
            &quot;humanDiff&quot;: &quot;0 seconds ago&quot;,
            &quot;human&quot;: &quot;Fri, Mar 6, 2026 12:14 AM&quot;
        }
    }
]</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-invoices" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-invoices"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-invoices"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-invoices" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-invoices">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-invoices" data-method="GET"
      data-path="api/v1/invoices"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-invoices', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-invoices"
                    onclick="tryItOut('GETapi-v1-invoices');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-invoices"
                    onclick="cancelTryOut('GETapi-v1-invoices');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-invoices"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/invoices</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept-Language</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept-Language"                data-endpoint="GETapi-v1-invoices"
               value="en"
               data-component="header">
    <br>
<p>Example: <code>en</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-invoices"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-invoices"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="invoice-management-POSTapi-v1-invoices">Store Invoice</h2>

<p>
</p>

<p>Store a newly created invoice in storage.</p>

<span id="example-requests-POSTapi-v1-invoices">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/invoices" \
    --header "Accept-Language: en" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"wash_ticket_id\": \"01JNXXXXXXXXXXXXXXXXXXXXXX\",
    \"amount\": 1500,
    \"notes\": \"Remise accordée.\",
    \"issued_at\": \"2026-03-05\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/invoices"
);

const headers = {
    "Accept-Language": "en",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "wash_ticket_id": "01JNXXXXXXXXXXXXXXXXXXXXXX",
    "amount": 1500,
    "notes": "Remise accordée.",
    "issued_at": "2026-03-05"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/invoices';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Accept-Language' =&gt; 'en',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'wash_ticket_id' =&gt; '01JNXXXXXXXXXXXXXXXXXXXXXX',
            'amount' =&gt; 1500,
            'notes' =&gt; 'Remise accordée.',
            'issued_at' =&gt; '2026-03-05',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-invoices">
            <blockquote>
            <p>Example response (201, Created):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Invoice created successfully&quot;,
    &quot;data&quot;: {
        &quot;id&quot;: &quot;01jkp5zz...&quot;,
        &quot;number&quot;: &quot;INV-2024-0001&quot;,
        &quot;amount&quot;: 5000
    }
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-v1-invoices" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-invoices"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-invoices"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-invoices" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-invoices">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-invoices" data-method="POST"
      data-path="api/v1/invoices"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-invoices', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-invoices"
                    onclick="tryItOut('POSTapi-v1-invoices');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-invoices"
                    onclick="cancelTryOut('POSTapi-v1-invoices');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-invoices"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/invoices</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept-Language</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept-Language"                data-endpoint="POSTapi-v1-invoices"
               value="en"
               data-component="header">
    <br>
<p>Example: <code>en</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-invoices"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-invoices"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>wash_ticket_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="wash_ticket_id"                data-endpoint="POSTapi-v1-invoices"
               value="01JNXXXXXXXXXXXXXXXXXXXXXX"
               data-component="body">
    <br>
<p>Identifiant ULID du ticket de lavage. The <code>id</code> of an existing record in the wash_tickets table. Example: <code>01JNXXXXXXXXXXXXXXXXXXXXXX</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>amount</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="amount"                data-endpoint="POSTapi-v1-invoices"
               value="1500"
               data-component="body">
    <br>
<p>Montant de la facture (FCFA). Must be at least 0. Example: <code>1500</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>notes</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="notes"                data-endpoint="POSTapi-v1-invoices"
               value="Remise accordée."
               data-component="body">
    <br>
<p>Notes ou observations. Example: <code>Remise accordée.</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>issued_at</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="issued_at"                data-endpoint="POSTapi-v1-invoices"
               value="2026-03-05"
               data-component="body">
    <br>
<p>Date d'émission. Must be a valid date. Example: <code>2026-03-05</code></p>
        </div>
        </form>

                    <h2 id="invoice-management-GETapi-v1-invoices--id_id-">Show Invoice</h2>

<p>
</p>

<p>Show the specified invoice.</p>

<span id="example-requests-GETapi-v1-invoices--id_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/invoices/consequatur" \
    --header "Accept-Language: en" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/invoices/consequatur"
);

const headers = {
    "Accept-Language": "en",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/invoices/consequatur';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Accept-Language' =&gt; 'en',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-invoices--id_id-">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;id&quot;: &quot;01kk07x423mrpbzkv52kreg13s&quot;,
    &quot;invoiceNumber&quot;: &quot;FAC-20260306-9600&quot;,
    &quot;amount&quot;: 2739,
    &quot;formattedAmount&quot;: &quot;2 739 FCFA&quot;,
    &quot;notes&quot;: null,
    &quot;issuedAt&quot;: &quot;2026-03-06T00:14:05.000000Z&quot;,
    &quot;createdAt&quot;: {
        &quot;datetime&quot;: &quot;2026-03-06T00:14:05.000000Z&quot;,
        &quot;humanDiff&quot;: &quot;0 seconds ago&quot;,
        &quot;human&quot;: &quot;Fri, Mar 6, 2026 12:14 AM&quot;
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-invoices--id_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-invoices--id_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-invoices--id_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-invoices--id_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-invoices--id_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-invoices--id_id-" data-method="GET"
      data-path="api/v1/invoices/{id_id}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-invoices--id_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-invoices--id_id-"
                    onclick="tryItOut('GETapi-v1-invoices--id_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-invoices--id_id-"
                    onclick="cancelTryOut('GETapi-v1-invoices--id_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-invoices--id_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/invoices/{id_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept-Language</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept-Language"                data-endpoint="GETapi-v1-invoices--id_id-"
               value="en"
               data-component="header">
    <br>
<p>Example: <code>en</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-invoices--id_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-invoices--id_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="id_id"                data-endpoint="GETapi-v1-invoices--id_id-"
               value="consequatur"
               data-component="url">
    <br>
<p>The ID of the id. Example: <code>consequatur</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>invoice</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="invoice"                data-endpoint="GETapi-v1-invoices--id_id-"
               value="consequatur"
               data-component="url">
    <br>
<p>The ID of the invoice (ULID) Example: <code>consequatur</code></p>
            </div>
                    </form>

                    <h2 id="invoice-management-DELETEapi-v1-invoices--id_id-">Delete Invoice</h2>

<p>
</p>

<p>Delete the specified invoice from storage.</p>

<span id="example-requests-DELETEapi-v1-invoices--id_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request DELETE \
    "http://localhost:8000/api/v1/invoices/consequatur" \
    --header "Accept-Language: en" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/invoices/consequatur"
);

const headers = {
    "Accept-Language": "en",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/invoices/consequatur';
$response = $client-&gt;delete(
    $url,
    [
        'headers' =&gt; [
            'Accept-Language' =&gt; 'en',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-DELETEapi-v1-invoices--id_id-">
            <blockquote>
            <p>Example response (204, Deleted):</p>
        </blockquote>
                <pre>
<code>Empty response</code>
 </pre>
    </span>
<span id="execution-results-DELETEapi-v1-invoices--id_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-DELETEapi-v1-invoices--id_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-DELETEapi-v1-invoices--id_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-DELETEapi-v1-invoices--id_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-DELETEapi-v1-invoices--id_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-DELETEapi-v1-invoices--id_id-" data-method="DELETE"
      data-path="api/v1/invoices/{id_id}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('DELETEapi-v1-invoices--id_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-DELETEapi-v1-invoices--id_id-"
                    onclick="tryItOut('DELETEapi-v1-invoices--id_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-DELETEapi-v1-invoices--id_id-"
                    onclick="cancelTryOut('DELETEapi-v1-invoices--id_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-DELETEapi-v1-invoices--id_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/v1/invoices/{id_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept-Language</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept-Language"                data-endpoint="DELETEapi-v1-invoices--id_id-"
               value="en"
               data-component="header">
    <br>
<p>Example: <code>en</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="DELETEapi-v1-invoices--id_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="DELETEapi-v1-invoices--id_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="id_id"                data-endpoint="DELETEapi-v1-invoices--id_id-"
               value="consequatur"
               data-component="url">
    <br>
<p>The ID of the id. Example: <code>consequatur</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>invoice</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="invoice"                data-endpoint="DELETEapi-v1-invoices--id_id-"
               value="consequatur"
               data-component="url">
    <br>
<p>The ID of the invoice (ULID) Example: <code>consequatur</code></p>
            </div>
                    </form>

                <h1 id="license-plate-management">License Plate management</h1>

    <p>APIs for managing vehicle license plates</p>

                                <h2 id="license-plate-management-GETapi-v1-license-plates">List License Plates</h2>

<p>
</p>

<p>Get a listing of the license plates.</p>

<span id="example-requests-GETapi-v1-license-plates">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/license-plates" \
    --header "Accept-Language: en" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/license-plates"
);

const headers = {
    "Accept-Language": "en",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/license-plates';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Accept-Language' =&gt; 'en',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-license-plates">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">[
    {
        &quot;id&quot;: &quot;01kk07x443pv8maczasr3yhkvf&quot;,
        &quot;countryCode&quot;: &quot;SN&quot;,
        &quot;country&quot;: &quot;S&eacute;n&eacute;gal&quot;,
        &quot;series&quot;: &quot;QE&quot;,
        &quot;number&quot;: &quot;5570&quot;,
        &quot;fullPlate&quot;: &quot;SN-QE-5570&quot;,
        &quot;createdAt&quot;: {
            &quot;datetime&quot;: &quot;2026-03-06T00:14:05.000000Z&quot;,
            &quot;humanDiff&quot;: &quot;0 seconds ago&quot;,
            &quot;human&quot;: &quot;Fri, Mar 6, 2026 12:14 AM&quot;
        }
    },
    {
        &quot;id&quot;: &quot;01kk07x44b5t5kc1qa1x1wjd2x&quot;,
        &quot;countryCode&quot;: &quot;ML&quot;,
        &quot;country&quot;: &quot;Mali&quot;,
        &quot;series&quot;: &quot;FU&quot;,
        &quot;number&quot;: &quot;7799&quot;,
        &quot;fullPlate&quot;: &quot;ML-FU-7799&quot;,
        &quot;createdAt&quot;: {
            &quot;datetime&quot;: &quot;2026-03-06T00:14:05.000000Z&quot;,
            &quot;humanDiff&quot;: &quot;0 seconds ago&quot;,
            &quot;human&quot;: &quot;Fri, Mar 6, 2026 12:14 AM&quot;
        }
    }
]</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-license-plates" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-license-plates"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-license-plates"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-license-plates" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-license-plates">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-license-plates" data-method="GET"
      data-path="api/v1/license-plates"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-license-plates', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-license-plates"
                    onclick="tryItOut('GETapi-v1-license-plates');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-license-plates"
                    onclick="cancelTryOut('GETapi-v1-license-plates');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-license-plates"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/license-plates</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept-Language</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept-Language"                data-endpoint="GETapi-v1-license-plates"
               value="en"
               data-component="header">
    <br>
<p>Example: <code>en</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-license-plates"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-license-plates"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="license-plate-management-POSTapi-v1-license-plates">Store License Plate</h2>

<p>
</p>

<p>Store a newly created license plate in storage.</p>

<span id="example-requests-POSTapi-v1-license-plates">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/license-plates" \
    --header "Accept-Language: en" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"country_code\": \"TG\",
    \"series\": \"AB\",
    \"number\": \"1234\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/license-plates"
);

const headers = {
    "Accept-Language": "en",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "country_code": "TG",
    "series": "AB",
    "number": "1234"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/license-plates';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Accept-Language' =&gt; 'en',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'country_code' =&gt; 'TG',
            'series' =&gt; 'AB',
            'number' =&gt; '1234',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-license-plates">
            <blockquote>
            <p>Example response (201, Created):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;License plate created successfully&quot;,
    &quot;data&quot;: {
        &quot;id&quot;: &quot;01jkp5zz...&quot;,
        &quot;number&quot;: &quot;ABC-1234&quot;
    }
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-v1-license-plates" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-license-plates"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-license-plates"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-license-plates" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-license-plates">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-license-plates" data-method="POST"
      data-path="api/v1/license-plates"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-license-plates', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-license-plates"
                    onclick="tryItOut('POSTapi-v1-license-plates');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-license-plates"
                    onclick="cancelTryOut('POSTapi-v1-license-plates');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-license-plates"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/license-plates</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept-Language</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept-Language"                data-endpoint="POSTapi-v1-license-plates"
               value="en"
               data-component="header">
    <br>
<p>Example: <code>en</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-license-plates"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-license-plates"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>country_code</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="country_code"                data-endpoint="POSTapi-v1-license-plates"
               value="TG"
               data-component="body">
    <br>
<p>Code ISO 3166-1 alpha-2 du pays (ex: TG, BJ, GH). Example: <code>TG</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>TG</code></li> <li><code>BJ</code></li> <li><code>GH</code></li> <li><code>CI</code></li> <li><code>SN</code></li> <li><code>ML</code></li> <li><code>BF</code></li> <li><code>NE</code></li> <li><code>NG</code></li></ul>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>series</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="series"                data-endpoint="POSTapi-v1-license-plates"
               value="AB"
               data-component="body">
    <br>
<p>Série alphabétique de la plaque. Must not be greater than 10 characters. Example: <code>AB</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>number</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="number"                data-endpoint="POSTapi-v1-license-plates"
               value="1234"
               data-component="body">
    <br>
<p>Numéro de la plaque. Must not be greater than 20 characters. Example: <code>1234</code></p>
        </div>
        </form>

                    <h2 id="license-plate-management-GETapi-v1-license-plates--id_id-">Show License Plate</h2>

<p>
</p>

<p>Show the specified license plate.</p>

<span id="example-requests-GETapi-v1-license-plates--id_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/license-plates/consequatur" \
    --header "Accept-Language: en" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/license-plates/consequatur"
);

const headers = {
    "Accept-Language": "en",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/license-plates/consequatur';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Accept-Language' =&gt; 'en',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-license-plates--id_id-">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;id&quot;: &quot;01kk07x45f8ne186khbez4dvnk&quot;,
    &quot;countryCode&quot;: &quot;SN&quot;,
    &quot;country&quot;: &quot;S&eacute;n&eacute;gal&quot;,
    &quot;series&quot;: &quot;QE&quot;,
    &quot;number&quot;: &quot;5570&quot;,
    &quot;fullPlate&quot;: &quot;SN-QE-5570&quot;,
    &quot;createdAt&quot;: {
        &quot;datetime&quot;: &quot;2026-03-06T00:14:05.000000Z&quot;,
        &quot;humanDiff&quot;: &quot;1 second ago&quot;,
        &quot;human&quot;: &quot;Fri, Mar 6, 2026 12:14 AM&quot;
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-license-plates--id_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-license-plates--id_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-license-plates--id_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-license-plates--id_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-license-plates--id_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-license-plates--id_id-" data-method="GET"
      data-path="api/v1/license-plates/{id_id}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-license-plates--id_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-license-plates--id_id-"
                    onclick="tryItOut('GETapi-v1-license-plates--id_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-license-plates--id_id-"
                    onclick="cancelTryOut('GETapi-v1-license-plates--id_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-license-plates--id_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/license-plates/{id_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept-Language</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept-Language"                data-endpoint="GETapi-v1-license-plates--id_id-"
               value="en"
               data-component="header">
    <br>
<p>Example: <code>en</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-license-plates--id_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-license-plates--id_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="id_id"                data-endpoint="GETapi-v1-license-plates--id_id-"
               value="consequatur"
               data-component="url">
    <br>
<p>The ID of the id. Example: <code>consequatur</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>licensePlate</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="licensePlate"                data-endpoint="GETapi-v1-license-plates--id_id-"
               value="consequatur"
               data-component="url">
    <br>
<p>The ID of the license plate (ULID) Example: <code>consequatur</code></p>
            </div>
                    </form>

                    <h2 id="license-plate-management-PUTapi-v1-license-plates--id_id-">Update License Plate</h2>

<p>
</p>

<p>Update the specified license plate in storage.</p>

<span id="example-requests-PUTapi-v1-license-plates--id_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request PUT \
    "http://localhost:8000/api/v1/license-plates/consequatur" \
    --header "Accept-Language: en" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"country_code\": \"TG\",
    \"series\": \"AB\",
    \"number\": \"1234\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/license-plates/consequatur"
);

const headers = {
    "Accept-Language": "en",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "country_code": "TG",
    "series": "AB",
    "number": "1234"
};

fetch(url, {
    method: "PUT",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/license-plates/consequatur';
$response = $client-&gt;put(
    $url,
    [
        'headers' =&gt; [
            'Accept-Language' =&gt; 'en',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'country_code' =&gt; 'TG',
            'series' =&gt; 'AB',
            'number' =&gt; '1234',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-PUTapi-v1-license-plates--id_id-">
            <blockquote>
            <p>Example response (200, Updated):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;License plate updated successfully&quot;,
    &quot;data&quot;: {
        &quot;id&quot;: &quot;01jkp5zz...&quot;,
        &quot;number&quot;: &quot;XYZ-9876&quot;
    }
}</code>
 </pre>
    </span>
<span id="execution-results-PUTapi-v1-license-plates--id_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-PUTapi-v1-license-plates--id_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PUTapi-v1-license-plates--id_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PUTapi-v1-license-plates--id_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-PUTapi-v1-license-plates--id_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-PUTapi-v1-license-plates--id_id-" data-method="PUT"
      data-path="api/v1/license-plates/{id_id}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PUTapi-v1-license-plates--id_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-PUTapi-v1-license-plates--id_id-"
                    onclick="tryItOut('PUTapi-v1-license-plates--id_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-PUTapi-v1-license-plates--id_id-"
                    onclick="cancelTryOut('PUTapi-v1-license-plates--id_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-PUTapi-v1-license-plates--id_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-darkblue">PUT</small>
            <b><code>api/v1/license-plates/{id_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept-Language</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept-Language"                data-endpoint="PUTapi-v1-license-plates--id_id-"
               value="en"
               data-component="header">
    <br>
<p>Example: <code>en</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PUTapi-v1-license-plates--id_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PUTapi-v1-license-plates--id_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="id_id"                data-endpoint="PUTapi-v1-license-plates--id_id-"
               value="consequatur"
               data-component="url">
    <br>
<p>The ID of the id. Example: <code>consequatur</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>licensePlate</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="licensePlate"                data-endpoint="PUTapi-v1-license-plates--id_id-"
               value="consequatur"
               data-component="url">
    <br>
<p>The ID of the license plate (ULID) Example: <code>consequatur</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>country_code</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="country_code"                data-endpoint="PUTapi-v1-license-plates--id_id-"
               value="TG"
               data-component="body">
    <br>
<p>Code ISO 3166-1 alpha-2 du pays (ex: TG, BJ, GH). Example: <code>TG</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>TG</code></li> <li><code>BJ</code></li> <li><code>GH</code></li> <li><code>CI</code></li> <li><code>SN</code></li> <li><code>ML</code></li> <li><code>BF</code></li> <li><code>NE</code></li> <li><code>NG</code></li></ul>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>series</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="series"                data-endpoint="PUTapi-v1-license-plates--id_id-"
               value="AB"
               data-component="body">
    <br>
<p>Série alphabétique de la plaque. Must not be greater than 10 characters. Example: <code>AB</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>number</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="number"                data-endpoint="PUTapi-v1-license-plates--id_id-"
               value="1234"
               data-component="body">
    <br>
<p>Numéro de la plaque. Must not be greater than 20 characters. Example: <code>1234</code></p>
        </div>
        </form>

                    <h2 id="license-plate-management-DELETEapi-v1-license-plates--id_id-">Delete License Plate</h2>

<p>
</p>

<p>Delete the specified license plate from storage.</p>

<span id="example-requests-DELETEapi-v1-license-plates--id_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request DELETE \
    "http://localhost:8000/api/v1/license-plates/consequatur" \
    --header "Accept-Language: en" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/license-plates/consequatur"
);

const headers = {
    "Accept-Language": "en",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/license-plates/consequatur';
$response = $client-&gt;delete(
    $url,
    [
        'headers' =&gt; [
            'Accept-Language' =&gt; 'en',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-DELETEapi-v1-license-plates--id_id-">
            <blockquote>
            <p>Example response (204, Deleted):</p>
        </blockquote>
                <pre>
<code>Empty response</code>
 </pre>
    </span>
<span id="execution-results-DELETEapi-v1-license-plates--id_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-DELETEapi-v1-license-plates--id_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-DELETEapi-v1-license-plates--id_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-DELETEapi-v1-license-plates--id_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-DELETEapi-v1-license-plates--id_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-DELETEapi-v1-license-plates--id_id-" data-method="DELETE"
      data-path="api/v1/license-plates/{id_id}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('DELETEapi-v1-license-plates--id_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-DELETEapi-v1-license-plates--id_id-"
                    onclick="tryItOut('DELETEapi-v1-license-plates--id_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-DELETEapi-v1-license-plates--id_id-"
                    onclick="cancelTryOut('DELETEapi-v1-license-plates--id_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-DELETEapi-v1-license-plates--id_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/v1/license-plates/{id_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept-Language</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept-Language"                data-endpoint="DELETEapi-v1-license-plates--id_id-"
               value="en"
               data-component="header">
    <br>
<p>Example: <code>en</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="DELETEapi-v1-license-plates--id_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="DELETEapi-v1-license-plates--id_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="id_id"                data-endpoint="DELETEapi-v1-license-plates--id_id-"
               value="consequatur"
               data-component="url">
    <br>
<p>The ID of the id. Example: <code>consequatur</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>licensePlate</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="licensePlate"                data-endpoint="DELETEapi-v1-license-plates--id_id-"
               value="consequatur"
               data-component="url">
    <br>
<p>The ID of the license plate (ULID) Example: <code>consequatur</code></p>
            </div>
                    </form>

                <h1 id="organization-management">Organization management</h1>

    <p>APIs for managing organizations</p>

                                <h2 id="organization-management-GETapi-v1-organizations">List Organizations</h2>

<p>
</p>

<p>Get a listing of the organizations.</p>

<span id="example-requests-GETapi-v1-organizations">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/organizations" \
    --header "Accept-Language: en" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/organizations"
);

const headers = {
    "Accept-Language": "en",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/organizations';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Accept-Language' =&gt; 'en',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-organizations">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">[
    {
        &quot;id&quot;: &quot;01kk07x46vc3xfhg2z4954aq08&quot;,
        &quot;nom&quot;: &quot;Mueller-Dibbert&quot;,
        &quot;phone&quot;: &quot;1-678-371-7199&quot;,
        &quot;status&quot;: &quot;active&quot;,
        &quot;isActive&quot;: true,
        &quot;image&quot;: &quot;&quot;,
        &quot;thumbnail&quot;: null,
        &quot;createdAt&quot;: {
            &quot;datetime&quot;: &quot;2026-03-06T00:14:06.000000Z&quot;,
            &quot;humanDiff&quot;: &quot;0 seconds ago&quot;,
            &quot;human&quot;: &quot;Fri, Mar 6, 2026 12:14 AM&quot;
        }
    },
    {
        &quot;id&quot;: &quot;01kk07x4705zm747zdny4jbrh9&quot;,
        &quot;nom&quot;: &quot;Douglas, Schultz and Williamson&quot;,
        &quot;phone&quot;: &quot;(205) 633-2847&quot;,
        &quot;status&quot;: &quot;active&quot;,
        &quot;isActive&quot;: true,
        &quot;image&quot;: &quot;&quot;,
        &quot;thumbnail&quot;: null,
        &quot;createdAt&quot;: {
            &quot;datetime&quot;: &quot;2026-03-06T00:14:06.000000Z&quot;,
            &quot;humanDiff&quot;: &quot;0 seconds ago&quot;,
            &quot;human&quot;: &quot;Fri, Mar 6, 2026 12:14 AM&quot;
        }
    }
]</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-organizations" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-organizations"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-organizations"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-organizations" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-organizations">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-organizations" data-method="GET"
      data-path="api/v1/organizations"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-organizations', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-organizations"
                    onclick="tryItOut('GETapi-v1-organizations');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-organizations"
                    onclick="cancelTryOut('GETapi-v1-organizations');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-organizations"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/organizations</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept-Language</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept-Language"                data-endpoint="GETapi-v1-organizations"
               value="en"
               data-component="header">
    <br>
<p>Example: <code>en</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-organizations"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-organizations"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="organization-management-POSTapi-v1-organizations">Store Organization</h2>

<p>
</p>

<p>Store a newly created organization in storage.</p>

<span id="example-requests-POSTapi-v1-organizations">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/organizations" \
    --header "Accept-Language: en" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"nom\": \"Lavage Auto Express\",
    \"phone\": \"+22890123456\",
    \"status\": \"active\",
    \"user_id\": \"01JNXXXXXXXXXXXXXXXXXXXXXX\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/organizations"
);

const headers = {
    "Accept-Language": "en",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "nom": "Lavage Auto Express",
    "phone": "+22890123456",
    "status": "active",
    "user_id": "01JNXXXXXXXXXXXXXXXXXXXXXX"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/organizations';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Accept-Language' =&gt; 'en',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'nom' =&gt; 'Lavage Auto Express',
            'phone' =&gt; '+22890123456',
            'status' =&gt; 'active',
            'user_id' =&gt; '01JNXXXXXXXXXXXXXXXXXXXXXX',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-organizations">
            <blockquote>
            <p>Example response (201, Created):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Organization created successfully&quot;,
    &quot;data&quot;: {
        &quot;id&quot;: &quot;01jkp5zz...&quot;,
        &quot;name&quot;: &quot;Auto Wash Corp&quot;
    }
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-v1-organizations" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-organizations"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-organizations"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-organizations" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-organizations">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-organizations" data-method="POST"
      data-path="api/v1/organizations"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-organizations', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-organizations"
                    onclick="tryItOut('POSTapi-v1-organizations');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-organizations"
                    onclick="cancelTryOut('POSTapi-v1-organizations');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-organizations"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/organizations</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept-Language</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept-Language"                data-endpoint="POSTapi-v1-organizations"
               value="en"
               data-component="header">
    <br>
<p>Example: <code>en</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-organizations"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-organizations"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>nom</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="nom"                data-endpoint="POSTapi-v1-organizations"
               value="Lavage Auto Express"
               data-component="body">
    <br>
<p>Nom de l'organisation. Must not be greater than 255 characters. Example: <code>Lavage Auto Express</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>phone</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="phone"                data-endpoint="POSTapi-v1-organizations"
               value="+22890123456"
               data-component="body">
    <br>
<p>Numéro de téléphone de l'organisation. Example: <code>+22890123456</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>status</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="status"                data-endpoint="POSTapi-v1-organizations"
               value="active"
               data-component="body">
    <br>
<p>Statut de l'organisation (active ou inactive). Par défaut : active. Example: <code>active</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>active</code></li> <li><code>inactive</code></li></ul>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>user_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="user_id"                data-endpoint="POSTapi-v1-organizations"
               value="01JNXXXXXXXXXXXXXXXXXXXXXX"
               data-component="body">
    <br>
<p>Identifiant ULID du gérant de l'organisation. The <code>id</code> of an existing record in the users table. Example: <code>01JNXXXXXXXXXXXXXXXXXXXXXX</code></p>
        </div>
        </form>

                    <h2 id="organization-management-GETapi-v1-organizations--id_id-">Show Organization</h2>

<p>
</p>

<p>Show the specified organization.</p>

<span id="example-requests-GETapi-v1-organizations--id_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/organizations/consequatur" \
    --header "Accept-Language: en" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/organizations/consequatur"
);

const headers = {
    "Accept-Language": "en",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/organizations/consequatur';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Accept-Language' =&gt; 'en',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-organizations--id_id-">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;id&quot;: &quot;01kk07x482ygqzn5bx87amxk9d&quot;,
    &quot;nom&quot;: &quot;Mueller-Dibbert&quot;,
    &quot;phone&quot;: &quot;(360) 260-4954&quot;,
    &quot;status&quot;: &quot;active&quot;,
    &quot;isActive&quot;: true,
    &quot;image&quot;: &quot;&quot;,
    &quot;thumbnail&quot;: null,
    &quot;createdAt&quot;: {
        &quot;datetime&quot;: &quot;2026-03-06T00:14:06.000000Z&quot;,
        &quot;humanDiff&quot;: &quot;0 seconds ago&quot;,
        &quot;human&quot;: &quot;Fri, Mar 6, 2026 12:14 AM&quot;
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-organizations--id_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-organizations--id_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-organizations--id_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-organizations--id_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-organizations--id_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-organizations--id_id-" data-method="GET"
      data-path="api/v1/organizations/{id_id}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-organizations--id_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-organizations--id_id-"
                    onclick="tryItOut('GETapi-v1-organizations--id_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-organizations--id_id-"
                    onclick="cancelTryOut('GETapi-v1-organizations--id_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-organizations--id_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/organizations/{id_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept-Language</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept-Language"                data-endpoint="GETapi-v1-organizations--id_id-"
               value="en"
               data-component="header">
    <br>
<p>Example: <code>en</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-organizations--id_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-organizations--id_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="id_id"                data-endpoint="GETapi-v1-organizations--id_id-"
               value="consequatur"
               data-component="url">
    <br>
<p>The ID of the id. Example: <code>consequatur</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>organization</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="organization"                data-endpoint="GETapi-v1-organizations--id_id-"
               value="consequatur"
               data-component="url">
    <br>
<p>The ID of the organization (ULID) Example: <code>consequatur</code></p>
            </div>
                    </form>

                    <h2 id="organization-management-PUTapi-v1-organizations--id_id-">Update Organization</h2>

<p>
</p>

<p>Update the specified organization in storage.</p>

<span id="example-requests-PUTapi-v1-organizations--id_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request PUT \
    "http://localhost:8000/api/v1/organizations/consequatur" \
    --header "Accept-Language: en" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"nom\": \"Lavage Auto Express\",
    \"phone\": \"+22890123456\",
    \"status\": \"inactive\",
    \"user_id\": \"01JNXXXXXXXXXXXXXXXXXXXXXX\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/organizations/consequatur"
);

const headers = {
    "Accept-Language": "en",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "nom": "Lavage Auto Express",
    "phone": "+22890123456",
    "status": "inactive",
    "user_id": "01JNXXXXXXXXXXXXXXXXXXXXXX"
};

fetch(url, {
    method: "PUT",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/organizations/consequatur';
$response = $client-&gt;put(
    $url,
    [
        'headers' =&gt; [
            'Accept-Language' =&gt; 'en',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'nom' =&gt; 'Lavage Auto Express',
            'phone' =&gt; '+22890123456',
            'status' =&gt; 'inactive',
            'user_id' =&gt; '01JNXXXXXXXXXXXXXXXXXXXXXX',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-PUTapi-v1-organizations--id_id-">
            <blockquote>
            <p>Example response (200, Updated):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Organization updated successfully&quot;,
    &quot;data&quot;: {
        &quot;id&quot;: &quot;01jkp5zz...&quot;,
        &quot;name&quot;: &quot;Updated Auto Wash&quot;
    }
}</code>
 </pre>
    </span>
<span id="execution-results-PUTapi-v1-organizations--id_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-PUTapi-v1-organizations--id_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PUTapi-v1-organizations--id_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PUTapi-v1-organizations--id_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-PUTapi-v1-organizations--id_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-PUTapi-v1-organizations--id_id-" data-method="PUT"
      data-path="api/v1/organizations/{id_id}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PUTapi-v1-organizations--id_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-PUTapi-v1-organizations--id_id-"
                    onclick="tryItOut('PUTapi-v1-organizations--id_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-PUTapi-v1-organizations--id_id-"
                    onclick="cancelTryOut('PUTapi-v1-organizations--id_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-PUTapi-v1-organizations--id_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-darkblue">PUT</small>
            <b><code>api/v1/organizations/{id_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept-Language</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept-Language"                data-endpoint="PUTapi-v1-organizations--id_id-"
               value="en"
               data-component="header">
    <br>
<p>Example: <code>en</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PUTapi-v1-organizations--id_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PUTapi-v1-organizations--id_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="id_id"                data-endpoint="PUTapi-v1-organizations--id_id-"
               value="consequatur"
               data-component="url">
    <br>
<p>The ID of the id. Example: <code>consequatur</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>organization</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="organization"                data-endpoint="PUTapi-v1-organizations--id_id-"
               value="consequatur"
               data-component="url">
    <br>
<p>The ID of the organization (ULID) Example: <code>consequatur</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>nom</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="nom"                data-endpoint="PUTapi-v1-organizations--id_id-"
               value="Lavage Auto Express"
               data-component="body">
    <br>
<p>Nom de l'organisation. Must not be greater than 255 characters. Example: <code>Lavage Auto Express</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>phone</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="phone"                data-endpoint="PUTapi-v1-organizations--id_id-"
               value="+22890123456"
               data-component="body">
    <br>
<p>Numéro de téléphone de l'organisation. Example: <code>+22890123456</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>status</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="status"                data-endpoint="PUTapi-v1-organizations--id_id-"
               value="inactive"
               data-component="body">
    <br>
<p>Statut de l'organisation (active ou inactive). Example: <code>inactive</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>active</code></li> <li><code>inactive</code></li></ul>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>user_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="user_id"                data-endpoint="PUTapi-v1-organizations--id_id-"
               value="01JNXXXXXXXXXXXXXXXXXXXXXX"
               data-component="body">
    <br>
<p>Identifiant ULID du nouveau gérant de l'organisation. The <code>id</code> of an existing record in the users table. Example: <code>01JNXXXXXXXXXXXXXXXXXXXXXX</code></p>
        </div>
        </form>

                    <h2 id="organization-management-DELETEapi-v1-organizations--id_id-">Delete Organization</h2>

<p>
</p>

<p>Delete the specified organization from storage.</p>

<span id="example-requests-DELETEapi-v1-organizations--id_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request DELETE \
    "http://localhost:8000/api/v1/organizations/consequatur" \
    --header "Accept-Language: en" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/organizations/consequatur"
);

const headers = {
    "Accept-Language": "en",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/organizations/consequatur';
$response = $client-&gt;delete(
    $url,
    [
        'headers' =&gt; [
            'Accept-Language' =&gt; 'en',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-DELETEapi-v1-organizations--id_id-">
            <blockquote>
            <p>Example response (204, Deleted):</p>
        </blockquote>
                <pre>
<code>Empty response</code>
 </pre>
    </span>
<span id="execution-results-DELETEapi-v1-organizations--id_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-DELETEapi-v1-organizations--id_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-DELETEapi-v1-organizations--id_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-DELETEapi-v1-organizations--id_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-DELETEapi-v1-organizations--id_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-DELETEapi-v1-organizations--id_id-" data-method="DELETE"
      data-path="api/v1/organizations/{id_id}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('DELETEapi-v1-organizations--id_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-DELETEapi-v1-organizations--id_id-"
                    onclick="tryItOut('DELETEapi-v1-organizations--id_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-DELETEapi-v1-organizations--id_id-"
                    onclick="cancelTryOut('DELETEapi-v1-organizations--id_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-DELETEapi-v1-organizations--id_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/v1/organizations/{id_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept-Language</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept-Language"                data-endpoint="DELETEapi-v1-organizations--id_id-"
               value="en"
               data-component="header">
    <br>
<p>Example: <code>en</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="DELETEapi-v1-organizations--id_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="DELETEapi-v1-organizations--id_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="id_id"                data-endpoint="DELETEapi-v1-organizations--id_id-"
               value="consequatur"
               data-component="url">
    <br>
<p>The ID of the id. Example: <code>consequatur</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>organization</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="organization"                data-endpoint="DELETEapi-v1-organizations--id_id-"
               value="consequatur"
               data-component="url">
    <br>
<p>The ID of the organization (ULID) Example: <code>consequatur</code></p>
            </div>
                    </form>

                <h1 id="service-management">Service management</h1>

    <p>APIs for managing wash services</p>

                                <h2 id="service-management-GETapi-v1-services">List Services</h2>

<p>
</p>

<p>Get a listing of the wash services.</p>

<span id="example-requests-GETapi-v1-services">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/services" \
    --header "Accept-Language: en" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/services"
);

const headers = {
    "Accept-Language": "en",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/services';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Accept-Language' =&gt; 'en',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-services">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">[
    {
        &quot;id&quot;: &quot;01kk07x49sk4jj5p9hb0btd1ga&quot;,
        &quot;name&quot;: &quot;Polissage&quot;,
        &quot;description&quot;: null,
        &quot;createdAt&quot;: {
            &quot;datetime&quot;: &quot;2026-03-06T00:14:06.000000Z&quot;,
            &quot;humanDiff&quot;: &quot;0 seconds ago&quot;,
            &quot;human&quot;: &quot;Fri, Mar 6, 2026 12:14 AM&quot;
        }
    },
    {
        &quot;id&quot;: &quot;01kk07x49yaka7qfra2zdq91jc&quot;,
        &quot;name&quot;: &quot;Nettoyage moteur&quot;,
        &quot;description&quot;: null,
        &quot;createdAt&quot;: {
            &quot;datetime&quot;: &quot;2026-03-06T00:14:06.000000Z&quot;,
            &quot;humanDiff&quot;: &quot;0 seconds ago&quot;,
            &quot;human&quot;: &quot;Fri, Mar 6, 2026 12:14 AM&quot;
        }
    }
]</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-services" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-services"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-services"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-services" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-services">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-services" data-method="GET"
      data-path="api/v1/services"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-services', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-services"
                    onclick="tryItOut('GETapi-v1-services');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-services"
                    onclick="cancelTryOut('GETapi-v1-services');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-services"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/services</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept-Language</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept-Language"                data-endpoint="GETapi-v1-services"
               value="en"
               data-component="header">
    <br>
<p>Example: <code>en</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-services"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-services"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="service-management-POSTapi-v1-services">Store Service</h2>

<p>
</p>

<p>Store a newly created service.</p>

<span id="example-requests-POSTapi-v1-services">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/services" \
    --header "Accept-Language: en" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"name\": \"Lavage complet\",
    \"description\": \"Lavage intérieur et extérieur avec aspiration.\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/services"
);

const headers = {
    "Accept-Language": "en",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "Lavage complet",
    "description": "Lavage intérieur et extérieur avec aspiration."
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/services';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Accept-Language' =&gt; 'en',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'name' =&gt; 'Lavage complet',
            'description' =&gt; 'Lavage intérieur et extérieur avec aspiration.',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-services">
            <blockquote>
            <p>Example response (201, Created):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Service created successfully&quot;,
    &quot;data&quot;: {
        &quot;id&quot;: &quot;01jkp5zz...&quot;,
        &quot;name&quot;: &quot;Full Wash&quot;,
        &quot;description&quot;: &quot;Exterior and interior wash&quot;
    }
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-v1-services" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-services"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-services"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-services" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-services">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-services" data-method="POST"
      data-path="api/v1/services"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-services', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-services"
                    onclick="tryItOut('POSTapi-v1-services');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-services"
                    onclick="cancelTryOut('POSTapi-v1-services');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-services"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/services</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept-Language</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept-Language"                data-endpoint="POSTapi-v1-services"
               value="en"
               data-component="header">
    <br>
<p>Example: <code>en</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-services"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-services"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="name"                data-endpoint="POSTapi-v1-services"
               value="Lavage complet"
               data-component="body">
    <br>
<p>Nom du service. Must not be greater than 255 characters. Example: <code>Lavage complet</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>description</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="description"                data-endpoint="POSTapi-v1-services"
               value="Lavage intérieur et extérieur avec aspiration."
               data-component="body">
    <br>
<p>Description du service. Example: <code>Lavage intérieur et extérieur avec aspiration.</code></p>
        </div>
        </form>

                    <h2 id="service-management-GETapi-v1-services--id_id-">Show Service</h2>

<p>
</p>

<p>Show the specified service.</p>

<span id="example-requests-GETapi-v1-services--id_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/services/consequatur" \
    --header "Accept-Language: en" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/services/consequatur"
);

const headers = {
    "Accept-Language": "en",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/services/consequatur';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Accept-Language' =&gt; 'en',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-services--id_id-">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;id&quot;: &quot;01kk07x4baxr6sqsvsxhphdmw3&quot;,
    &quot;name&quot;: &quot;Lavage simple&quot;,
    &quot;description&quot;: &quot;Amet iste laborum eius est dolor dolores.&quot;,
    &quot;createdAt&quot;: {
        &quot;datetime&quot;: &quot;2026-03-06T00:14:06.000000Z&quot;,
        &quot;humanDiff&quot;: &quot;0 seconds ago&quot;,
        &quot;human&quot;: &quot;Fri, Mar 6, 2026 12:14 AM&quot;
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-services--id_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-services--id_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-services--id_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-services--id_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-services--id_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-services--id_id-" data-method="GET"
      data-path="api/v1/services/{id_id}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-services--id_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-services--id_id-"
                    onclick="tryItOut('GETapi-v1-services--id_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-services--id_id-"
                    onclick="cancelTryOut('GETapi-v1-services--id_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-services--id_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/services/{id_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept-Language</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept-Language"                data-endpoint="GETapi-v1-services--id_id-"
               value="en"
               data-component="header">
    <br>
<p>Example: <code>en</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-services--id_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-services--id_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="id_id"                data-endpoint="GETapi-v1-services--id_id-"
               value="consequatur"
               data-component="url">
    <br>
<p>The ID of the id. Example: <code>consequatur</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>service</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="service"                data-endpoint="GETapi-v1-services--id_id-"
               value="consequatur"
               data-component="url">
    <br>
<p>The ID of the service (ULID) Example: <code>consequatur</code></p>
            </div>
                    </form>

                    <h2 id="service-management-PUTapi-v1-services--id_id-">Update Service</h2>

<p>
</p>

<p>Update the specified service.</p>

<span id="example-requests-PUTapi-v1-services--id_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request PUT \
    "http://localhost:8000/api/v1/services/consequatur" \
    --header "Accept-Language: en" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"name\": \"Lavage complet\",
    \"description\": \"Lavage intérieur et extérieur.\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/services/consequatur"
);

const headers = {
    "Accept-Language": "en",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "Lavage complet",
    "description": "Lavage intérieur et extérieur."
};

fetch(url, {
    method: "PUT",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/services/consequatur';
$response = $client-&gt;put(
    $url,
    [
        'headers' =&gt; [
            'Accept-Language' =&gt; 'en',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'name' =&gt; 'Lavage complet',
            'description' =&gt; 'Lavage intérieur et extérieur.',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-PUTapi-v1-services--id_id-">
            <blockquote>
            <p>Example response (200, Updated):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Service updated successfully&quot;,
    &quot;data&quot;: {
        &quot;id&quot;: &quot;01jkp5zz...&quot;,
        &quot;name&quot;: &quot;Premium Wash&quot;
    }
}</code>
 </pre>
    </span>
<span id="execution-results-PUTapi-v1-services--id_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-PUTapi-v1-services--id_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PUTapi-v1-services--id_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PUTapi-v1-services--id_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-PUTapi-v1-services--id_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-PUTapi-v1-services--id_id-" data-method="PUT"
      data-path="api/v1/services/{id_id}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PUTapi-v1-services--id_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-PUTapi-v1-services--id_id-"
                    onclick="tryItOut('PUTapi-v1-services--id_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-PUTapi-v1-services--id_id-"
                    onclick="cancelTryOut('PUTapi-v1-services--id_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-PUTapi-v1-services--id_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-darkblue">PUT</small>
            <b><code>api/v1/services/{id_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept-Language</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept-Language"                data-endpoint="PUTapi-v1-services--id_id-"
               value="en"
               data-component="header">
    <br>
<p>Example: <code>en</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PUTapi-v1-services--id_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PUTapi-v1-services--id_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="id_id"                data-endpoint="PUTapi-v1-services--id_id-"
               value="consequatur"
               data-component="url">
    <br>
<p>The ID of the id. Example: <code>consequatur</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>service</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="service"                data-endpoint="PUTapi-v1-services--id_id-"
               value="consequatur"
               data-component="url">
    <br>
<p>The ID of the service (ULID) Example: <code>consequatur</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="name"                data-endpoint="PUTapi-v1-services--id_id-"
               value="Lavage complet"
               data-component="body">
    <br>
<p>Nom du service. Must not be greater than 255 characters. Example: <code>Lavage complet</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>description</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="description"                data-endpoint="PUTapi-v1-services--id_id-"
               value="Lavage intérieur et extérieur."
               data-component="body">
    <br>
<p>Description du service. Example: <code>Lavage intérieur et extérieur.</code></p>
        </div>
        </form>

                    <h2 id="service-management-DELETEapi-v1-services--id_id-">Delete Service</h2>

<p>
</p>

<p>Delete the specified service.</p>

<span id="example-requests-DELETEapi-v1-services--id_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request DELETE \
    "http://localhost:8000/api/v1/services/consequatur" \
    --header "Accept-Language: en" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/services/consequatur"
);

const headers = {
    "Accept-Language": "en",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/services/consequatur';
$response = $client-&gt;delete(
    $url,
    [
        'headers' =&gt; [
            'Accept-Language' =&gt; 'en',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-DELETEapi-v1-services--id_id-">
            <blockquote>
            <p>Example response (204, Deleted):</p>
        </blockquote>
                <pre>
<code>Empty response</code>
 </pre>
    </span>
<span id="execution-results-DELETEapi-v1-services--id_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-DELETEapi-v1-services--id_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-DELETEapi-v1-services--id_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-DELETEapi-v1-services--id_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-DELETEapi-v1-services--id_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-DELETEapi-v1-services--id_id-" data-method="DELETE"
      data-path="api/v1/services/{id_id}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('DELETEapi-v1-services--id_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-DELETEapi-v1-services--id_id-"
                    onclick="tryItOut('DELETEapi-v1-services--id_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-DELETEapi-v1-services--id_id-"
                    onclick="cancelTryOut('DELETEapi-v1-services--id_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-DELETEapi-v1-services--id_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/v1/services/{id_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept-Language</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept-Language"                data-endpoint="DELETEapi-v1-services--id_id-"
               value="en"
               data-component="header">
    <br>
<p>Example: <code>en</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="DELETEapi-v1-services--id_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="DELETEapi-v1-services--id_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="id_id"                data-endpoint="DELETEapi-v1-services--id_id-"
               value="consequatur"
               data-component="url">
    <br>
<p>The ID of the id. Example: <code>consequatur</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>service</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="service"                data-endpoint="DELETEapi-v1-services--id_id-"
               value="consequatur"
               data-component="url">
    <br>
<p>The ID of the service (ULID) Example: <code>consequatur</code></p>
            </div>
                    </form>

                <h1 id="user-management">User management</h1>

    <p>APIs for managing users</p>

                                <h2 id="user-management-GETapi-v1-users">List Users</h2>

<p>
</p>

<p>Get a listing of the users.</p>

<span id="example-requests-GETapi-v1-users">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/users" \
    --header "Accept-Language: en" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/users"
);

const headers = {
    "Accept-Language": "en",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/users';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Accept-Language' =&gt; 'en',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-users">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">[
    {
        &quot;id&quot;: &quot;01kk07x4csq10eb3bcwwe0hcex&quot;,
        &quot;email&quot;: &quot;carolyne.luettgen@example.org&quot;,
        &quot;phone&quot;: &quot;+1-850-772-8151&quot;,
        &quot;lastName&quot;: &quot;Williamson&quot;,
        &quot;firstName&quot;: &quot;Aurelia&quot;,
        &quot;fullName&quot;: &quot;Aurelia Williamson&quot;,
        &quot;birthday&quot;: &quot;1986-05-02&quot;,
        &quot;gender&quot;: &quot;female&quot;,
        &quot;role&quot;: null,
        &quot;address&quot;: &quot;54954 Eduardo Fall Suite 098\nKacieview, AK 23144-7088&quot;,
        &quot;image&quot;: &quot;http://localhost:8000/storage/13/user.jpg&quot;,
        &quot;thumbnail&quot;: &quot;http://localhost:8000/storage/13/user.jpg&quot;,
        &quot;createdAt&quot;: {
            &quot;datetime&quot;: &quot;2026-03-06T00:14:06.000000Z&quot;,
            &quot;humanDiff&quot;: &quot;0 seconds ago&quot;,
            &quot;human&quot;: &quot;Fri, Mar 6, 2026 12:14 AM&quot;
        }
    },
    {
        &quot;id&quot;: &quot;01kk07x4dgmk0gg0jhmegf74cs&quot;,
        &quot;email&quot;: &quot;tnitzsche@example.org&quot;,
        &quot;phone&quot;: &quot;1-270-634-6063&quot;,
        &quot;lastName&quot;: &quot;Kohler&quot;,
        &quot;firstName&quot;: &quot;Chelsie&quot;,
        &quot;fullName&quot;: &quot;Chelsie Kohler&quot;,
        &quot;birthday&quot;: &quot;2021-12-15&quot;,
        &quot;gender&quot;: &quot;male&quot;,
        &quot;role&quot;: null,
        &quot;address&quot;: &quot;8435 Alayna Squares Suite 143\nNew Micheal, MA 67733-5810&quot;,
        &quot;image&quot;: &quot;http://localhost:8000/storage/14/user.jpg&quot;,
        &quot;thumbnail&quot;: &quot;http://localhost:8000/storage/14/user.jpg&quot;,
        &quot;createdAt&quot;: {
            &quot;datetime&quot;: &quot;2026-03-06T00:14:06.000000Z&quot;,
            &quot;humanDiff&quot;: &quot;0 seconds ago&quot;,
            &quot;human&quot;: &quot;Fri, Mar 6, 2026 12:14 AM&quot;
        }
    }
]</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-users" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-users"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-users"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-users" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-users">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-users" data-method="GET"
      data-path="api/v1/users"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-users', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-users"
                    onclick="tryItOut('GETapi-v1-users');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-users"
                    onclick="cancelTryOut('GETapi-v1-users');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-users"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/users</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept-Language</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept-Language"                data-endpoint="GETapi-v1-users"
               value="en"
               data-component="header">
    <br>
<p>Example: <code>en</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-users"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-users"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="user-management-POSTapi-v1-users">Store User</h2>

<p>
</p>

<p>Store a newly created resource in storage.</p>

<span id="example-requests-POSTapi-v1-users">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/users" \
    --header "Accept-Language: en" \
    --header "Content-Type: multipart/form-data" \
    --header "Accept: application/json" \
    --form "last_name=Doe"\
    --form "first_name=John"\
    --form "birthday=1990-01-01"\
    --form "gender=male"\
    --form "role=admin"\
    --form "email=johnny@example.com"\
    --form "password=password"\
    --form "phone=22893413639"\
    --form "image=@C:\Users\Lenovo\AppData\Local\Temp\php94A5.tmp" </code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/users"
);

const headers = {
    "Accept-Language": "en",
    "Content-Type": "multipart/form-data",
    "Accept": "application/json",
};

const body = new FormData();
body.append('last_name', 'Doe');
body.append('first_name', 'John');
body.append('birthday', '1990-01-01');
body.append('gender', 'male');
body.append('role', 'admin');
body.append('email', 'johnny@example.com');
body.append('password', 'password');
body.append('phone', '22893413639');
body.append('image', document.querySelector('input[name="image"]').files[0]);

fetch(url, {
    method: "POST",
    headers,
    body,
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/users';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Accept-Language' =&gt; 'en',
            'Content-Type' =&gt; 'multipart/form-data',
            'Accept' =&gt; 'application/json',
        ],
        'multipart' =&gt; [
            [
                'name' =&gt; 'last_name',
                'contents' =&gt; 'Doe'
            ],
            [
                'name' =&gt; 'first_name',
                'contents' =&gt; 'John'
            ],
            [
                'name' =&gt; 'birthday',
                'contents' =&gt; '1990-01-01'
            ],
            [
                'name' =&gt; 'gender',
                'contents' =&gt; 'male'
            ],
            [
                'name' =&gt; 'role',
                'contents' =&gt; 'admin'
            ],
            [
                'name' =&gt; 'email',
                'contents' =&gt; 'johnny@example.com'
            ],
            [
                'name' =&gt; 'password',
                'contents' =&gt; 'password'
            ],
            [
                'name' =&gt; 'phone',
                'contents' =&gt; '22893413639'
            ],
            [
                'name' =&gt; 'image',
                'contents' =&gt; fopen('C:\Users\Lenovo\AppData\Local\Temp\php94A5.tmp', 'r')
            ],
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-users">
            <blockquote>
            <p>Example response (201, Created):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;User created successfully&quot;,
    &quot;data&quot;: {
        &quot;id&quot;: &quot;01jkp5zz...&quot;,
        &quot;email&quot;: &quot;user@example.com&quot;,
        &quot;lastName&quot;: &quot;Doe&quot;,
        &quot;firstName&quot;: &quot;John&quot;
    }
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-v1-users" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-users"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-users"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-users" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-users">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-users" data-method="POST"
      data-path="api/v1/users"
      data-authed="0"
      data-hasfiles="1"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-users', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-users"
                    onclick="tryItOut('POSTapi-v1-users');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-users"
                    onclick="cancelTryOut('POSTapi-v1-users');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-users"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/users</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept-Language</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept-Language"                data-endpoint="POSTapi-v1-users"
               value="en"
               data-component="header">
    <br>
<p>Example: <code>en</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-users"
               value="multipart/form-data"
               data-component="header">
    <br>
<p>Example: <code>multipart/form-data</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-users"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>last_name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="last_name"                data-endpoint="POSTapi-v1-users"
               value="Doe"
               data-component="body">
    <br>
<p>The last name of the user. Must not be greater than 255 characters. Example: <code>Doe</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>first_name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="first_name"                data-endpoint="POSTapi-v1-users"
               value="John"
               data-component="body">
    <br>
<p>The first name of the user. Must not be greater than 255 characters. Example: <code>John</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>birthday</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="birthday"                data-endpoint="POSTapi-v1-users"
               value="1990-01-01"
               data-component="body">
    <br>
<p>The birth date of the user. Must be a valid date. Must be a date before <code>today</code>. Example: <code>1990-01-01</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>gender</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="gender"                data-endpoint="POSTapi-v1-users"
               value="male"
               data-component="body">
    <br>
<p>The gender of the user. Example: <code>male</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>role</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="role"                data-endpoint="POSTapi-v1-users"
               value="admin"
               data-component="body">
    <br>
<p>The role of the user. Must not be greater than 255 characters. Example: <code>admin</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>email</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="email"                data-endpoint="POSTapi-v1-users"
               value="johnny@example.com"
               data-component="body">
    <br>
<p>The email address of the user. Must be a valid email address. Must not be greater than 255 characters. Example: <code>johnny@example.com</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>password</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="password"                data-endpoint="POSTapi-v1-users"
               value="password"
               data-component="body">
    <br>
<p>The password for the user account. Must be at least 8 characters. Example: <code>password</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>phone</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="phone"                data-endpoint="POSTapi-v1-users"
               value="22893413639"
               data-component="body">
    <br>
<p>The phone number of the user. Must match the regex /^([0-9\s-+()]*)$/. Must be at least 8 characters. Must not be greater than 12 characters. Example: <code>22893413639</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>image</code></b>&nbsp;&nbsp;
<small>file</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="file" style="display: none"
                              name="image"                data-endpoint="POSTapi-v1-users"
               value=""
               data-component="body">
    <br>
<p>The image of the user. Must be an image. Must not be greater than 2048 kilobytes. Example: <code>C:\Users\Lenovo\AppData\Local\Temp\php94A5.tmp</code></p>
        </div>
        </form>

                    <h2 id="user-management-GETapi-v1-users--id_id-">Show User</h2>

<p>
</p>

<p>Show the specified resource.</p>

<span id="example-requests-GETapi-v1-users--id_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/users/consequatur" \
    --header "Accept-Language: en" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/users/consequatur"
);

const headers = {
    "Accept-Language": "en",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/users/consequatur';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Accept-Language' =&gt; 'en',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-users--id_id-">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;id&quot;: &quot;01kk07x4ftsmpmmcdhj8q2wr9w&quot;,
    &quot;email&quot;: &quot;russel.bert@example.net&quot;,
    &quot;phone&quot;: &quot;+1 (779) 812-6447&quot;,
    &quot;lastName&quot;: &quot;Ankunding&quot;,
    &quot;firstName&quot;: &quot;Jerel&quot;,
    &quot;fullName&quot;: &quot;Jerel Ankunding&quot;,
    &quot;birthday&quot;: &quot;2005-12-26&quot;,
    &quot;gender&quot;: &quot;male&quot;,
    &quot;role&quot;: null,
    &quot;address&quot;: &quot;62028 Trudie Mills\nNorth Cordie, IL 75592&quot;,
    &quot;image&quot;: &quot;http://localhost:8000/storage/15/user.jpg&quot;,
    &quot;thumbnail&quot;: &quot;http://localhost:8000/storage/15/user.jpg&quot;,
    &quot;createdAt&quot;: {
        &quot;datetime&quot;: &quot;2026-03-06T00:14:06.000000Z&quot;,
        &quot;humanDiff&quot;: &quot;0 seconds ago&quot;,
        &quot;human&quot;: &quot;Fri, Mar 6, 2026 12:14 AM&quot;
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-users--id_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-users--id_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-users--id_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-users--id_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-users--id_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-users--id_id-" data-method="GET"
      data-path="api/v1/users/{id_id}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-users--id_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-users--id_id-"
                    onclick="tryItOut('GETapi-v1-users--id_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-users--id_id-"
                    onclick="cancelTryOut('GETapi-v1-users--id_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-users--id_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/users/{id_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept-Language</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept-Language"                data-endpoint="GETapi-v1-users--id_id-"
               value="en"
               data-component="header">
    <br>
<p>Example: <code>en</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-users--id_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-users--id_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="id_id"                data-endpoint="GETapi-v1-users--id_id-"
               value="consequatur"
               data-component="url">
    <br>
<p>The ID of the id. Example: <code>consequatur</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>user</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="user"                data-endpoint="GETapi-v1-users--id_id-"
               value="consequatur"
               data-component="url">
    <br>
<p>The ID of the user (ULID) Example: <code>consequatur</code></p>
            </div>
                    </form>

                    <h2 id="user-management-PUTapi-v1-users--id_id-">Update User</h2>

<p>
</p>

<p>Update the specified resource in storage.</p>

<span id="example-requests-PUTapi-v1-users--id_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request PUT \
    "http://localhost:8000/api/v1/users/consequatur" \
    --header "Accept-Language: en" \
    --header "Content-Type: multipart/form-data" \
    --header "Accept: application/json" \
    --form "last_name=Doe"\
    --form "first_name=John"\
    --form "birthday=1990-01-01"\
    --form "gender=male"\
    --form "role=admin"\
    --form "email=johnny@example.com"\
    --form "password=password"\
    --form "phone=22893413639"\
    --form "address[place_id]=ChIJN1t_tDeuEmsRUsoyG83frY4"\
    --form "address[place_name]=Eiffel Tower"\
    --form "address[longitude]=2.2945"\
    --form "address[latitude]=48.8584"\
    --form "address[street_name]=Champ de Mars"\
    --form "address[phone]=93078910"\
    --form "image=@C:\Users\Lenovo\AppData\Local\Temp\php94E5.tmp" </code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/users/consequatur"
);

const headers = {
    "Accept-Language": "en",
    "Content-Type": "multipart/form-data",
    "Accept": "application/json",
};

const body = new FormData();
body.append('last_name', 'Doe');
body.append('first_name', 'John');
body.append('birthday', '1990-01-01');
body.append('gender', 'male');
body.append('role', 'admin');
body.append('email', 'johnny@example.com');
body.append('password', 'password');
body.append('phone', '22893413639');
body.append('address[place_id]', 'ChIJN1t_tDeuEmsRUsoyG83frY4');
body.append('address[place_name]', 'Eiffel Tower');
body.append('address[longitude]', '2.2945');
body.append('address[latitude]', '48.8584');
body.append('address[street_name]', 'Champ de Mars');
body.append('address[phone]', '93078910');
body.append('image', document.querySelector('input[name="image"]').files[0]);

fetch(url, {
    method: "PUT",
    headers,
    body,
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/users/consequatur';
$response = $client-&gt;put(
    $url,
    [
        'headers' =&gt; [
            'Accept-Language' =&gt; 'en',
            'Content-Type' =&gt; 'multipart/form-data',
            'Accept' =&gt; 'application/json',
        ],
        'multipart' =&gt; [
            [
                'name' =&gt; 'last_name',
                'contents' =&gt; 'Doe'
            ],
            [
                'name' =&gt; 'first_name',
                'contents' =&gt; 'John'
            ],
            [
                'name' =&gt; 'birthday',
                'contents' =&gt; '1990-01-01'
            ],
            [
                'name' =&gt; 'gender',
                'contents' =&gt; 'male'
            ],
            [
                'name' =&gt; 'role',
                'contents' =&gt; 'admin'
            ],
            [
                'name' =&gt; 'email',
                'contents' =&gt; 'johnny@example.com'
            ],
            [
                'name' =&gt; 'password',
                'contents' =&gt; 'password'
            ],
            [
                'name' =&gt; 'phone',
                'contents' =&gt; '22893413639'
            ],
            [
                'name' =&gt; 'address[place_id]',
                'contents' =&gt; 'ChIJN1t_tDeuEmsRUsoyG83frY4'
            ],
            [
                'name' =&gt; 'address[place_name]',
                'contents' =&gt; 'Eiffel Tower'
            ],
            [
                'name' =&gt; 'address[longitude]',
                'contents' =&gt; '2.2945'
            ],
            [
                'name' =&gt; 'address[latitude]',
                'contents' =&gt; '48.8584'
            ],
            [
                'name' =&gt; 'address[street_name]',
                'contents' =&gt; 'Champ de Mars'
            ],
            [
                'name' =&gt; 'address[phone]',
                'contents' =&gt; '93078910'
            ],
            [
                'name' =&gt; 'image',
                'contents' =&gt; fopen('C:\Users\Lenovo\AppData\Local\Temp\php94E5.tmp', 'r')
            ],
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-PUTapi-v1-users--id_id-">
            <blockquote>
            <p>Example response (200, Updated):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;User updated successfully&quot;,
    &quot;data&quot;: {
        &quot;id&quot;: &quot;01jkp5zz...&quot;,
        &quot;email&quot;: &quot;user@example.com&quot;
    }
}</code>
 </pre>
    </span>
<span id="execution-results-PUTapi-v1-users--id_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-PUTapi-v1-users--id_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PUTapi-v1-users--id_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PUTapi-v1-users--id_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-PUTapi-v1-users--id_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-PUTapi-v1-users--id_id-" data-method="PUT"
      data-path="api/v1/users/{id_id}"
      data-authed="0"
      data-hasfiles="1"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PUTapi-v1-users--id_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-PUTapi-v1-users--id_id-"
                    onclick="tryItOut('PUTapi-v1-users--id_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-PUTapi-v1-users--id_id-"
                    onclick="cancelTryOut('PUTapi-v1-users--id_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-PUTapi-v1-users--id_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-darkblue">PUT</small>
            <b><code>api/v1/users/{id_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept-Language</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept-Language"                data-endpoint="PUTapi-v1-users--id_id-"
               value="en"
               data-component="header">
    <br>
<p>Example: <code>en</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PUTapi-v1-users--id_id-"
               value="multipart/form-data"
               data-component="header">
    <br>
<p>Example: <code>multipart/form-data</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PUTapi-v1-users--id_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="id_id"                data-endpoint="PUTapi-v1-users--id_id-"
               value="consequatur"
               data-component="url">
    <br>
<p>The ID of the id. Example: <code>consequatur</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>user</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="user"                data-endpoint="PUTapi-v1-users--id_id-"
               value="consequatur"
               data-component="url">
    <br>
<p>The ID of the user (ULID) Example: <code>consequatur</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>last_name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="last_name"                data-endpoint="PUTapi-v1-users--id_id-"
               value="Doe"
               data-component="body">
    <br>
<p>The last name of the user. Must not be greater than 255 characters. Example: <code>Doe</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>first_name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="first_name"                data-endpoint="PUTapi-v1-users--id_id-"
               value="John"
               data-component="body">
    <br>
<p>The first name of the user. Must not be greater than 255 characters. Example: <code>John</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>birthday</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="birthday"                data-endpoint="PUTapi-v1-users--id_id-"
               value="1990-01-01"
               data-component="body">
    <br>
<p>The birth date of the user. Must be a valid date. Must be a date before <code>today</code>. Example: <code>1990-01-01</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>gender</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="gender"                data-endpoint="PUTapi-v1-users--id_id-"
               value="male"
               data-component="body">
    <br>
<p>The gender of the user. Example: <code>male</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>role</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="role"                data-endpoint="PUTapi-v1-users--id_id-"
               value="admin"
               data-component="body">
    <br>
<p>The role of the user. Must not be greater than 255 characters. Example: <code>admin</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>email</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="email"                data-endpoint="PUTapi-v1-users--id_id-"
               value="johnny@example.com"
               data-component="body">
    <br>
<p>The email address of the user. Must be a valid email address. Must not be greater than 255 characters. Example: <code>johnny@example.com</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>password</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="password"                data-endpoint="PUTapi-v1-users--id_id-"
               value="password"
               data-component="body">
    <br>
<p>The password for the user account. Must be at least 8 characters. Example: <code>password</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>phone</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="phone"                data-endpoint="PUTapi-v1-users--id_id-"
               value="22893413639"
               data-component="body">
    <br>
<p>The phone number of the user. Must match the regex /^([0-9\s-+()]*)$/. Must be at least 8 characters. Must not be greater than 12 characters. Example: <code>22893413639</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>image</code></b>&nbsp;&nbsp;
<small>file</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="file" style="display: none"
                              name="image"                data-endpoint="PUTapi-v1-users--id_id-"
               value=""
               data-component="body">
    <br>
<p>The image of the user. Must be an image. Must not be greater than 2048 kilobytes. Example: <code>C:\Users\Lenovo\AppData\Local\Temp\php94E5.tmp</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
        <details>
            <summary style="padding-bottom: 10px;">
                <b style="line-height: 2;"><code>address</code></b>&nbsp;&nbsp;
<small>object</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
<br>

            </summary>
                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>place_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="address.place_id"                data-endpoint="PUTapi-v1-users--id_id-"
               value="ChIJN1t_tDeuEmsRUsoyG83frY4"
               data-component="body">
    <br>
<p>The unique identifier of the address. Example: <code>ChIJN1t_tDeuEmsRUsoyG83frY4</code></p>
                    </div>
                                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>place_name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="address.place_name"                data-endpoint="PUTapi-v1-users--id_id-"
               value="Eiffel Tower"
               data-component="body">
    <br>
<p>The name of the place associated with the address. Must not be greater than 255 characters. Example: <code>Eiffel Tower</code></p>
                    </div>
                                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>longitude</code></b>&nbsp;&nbsp;
<small>number</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="address.longitude"                data-endpoint="PUTapi-v1-users--id_id-"
               value="2.2945"
               data-component="body">
    <br>
<p>The longitude of the address. Example: <code>2.2945</code></p>
                    </div>
                                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>latitude</code></b>&nbsp;&nbsp;
<small>number</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="address.latitude"                data-endpoint="PUTapi-v1-users--id_id-"
               value="48.8584"
               data-component="body">
    <br>
<p>The latitude of the address. Example: <code>48.8584</code></p>
                    </div>
                                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>street_name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="address.street_name"                data-endpoint="PUTapi-v1-users--id_id-"
               value="Champ de Mars"
               data-component="body">
    <br>
<p>The street name of the address. Must not be greater than 255 characters. Example: <code>Champ de Mars</code></p>
                    </div>
                                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>phone</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="address.phone"                data-endpoint="PUTapi-v1-users--id_id-"
               value="93078910"
               data-component="body">
    <br>
<p>A unique phone number associated with the address. Must match the regex /^([0-9\s-+()]*)$/. Must be at least 8 characters. Must not be greater than 12 characters. Example: <code>93078910</code></p>
                    </div>
                                    </details>
        </div>
        </form>

                    <h2 id="user-management-DELETEapi-v1-users--id_id-">Delete User</h2>

<p>
</p>

<p>Delete the specified resource from storage.</p>

<span id="example-requests-DELETEapi-v1-users--id_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request DELETE \
    "http://localhost:8000/api/v1/users/consequatur" \
    --header "Accept-Language: en" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/users/consequatur"
);

const headers = {
    "Accept-Language": "en",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/users/consequatur';
$response = $client-&gt;delete(
    $url,
    [
        'headers' =&gt; [
            'Accept-Language' =&gt; 'en',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-DELETEapi-v1-users--id_id-">
            <blockquote>
            <p>Example response (204, Deleted):</p>
        </blockquote>
                <pre>
<code>Empty response</code>
 </pre>
    </span>
<span id="execution-results-DELETEapi-v1-users--id_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-DELETEapi-v1-users--id_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-DELETEapi-v1-users--id_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-DELETEapi-v1-users--id_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-DELETEapi-v1-users--id_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-DELETEapi-v1-users--id_id-" data-method="DELETE"
      data-path="api/v1/users/{id_id}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('DELETEapi-v1-users--id_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-DELETEapi-v1-users--id_id-"
                    onclick="tryItOut('DELETEapi-v1-users--id_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-DELETEapi-v1-users--id_id-"
                    onclick="cancelTryOut('DELETEapi-v1-users--id_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-DELETEapi-v1-users--id_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/v1/users/{id_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept-Language</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept-Language"                data-endpoint="DELETEapi-v1-users--id_id-"
               value="en"
               data-component="header">
    <br>
<p>Example: <code>en</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="DELETEapi-v1-users--id_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="DELETEapi-v1-users--id_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="id_id"                data-endpoint="DELETEapi-v1-users--id_id-"
               value="consequatur"
               data-component="url">
    <br>
<p>The ID of the id. Example: <code>consequatur</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>user</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="user"                data-endpoint="DELETEapi-v1-users--id_id-"
               value="consequatur"
               data-component="url">
    <br>
<p>The ID of the user (ULID) Example: <code>consequatur</code></p>
            </div>
                    </form>

                <h1 id="wash-ticket-management">Wash Ticket management</h1>

    <p>APIs for managing wash tickets</p>

                                <h2 id="wash-ticket-management-GETapi-v1-wash-tickets">List Wash Tickets</h2>

<p>
</p>

<p>Get a listing of the wash tickets.</p>

<span id="example-requests-GETapi-v1-wash-tickets">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/wash-tickets" \
    --header "Accept-Language: en" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/wash-tickets"
);

const headers = {
    "Accept-Language": "en",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/wash-tickets';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Accept-Language' =&gt; 'en',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-wash-tickets">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">[
    {
        &quot;id&quot;: &quot;01kk07x4rxsx1y02sg4e47zn6q&quot;,
        &quot;ticketNumber&quot;: &quot;#1030803&quot;,
        &quot;status&quot;: &quot;arrive&quot;,
        &quot;statusLabel&quot;: &quot;Arriv&eacute;&quot;,
        &quot;paymentStatus&quot;: &quot;non_paye&quot;,
        &quot;paymentStatusLabel&quot;: &quot;Non pay&eacute;&quot;,
        &quot;invoicePrice&quot;: 2739,
        &quot;formattedInvoicePrice&quot;: &quot;2 739 FCFA&quot;,
        &quot;cost&quot;: 1244,
        &quot;isPaid&quot;: false,
        &quot;isCancelled&quot;: false,
        &quot;createdAt&quot;: {
            &quot;datetime&quot;: &quot;2026-03-06T00:14:06.000000Z&quot;,
            &quot;humanDiff&quot;: &quot;0 seconds ago&quot;,
            &quot;human&quot;: &quot;Fri, Mar 6, 2026 12:14 AM&quot;
        }
    },
    {
        &quot;id&quot;: &quot;01kk07x4sw3g2f2p3knfq7pvrd&quot;,
        &quot;ticketNumber&quot;: &quot;#3609759&quot;,
        &quot;status&quot;: &quot;arrive&quot;,
        &quot;statusLabel&quot;: &quot;Arriv&eacute;&quot;,
        &quot;paymentStatus&quot;: &quot;non_paye&quot;,
        &quot;paymentStatusLabel&quot;: &quot;Non pay&eacute;&quot;,
        &quot;invoicePrice&quot;: 2101,
        &quot;formattedInvoicePrice&quot;: &quot;2 101 FCFA&quot;,
        &quot;cost&quot;: 174,
        &quot;isPaid&quot;: false,
        &quot;isCancelled&quot;: false,
        &quot;createdAt&quot;: {
            &quot;datetime&quot;: &quot;2026-03-06T00:14:06.000000Z&quot;,
            &quot;humanDiff&quot;: &quot;0 seconds ago&quot;,
            &quot;human&quot;: &quot;Fri, Mar 6, 2026 12:14 AM&quot;
        }
    }
]</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-wash-tickets" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-wash-tickets"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-wash-tickets"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-wash-tickets" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-wash-tickets">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-wash-tickets" data-method="GET"
      data-path="api/v1/wash-tickets"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-wash-tickets', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-wash-tickets"
                    onclick="tryItOut('GETapi-v1-wash-tickets');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-wash-tickets"
                    onclick="cancelTryOut('GETapi-v1-wash-tickets');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-wash-tickets"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/wash-tickets</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept-Language</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept-Language"                data-endpoint="GETapi-v1-wash-tickets"
               value="en"
               data-component="header">
    <br>
<p>Example: <code>en</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-wash-tickets"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-wash-tickets"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="wash-ticket-management-POSTapi-v1-wash-tickets">Store Wash Ticket</h2>

<p>
</p>

<p>Store a newly created wash ticket in storage.</p>

<span id="example-requests-POSTapi-v1-wash-tickets">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/wash-tickets" \
    --header "Accept-Language: en" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"organization_id\": \"01JNXXXXXXXXXXXXXXXXXXXXXX\",
    \"vehicle_id\": \"01JNXXXXXXXXXXXXXXXXXXXXXX\",
    \"service_id\": \"01JNXXXXXXXXXXXXXXXXXXXXXX\",
    \"user_id\": \"01JNXXXXXXXXXXXXXXXXXXXXXX\",
    \"client_id\": \"01JNXXXXXXXXXXXXXXXXXXXXXX\",
    \"status\": \"pret\",
    \"payment_status\": \"paye\",
    \"invoice_price\": 1500,
    \"cost\": 500
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/wash-tickets"
);

const headers = {
    "Accept-Language": "en",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "organization_id": "01JNXXXXXXXXXXXXXXXXXXXXXX",
    "vehicle_id": "01JNXXXXXXXXXXXXXXXXXXXXXX",
    "service_id": "01JNXXXXXXXXXXXXXXXXXXXXXX",
    "user_id": "01JNXXXXXXXXXXXXXXXXXXXXXX",
    "client_id": "01JNXXXXXXXXXXXXXXXXXXXXXX",
    "status": "pret",
    "payment_status": "paye",
    "invoice_price": 1500,
    "cost": 500
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/wash-tickets';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Accept-Language' =&gt; 'en',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'organization_id' =&gt; '01JNXXXXXXXXXXXXXXXXXXXXXX',
            'vehicle_id' =&gt; '01JNXXXXXXXXXXXXXXXXXXXXXX',
            'service_id' =&gt; '01JNXXXXXXXXXXXXXXXXXXXXXX',
            'user_id' =&gt; '01JNXXXXXXXXXXXXXXXXXXXXXX',
            'client_id' =&gt; '01JNXXXXXXXXXXXXXXXXXXXXXX',
            'status' =&gt; 'pret',
            'payment_status' =&gt; 'paye',
            'invoice_price' =&gt; 1500,
            'cost' =&gt; 500,
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-wash-tickets">
            <blockquote>
            <p>Example response (201, Created):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Wash ticket created successfully&quot;,
    &quot;data&quot;: {
        &quot;id&quot;: &quot;01jkp5zz...&quot;,
        &quot;totalAmount&quot;: 5000
    }
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-v1-wash-tickets" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-wash-tickets"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-wash-tickets"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-wash-tickets" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-wash-tickets">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-wash-tickets" data-method="POST"
      data-path="api/v1/wash-tickets"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-wash-tickets', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-wash-tickets"
                    onclick="tryItOut('POSTapi-v1-wash-tickets');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-wash-tickets"
                    onclick="cancelTryOut('POSTapi-v1-wash-tickets');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-wash-tickets"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/wash-tickets</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept-Language</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept-Language"                data-endpoint="POSTapi-v1-wash-tickets"
               value="en"
               data-component="header">
    <br>
<p>Example: <code>en</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-wash-tickets"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-wash-tickets"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>organization_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="organization_id"                data-endpoint="POSTapi-v1-wash-tickets"
               value="01JNXXXXXXXXXXXXXXXXXXXXXX"
               data-component="body">
    <br>
<p>Identifiant ULID de l'organisation. The <code>id</code> of an existing record in the organizations table. Example: <code>01JNXXXXXXXXXXXXXXXXXXXXXX</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>vehicle_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="vehicle_id"                data-endpoint="POSTapi-v1-wash-tickets"
               value="01JNXXXXXXXXXXXXXXXXXXXXXX"
               data-component="body">
    <br>
<p>Identifiant ULID de l'engin. The <code>id</code> of an existing record in the vehicles table. Example: <code>01JNXXXXXXXXXXXXXXXXXXXXXX</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>service_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="service_id"                data-endpoint="POSTapi-v1-wash-tickets"
               value="01JNXXXXXXXXXXXXXXXXXXXXXX"
               data-component="body">
    <br>
<p>Identifiant ULID du service. The <code>id</code> of an existing record in the services table. Example: <code>01JNXXXXXXXXXXXXXXXXXXXXXX</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>user_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="user_id"                data-endpoint="POSTapi-v1-wash-tickets"
               value="01JNXXXXXXXXXXXXXXXXXXXXXX"
               data-component="body">
    <br>
<p>Identifiant ULID de la caissière. The <code>id</code> of an existing record in the users table. Example: <code>01JNXXXXXXXXXXXXXXXXXXXXXX</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>client_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="client_id"                data-endpoint="POSTapi-v1-wash-tickets"
               value="01JNXXXXXXXXXXXXXXXXXXXXXX"
               data-component="body">
    <br>
<p>Identifiant ULID du client déposant l'engin. The <code>id</code> of an existing record in the users table. Example: <code>01JNXXXXXXXXXXXXXXXXXXXXXX</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>status</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="status"                data-endpoint="POSTapi-v1-wash-tickets"
               value="pret"
               data-component="body">
    <br>
<p>Example: <code>pret</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>arrive</code></li> <li><code>enregistre</code></li> <li><code>en_attente</code></li> <li><code>en_lavage</code></li> <li><code>lave</code></li> <li><code>pret</code></li> <li><code>livre</code></li> <li><code>annule</code></li></ul>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>payment_status</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="payment_status"                data-endpoint="POSTapi-v1-wash-tickets"
               value="paye"
               data-component="body">
    <br>
<p>Example: <code>paye</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>non_paye</code></li> <li><code>paye</code></li></ul>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>invoice_price</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="invoice_price"                data-endpoint="POSTapi-v1-wash-tickets"
               value="1500"
               data-component="body">
    <br>
<p>Prix facturé au client (FCFA). Must be at least 0. Example: <code>1500</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>cost</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="cost"                data-endpoint="POSTapi-v1-wash-tickets"
               value="500"
               data-component="body">
    <br>
<p>Coût interne du lavage (FCFA). Must be at least 0. Example: <code>500</code></p>
        </div>
        </form>

                    <h2 id="wash-ticket-management-GETapi-v1-wash-tickets--id_id-">Show Wash Ticket</h2>

<p>
</p>

<p>Show the specified wash ticket.</p>

<span id="example-requests-GETapi-v1-wash-tickets--id_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/wash-tickets/consequatur" \
    --header "Accept-Language: en" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/wash-tickets/consequatur"
);

const headers = {
    "Accept-Language": "en",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/wash-tickets/consequatur';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Accept-Language' =&gt; 'en',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-wash-tickets--id_id-">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;id&quot;: &quot;01kk07x4vsva03z5rrwdakac5y&quot;,
    &quot;ticketNumber&quot;: &quot;#9408620&quot;,
    &quot;status&quot;: &quot;arrive&quot;,
    &quot;statusLabel&quot;: &quot;Arriv&eacute;&quot;,
    &quot;paymentStatus&quot;: &quot;non_paye&quot;,
    &quot;paymentStatusLabel&quot;: &quot;Non pay&eacute;&quot;,
    &quot;invoicePrice&quot;: 2739,
    &quot;formattedInvoicePrice&quot;: &quot;2 739 FCFA&quot;,
    &quot;cost&quot;: 1244,
    &quot;isPaid&quot;: false,
    &quot;isCancelled&quot;: false,
    &quot;createdAt&quot;: {
        &quot;datetime&quot;: &quot;2026-03-06T00:14:06.000000Z&quot;,
        &quot;humanDiff&quot;: &quot;0 seconds ago&quot;,
        &quot;human&quot;: &quot;Fri, Mar 6, 2026 12:14 AM&quot;
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-wash-tickets--id_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-wash-tickets--id_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-wash-tickets--id_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-wash-tickets--id_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-wash-tickets--id_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-wash-tickets--id_id-" data-method="GET"
      data-path="api/v1/wash-tickets/{id_id}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-wash-tickets--id_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-wash-tickets--id_id-"
                    onclick="tryItOut('GETapi-v1-wash-tickets--id_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-wash-tickets--id_id-"
                    onclick="cancelTryOut('GETapi-v1-wash-tickets--id_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-wash-tickets--id_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/wash-tickets/{id_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept-Language</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept-Language"                data-endpoint="GETapi-v1-wash-tickets--id_id-"
               value="en"
               data-component="header">
    <br>
<p>Example: <code>en</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-wash-tickets--id_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-wash-tickets--id_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="id_id"                data-endpoint="GETapi-v1-wash-tickets--id_id-"
               value="consequatur"
               data-component="url">
    <br>
<p>The ID of the id. Example: <code>consequatur</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>washTicket</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="washTicket"                data-endpoint="GETapi-v1-wash-tickets--id_id-"
               value="consequatur"
               data-component="url">
    <br>
<p>The ID of the wash ticket (ULID) Example: <code>consequatur</code></p>
            </div>
                    </form>

                    <h2 id="wash-ticket-management-PUTapi-v1-wash-tickets--id_id-">Update Wash Ticket</h2>

<p>
</p>

<p>Update the specified wash ticket in storage.</p>

<span id="example-requests-PUTapi-v1-wash-tickets--id_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request PUT \
    "http://localhost:8000/api/v1/wash-tickets/consequatur" \
    --header "Accept-Language: en" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"status\": \"en_lavage\",
    \"payment_status\": \"paye\",
    \"invoice_price\": 1500,
    \"cost\": 500
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/wash-tickets/consequatur"
);

const headers = {
    "Accept-Language": "en",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "status": "en_lavage",
    "payment_status": "paye",
    "invoice_price": 1500,
    "cost": 500
};

fetch(url, {
    method: "PUT",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/wash-tickets/consequatur';
$response = $client-&gt;put(
    $url,
    [
        'headers' =&gt; [
            'Accept-Language' =&gt; 'en',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'status' =&gt; 'en_lavage',
            'payment_status' =&gt; 'paye',
            'invoice_price' =&gt; 1500,
            'cost' =&gt; 500,
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-PUTapi-v1-wash-tickets--id_id-">
            <blockquote>
            <p>Example response (200, Updated):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Wash ticket updated successfully&quot;,
    &quot;data&quot;: {
        &quot;id&quot;: &quot;01jkp5zz...&quot;
    }
}</code>
 </pre>
    </span>
<span id="execution-results-PUTapi-v1-wash-tickets--id_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-PUTapi-v1-wash-tickets--id_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PUTapi-v1-wash-tickets--id_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PUTapi-v1-wash-tickets--id_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-PUTapi-v1-wash-tickets--id_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-PUTapi-v1-wash-tickets--id_id-" data-method="PUT"
      data-path="api/v1/wash-tickets/{id_id}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PUTapi-v1-wash-tickets--id_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-PUTapi-v1-wash-tickets--id_id-"
                    onclick="tryItOut('PUTapi-v1-wash-tickets--id_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-PUTapi-v1-wash-tickets--id_id-"
                    onclick="cancelTryOut('PUTapi-v1-wash-tickets--id_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-PUTapi-v1-wash-tickets--id_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-darkblue">PUT</small>
            <b><code>api/v1/wash-tickets/{id_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept-Language</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept-Language"                data-endpoint="PUTapi-v1-wash-tickets--id_id-"
               value="en"
               data-component="header">
    <br>
<p>Example: <code>en</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PUTapi-v1-wash-tickets--id_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PUTapi-v1-wash-tickets--id_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="id_id"                data-endpoint="PUTapi-v1-wash-tickets--id_id-"
               value="consequatur"
               data-component="url">
    <br>
<p>The ID of the id. Example: <code>consequatur</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>washTicket</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="washTicket"                data-endpoint="PUTapi-v1-wash-tickets--id_id-"
               value="consequatur"
               data-component="url">
    <br>
<p>The ID of the wash ticket (ULID) Example: <code>consequatur</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>status</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="status"                data-endpoint="PUTapi-v1-wash-tickets--id_id-"
               value="en_lavage"
               data-component="body">
    <br>
<p>Nouveau statut du ticket. Example: <code>en_lavage</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>arrive</code></li> <li><code>enregistre</code></li> <li><code>en_attente</code></li> <li><code>en_lavage</code></li> <li><code>lave</code></li> <li><code>pret</code></li> <li><code>livre</code></li> <li><code>annule</code></li></ul>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>payment_status</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="payment_status"                data-endpoint="PUTapi-v1-wash-tickets--id_id-"
               value="paye"
               data-component="body">
    <br>
<p>Statut du paiement. Example: <code>paye</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>non_paye</code></li> <li><code>paye</code></li></ul>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>invoice_price</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="invoice_price"                data-endpoint="PUTapi-v1-wash-tickets--id_id-"
               value="1500"
               data-component="body">
    <br>
<p>Prix facturé (FCFA). Must be at least 0. Example: <code>1500</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>cost</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="cost"                data-endpoint="PUTapi-v1-wash-tickets--id_id-"
               value="500"
               data-component="body">
    <br>
<p>Coût interne (FCFA). Must be at least 0. Example: <code>500</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>user_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="user_id"                data-endpoint="PUTapi-v1-wash-tickets--id_id-"
               value=""
               data-component="body">
    <br>
<p>The <code>id</code> of an existing record in the users table.</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>client_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="client_id"                data-endpoint="PUTapi-v1-wash-tickets--id_id-"
               value=""
               data-component="body">
    <br>
<p>The <code>id</code> of an existing record in the users table.</p>
        </div>
        </form>

                    <h2 id="wash-ticket-management-DELETEapi-v1-wash-tickets--id_id-">Delete Wash Ticket</h2>

<p>
</p>

<p>Delete the specified wash ticket from storage.</p>

<span id="example-requests-DELETEapi-v1-wash-tickets--id_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request DELETE \
    "http://localhost:8000/api/v1/wash-tickets/consequatur" \
    --header "Accept-Language: en" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/wash-tickets/consequatur"
);

const headers = {
    "Accept-Language": "en",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/wash-tickets/consequatur';
$response = $client-&gt;delete(
    $url,
    [
        'headers' =&gt; [
            'Accept-Language' =&gt; 'en',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-DELETEapi-v1-wash-tickets--id_id-">
            <blockquote>
            <p>Example response (204, Deleted):</p>
        </blockquote>
                <pre>
<code>Empty response</code>
 </pre>
    </span>
<span id="execution-results-DELETEapi-v1-wash-tickets--id_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-DELETEapi-v1-wash-tickets--id_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-DELETEapi-v1-wash-tickets--id_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-DELETEapi-v1-wash-tickets--id_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-DELETEapi-v1-wash-tickets--id_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-DELETEapi-v1-wash-tickets--id_id-" data-method="DELETE"
      data-path="api/v1/wash-tickets/{id_id}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('DELETEapi-v1-wash-tickets--id_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-DELETEapi-v1-wash-tickets--id_id-"
                    onclick="tryItOut('DELETEapi-v1-wash-tickets--id_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-DELETEapi-v1-wash-tickets--id_id-"
                    onclick="cancelTryOut('DELETEapi-v1-wash-tickets--id_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-DELETEapi-v1-wash-tickets--id_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/v1/wash-tickets/{id_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept-Language</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept-Language"                data-endpoint="DELETEapi-v1-wash-tickets--id_id-"
               value="en"
               data-component="header">
    <br>
<p>Example: <code>en</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="DELETEapi-v1-wash-tickets--id_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="DELETEapi-v1-wash-tickets--id_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="id_id"                data-endpoint="DELETEapi-v1-wash-tickets--id_id-"
               value="consequatur"
               data-component="url">
    <br>
<p>The ID of the id. Example: <code>consequatur</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>washTicket</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="washTicket"                data-endpoint="DELETEapi-v1-wash-tickets--id_id-"
               value="consequatur"
               data-component="url">
    <br>
<p>The ID of the wash ticket (ULID) Example: <code>consequatur</code></p>
            </div>
                    </form>

            

        
    </div>
    <div class="dark-box">
                    <div class="lang-selector">
                                                        <button type="button" class="lang-button" data-language-name="bash">bash</button>
                                                        <button type="button" class="lang-button" data-language-name="javascript">javascript</button>
                                                        <button type="button" class="lang-button" data-language-name="php">php</button>
                            </div>
            </div>
</div>
</body>
</html>
