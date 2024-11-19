<?php
require_once 'rpc_client.php';

global $name, $symbol, $decimals, $totalSupply, $error;

function decodeTokenName($data) {
    // Remove the "0x" prefix if present
    if (str_starts_with($data, "0x")) {
        $data = substr($data, 2);
    }

    // Convert hex string to binary
    $stringData = hex2bin($data);

    // Return the string
    return $stringData;
}

function decodeTokenSymbol($data) {
    // Remove the "0x" prefix if present
    if (str_starts_with($data, "0x")) {
        $data = substr($data, 2);
    }

    // Convert hex string to binary
    $stringData = hex2bin($data);

    // Return the string
    return $stringData;
}


function decodeTotalSupply($data) {
    // Remove the "0x" prefix if present
    if (str_starts_with($data, "0x")) {
        $data = substr($data, 2);
    }

    // Convert hex to decimal
    $totalSupply = hexdec($data);

    return $totalSupply;
}

$error = "";

$token = [
    "name" => "",
    "symbol" => "",
    "decimals" => "",
    "supply" => "0"
];

if (isset($_GET['address'])) {
    /**
     * obtain
     * - name() = 0x06fdde03
     * - symbol() = 0x95d89b41
     * - decimals() = 0x313ce567 
     * - totalSupply() = 0x18160ddd
     */
    $contractAddress = $_GET['address'];
    // get token name
    $txHash = [
        "to" => $contractAddress,
        "data" => "0x06fdde03"
    ];
    $result = rpcRequest('eth_call', [$txHash]);
    if (!isset($result['error'])) {
        $name = decodeTokenName($result['result']);
    } else {
        $name = "0x0";
        $error = $result['error']['message'];
    }
    // get token symbol
    $txHash = [
        "to" => $contractAddress,
        "data" => "0x95d89b41"
    ];
    $result = rpcRequest('eth_call', [$txHash]);
    if (!isset($result['error'])) {
        $symbol = decodeTokenSymbol($result['result']);
    } else {
        $symbol = "0x0";
        $error = $result['error']['message'];
    }
    // get token decimals
    $txHash = [
        "to" => $contractAddress,
        "data" => "0x313ce567"
    ];
    $result = rpcRequest('eth_call', [$txHash]);
    if (!isset($result['error'])) {
        $decimals = hexdec(substr($result['result'], -2));
    } else {
        $decimals = "0x0";
    }
    // get token total supply
    $txHash = [
        "to" => $contractAddress,
        "data" => "0x18160ddd"
    ];
    $result = rpcRequest('eth_call', [$txHash]);
    if (!isset($result['error'])) {
        $totalSupply = decodeTotalSupply($result['result']) / pow(10, $decimals);;
    } else {
        $totalSupply = "0x0";
    }

} else {
    $txHash = [
        "fromBlock" => "0x0",          
        "toBlock" => "latest",        
        "topics" => [
            "0xddf252ad1be2c89b69c2b068fc378daa952ba7f163c4a11628f55a4df523b3ef"
        ]
    ];
    $result = rpcRequest('eth_getLogs', [$txHash]);
    $logs = $result['result'];
    
    $hash = array();
    foreach($logs as $log) {
        $transactionHash = $log['transactionHash'];
        array_push($hash, $transactionHash);
    }
    $contracts = array();
    foreach($hash as $tx) {
        $txHash = $tx;
        $result = rpcRequest('eth_getTransactionReceipt',[$txHash]);
        $contractAddress = $result['result']['contractAddress'];
        array_push($contracts,$contractAddress);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Token Information</title>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
</head>
<body>
    <div class="w3-container w3-padding-16 w3-light-grey">
        <?php if (isset($_GET['address'])) : ?>
            <h1 class="w3-center w3-text-teal">Token Information</h1>
            <div class="w3-card w3-white w3-round w3-padding-16 w3-margin-top">
                <h2 class="w3-text-dark-gray">Token Details</h2>
                <table class="w3-table w3-bordered w3-striped w3-white">
                    <tbody>
                        <tr>
                            <td><strong>Token Address:</strong></td>
                            <td><?php echo htmlspecialchars($_GET["address"]); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Token Name:</strong></td>
                            <td><?php echo htmlspecialchars($name); ?> <?php echo ($error !== "" ? "(".$error.")" : ""); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Token Symbol:</strong></td>
                            <td><?php echo htmlspecialchars($symbol); ?> <?php echo ($error !== "" ? "(".$error.")" : ""); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Token Decimals:</strong></td>
                            <td><?php echo htmlspecialchars($decimals); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Token Total Supply:</strong></td>
                            <td><?php echo htmlspecialchars($totalSupply); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php else : ?>
            <h1 class="w3-center w3-text-teal">Token Tracker (ERC-20)</h1>
            <p>A total of <b><?php echo count($contracts); ?></b> Token Contracts found.</p>
            <div class="w3-card w3-white w3-round w3-padding-16 w3-margin-top">
                <table class="w3-table w3-bordered w3-striped w3-white">
                    <tr>
                        <th>#</th>
                        <th>Token</th>
                    </tr>
                    <?php $index = 1; ?>
                    <?php for($i=0; $i<count($contracts); $i++) : ?>
                        <tr>
                            <td><?php echo $i+1; ?></td>
                            <td><a href="token.php?address=<?php echo $contracts[$i]; ?>"><?php echo $contracts[$i]; ?></a></td>
                        </tr>
                    <?php endfor; ?>
                </table>
            </div>
        <?php endif; ?>

        <div class="w3-center w3-margin-top">
            <a href="index.php" class="w3-button w3-teal w3-round">Back to Explorer</a>
        </div>
    </div>
</body>
</html>