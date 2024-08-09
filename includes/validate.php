<?php

function getUser($conn, $email) {
    $sql = "SELECT * FROM customer WHERE Username=?";
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt === false) {
        echo mysqli_error($conn);
    } else {
        mysqli_stmt_bind_param($stmt, "s", $email);

        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            return mysqli_fetch_array($result, MYSQLI_ASSOC);
        }
    }
}

function validateEmail($email) {
    // Removed the redundant call to getUser
    // Regular expression pattern for email validation
    $pattern = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';

    // Validate email address
    if (preg_match($pattern, $email)) {
        return true; // Email is valid
    } else {
        return false; // Email is not valid
    }
}

?>
