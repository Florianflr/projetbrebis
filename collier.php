<?php
session_start();

// Afficher les erreurs pour le débogage
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Vérification du rôle
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
    die("Erreur de connexion : " . $conn->connect_error);
}

$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ajouter une brebis
    if (isset($_POST['nom']) && !isset($_POST['delete_id'])) {
        $nom = trim($_POST['nom']);
        $race = trim($_POST['race']);
        $medaille = trim($_POST['medaille']);
        $id_troupeau = intval($_POST['id_troupeau']);

        if (empty($nom) || empty($race)) {
            $message = "Erreur : les champs Nom et Race sont obligatoires.";
        } else {
            $stmt = $conn->prepare("INSERT INTO BREBIS_COLLIER (nom, race, medaille, ID_utilisateur, ID_troupeau) VALUES (?, ?, ?, 1, ?)");
            $stmt->bind_param("sssi", $nom, $race, $medaille, $id_troupeau);
            if ($stmt->execute()) {
                $message = "Brebis ajoutée avec succès.";
            } else {
                $message = "Erreur lors de l'ajout : " . $stmt->error;
            }
            $stmt->close();
        }
    }

    // Supprimer une brebis
    if (isset($_POST['delete_id'])) {
        $id_brebis = intval($_POST['delete_id']);
        $delete = $conn->prepare("DELETE FROM BREBIS_COLLIER WHERE ID_brebis = ?");
        $delete->bind_param("i", $id_brebis);
        if ($delete->execute()) {
            $message = "Brebis supprimée avec succès.";
        } else {
            $message = "Erreur lors de la suppression : " . $delete->error;
        }
        $delete->close();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gérer les brebis</title>
    <link rel="stylesheet" href="../css/monstyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
    
    </style>
</head>
<body>
<header>
    <h1>Gestion des brebis</h1>
    <nav>
        <ul>
            <li><a href="accueil.php"><i class="fas fa-home"></i></a></li>
            <li><a href="position.php">Position</a></li>
            <li><a href="collier.php">Colliers</a></li>
            <li><a href="creationtroupeau.php">Troupeaux</a></li>
            <li><a href="admin.php">Administration</a></li>
            <li><a href="index.php"><i class="fas fa-sign-out-alt"></i></a></li>
        </ul>
    </nav>
</header>

<main>
    <div class="sections-container">
        <div class="form-section">
            <h2>Ajouter une brebis</h2>

            <?php if (!empty($message)): ?>
                <p class="message <?= strpos($message, 'Erreur') === false ? 'success' : 'error' ?>">
                    <?= htmlspecialchars($message) ?>
                </p>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="nom">Nom</label>
                    <input type="text" name="nom" id="nom" required placeholder="Nom de la brebis">
                </div>
                <div class="form-group">
                    <label for="race">Race</label>
                    <input type="text" name="race" id="race" required placeholder="Race de la brebis">
                </div>
                <div class="form-group">
                    <label for="medaille">Médaille (optionnel)</label>
                    <input type="text" name="medaille" id="medaille" placeholder="Numéro de médaille">
                </div>
                <div class="form-group">
                    <label for="id_troupeau">Troupeau</label>
                    <select name="id_troupeau" id="id_troupeau" required>
                        <?php
                        $troupeaux = $conn->query("SELECT ID_troupeau, libele FROM TROUPEAU");
                        while ($row = $troupeaux->fetch_assoc()) {
                            echo '<option value="' . $row['ID_troupeau'] . '">' . htmlspecialchars($row['libele']) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <button type="submit">Ajouter</button>
            </form>
        </div>

        <div class="form-section">
            <h2>Supprimer une brebis</h2>
            <?php
            $result = $conn->query("SELECT ID_brebis, nom FROM BREBIS_COLLIER");
            if ($result->num_rows > 0) {
                echo '<form method="POST" action="">';
                while ($row = $result->fetch_assoc()) {
                    echo '<div class="sheep-item">';
                    echo '<label>';
                    echo '<input type="radio" name="delete_id" value="' . $row['ID_brebis'] . '" required>';
                    echo '<span class="sheep-info">' . htmlspecialchars($row['nom']) . '</span>';
                    echo '</label>';
                    echo '</div>';
                }
                echo '<button type="submit">Supprimer la brebis sélectionnée</button>';
                echo '</form>';
            } else {
                echo "<p>Aucune brebis à supprimer.</p>";
            }
            $conn->close();
            ?>
        </div>
    </div>
</main>
</body>
</html>