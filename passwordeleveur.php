<?php
// Connexion
$servername = "10.0.133.80";
$username = "admin";
$password = "mdp_admin";
$dbname = "brebis";

$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

// Données utilisateur
$type = 'éleveur';
$nom = 'leo';
$prenom = 'duran';
$login = 'éleveur';
$motdepasse = 'mdp_eleveur';
$motdepasse_hash = password_hash($motdepasse, PASSWORD_DEFAULT);
$adresse = '10 rue toi';
$email = 'aurevoir@mail.fr';
$telephone = '0201010101';

// Préparation de la requête
$stmt = $conn->prepare("INSERT INTO UTILISATEUR (type, nom, prenom, login, mot_de_passe, adresse, email, telephone) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param("ssssssss", $type, $nom, $prenom, $login, $motdepasse_hash, $adresse, $email, $telephone);

// Exécution
if ($stmt->execute()) {
    echo "✅ Compte eleveur ajouté avec succès.";
} else {
    echo "❌ Erreur lors de l'ajout : " . $stmt->error;
}

$stmt->close();
$conn->close();
?>