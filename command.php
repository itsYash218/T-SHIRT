<?php
// Simple Linux Command Injection Demo (Safe Version)

if (isset($_GET['cmd'])) {

    $cmd = $_GET['cmd'];

    // Execute command
    $output = shell_exec($cmd . " 2>&1");
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Command Injection Demo</title>
<style>
body { background:#000; color:#0f0; font-family:monospace; padding:20px; }
input { width:80%; padding:10px; }

/* Input box */
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
    background: #003300;         
    color: #00ff00;               
    border: 1px solid #00ff00;   
    border-radius: 20px;
    cursor: pointer;
    font-weight: bold;
    transition: 0.2s;
}

</style>
</head>
<body>

<h2>Command Injection</h2>

<form method="GET">
    <input type="text" name="cmd" placeholder="ls; whoami; uname -a" value="<?= htmlspecialchars($_GET['cmd'] ?? '') ?>">
    <button type="submit">Run</button>
</form>

<?php if (!empty($output)): ?>
<pre><?= htmlspecialchars($output) ?></pre>
<?php endif; ?>

</body>
</html>
