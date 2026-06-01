<?php
function e_reset($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function pastikanTabelPasswordResets($conn) {
    mysqli_query($conn, "
        CREATE TABLE IF NOT EXISTS password_resets (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            email VARCHAR(100) NOT NULL,
            otp VARCHAR(255) NOT NULL,
            expires_at DATETIME NOT NULL,
            used TINYINT(1) NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_user_email (user_id, email),
            INDEX idx_email_used (email, used),
            INDEX idx_expires_at (expires_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
}

function ambilUserResetByEmail($conn, $email) {
    $stmt = mysqli_prepare($conn, "SELECT id, nama, email FROM users WHERE email = ? LIMIT 1");
    if (!$stmt) return null;
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $user ?: null;
}

function tandaiOtpAktifSebagaiUsed($conn, $userId, $email) {
    $stmt = mysqli_prepare($conn, "UPDATE password_resets SET used = 1 WHERE user_id = ? AND email = ? AND used = 0");
    if (!$stmt) return false;
    mysqli_stmt_bind_param($stmt, "is", $userId, $email);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function resetOtpAttempts($email) {
    unset($_SESSION['reset_otp_attempts'][$email]);
}

function otpAttempts($email) {
    return (int)($_SESSION['reset_otp_attempts'][$email] ?? 0);
}

function tambahOtpAttempt($email) {
    if (!isset($_SESSION['reset_otp_attempts'])) {
        $_SESSION['reset_otp_attempts'] = [];
    }
    $_SESSION['reset_otp_attempts'][$email] = otpAttempts($email) + 1;
}
?>
