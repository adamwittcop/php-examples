<?php

// Sample data for creating token
$publicKey = "yNdpgvgFQqOKaomw5Wnfea9c8CQaSLpO4xYMvVtRyS3_8NrNZlRpeY7LW4OZZMfi"; // read from environment
$privateKey = "dAi0CckK-EUVnbb0U97SjOuj_BtkgnUCTkqDwW6NtKdVl6Q2nQBF1fg3-tzQ_cUEcqEU5VdAtwntqYvaL-AC4w"; // read from environment
$issuedAt = time();
$audience = [
    'post.https://api.moov.io/enroll'
];
$organizationID = 'your-id'; // generated after signup

// TODO(adam): paygate, customers, quick-enroll, hydrate need to verify tumbler claims

// Create the API token
print("Creating API token..." . "\n");
$token = createApiToken($publicKey, $privateKey, $issuedAt, $audience, $organizationID);
print("Created token: " . $token ."\n");

// Use API token to authenticate
print("Authentication with API token..." . "\n");
$authResponse = authWithToken($token);
print("Auth response body: " . "\n" . $authResponse . "\n");
print("Authentication complete" . "\n");

function createApiToken($publicKey, $secretKey, $issuedAt, $audience) {
    $header = array(
        "alg" => "HS256",
        "kid" => $publicKey,
    );
    $claims = array(
        "iat" => $issuedAt,
        "exp" => $issuedAt + (60 * 15),
        "aud" => $audience,
        "org" => $organizationID
    );
    $header64 = base64url_encode(json_encode($header, JSON_UNESCAPED_SLASHES));
    $claims64 = base64url_encode(json_encode($claims, JSON_UNESCAPED_SLASHES));

    $content = $header64 . "." . $claims64;

    $key64 = base64url_decode($secretKey);

    // Create hash
    $s = hash_hmac('sha256', $content, $key64, true);
    $hash = base64url_encode($s);

    return $content . "." . $hash;
}

function authWithToken($token) {
    $opts = array(
        'http' => array(
            'method' => 'GET',
            'header' => 'Authorization: Bearer ' . $token . "\r\n"
        )
    );

    $context = stream_context_create($opts);

    $resp = file_get_contents('https://api.moov.io/enroll', false, $context);
}

function base64url_encode($data) {
    return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
}

function base64url_decode($data) {
    return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
}
