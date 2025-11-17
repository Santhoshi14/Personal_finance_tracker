<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "tracker");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Initialize variables for form fields
$username = "";
$password = "";

if (isset($_POST["login"])) {
    $username = mysqli_real_escape_string($conn, $_POST["username"]);
    $password = mysqli_real_escape_string($conn, $_POST["password"]);
    
    // Fetch user from database
    $query = "SELECT * FROM userstable WHERE username='$username'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        
        // Check if the password matches (hashed or plaintext)
        if (password_verify($password, $row['password'])) {
            // Login successful, redirect to index.php
            $_SESSION["username"] = $username;
            header("Location: index.php"); // Redirect to index.php
            exit();
        } elseif ($password === $row['password']) {
            // This block is for testing or cases where passwords are stored in plaintext
            $_SESSION["username"] = $username;
            header("Location: index.php"); // Redirect to index.php
            exit();
        } else {
            echo "<script>alert('Invalid username or password');</script>";
        }
    } else {
        echo "<script>alert('Invalid username or password');</script>";
    }
}

// Clear form field values if not submitted or if login failed
if (!isset($_POST["login"]) || isset($_POST["login"]) && mysqli_num_rows($result) != 1) {
    $username = "";
    $password = "";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Login</h2>
        <form method="post" action="" autocomplete="off">
            <div class="input-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
            </div>
            <div class="input-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" value="<?php echo htmlspecialchars($password); ?>" required>
            </div>
            <button type="submit" name="login">Login</button>
        </form>
        <p>Don't have an account? <a href="sign.php">Sign Up</a></p>
    </div>
</body>
</html>