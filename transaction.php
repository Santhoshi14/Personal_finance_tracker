<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION["username"])) {
    // Redirect to the login page if not logged in
    header("Location: login.php");
    exit();
}

// Establish database connection
$conn = mysqli_connect("localhost", "root", "", "tracker");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get the username of the logged-in user
$username = $_SESSION["username"];

// Fetch the user ID based on the username
$query = "SELECT id FROM userstable WHERE username='$username'";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) == 1) {
    $row = mysqli_fetch_assoc($result);
    $userID = $row["id"];
    
    // Define the user-specific transactions table
    $tableName = "transactions_$userID";

    // Handle adding and deleting transactions
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action"])) {
        if ($_POST["action"] === "add") {
            $category = mysqli_real_escape_string($conn, $_POST["category"]);
            $date = mysqli_real_escape_string($conn, $_POST["date"]);
            $label = mysqli_real_escape_string($conn, $_POST["label"]);
            $amount = mysqli_real_escape_string($conn, $_POST["amount"]);
            
            if (!empty($category) && !empty($date) && !empty($label) && !empty($amount)) {
                $query = "INSERT INTO $tableName (category, date, label, amount) VALUES ('$category', '$date', '$label', '$amount')";
                if (mysqli_query($conn, $query)) {
                    echo json_encode(["status" => "success"]);
                } else {
                    echo json_encode(["status" => "error", "message" => mysqli_error($conn)]);
                }
            } else {
                echo json_encode(["status" => "error", "message" => "Please fill out all fields."]);
            }
            exit();
        } elseif ($_POST["action"] === "delete") {
            $transactionId = mysqli_real_escape_string($conn, $_POST["id"]);
            $query = "DELETE FROM $tableName WHERE id = '$transactionId'";
            if (mysqli_query($conn, $query)) {
                echo json_encode(["status" => "success"]);
            } else {
                echo json_encode(["status" => "error", "message" => mysqli_error($conn)]);
            }
            exit();
        } elseif ($_POST["action"] === "fetchTransactionData") {
            // Fetch transaction data grouped by month and year
            $query = "SELECT MONTH(date) AS month, YEAR(date) AS year, SUM(amount) AS total_amount
                      FROM $tableName
                      GROUP BY YEAR(date), MONTH(date)
                      ORDER BY YEAR(date) DESC, MONTH(date) DESC";
            
            $result = mysqli_query($conn, $query);
            $transactionData = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $transactionData[] = $row;
            }
            echo json_encode($transactionData);

            // Fetch total amount
            $totalQuery = "SELECT SUM(amount) AS totalAmount FROM $tableName";
            $totalResult = mysqli_query($conn, $totalQuery);
            $totalRow = mysqli_fetch_assoc($totalResult);
            echo "|" . $totalRow['totalAmount'];
            exit();
        }
    }
} else {
    // Redirect to login page if user not found
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction Page</title>
    <link rel="stylesheet" href="all.css">
</head>
<body>
    <header>
        <nav>
            <h1>Transaction Page</h1>
            <ul style="text-align: center">
                <li><a href="index.php" id="home">Home</a></li>
                <li><a href="transaction.php" id="Transaction">Transaction</a></li>
                <li><a href="observation.php">Observation</a></li>
                <li><a href="income.php" id="income">Income</a></li>
                <li><a href="wallet.php" id="wallet">Wallet</a></li>
                <li><a href="logout.php" id="logout">Logout</a></li>
            </ul>
        </nav>
      
        <div class="container">
            <!-- Display the image -->
            <div class="circle" id="circle"></div>
            <!-- Display the username below the image in a div -->
            <div class="username-text">
                <?php echo $_SESSION["username"]; ?>
            </div>
        </div>
    </header>

    <main>
        <div class="transaction-form">
            <button class="add-transaction-btn" onclick="toggleForm()">
                <span>&#43;</span> Transaction
            </button>
            <div class="transaction-options" id="transaction-options" style="display: none;">
                <table>
                    <tr>
                        <td>
                            <label for="category">Category:</label>
                            <select id="category">
                                <option value="food">Food</option>
                                <option value="drink">Drink</option>
                                <option value="bill">Bill</option>
                                <option value="fee">Fee</option>
                                <option value="transport">Transport</option>
                                <option value="shopping">Shopping</option>
                                <option value="home">Home</option>
                                <option value="education">Education</option>
                                <option value="beauty">Beauty</option>
                            </select>
                        </td>
                        <td>
                            <label for="date">Date:</label>
                            <input type="date" id="date">
                        </td>
                        <td>
                            <label for="label">Label:</label>
                            <input type="text" id="label">
                        </td>
                        <td>
                            <label for="amount">Amount:</label>
                            <input type="number" id="amount" step="0.01">
                        </td>
                        <td>
                            <button onclick="addTransaction()">SAVE</button>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="transaction-summary">
            <h2>Monthly Transactions</h2>
            <table id="transaction-table">
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Year</th>
                        <th>Total Amount</th>
                    </tr>
                </thead>
                <tbody id="transaction-tbody">
                    <!-- Transactions will be dynamically loaded here -->
                </tbody>
            </table>
        </div>

        <div class="total-amount">
            <p>Total Amount Spent: $<span id="total-amount">0.00</span></p>
        </div>
    </main>

    <script>
        function toggleForm() {
            const form = document.getElementById("transaction-options");
            form.style.display = form.style.display === "none" || form.style.display === "" ? "block" : "none";
        }

        function addTransaction() {
            const category = document.getElementById("category").value;
            const date = document.getElementById("date").value;
            const label = document.getElementById("label").value;
            const amount = parseFloat(document.getElementById("amount").value);

            if (!category || !date || !label || isNaN(amount)) {
                alert("Please fill out all fields");
                return;
            }

            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'transaction.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.status === 'success') {
                        fetchTransactions();
                        toggleForm();
                    } else {
                        alert(response.message);
                    }
                }
            };
            xhr.send(`action=add&category=${category}&date=${date}&label=${label}&amount=${amount}`);
        }

        function deleteTransaction(button, transactionId) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'transaction.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.status === 'success') {
                        button.parentElement.parentElement.remove();
                        updateTotalAmount();
                    } else {
                        alert(response.message);
                    }
                }
            };
            xhr.send(`action=delete&id=${transactionId}`);
        }

        function fetchTransactions() {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'transaction.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            const responseText = xhr.responseText;
            const [data, total] = responseText.split('|');
            const transactionData = JSON.parse(data);

            // Month names array
            const monthNames = [
                'January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December'
            ];

            const tbody = document.getElementById("transaction-tbody");
            tbody.innerHTML = '';

            transactionData.forEach(transaction => {
                const monthName = monthNames[transaction.month - 1]; // Adjust for 0-based index
                const newRow = `
                    <tr>
                        <td>${monthName}</td>
                        <td>${transaction.year}</td>
                        <td>${parseFloat(transaction.total_amount).toFixed(2)}</td>
                    </tr>
                `;
                tbody.insertAdjacentHTML('beforeend', newRow);
            });

            document.getElementById("total-amount").textContent = parseFloat(total).toFixed(2);
        }
    };
    xhr.send('action=fetchTransactionData');
}

        function updateTotalAmount() {
            let totalAmount = 0;
            const rows = document.querySelectorAll("#transaction-tbody tr");

            rows.forEach(row => {
                totalAmount += parseFloat(row.cells[2].textContent);
            });

            document.getElementById("total-amount").textContent = totalAmount.toFixed(2);
        }

        document.addEventListener("DOMContentLoaded", function() {
            fetchTransactions();
        });
    </script>
</body>
</html>
