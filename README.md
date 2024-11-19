# Blockchain Explorer

I was looking for a basic and simple to implement blockchain explorer using the RPC API preferrably in PHP, but only found out-dated convoluted examples. So I asked chatgpt.com the following,

```
create a blockchain explorer like etherscan.io in PHP that interacts with the RPC blockchain API
```

then added additional customizations to deliver the simple PHP implementation similar to the[ etherscan.io.](https://etherscan.io)

![1732040146767](image/README/1732040146767.png)

![1732040214738](image/README/1732040214738.png)

![1732040229436](image/README/1732040229436.png)

When the redirects are properly configured, the token information can be view from metamask links,

![1732041456597](image/README/1732041456597.png)

# EIP-3091: Block Explorer API Routes

See [https://eips.ethereum.org/EIPS/eip-3091](https://eips.ethereum.org/EIPS/eip-3091)

To make this blockchain explorer compliant with EIP-3091 involves  creating redirect rules in .htaccess, (in htaccess_production), rename to .htaccess

```
RewriteEngine On
RewriteRule ^block/(.*)$ /blocks.php?block=$1 [L,R=301]
RewriteRule ^tx/(.*)$ /transaction.php?tx=$1 [L,R=301]
RewriteRule ^address/(.*)$ /account.php?address=$1 [L,R=301]
RewriteRule ^token/(.*)$ /token.php?address=$1 [L,R=301]

```

to view a token directly (especially invoke from metamask), uses the URL of

```
https://[EXPLORER_URL]/token/0x2894227C135C696Ce21700f120d2C3261beD86Ae
```

Steps to obtain the list of ERC-20 tokens. There is no direct ETH RPC request but is accomplished through a series of request,

1. Get the logs a specific token events, e.g. "topics" => ["0xddf252ad1be2c89b69c2b068fc378daa952ba7f163c4a11628f55a4df523b3ef"]
2. Loop through the result array reading the "transactionHash": "0x882da3c9b1e54507166959581d7ee73921887ed4dfe70d54e45ede5d65c6addd" and invoking eth_getTransactionReceipt
3. read the contractAddress from the result array and invoke eth_call for retrieving the name, symbol, decimals and totalSupply, skip any response with an error.

The Keccak-256 hash is used in Ethereum for various purposes, such as function signatures in smart contracts. To compute the Keccak-256 hash for the functions `name()`, `symbol()`, `decimals()`, and `totalSupply()`, you need to hash their signatures.

### Complete Table

| Function                | Signature         | Keccak-256 Hash (Full)                                                 | Function Selector (4 bytes) |
| ----------------------- | ----------------- | ---------------------------------------------------------------------- | --------------------------- |
| **name()**        | `name()`        | `0x06fdde0385b5a88f3f7d7d54fbe6e69b946f352c2f708c2e3d9a11d8477f09f9` | `0x06fdde03`              |
| **symbol()**      | `symbol()`      | `0x95d89b41584f4c13d61c871fa86a44e0e774b9bfb2e218943ca9d0f62f18ca2e` | `0x95d89b41`              |
| **decimals()**    | `decimals()`    | `0x313ce567d505fb78c3a33a5a7f9e4d87eb74e4d1593a158e96a867c3a0733cbb` | `0x313ce567`              |
| **totalSupply()** | `totalSupply()` | `0x18160dddcd618e2c3dcd9f2f919b164fce5fc4b1156e0f2af1a4298bf9e3fafe` | `0x18160ddd`              |

Let me know if you'd like to compute any other function hashes!

## ChatGPT Initial Response

Creating a blockchain explorer in PHP that interacts with an RPC blockchain API is a powerful project! Here’s an outline of the major components and a guide to get you started with basic functionality.

### Requirements

1. **PHP**: To handle server-side operations.
2. **Blockchain RPC API**: Your blockchain must support JSON-RPC or REST API calls.
3. **Database**: MySQL or PostgreSQL for storing data (e.g., transactions, blocks).
4. **JavaScript**: Optional but useful for dynamic updates on the front end.

### Basic Structure

A blockchain explorer typically includes:

1. **Block Information**: Displaying details about each block.
2. **Transaction Details**: Showing transaction data, including sender, receiver, amount, and fees.
3. **Account Information**: Viewing account balances and recent transactions.
4. **Search Functionality**: Finding specific blocks, transactions, or accounts.

---

### Step 1: Set Up the PHP Project

1. **Install PHP** and necessary extensions (`curl`, `json`, `pdo_mysql`, etc.).
2. Create a new project directory (e.g., `blockchain-explorer`).

```bash
mkdir blockchain-explorer
cd blockchain-explorer
```

3. Create your project files:

   - `index.php`: Main entry point for the web interface.
   - `config.php`: Configuration file for your API connection and database.
   - `rpc_client.php`: PHP file to handle JSON-RPC requests.
   - `blocks.php`: Page to display block information.
   - `transaction.php`: Page to display transaction details.
   - `account.php`: Page for account information.

---

### Step 2: Configure Blockchain RPC Connection

In `config.php`, add your connection information to the blockchain node.

```php
<?php
// RPC API credentials
define('RPC_URL', 'http://127.0.0.1:8545'); // Replace with your blockchain node's URL
define('RPC_USER', ''); // Set if required
define('RPC_PASSWORD', ''); // Set if required
?>
```

---

### Step 3: Create the RPC Client

The `rpc_client.php` file will handle JSON-RPC requests to the blockchain node.

```php
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
        echo 'Error:' . curl_error($ch);
    }
    curl_close($ch);

    return json_decode($response, true);
}
?>
```

---

### Step 4: Build the User Interface Pages

#### 1. Block Information (`blocks.php`)

This page will list blocks and allow you to view details of a specific block.

```php
<?php
require_once 'rpc_client.php';

if (isset($_GET['block'])) {
    $blockHash = $_GET['block'];
    $block = rpcRequest('eth_getBlockByHash', [$blockHash, true]);
} else {
    $blockNumber = rpcRequest('eth_blockNumber', []);
    $blockNumber = hexdec($blockNumber['result']);
    $block = rpcRequest('eth_getBlockByNumber', ['0x' . dechex($blockNumber), true]);
}

echo "<h2>Block Information</h2>";
echo "<pre>" . print_r($block, true) . "</pre>";
?>
```

#### 2. Transaction Details (`transaction.php`)

This page will show details for a specific transaction.

```php
<?php
require_once 'rpc_client.php';

if (isset($_GET['tx'])) {
    $txHash = $_GET['tx'];
    $transaction = rpcRequest('eth_getTransactionByHash', [$txHash]);

    echo "<h2>Transaction Information</h2>";
    echo "<pre>" . print_r($transaction, true) . "</pre>";
} else {
    echo "Transaction hash is required.";
}
?>
```

#### 3. Account Information (`account.php`)

This page will show information for a specific account, including its balance and recent transactions.

```php
<?php
require_once 'rpc_client.php';

if (isset($_GET['address'])) {
    $address = $_GET['address'];
    $balance = rpcRequest('eth_getBalance', [$address, 'latest']);

    echo "<h2>Account Information</h2>";
    echo "Address: $address<br>";
    echo "Balance: " . hexdec($balance['result']) . "<br>";

    // Optional: list recent transactions by scanning recent blocks
} else {
    echo "Address is required.";
}
?>
```

---

### Step 5: Add Search Functionality

Create a simple form on `index.php` that lets users search by block hash, transaction hash, or address.

```php
<!DOCTYPE html>
<html>
<head>
    <title>Blockchain Explorer</title>
</head>
<body>
    <h1>Blockchain Explorer</h1>
    <form action="search.php" method="GET">
        <input type="text" name="query" placeholder="Block, Transaction, or Address">
        <button type="submit">Search</button>
    </form>
</body>
</html>
```

In `search.php`, detect the type of query based on the format (block hashes, transaction hashes, and addresses have distinct lengths and characters) and redirect to the appropriate page.

```php
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
```

---

### Step 6: Optional Enhancements

- **Database Caching**: Store frequent data to reduce load on the RPC API.
- **Pagination**: Add pagination to blocks and transactions.
- **Real-time Updates**: Use JavaScript and WebSockets to dynamically update recent transactions and blocks.

This setup will give you a basic blockchain explorer structure in PHP! With further improvements, such as enhanced styling, real-time updates, and caching, you can make it more robust and user-friendly.

## Testing

Testing perform using php-cli,

```
php -S 127.0.0.1:8080 -t ./
```

## Domain Registration

Scanning porkbun.com for the "renews at $4." found the .top TLD as the lowest priced TLD, the other domains at .in (India country specific)

### About the TOP domain

Why choose a .top domain?

#### .top overview

Show your audience that your products are the best with a customized .top domain name. If you’re on top, you’re way beyond the competition. This TLD stands out from the crowd, and it tells your website visitors that your business does too. The possibilities with .top are endless. What’s undeniable is that this short, memorable TLD comes with plenty of positive associations.

#### .top facts, stats & history

Available to the public since 2014, .top is a newer, generic top-level domain (TLD). That means that anyone in the world can register a .top domain name, and you have a much better chance of getting the exact name you want. Plus, unique TLDs like .top can help you stand out. How better to get your visitors’ attention than saying you’re above the rest before they even visit your site?

### Hosting

Once you have registered your TLD, you want to choose a hosting provider that will provide two sub-domain options, because your want to have a blockchain explorer for TEST and LIVE blockchain

## Adding to chainid.network

Visit [https://github.com/ethereum-lists/chains](https://github.com/ethereum-lists/chains)

## Live Example of a Test Network

Visit [https://test-explorer.exemptliquiditymarket.exchange/](https://test-explorer.exemptliquiditymarket.exchange/)
