<?php
require_once 'rpc_client.php';

if (isset($_GET['block'])) {
    $blockNumber = $_GET['block'];
    $block = rpcRequest('eth_getBlockByNumber', [$blockNumber, true]);
} else {
    $blockNumber = rpcRequest('eth_blockNumber', []);
    $blockNumber = hexdec($blockNumber['result']);
    $block = rpcRequest('eth_getBlockByNumber', ['0x' . dechex($blockNumber), true]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Block Information</title>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
</head>
<body>
    <div class="w3-container w3-padding-16 w3-light-grey">
        <h1 class="w3-center w3-text-teal">Block Information</h1>

        <div class="w3-card w3-white w3-round w3-padding-16 w3-margin-top">
            <h2 class="w3-text-dark-gray">Block Details</h2>
            <table class="w3-table w3-bordered w3-striped w3-white">
                <tbody>
                    <tr>
                        <td><strong>Hash:</strong></td>
                        <td><?php echo htmlspecialchars($block['result']['hash']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Parent Hash:</strong></td>
                        <td><?php echo htmlspecialchars($block['result']['parentHash']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Miner:</strong></td>
                        <td><?php echo htmlspecialchars($block['result']['miner']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Block Number:</strong></td>
                        <td><?php echo hexdec($block['result']['number']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Gas Limit:</strong></td>
                        <td><?php echo hexdec($block['result']['gasLimit']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Gas Used:</strong></td>
                        <td><?php echo hexdec($block['result']['gasUsed']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Timestamp:</strong></td>
                        <td><?php echo date('Y-m-d H:i:s', hexdec($block['result']['timestamp'])); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Transactions:</strong></td>
                        <td><?php echo count($block['result']['transactions']); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="w3-margin-top">
            <h3 class="w3-text-dark-gray">Transaction List</h3>
            <div class="w3-card w3-white w3-round w3-padding-16">
                <ul class="w3-ul w3-hoverable">
                    <?php foreach ($block['result']['transactions'] as $transaction): ?>
                        <li>
                            <strong>Transaction Hash:</strong> 
                            <a href="transaction.php?tx=<?php echo htmlspecialchars($transaction['hash']); ?>" class="w3-text-teal">
                                <?php echo htmlspecialchars($transaction['hash']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

    <div class="w3-center w3-margin-top">
        <a href="index.php" class="w3-button w3-teal w3-round">Back to Explorer</a>
    </div>
</body>
</html>
