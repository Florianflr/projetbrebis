<?php
require_once 'auth.php'; // Vérifie que l'utilisateur est connecté

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'administrateur') {
    header("Location: accueil.php?error=access_denied");
    exit();
}
