<?php
require_once 'rpc_client.php';

function getBlockByNumber($blockNumber) {
    $hexBlockNumber = '0x' . dechex($blockNumber);
    return rpcRequest('eth_getBlockByNumber', [$hexBlockNumber, true]);
}


// Fetch Ethereum Price and Market Cap from CoinGecko API
$ethPriceData = json_decode(file_get_contents("https://api.coingecko.com/api/v3/simple/price?ids=ethereum&vs_currencies=usd&include_market_cap=true"), true);
$ethPrice = $ethPriceData['ethereum']['usd'] ?? 'Unavailable';
$ethMarketCap = $ethPriceData['ethereum']['usd_market_cap'] ?? 'Unavailable';

// Get the latest block number and calculate TPS
//$latestBlockNumberHex = rpcRequest('eth_blockNumber', []);
//$latestBlockNumber = isset($latestBlockNumberHex['result']) ? hexdec($latestBlockNumberHex['result']) : null;
// Get the latest block number
$latestBlockNumberHex = rpcRequest('eth_blockNumber', []);
$latestBlockNumber = hexdec($latestBlockNumberHex['result']);

// Set default values for total transactions and TPS
$totalTransactions = 0;
$tps = 0;
$startTime = null;
$endTime = null;

// Ensure we have a valid block number before proceeding
if ($latestBlockNumber !== null) {
    // Loop through the last 100 blocks
    for ($i = 0; $i < 100; $i++) {
        $block = rpcRequest('eth_getBlockByNumber', ['0x' . dechex($latestBlockNumber - $i), true]);

        if (isset($block['result']['transactions'])) {
            $totalTransactions += count($block['result']['transactions']);
            $blockTimestamp = hexdec($block['result']['timestamp'] ?? 0);

            // Capture the end time at the first iteration
            if ($i === 0) $endTime = $blockTimestamp;
            if ($i === 99) $startTime = $blockTimestamp;
        }
    }
    // Calculate TPS if start and end times are valid
    if ($startTime && $endTime && ($endTime - $startTime) > 0) {
        $tps = $totalTransactions / ($endTime - $startTime);
    }
}

// Get Median Gas Price from the latest block
$latestBlock = rpcRequest('eth_getBlockByNumber', ['0x' . dechex($latestBlockNumber), true]);
$medianGasPrice = isset($latestBlock['result']['baseFeePerGas']) ? hexdec($latestBlock['result']['baseFeePerGas']) / 1e9 : 'Unavailable';

// Last Finalized Block (assuming `eth_syncing` provides it)
$syncingStatus = rpcRequest('eth_syncing', []);
$lastFinalizedBlock = isset($syncingStatus['result']['finalized']) ? hexdec($syncingStatus['result']['finalized']) : ($latestBlockNumber ? $latestBlockNumber - 12 : 'Unavailable');
$lastSafeBlock = $latestBlockNumber ? $latestBlockNumber - 6 : 'Unavailable';

// Fetch the last 10 blocks
$blocks = [];
for ($i = 0; $i < 10; $i++) {
    $block = getBlockByNumber($latestBlockNumber - $i);
    if (isset($block['result'])) {
        $blocks[] = $block['result'];
    }
}

