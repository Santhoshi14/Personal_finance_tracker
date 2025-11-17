<?php
session_start();

if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

$conn = mysqli_connect("localhost", "root", "", "tracker");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Function to calculate total income
function getTotalIncome($conn, $tableName) {
    $sql = "SELECT SUM(amount) AS total FROM $tableName";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    return $row['total'] ? $row['total'] : 0;
}

// Function to calculate total spending
function getTotalSpending($conn, $tableName) {
    $sql = "SELECT SUM(amount) AS total FROM $tableName";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    return $row['total'] ? $row['total'] : 0;
}

// Get the username of the logged-in user
$username = $_SESSION["username"];

// Fetch the user ID based on the username
$query = "SELECT id FROM userstable WHERE username='$username'";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) == 1) {
    $row = mysqli_fetch_assoc($result);
    $userId = $row["id"];
} else {
    echo "User not found.";
    exit();
}

// Define the user-specific income and spending tables
$incomeTable = "income_" . $userId;
$spendingTable = "transactions_" . $userId;

// Calculate total income and total spending
$totalIncome = getTotalIncome($conn, $incomeTable);
$totalSpending = getTotalSpending($conn, $spendingTable);

// Calculate current balance
$currentBalance = $totalIncome - $totalSpending;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wallet</title>
    <link rel="stylesheet" href="all.css">
  
</head>
<body>
    <header>
        <nav>
            <h1>Wallet</h1>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="transaction.php">Transactions</a></li>
                <li><a href="observation.php">Observations</a></li>
                <li><a href="income.php">Income</a></li>
                <li><a href="wallet.php">Wallet</a></li>
                <li><a href="logout.php">Logout</a></li>
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
        <div class="balance-section">
            <h2>Current Balance</h2>
            <p>Total Income: $<?php echo number_format($totalIncome, 2); ?></p>
            <p>Total Spending: $<?php echo number_format($totalSpending, 2); ?></p>
            <p>Balance: $<?php echo number_format($currentBalance, 2); ?></p>
        </div>
    </main>
</body>
</html>
