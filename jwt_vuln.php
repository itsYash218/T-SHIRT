<?php
$weak_secret = "123"; // extremely weak secret key

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode($data) {
    return base64_decode(strtr($data, '-_', '+/'));
}

// Create a vulnerable JWT
function create_vuln_jwt($username, $is_admin = false) {
    global $weak_secret;

    $header = base64url_encode(json_encode([
        "alg" => "HS256",
        "typ" => "JWT"
    ]));

    $payload = base64url_encode(json_encode([
        "user" => $username,
        "admin" => $is_admin
    ]));

    // ❗ Weak signature
    $signature = base64url_encode(hash_hmac("sha256", "$header.$payload", $weak_secret, true));

    return "$header.$payload.$signature";
}

// Validate (poorly)
function validate_vuln_jwt($token) {
    global $weak_secret;

    $parts = explode(".", $token);
    if (count($parts) !== 3) return false;

    list($header_b64, $payload_b64, $signature_b64) = $parts;

    $header = json_decode(base64url_decode($header_b64), true);

    // ❗ Allow "none" algorithm = bypass
    if ($header["alg"] === "none") {
        return json_decode(base64url_decode($payload_b64), true);
    }

    // ❗ Very weak validation
    $expected = base64url_encode(hash_hmac("sha256", "$header_b64.$payload_b64", $weak_secret, true));

    if ($expected === $signature_b64) {
        return json_decode(base64url_decode($payload_b64), true);
    }

    return false;
}
?>