// Fetch the last 10 transactions
$transactions = [];
foreach ($blocks as $block) {
    if (count($transactions) >= 10) break;
    foreach ($block['transactions'] as $tx) {
        if (count($transactions) >= 10) break;
        $tx['timestamp'] = $block['timestamp'];
        $transactions[] = $tx;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Blockchain Explorer</title>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
</head>
<body>
    <!-- Wrapper for the main content -->
    <div class="w3-container w3-padding-16 w3-light-grey">
        <!-- Display Network Name in the Header -->
        <header class="w3-center w3-padding-24">
            <h1 class="w3-text-teal"><?php echo NETWORK_NAME; ?> Blockchain Explorer</h1>
        </header>

        <!-- Search Field Section -->
        <div class="w3-container w3-card w3-white w3-round w3-padding-16 w3-margin-bottom">
            <h2 class="w3-text-dark-gray">Search Blockchain</h2>
            <form method="GET" action="search.php" class="w3-row">
                <input type="text" name="query" placeholder="Enter transaction hash, block number, or address" class="w3-input w3-border w3-round w3-margin-bottom" required>
                <button type="submit" class="w3-button w3-block w3-teal w3-round">Search</button>
            </form>
        </div>

        <!-- View Tokens -->
        <div class="w3-container w3-card w3-white w3-round w3-padding-16 w3-margin-bottom">
            <a href="token.php" class="w3-button w3-block w3-teal w3-round">View Token(s)</a>
        </div>

        <!-- Stats Section -->
        <div class="w3-row-padding w3-margin-bottom">
            <div class="w3-third">
                <div class="w3-card w3-padding w3-white w3-round">
                    <h3>Current ETH Price</h3>
                    <p class="w3-large w3-text-teal">$<?php echo number_format($ethPrice, 2); ?></p>
                </div>
            </div>
            <div class="w3-third">
                <div class="w3-card w3-padding w3-white w3-round">
                    <h3>Total Transactions</h3>
                    <p class="w3-large"><?php echo number_format($totalTransactions); ?> (TPS: <?php echo $tps; ?>)</p>
                </div>
            </div>
            <div class="w3-third">
                <div class="w3-card w3-padding w3-white w3-round">
                    <h3>Market Cap</h3>
                    <p class="w3-large w3-text-teal">$<?php echo number_format($ethMarketCap); ?></p>
                </div>
            </div>
        </div>

        <!-- Additional Stats Section -->
        <div class="w3-row-padding w3-margin-bottom">
            <div class="w3-third">
                <div class="w3-card w3-padding w3-white w3-round">
                    <h3>Median Gas Price</h3>
                    <p class="w3-large"><?php echo $medianGasPrice; ?> Gwei</p>
                </div>
            </div>
            <div class="w3-third">
                <div class="w3-card w3-padding w3-white w3-round">
                    <h3>Last Finalized Block</h3>
                    <p class="w3-large"><?php echo $lastFinalizedBlock; ?></p>
                </div>
            </div>
            <div class="w3-third">
                <div class="w3-card w3-padding w3-white w3-round">
                    <h3>Last Safe Block</h3>
                    <p class="w3-large"><?php echo $lastSafeBlock; ?></p>
                </div>
            </div>
        </div>

        <!-- Last 10 Blocks Section -->
        <div class="w3-container w3-card w3-white w3-round w3-padding-16 w3-margin-bottom">
            <h2 class="w3-text-dark-gray">Last 10 Blocks</h2>
            <table class="w3-table w3-bordered w3-striped w3-white">
                <thead>
                    <tr class="w3-teal">
                        <th>Block Number</th>
                        <th>Transactions</th>
                        <th>Miner</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($blocks as $block): ?>
                        <tr>
                            <td><a href="blocks.php?block=<?php echo $block['number']; ?>"><?php echo $block['number']; ?></a></td>
                            <td><?php echo count($block['transactions']); ?></td>
                            <td><?php echo $block['miner']; ?></td>
                            <td><?php echo date('Y-m-d H:i:s', hexdec($block['timestamp'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Last 10 Transactions Section -->
        <div class="w3-container w3-card w3-white w3-round w3-padding-16 w3-margin-bottom">
            <h2 class="w3-text-dark-gray">Last 10 Transactions</h2>
            <table class="w3-table w3-bordered w3-striped w3-white">
                <thead>
                    <tr class="w3-teal">
                        <th>Transaction Hash</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Value (ETH)</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $transaction): ?>
                        <tr>
                            <td><a href="transaction.php?tx=<?php echo $transaction['hash']; ?>"><?php echo substr($transaction['hash'], 0, 15) . '...'; ?></a></td>
                            <td><?php echo $transaction['from']; ?></td>
                            <td><?php echo $transaction['to']; ?></td>
                            <td><?php echo hexdec($transaction['value']) / 1e18; // Convert Wei to ETH ?></td>
                            <td><?php echo date('Y-m-d H:i:s', hexdec($transaction['timestamp'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
