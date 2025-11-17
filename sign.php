<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "tracker");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if (isset($_POST["submit"])) {
    $usernameid = mysqli_real_escape_string($conn, $_POST["username"]);
    $password = mysqli_real_escape_string($conn, $_POST["password"]);
    $confirmPassword = mysqli_real_escape_string($conn, $_POST["confirm-password"]);
    $email = mysqli_real_escape_string($conn, $_POST["email"]);

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email format');</script>";
    } else if (!preg_match('/^[a-zA-Z0-9]+[a-zA-Z0-9._%+-]*[0-9]+@gmail\.com$/i', $email)) {
        echo "<script>alert('Email must follow a format of nameNUMERIC@gmail.com eg:abc01@gmail.com');</script>";
    } else {
        // Check for duplicate username or email
        $duplicate = mysqli_query($conn, "SELECT * FROM userstable WHERE username='$usernameid' OR email='$email'");
        
        if (mysqli_num_rows($duplicate) != 0) {
            echo "<script>alert('Username or email is already taken');</script>";
        } else {
            if ($password == $confirmPassword) {
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                
                // Insert the new user into the userstable
                $query = "INSERT INTO userstable (username, password, email) VALUES ('$usernameid', '$hashedPassword', '$email')";
                if (mysqli_query($conn, $query)) {
                    // Get the last inserted user ID
                    $userid = mysqli_insert_id($conn);

                    // Create a user-specific transactions table
                    $userTable = "transactions_" . $userid;
                    $createTableQuery = "CREATE TABLE $userTable (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        category VARCHAR(50) NOT NULL,
                        date DATE NOT NULL,
                        label VARCHAR(100) NOT NULL,
                        amount DECIMAL(11, 0) NOT NULL
                    )";

                    if (mysqli_query($conn, $createTableQuery)) {
                        // Create a user-specific income table
                        $incomeTable = "income_" . $userid;
                        $createIncomeTableQuery = "CREATE TABLE $incomeTable (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            source VARCHAR(50) NOT NULL,
                            date DATE NOT NULL,
                            amount INT NOT NULL
                        )";
                                              
                        if (mysqli_query($conn, $createIncomeTableQuery)) {
                            echo "<script>alert('Signup is successful and user tables created');</script>";
                            echo "<script>window.location.href = 'login.php';</script>";
                        } else {
                            echo "<script>alert('Error creating income table: " . mysqli_error($conn) . "');</script>";
                        }
                    } else {
                        echo "<script>alert('Error creating user table: " . mysqli_error($conn) . "');</script>";
                    }
                } else {
                    echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
                }
            } else {
                echo "<script>alert('Passwords do not match');</script>";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        function validateEmail() {
            var email = document.getElementById('email').value;
            if (!email.match(/^[a-zA-Z0-9]+[a-zA-Z0-9._%+-]*[0-9]+@gmail\.com$/i)) {
                alert('Email must follow a format of nameNUMERIC@gmail.com eg:abc01@gmail.com');
                return false;
            }
            return true;
        }
    </script>
</head>
<body>
    <div class="container">
        <h2>Sign Up</h2>
        <form method="post" action="" autocomplete="off" onsubmit="return validateEmail()">
            <div class="input-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="input-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="input-group">
                <label for="confirm-password">Confirm Password:</label>
                <input type="password" id="confirm-password" name="confirm-password" required>
            </div>
            <div class="input-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <button type="submit" name="submit">Sign Up</button>
        </form>
        <p>Already have an account? <a href="login.php">Login</a></p>
    </div>
</body>
</html>
