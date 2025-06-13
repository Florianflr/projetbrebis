<?php
session_start();

// Paramètres de connexion à la base de données
$servername = "10.0.133.80";
$dbname = "brebis";
$dbuser = "admin";
$dbpass = "mdp_admin";

// Connexion
$conn = new mysqli($servername, $dbuser, $dbpass, $dbname);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    error_log("Erreur de connexion à la base de données : " . $conn->connect_error);
    http_response_code(500);
    exit("Erreur serveur. Veuillez réessayer plus tard.");
}

// Traitement du formulaire si soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($login === '' || $password === '') {
        $erreur = "Veuillez remplir tous les champs.";
    } else {
        // Préparation et exécution de la requête
        $stmt = $conn->prepare("SELECT type, mot_de_passe FROM UTILISATEUR WHERE login = ?");
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['mot_de_passe'])) {
            // Regenerate session ID to prevent session fixation
            session_regenerate_id(true);

            // Authentification réussie
            $_SESSION['login'] = $login;
            $_SESSION['role'] = $user['type'];

            // Redirection vers accueil.php
            header("Location: accueil.php");
            exit;
        } else {
            $erreur = "Identifiants incorrects.";
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/index.css">
    <title>Connexion</title>
</head>
<body>
    <h1>Connexion</h1>

    <?php if (!empty($erreur)) : ?>
        <p style="color:red;"><?php echo htmlspecialchars($erreur, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>

    <form action="" method="post">
        <label for="login">Identifiant :</label>
        <input type="text" name="login" id="login" required><br>

        <label for="password">Mot de passe :</label>
        <input type="password" name="password" id="password" required><br>

        <button type="submit">Se connecter</button>
    </form>
</body>
</html>