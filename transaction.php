<?php
require_once 'rpc_client.php';

$error = "";

if (isset($_GET['tx'])) {
    $txHash = $_GET['tx'];
    $result = rpcRequest('eth_getTransactionByHash', [$txHash]);
    $transaction = $result['result'];
} else {
    $error = "Transaction hash is required.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Transaction Information</title>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
</head>
<body>
    <div class="w3-container w3-padding-16 w3-light-grey">
        <h1 class="w3-center w3-text-teal">Transaction Information</h1>

        <?php if (isset($error) && !empty($error)): ?>
            <div class="w3-panel w3-red w3-round">
                <h2 class="w3-red">Error!</h2>
                <p><?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php else: ?>
            <div class="w3-card w3-white w3-round w3-padding-16 w3-margin-top">
                <h2 class="w3-text-dark-gray">Transaction Details</h2>
                <table class="w3-table w3-bordered w3-striped w3-white">
                    <tbody>
                        <tr>
                            <td><strong>Transaction Hash:</strong></td>
                            <td><?php echo htmlspecialchars($transaction['hash']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Block Number:</strong></td>
                            <td><?php echo hexdec($transaction['blockNumber']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>From:</strong></td>
                            <td><?php echo htmlspecialchars($transaction['from']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>To:</strong></td>
                            <td><?php echo htmlspecialchars($transaction['to']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Value:</strong></td>
                            <td><?php echo number_format(hexdec($transaction['value']) / 1e18, 18) . ' ETH'; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Nonce:</strong></td>
                            <td><?php echo hexdec($transaction['nonce']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Gas Price:</strong></td>
                            <td><?php echo number_format(hexdec($transaction['gasPrice']) / 1e9, 9) . ' Gwei'; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Gas Limit:</strong></td>
                            <td><?php echo hexdec($transaction['gas']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Max Fee Per Gas:</strong></td>
                            <td><?php echo number_format(hexdec($transaction['maxFeePerGas']) / 1e9, 9) . ' Gwei'; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Max Priority Fee Per Gas:</strong></td>
                            <td><?php echo number_format(hexdec($transaction['maxPriorityFeePerGas']) / 1e9, 9) . ' Gwei'; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Input Data:</strong></td>
                            <td><?php echo htmlspecialchars($transaction['input']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Chain ID:</strong></td>
                            <td><?php echo hexdec($transaction['chainId']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Block Hash:</strong></td>
                            <td><?php echo htmlspecialchars($transaction['blockHash']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Transaction Index:</strong></td>
                            <td><?php echo hexdec($transaction['transactionIndex']); ?></td>
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