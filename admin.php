<?php
session_start();

// Vérification du rôle administrateur
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'administrateur') {
    header("Location: accueil.php?error=access_denied");
    exit();
}

// Connexion à la base de données
$servername = "10.0.133.80";
$username = "admin";
$password = "mdp_admin";
$dbname = "brebis";

$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

// Traitement du formulaire
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['userName'] ?? '');
    $motdepasse = trim($_POST['userPassword'] ?? '');
    $role = $_POST['userRole'] ?? 'eleveur';

    if (empty($nom) || empty($motdepasse)) {
        $message = "❌ Tous les champs sont requis.";
    } elseif (strlen($motdepasse) < 6) {
        $message = "❌ Le mot de passe doit contenir au moins 6 caractères.";
    } else {
        $motdepasse_hash = password_hash($motdepasse, PASSWORD_DEFAULT);
        $prenom = '';
        $login = $nom;
        $adresse = '';
        $email = NULL; // Changé de '' à NULL pour éviter la violation de contrainte unique
        $telephone = '';

        $stmt = $conn->prepare("INSERT INTO UTILISATEUR (type, nom, prenom, login, mot_de_passe, adresse, email, telephone) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt === false) {
            $message = "❌ Erreur de préparation : " . $conn->error;
        } else {
            $stmt->bind_param("ssssssss", $role, $nom, $prenom, $login, $motdepasse_hash, $adresse, $email, $telephone);

            if ($stmt->execute()) {
                $message = "✅ Nouvel utilisateur ajouté avec succès.";
            } else {
                $message = "❌ Erreur lors de l'ajout : " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - Brebis Connecté</title>
    <link rel="stylesheet" href="../css/monstyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .form-section {
            background: #fff;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 2rem auto;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: #5c4033;
            font-size: 1rem;
        }

        .form-group input[type="text"],
        .form-group input[type="password"],
        .form-group select {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #fafafa;
        }

        .form-group input[type="text"]:focus,
        .form-group input[type="password"]:focus,
        .form-group select:focus {
            border-color: #5c4033;
            outline: none;
            background: #fff;
            box-shadow: 0 0 5px rgba(92, 64, 51, 0.2);
        }

        .form-group select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%235c4033' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14L2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: calc(100% - 1rem) center;
            padding-right: 2.5rem;
        }

        button[type="submit"] {
            width: 100%;
            padding: 0.9rem;
            background: linear-gradient(to right, #5c4033, #7d5a47);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        button[type="submit"]:hover {
            background: linear-gradient(to right, #7d5a47, #9c7663);
            transform: scale(1.02);
        }

        h2 {
            color: #3a1c0f;
            text-align: center;
            margin-bottom: 2rem;
            font-size: 1.8rem;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <header>
        <h1>Administration</h1>
        <nav>
            <ul>
                <li><a href="accueil.php"><i class="fas fa-home"></i></a></li>
                <li><a href="position.php">Position des troupeaux</a></li>
                <li><a href="collier.php">Colliers</a></li>
                <li><a href="creationtroupeau.php">Troupeaux</a></li>
                <li class="active"><a href="admin.php">Administration</a></li>
                <li><a href="index.php"><i class="fas fa-sign-out-alt"></i></a></li>
            </ul>
        </nav>
    </header><br>

    <main>
        <div class="form-section">
            <?php if ($message): ?>
                <div class="message <?php echo strpos($message, '✅') === 0 ? 'success' : 'error'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            <h2>Créer un utilisateur</h2>
            <form method="POST" action="admin.php">
                <div class="form-group">
                    <label for="userName">Nom de l'utilisateur</label>
                    <input type="text" id="userName" name="userName" required placeholder="Entrez le nom de l'utilisateur">
                </div>
                <div class="form-group">
                    <label for="userPassword">Mot de passe</label>
                    <input type="password" id="userPassword" name="userPassword" required placeholder="Minimum 6 caractères">
                </div>
                <div class="form-group">
                    <label for="userRole">Rôle</label>
                    <select id="userRole" name="userRole" required>
                        <option value="administrateur">Administrateur</option>
                        <option value="eleveur">Éleveur</option>
                        <option value="berger">Berger</option>
                    </select>
                </div>
                <button type="submit">Créer</button>
            </form>
        </div>
    </main>
</body>
</html>