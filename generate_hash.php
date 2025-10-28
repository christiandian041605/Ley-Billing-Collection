<?php
if (isset($_POST['password'])) {
    $password = $_POST['password'];
    $hash = password_hash($password, PASSWORD_DEFAULT);
    echo "<h2>Generated Hash:</h2>";
    echo "<p>" . htmlspecialchars($hash) . "</p>";
    echo "<hr>";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Password Hash Generator</title>
</head>
<body>
    <h1>Password Hash Generator</h1>
    <form method="POST">
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <button type="submit">Generate Hash</button>
    </form>
</body>
</html>