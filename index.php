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
    $user_id = $row["id"];

    // Define the transaction table corresponding to the user ID
    $transaction_table_name = "transactions_$user_id";

    // Get current month and year
    $currentMonth = date('m');
    $currentYear = date('Y');

    // Fetch transactions for the current month
    $transaction_query = "SELECT * FROM $transaction_table_name WHERE MONTH(date) = '$currentMonth' AND YEAR(date) = '$currentYear'";
    $transaction_result = mysqli_query($conn, $transaction_query);
    
    // Calculate total spending for the current month
    $total_spending = 0;
    while ($row = mysqli_fetch_assoc($transaction_result)) {
        $total_spending += $row['amount'];
    }

    // Fetch income for the current month
    $income_table_name = "income_$user_id";
    $income_query = "SELECT * FROM $income_table_name WHERE MONTH(date) = '$currentMonth' AND YEAR(date) = '$currentYear'";
    $income_result = mysqli_query($conn, $income_query);
    
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
    <header >
        <nav>
            <h1>PERSONAL FINANCE TRACKER</h1>
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
        <section id="home-section">
            <h1>Financial Overview for <?php echo date('F Y'); ?></h1>
           
            <?php if ($income_result && mysqli_num_rows($income_result) > 0): ?>
                <?php
                $total_income = 0;
                while ($row = mysqli_fetch_assoc($income_result)) {
                    $total_income += $row['amount'];
                }
                ?>
                <p id="income">Total Income: $<?php echo $total_income; ?></p>
            <?php endif; ?>
            <p id="spending">Total Spending: $<?php echo $total_spending; ?></p>
        </section>
        
        <h3>Your Income for <?php echo date('F Y'); ?></h3>
        <?php if ($income_result && mysqli_num_rows($income_result) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Source</th>
                        <th>Date</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php mysqli_data_seek($income_result, 0); ?>
                    <?php while ($row = mysqli_fetch_assoc($income_result)): ?>
                        <tr>
                            <td><?php echo $row['source']; ?></td>
                            <td><?php echo $row['date']; ?></td>
                            <td><?php echo $row['amount']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No income found for <?php echo date('F Y'); ?>.</p>
        <?php endif; ?>
        <h3>Your Spending for <?php echo date('F Y'); ?></h3>
        <?php if ($transaction_result && mysqli_num_rows($transaction_result) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Date</th>
                        <th>Label</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php mysqli_data_seek($transaction_result, 0); ?>
                    <?php while ($row = mysqli_fetch_assoc($transaction_result)): ?>
                        <tr>
                            <td><?php echo $row['category']; ?></td>
                            <td><?php echo $row['date']; ?></td>
                            <td><?php echo $row['label']; ?></td>
                            <td><?php echo $row['amount']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No transactions found for <?php echo date('F Y'); ?>.</p>
        <?php endif; ?>

    </main>
</body>
</html>
