<?php
// Redirect to index.php if config.php exists
if (file_exists(__DIR__ . '/config.php')) {
    header('Location: index.php');
    exit;
}

$install_done = false;
$install_error = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capture POST data
    $networkName = $_POST['network_name'] ?? 'TEST';
    $rpcUrl = $_POST['rpc_url'] ?? 'http://localhost:8545';
    $rpcUser = $_POST['rpc_user'] ?? '';
    $rpcPassword = $_POST['rpc_password'] ?? '';

    // Define the content for the config.php file
    $configContent = "<?php\n";
    $configContent .= "define('NETWORK_NAME', '" . addslashes($networkName) . "');\n";
    $configContent .= "define('RPC_URL', '" . addslashes($rpcUrl) . "');\n";
    $configContent .= "define('RPC_USER', '" . addslashes($rpcUser) . "');\n";
    $configContent .= "define('RPC_PASSWORD', '" . addslashes($rpcPassword) . "');\n";

    // Save to config.php
    $configFile = __DIR__ . '/config.php';
    if (file_put_contents($configFile, $configContent)) {
        $install_done = true;
    } else {
        $install_done = true;
        $install_error = true;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blockchain Explorer Installer</title>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="w3-light-grey">

<div class="w3-container w3-padding-16 w3-light-grey">
    <h1 class="w3-center w3-text-teal">Blockchain Explorer Installer</h1>
    <?php if ($install_done === false) : ?>
    <form method="post" class="w3-card w3-white w3-round w3-padding">
        <label for="network_name" class="w3-text-teal"><b>Network Name:</b></label>
        <input type="text" id="network_name" name="network_name" class="w3-input w3-border w3-round" value="TEST" required><br>

        <label for="rpc_url" class="w3-text-teal"><b>RPC URL:</b></label>
        <input type="url" id="rpc_url" name="rpc_url" class="w3-input w3-border w3-round" value="http://localhost:8545" required><br>

        <label for="rpc_user" class="w3-text-teal"><b>RPC User:</b></label>
        <input type="text" id="rpc_user" name="rpc_user" class="w3-input w3-border w3-round"><br>

        <label for="rpc_password" class="w3-text-teal"><b>RPC Password:</b></label>
        <input type="password" id="rpc_password" name="rpc_password" class="w3-input w3-border w3-round"><br>

        <button type="submit" class="w3-button w3-black w3-block w3-round">Install</button>
    </form>
    <?php else : ?>
        <?php if ($install_error === false) : ?>
            <div class="w3-panel w3-center w3-round w3-padding w3-card-4">
                <h3 class="w3-text-white"><i class="w3-margin-right fa fa-check-circle"></i>Success!</h3>
                <p>The configuration file <strong>'config.php'</strong> has been created successfully.</p>
            </div>
            <div class="w3-center w3-margin-top">
                <a href="index.php" class="w3-button w3-teal w3-round">Back to Explorer</a>
            </div>
        <?php else : ?>
            <div class='w3-panel w3-red w3-round w3-padding'>Failed to create the configuration file. Please check permissions.</div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<div class="w3-container w3-text-teal w3-padding w3-margin-top">
    <p class="w3-center">Powered by W3.CSS</p>
</div>

</body>
</html>
