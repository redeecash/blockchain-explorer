<?php
require_once 'rpc_client.php';

// Get latest block number
$latestBlock = rpcRequest("eth_blockNumber")['result'];
$latestBlockNumber = hexdec($latestBlock);

// Define how many blocks to fetch, for example, the last 10 blocks
$blocksToFetch = 10;

// Configuration
$blocksPerPage = 10; // Number of blocks to display per page
$totalBlocks = $latestBlockNumber; // Replace with actual number of blocks fetched from your RPC API
$totalPages = ceil($totalBlocks / $blocksPerPage);

// Get the current page from the query string, default to 1
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) {
    $current_page = 1;
}
if ($current_page > $totalPages) {
    $current_page = $totalPages;
}

// Calculate the offset for the database query
$offset = ($current_page - 1) * $blocksPerPage;
$latestBlockNumber -= $offset;

// Fetch blocks for the current page (this is where you would call your RPC API)
$blocks = []; // Replace with your actual block-fetching logic

// Example of blocks retrieval (you would replace this with your actual RPC call)
for ($i = $offset; $i < $offset + $blocksPerPage && $i < $totalBlocks; $i++) {
    if ($i > 0) {
        $blocks[] = [
            'hash' => 'BlockHash' . $i, // Replace with actual block hash
            'number' => $i, // Replace with actual block number
            // Add other block details as necessary
        ];    
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Blocks</title>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
</head>
<body>

<div class="w3-container w3-padding-32">
    <h2 class="w3-center">Blockchain Explorer - All Blocks</h2>

    <div class="w3-responsive w3-card-4 w3-margin-top">
        <table class="w3-table w3-bordered w3-striped w3-hoverable">
            <thead>
                <tr class="w3-blue">
                    <th>Block Number</th>
                    <th>Hash</th>
                    <th>Parent Hash</th>
                    <th>Miner</th>
                    <th>Timestamp</th>
                    <th>Transactions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch block data
                for ($i = $latestBlockNumber; $i > $latestBlockNumber - $blocksToFetch; $i--) {
                    $blockData = rpcRequest("eth_getBlockByNumber", ['0x' . dechex($i), true]);

                    if (isset($blockData['result'])) {
                        $block = $blockData['result'];
                        echo "<tr>";
                        echo "<td><a href='blocks.php?block={$block['number']}'>" . hexdec($block['number']) . "</a></td>";
                        echo "<td>{$block['hash']}</td>";
                        echo "<td>{$block['parentHash']}</td>";
                        echo "<td>{$block['miner']}</td>";
                        echo "<td>" . date("Y-m-d H:i:s", hexdec($block['timestamp'])) . "</td>";
                        echo "<td>" . count($block['transactions']) . "</td>";
                        echo "</tr>";
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
    <br/>
    <!-- Pagination Links -->
    <div class="w3-bar w3-light-grey w3-center">
        <?php if ($current_page > 1): ?>
            <a href="?page=<?php echo $current_page - 1; ?>" class="w3-button w3-left">Previous</a>
        <?php endif; ?>
        
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?php echo $i; ?>" class="w3-button <?php if ($i == $current_page) echo 'w3-blue'; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>

        <?php if ($current_page < $totalPages): ?>
            <a href="?page=<?php echo $current_page + 1; ?>" class="w3-button w3-right">Next</a>
        <?php endif; ?>
    </div>

    <div class="w3-center w3-margin-top">
        <a href="index.php" class="w3-button w3-teal w3-round">Back to Explorer</a>
    </div>
</div>

</body>
</html>