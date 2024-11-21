<?php
require_once 'rpc_client.php';

global $address, $name, $symbol;

if (isset($_GET['address'])) {
    $address = $_GET['address'];

    // get token name
    $txHash = [
        "to" => $address,
        "data" => "0x06fdde03"
    ];
    $result = rpcRequest('eth_call', [$txHash]);
    if (!isset($result['error'])) {
        $name = decodeTokenName($result['result']);
    } else {
        $name = $result['error']['message'];
    }

     // get token symbol
     $txHash = [
        "to" => $address,
        "data" => "0x95d89b41"
    ];
    $result = rpcRequest('eth_call', [$txHash]);
    if (!isset($result['error'])) {
        $symbol = decodeTokenSymbol($result['result']);
    } else {
        $symbol = $result['error']['message'];
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Contract User Interface (UI)</title>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/web3/1.8.0/web3.min.js"></script>
</head>
<body>

<div class="w3-container w3-center">
    <h2>Smart Contract User Interface (UI)</h2>
    <form id="contractForm" class="w3-card-4 w3-padding">
        <label for="rpcUrl">RPC URL:</label>
        <input type="text" id="rpcUrl" class="w3-input w3-border w3-light-grey" placeholder="Enter RPC URL" value="<?php echo RPC_URL; ?>" readonly><br><br>
        
        <label for="contractAddress">Contract Address:</label>
        <input type="text" id="contractAddress" class="w3-input w3-border w3-light-grey" placeholder="Enter Contract Address" value="<?php echo $address; ?>" readonly><br><br>
        
        <label for="abiFile">Upload ABI JSON File:</label>
        <input type="file" id="abiFile" class="w3-input w3-border" accept=".json" onchange="handleFileUpload(event)" required><br><br>
        
        <button type="button" class="w3-button w3-block w3-blue" onclick="generateForm()">Generate Form</button>
    </form>
    
    <div id="dynamicForm" class="w3-card-4 w3-padding" style="display:none;">
        <h3>Contract Methods</h3>
        <div id="formFields"></div>
    </div>

    <!-- MESSAGE DIALOG -->
    <div id="message-dialog" class="w3-modal">
        <div class="w3-modal-content">
            <div class="w3-container w3-white">
                <h2 class="w3-green" id="message-dialog-title"></h2>
                <p id="message-dialog-content"></p>
                <br/>
                <button class="w3-button w3-block w3-black" onclick="document.getElementById('message-dialog').style.display='none';">OK</button>
                <br/>
            </div>
        </div>
    </div>

    <div class="w3-center w3-margin-top">
        <a href="index.php" class="w3-button w3-teal w3-round">Back to Explorer</a>
    </div>
</div>

<script>
    let web3;
    let contractABI;
    let contract;

    const messageDialog = document.getElementById("message-dialog");
    const messageDialogTitle = document.getElementById("message-dialog-title");
    const messageDialogContent = document.getElementById("message-dialog-content");

    // Handle ABI JSON file upload
    function handleFileUpload(event) {
        const file = event.target.files[0];
        if (file && file.type === 'application/json') {
            const reader = new FileReader();
            reader.onload = function(e) {
                try {
                    const abi = JSON.parse(e.target.result).abi;
                    contractABI = abi; // Save the ABI for later use

                    messageDialogTitle.classList.remove("w3-red");
                    messageDialogTitle.classList.add("w3-green");
                    messageDialogTitle.innerHTML = "ABI Loading Status";
                    messageDialogContent.innerHTML = 'ABI loaded successfully!';
                    messageDialog.style.display = "block";
                } catch (error) {
                    messageDialogTitle.classList.remove("w3-green");
                    messageDialogTitle.classList.add("w3-red");
                    messageDialogTitle.innerHTML = "ABI Loading Status";
                    messageDialogContent.innerHTML = 'Error parsing ABI JSON file.';
                    messageDialog.style.display = "block";
                }
            };
            reader.readAsText(file);
        } else {
            messageDialogTitle.classList.remove("w3-green");
            messageDialogTitle.classList.add("w3-red");
            messageDialogTitle.innerHTML = "ABI Loading Status";
            messageDialogContent.innerHTML = 'Please upload a valid JSON file';
            messageDialog.style.display = "block";

        }
    }

    // Function to generate form dynamically based on ABI
    function generateForm() {
        const rpcUrl = document.getElementById('rpcUrl').value;
        const contractAddress = document.getElementById('contractAddress').value;

        // Validate inputs
        if (!contractABI || !rpcUrl || !contractAddress) {
            messageDialogTitle.classList.remove("w3-green");
            messageDialogTitle.classList.add("w3-red");
            messageDialogTitle.innerHTML = "ABI Loading Status";
            messageDialogContent.innerHTML = 'Please fill in all fields and upload the ABI JSON file.';
            messageDialog.style.display = "block";
            return;
        }

        // Initialize Web3
        web3 = new Web3(new Web3.providers.HttpProvider(rpcUrl));
        contract = new web3.eth.Contract(contractABI, contractAddress);

        // Clear previous form fields
        const formFields = document.getElementById('formFields');
        formFields.innerHTML = '';

        // Generate form fields for contract functions
        contractABI.forEach(item => {
            if (item.type === 'function') {
                const methodDiv = document.createElement('div');
                methodDiv.classList.add('w3-margin-bottom', 'w3-padding', 'w3-border');

                const methodLabel = document.createElement('h4');
                methodLabel.textContent = item.name;
                methodDiv.appendChild(methodLabel);

                // Input fields for function parameters
                if (item.inputs.length > 0) {
                    item.inputs.forEach((input, index) => {
                        const inputLabel = document.createElement('label');
                        inputLabel.textContent = `${input.name || `Param ${index + 1}`} (${input.type}):`;
                        methodDiv.appendChild(inputLabel);

                        const inputField = document.createElement('input');
                        inputField.type = 'text';
                        inputField.id = `${item.name}_input_${index}`;
                        inputField.classList.add('w3-input', 'w3-border');
                        inputField.placeholder = `Enter value for ${input.name || `Param ${index + 1}`}`;
                        methodDiv.appendChild(inputField);
                    });
                }

                // Output field
                const outputField = document.createElement('textarea');
                outputField.id = `${item.name}_output`;
                outputField.classList.add('w3-input', 'w3-border', 'w3-light-grey');
                outputField.placeholder = 'Output will appear here...';
                outputField.readOnly = true;
                methodDiv.appendChild(outputField);

                // Button to interact with the function
                const interactButton = document.createElement('button');
                interactButton.textContent = `Call ${item.name}`;
                interactButton.classList.add('w3-button', 'w3-green', 'w3-margin-top', 'w3-block');
                interactButton.onclick = async () => await interactWithFunction(item);
                methodDiv.appendChild(interactButton);

                formFields.appendChild(methodDiv);
            }
        });

        // Show dynamic form
        document.getElementById('contractForm').style.display = 'none';
        document.getElementById('dynamicForm').style.display = 'block';
    }

    // Function to interact with the smart contract
    async function interactWithFunction(functionABI) {
        try {
            const inputs = [];
            functionABI.inputs.forEach((input, index) => {
                const value = document.getElementById(`${functionABI.name}_input_${index}`).value;
                inputs.push(value);
            });

            const method = contract.methods[functionABI.name](...inputs);
            
            if (functionABI.stateMutability === 'view' || functionABI.stateMutability === 'pure') {
                // Call for view or pure functions
                const result = await method.call();
                document.getElementById(`${functionABI.name}_output`).value = JSON.stringify(result, null, 2);
            } else {
                // Send for state-changing functions
                const accounts = await web3.eth.getAccounts();
                const result = await method.send({ from: accounts[0] });
                document.getElementById(`${functionABI.name}_output`).value = `Transaction Hash: ${result.transactionHash}`;
            }
        } catch (error) {
            document.getElementById(`${functionABI.name}_output`).value = `Error: ${error.message}`;
        }
    }
</script>

</body>
</html>
