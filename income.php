
<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION["username"])) {
    // Redirect to the login page if not logged in
    header("Location: login.php");
    exit();
}

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
    
    // Define the user-specific income table
    $tableName = "income_" . $userID;

    // Handle adding income
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action"])) {
        if ($_POST["action"] === "addIncome") {
            // Validate input
            $source = mysqli_real_escape_string($conn, $_POST["source"]);
            $amount = mysqli_real_escape_string($conn, $_POST["amount"]);
            $date = mysqli_real_escape_string($conn, $_POST["date"]);
            
            if (!empty($source) && !empty($amount) && !empty($date)) {
                // Insert income data into user-specific income table
                $query = "INSERT INTO $tableName (source, amount, date) VALUES ('$source', '$amount', '$date')";
                if (mysqli_query($conn, $query)) {
                    echo "Income added successfully.";
                } else {
                    echo "Error: " . mysqli_error($conn);
                }
            } else {
                echo "Please fill out all fields.";
            }
            exit();
        }

   // Handle fetching income data
if ($_POST["action"] === "fetchIncomeData") {
    // Fetch income data grouped by month from user-specific income table
    $query = "SELECT MONTH(date) AS month, YEAR(date) AS year, SUM(amount) AS totalAmount
              FROM $tableName
              GROUP BY YEAR(date), MONTH(date)
              ORDER BY year DESC, month DESC";

    $result = mysqli_query($conn, $query);
    $incomeData = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $incomeData[] = $row;
    }
    echo json_encode($incomeData);

    // Fetch total amount
    $totalQuery = "SELECT SUM(amount) AS totalAmount FROM $tableName";
    $totalResult = mysqli_query($conn, $totalQuery);
    $totalRow = mysqli_fetch_assoc($totalResult);
    echo "|" . $totalRow['totalAmount'];

    exit();
}


            // Fetch total amount
            $totalQuery = "SELECT SUM(amount) AS totalAmount FROM $tableName";
            $totalResult = mysqli_query($conn, $totalQuery);
            $totalRow = mysqli_fetch_assoc($totalResult);
            echo "|" . $totalRow['totalAmount'];
            exit();
        }
    }
 else {
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
    <title>Income Page</title>
    <link rel="stylesheet" href="all.css">
</head>
<body>
    <header>
        <nav>
            <h1>Income Page</h1>
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
  
    <button id="addIncomeBtn" onclick="showIncomeForm()">
    <span>&#43;</span> Add Income</button>
    <div id="incomeForm" style="display: none;">
        <table id="income-form-table">
            <tr>
                <td>
                    <label for="source">Source:</label>
                    <select id="source" name="source">
                        <option value="Business">Business</option>
                        <option value="Pocket Money">Pocket Money</option>
                        <option value="Side Hustle">Side Hustle</option>
                        <option value="Part-time Job">Part-time Job</option>
                    </select>
                </td>
                <td>
                    <label for="amount">Amount:</label>
                    <input type="number" id="amount" name="amount" step="0.01" required>
                </td>
                <td>
                    <label for="date">Date:</label>
                    <input type="date" id="date" name="date" required>
                </td>
                <td>
                    <button type="button" onclick="submitIncome()">Submit</button>
                </td>
            </tr>
        </table>
    </div>
    <h2>Monthly Income</h2>
    <table id="income-table">
        <thead>
            <tr>
                <th>Month</th>
                <th>Total Amount</th>
            </tr>
        </thead>
        <tbody id="income-table-body">
            <!-- Month-wise income data will be dynamically added here -->
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" id="total-amount">Total Income: $0.00</td>
            </tr>
        </tfoot>
    </table>
    <p id="response"></p>
</main>



    <script> function showIncomeForm() {
    document.getElementById("incomeForm").style.display = "block";
}

function submitIncome() {
    const source = document.getElementById("source").value;
    const amount = document.getElementById("amount").value;
    const date = document.getElementById("date").value;

    // Create FormData object to send data to PHP
    const formData = new FormData();
    formData.append("action", "addIncome");
    formData.append("source", source);
    formData.append("amount", amount);
    formData.append("date", date);

    // Send AJAX request to PHP
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "income.php", true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            // Display response message
            document.getElementById("response").textContent = xhr.responseText;
            // Clear input fields
            document.getElementById("source").value = "";
            document.getElementById("amount").value = "";
            document.getElementById("date").value = "";
            // Update income table and total amount
            fetchIncome();
        } else {
            // Display error message
            document.getElementById("response").textContent = "Error: Unable to add income.";
        }
    };
    xhr.send(formData);
}

function fetchIncome() {
    // Send AJAX request to fetch income data grouped by month
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "income.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onload = function() {
        if (xhr.status === 200) {
            const response = xhr.responseText.split("|");
            const incomeData = JSON.parse(response[0]);
            const totalAmount = response[1];
            updateIncomeByMonth(incomeData);
            updateTotalAmount(totalAmount);
        } else {
            // Display error message
            document.getElementById("response").textContent = "Error: Unable to fetch income data.";
        }
    };
    xhr.send("action=fetchIncomeData");
}

function updateIncomeByMonth(incomeData) {
    // Clear previous data
    document.getElementById("income-table-body").innerHTML = "";

    // Group income data by month and year
    const groupedData = {};
    incomeData.forEach(function(income) {
        const monthYear = income.year + "-" + income.month;
        if (!groupedData[monthYear]) {
            groupedData[monthYear] = {
                year: income.year,
                month: income.month,
                totalAmount: parseFloat(income.totalAmount)
            };
        } else {
            groupedData[monthYear].totalAmount += parseFloat(income.totalAmount);
        }
    });

    // Display income data grouped by month and year
    for (const key in groupedData) {
        if (groupedData.hasOwnProperty(key)) {
            const data = groupedData[key];
            const row = document.createElement("tr");
            row.innerHTML = `
                <td>${getMonthName(data.month)} ${data.year}</td>
                <td>${data.totalAmount.toFixed(2)}</td>
            `;
            document.getElementById("income-table-body").appendChild(row);
        }
    }
}

function updateTotalAmount(totalAmount) {
    document.getElementById("total-amount").textContent = "Total Income: $" + totalAmount;
}

function getMonthName(monthNumber) {
    const months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    return months[monthNumber - 1];
}

// Fetch income data on page load
fetchIncome();
</script>
</body>
</html>
