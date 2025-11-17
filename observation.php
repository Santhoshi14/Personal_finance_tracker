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

// Define the user-specific tables
$incomeTable = "income_" . $userID;
$transactionsTable = "transactions_".$userID;

// Fetch income data
$incomeQuery = "SELECT SUM(amount) AS totalIncome FROM $incomeTable";
$incomeResult = mysqli_query($conn, $incomeQuery);
$incomeRow = mysqli_fetch_assoc($incomeResult);
$totalIncome = $incomeRow['totalIncome'];

// Fetch spending data per category
$spendingQuery = "SELECT category, SUM(amount) AS totalSpent FROM $transactionsTable GROUP BY category";
$spendingResult = mysqli_query($conn, $spendingQuery);

$spendingData = [];
while ($row = mysqli_fetch_assoc($spendingResult)) {
    $spendingData[] = $row;
}
} else {
    // Redirect to login page if user not found
    header("Location: login.php");
    exit();
}
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Observation Page</title>
    <link rel="stylesheet" href="all.css">
    <!-- Include Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .chart-container {
            width: 300px; /* Adjust the width of the box */
            height: 300px; /* Adjust the height of the box */
            border: 1px solid #ccc; /* Add border for the box */
            padding: 20px; /* Add padding for the box */
            margin: 0 auto; /* Center the box horizontally */
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <h1>Observation Page</h1>
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
    <div class="chart-container" style="height: 650px; width: 400px;">

            <h2>Income and Spending Overview</h2>
            <canvas id="incomeSpendingChart"style="height: 400px; width: 350px;"></canvas>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const totalIncome = <?php echo json_encode($totalIncome); ?>;
            const spendingData = <?php echo json_encode($spendingData); ?>;

            const labels = spendingData.map(item => item.category);
            const spendingAmounts = spendingData.map(item => item.totalSpent);
            const data = {
                labels: labels,
                datasets: [{
                    label: 'Spending by Category',
                    data: spendingAmounts,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(255, 159, 64, 0.2)',
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)',
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                    ],
                    borderWidth: 1
                }]
            };

            const config = {
                type: 'pie',
                data: data,
                options: {
                    responsive: true,
                    maintainAspectRatio: false, // Disable aspect ratio to freely adjust size
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Income vs Spending by Category'
                        }
                    }
                }
            };

            const incomeSpendingChart = new Chart(
                document.getElementById('incomeSpendingChart'),
                config
            );
        });
    </script>
</body>
</html>
