<?php
// Pengalihan otomatis karena fitur reset data telah dinonaktifkan
require_once __DIR__ . '/../../config/database.php';
header("Location: " . BASE_URL . "/pages/pemilik/dashboard.php");
exit;
