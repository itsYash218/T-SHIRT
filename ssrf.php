<?php
// ----- YOUR ORIGINAL SSRF DEMO LOGIC (unchanged) -----
$url = $_GET['url'] ?? '';
?>
<!DOCTYPE html>
<html>
<head>
<title>SSRF Demo</title>

<style>
/* ----------- Green Terminal Theme ----------- */
body {
    background: #000;
    color: #0f0;
    font-family: monospace;
    padding: 20px;
}

h2 {
    color: #00ff00;
    border-bottom: 1px solid #00ff00;
    padding-bottom: 5px;
    margin-bottom: 20px;
}

form {
    margin: 20px 0;
}

input[type="text"] {
    padding: 10px 16px;
    width: 70%;
    border: 1px solid #00ff00;
    border-radius: 20px;
    background: #003300;
    color: #00ff00;
    font-size: 1em;
    outline: none;
    transition: 0.2s;
}   

button {
    padding: 10px 20px;
    margin-left: 10px;
    background: #003300;
    color: #00ff00;
    border: 1px solid #00ff00;
    border-radius: 20px;
    cursor: pointer;
    font-weight: bold;
    transition: 0.2s;
}


pre {
    background: #001900;
    padding: 15px;
    border: 1px solid #00ff00;
    border-radius: 10px;
    color: #00ff00;
    overflow-x: auto;
}
</style>

</head>
<body>

<h2>🔵 SSRF Demonstration</h2>

<form method="get">
    <input type="text" name="url" placeholder="Enter URL..." 
           value="<?= htmlspecialchars($url) ?>">
    <button type="submit">Fetch</button>
</form>

<?php
if ($url) {
    echo "<p><strong>Fetching:</strong> " . htmlspecialchars($url) . "</p>";

    // your intentionally unsafe SSRF line (unchanged)
    $response = @file_get_contents($url);

    echo "<pre>";
    echo htmlspecialchars($response);
    echo "</pre>";
}
?>

</body>
</html>
