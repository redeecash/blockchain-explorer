<?php
require_once 'rpc_client.php';
$error = "";

if (isset($_GET['address'])) {
    $address = $_GET['address'];
    $balance = rpcRequest('eth_getBalance', [$address, 'latest']);
} else {
    $error = "Address is required.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Account Information</title>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
</head>
<body>
    <div class="w3-container w3-padding-16 w3-light-grey">
        <h1 class="w3-center w3-text-teal">Account Information</h1>
        
        <?php if (isset($error) && !empty($error)) : ?>
            <div class="w3-panel w3-red w3-round">
                <h2 class="w3-red">Error!</h2>
                <p><?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php else : ?>
            <div class="w3-card w3-white w3-round w3-padding-16 w3-margin-top">
                <h2 class="w3-text-dark-gray">Account Details</h2>
                <table class="w3-table w3-bordered w3-striped w3-white">
                    <tbody>
                        <tr>
                            <td><strong>Address:</strong></td>
                            <td><?php echo htmlspecialchars($address); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Balance:</strong></td>
                            <td><?php echo number_format(hexdec($balance['result']) / 1e18, 18) . ' ETH'; ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        <div class="w3-center w3-margin-top">
            <a href="index.php" class="w3-button w3-teal w3-round">Back to Explorer</a>
        </div>
    </div>
</body>
</html>
