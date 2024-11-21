<?php
require_once 'config.php';

function rpcRequest($method, $params = []) {
    $payload = json_encode([
        'jsonrpc' => '2.0',
        'method'  => $method,
        'params'  => $params,
        'id'      => 1
    ]);

    $ch = curl_init(RPC_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

    if (RPC_USER && RPC_PASSWORD) {
        curl_setopt($ch, CURLOPT_USERPWD, RPC_USER . ":" . RPC_PASSWORD);
    }

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        //echo 'Error:' . curl_error($ch);
        error_log(curl_error($ch));
    }
    curl_close($ch);

    return json_decode($response, true);
}

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

?>