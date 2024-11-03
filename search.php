<?php
$query = $_GET['query'];

// Detect type
if (preg_match('/^0x[0-9a-fA-F]{64}$/', $query)) {
    // Likely a transaction hash
    header("Location: transaction.php?tx=$query");
} elseif (preg_match('/^0x[0-9a-fA-F]{40}$/', $query)) {
    // Likely an address
    header("Location: account.php?address=$query");
} else {
    // Default to assuming it's a block hash
    header("Location: blocks.php?block=$query");
}
exit();
?>