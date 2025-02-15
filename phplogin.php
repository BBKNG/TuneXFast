<?php
// login.php
session_start();

// Exemple simplifié d'identifiants
$validUsername = "admin";
$validPassword = "password";

// Récupération des données du formulaire
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

// Vérification des identifiants
if ($username === $validUsername && $password === $validPassword) {
    // Connexion réussie
    $_SESSION['logged_in'] = true;
    // Redirection vers la page principale
    header('Location: index.php');
    exit;
} else {
    // Connexion échouée : on renvoie vers la page de login avec un message d'erreur
    header('Location: login.html?error=1');
    exit;
}
