<?php
session_start();

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

// Require login - redirect if not authenticated
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /login.php');
        exit;
    }
}

// Login function
function login($username, $password) {
    require_once 'database.php';
    
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT id, username, password, nama_lengkap FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            
            $stmt->close();
            closeDBConnection($conn);
            return true;
        }
    }
    
    $stmt->close();
    closeDBConnection($conn);
    return false;
}

// Logout function
function logout() {
    session_destroy();
    header('Location: /login.php');
    exit;
}

// Get current user info
function getCurrentUser() {
    if (isLoggedIn()) {
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'nama_lengkap' => $_SESSION['nama_lengkap'] ?? 'Admin'
        ];
    }
    return null;
}
?>
