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
?>