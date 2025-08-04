<?php
$password = "admin123"; // Replace with your chosen password
$hashed_password = password_hash($password, PASSWORD_BCRYPT);
echo "Hashed Password: " . $hashed_password;
?>