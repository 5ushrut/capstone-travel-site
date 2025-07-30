<?php
require_once '../includes/db.php';

// Destroy session and redirect
session_destroy();
redirect('../index.php?message=logged_out');
?>
