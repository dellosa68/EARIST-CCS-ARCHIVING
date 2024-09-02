<?php
session_start();

if (isset($_POST['pin'])) {
    $entered_pin = $_POST['pin'];

    if (isset($_SESSION['pin']) && isset($_SESSION['pin_expiration'])) {
        if (time() <= $_SESSION['pin_expiration']) {
            if ($entered_pin == $_SESSION['pin']) {
                // PIN is correct and not expired
                header("Location: reset_password.php");
                exit();
            } else {
                header("Location: verify_pin.php?error=Invalid PIN");
                exit();
            }
        } else {
            header("Location: verify_pin.php?error=PIN expired");
            exit();
        }
    } else {
        header("Location: forgot_password.php?error=PIN not found");
        exit();
    }
} else {
    header("Location: verify_pin.php");
    exit();
}
?>
