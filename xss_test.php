<?php
// WARNING: This file is intentionally vulnerable to demonstrate XSS.
// DO NOT use it on production servers.
// Keep it only for learning/testing in a safe environment.

$q = $_GET['q'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>XSS Test Page</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background: #111;
        color: #eee;
        padding: 30px;
    }
    input {
        padding: 10px;
        width: 300px;
        border-radius: 5px;
        border: none;
    }
    button {
        padding: 10px;
        border: none;
        background: #00b0ff;
        color: #000;
        font-weight: bold;
        border-radius: 5px;
        cursor: pointer;
    }
    .output-box {
        background: #222;
        padding: 20px;
        margin-top: 20px;
        border-radius: 10px;
    }
</style>
</head>
<body>

<h2>XSS Testing Sandbox</h2>
<p>Type any input to see how it behaves when not escaped.</p>

<form method="get">
    <input type="text" name="q" placeholder="Enter payload…" value="<?= $q ?>">
    <button type="submit">Run</button>
</form>

<div class="output-box">
    <strong>Output (vulnerable):</strong><br><br>
    <?= $q ?> <!-- RAW, unescaped -->
</div>

</body>
</html>
