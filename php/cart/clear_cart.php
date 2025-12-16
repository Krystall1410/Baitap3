<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /baitap3/view/cart.php');
    exit;
}

unset($_SESSION['cart']);

header('Location: /baitap3/view/cart.php');
exit;
