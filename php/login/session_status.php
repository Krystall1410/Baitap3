<?php
header('Content-Type: application/json');
session_start();
$resp = ['loggedin' => false, 'role' => null, 'username' => null];
if (!empty($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    $resp['loggedin'] = true;
    $resp['role'] = $_SESSION['role'] ?? null;
    $resp['username'] = $_SESSION['username'] ?? null;
}
echo json_encode($resp);