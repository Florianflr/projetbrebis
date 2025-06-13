<?php
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'administrateur') {
    header("Location: accueil.php?error=access_denied");
    exit();
}

// Database connection
$servername = "10.0.133.80";
$username = "admin";
$password = "mdp_admin";
$dbname = "brebis";

$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("Erreur connexion : " . $conn->connect_error);
}

$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $libele = trim($_POST['libele']);
    $selected_brebis = isset($_POST['brebis']) ? $_POST['brebis'] : [];

    if (empty($libele)) {
        $message = "Erreur : le libellé est obligatoire.";
    } else {
        // Vérifier si la colonne 'libele' existe (optionnel, pour débogage)
        $columns = $conn->query("SHOW COLUMNS FROM TROUPEAU LIKE 'libele'");
        if ($columns->num_rows === 0) {
            die("Erreur : la colonne 'libele' n'existe pas dans la table TROUPEAU. Vérifiez la structure de la base de données.");
        }

        // Insert the new troupeau
        $insert = $conn->prepare("INSERT INTO TROUPEAU (libele) VALUES (?)");
        $insert->bind_param("s", $libele);
        if ($insert->execute()) {
            $id_troupeau = $conn->insert_id;

            // Update associated sheep with the new troupeau ID
            if (!empty($selected_brebis)) {
                $update_stmt = $conn->prepare("UPDATE BREBIS_COLLIER SET ID_troupeau = ? WHERE ID_brebis = ?");
                foreach ($selected_brebis as $id_brebis) {
                    $id_brebis = intval($id_brebis); // Ensure integer value
                    $update_stmt->bind_param("ii", $id_troupeau, $id_brebis);
                    $update_stmt->execute();
                }
                $update_stmt->close();
            }
            $message = "Troupeau ajouté avec succès.";
        } else {
            $message = "Erreur lors de l'ajout : " . $insert->error;
        }
        $insert->close();
    }
}

// Fetch all sheep to display in the form, including their current troupeau
$brebis_result = $conn->query("SELECT ID_brebis, nom, ID_troupeau FROM BREBIS_COLLIER");

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Créer un troupeau</title>
    <link rel="stylesheet" href="../css/monstyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Style pour le conteneur de la section */
        .form-section {
            max-width: 500px;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .form-section h2 {
            color: #333;
            border-bottom: 2px solid #8B4513;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }

        /* Style des messages */
        .message {
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            text-align: center;
            font-weight: bold;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Style des champs de formulaire */
        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
            color: #555;
        }

        .form-group input[type="text"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 14px;
        }

        /* Style de la liste des brebis */
        .brebis-list {
            margin-top: 5px;
        }

        .brebis-list label {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
        }

        .brebis-list input[type="checkbox"] {
            margin-right: 10px;
        }

        .brebis-assigned {
            color: #888;
            font-style: italic;
            margin-left: 10px;
        }

        /* Style des boutons */
        button {
            background-color: #8B4513;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #654321;
        }
    </style>
</head>
<body>
    <header>
        <h1>Gestion des troupeaux</h1>
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
            <h2>Créer un nouveau troupeau</h2>

            <?php if (!empty($message)): ?>
                <p class="message <?= strpos($message, 'Erreur') === false ? 'success' : 'error' ?>">
                    <?= htmlspecialchars($message) ?>
                </p>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="libele">Libellé du troupeau</label>
                    <input type="text" name="libele" id="libele" required placeholder="Entrez le nom du troupeau">
                </div>
                <div class="form-group">
                    <label for="brebis">Sélectionner les brebis à associer</label>
                    <div class="brebis-list">
                        <?php
                        if ($brebis_result->num_rows > 0) {
                            while ($row = $brebis_result->fetch_assoc()) {
                                $is_assigned = !is_null($row['ID_troupeau']) && $row['ID_troupeau'] != 0;
                                echo '<label>';
                                echo '<input type="checkbox" name="brebis[]" value="' . htmlspecialchars($row['ID_brebis']) . '"' . ($is_assigned ? ' disabled' : '') . '>';
                                echo htmlspecialchars($row['nom']);
                                if ($is_assigned) {
                                    echo '<span class="brebis-assigned">(Déjà associé au troupeau ' . htmlspecialchars($row['ID_troupeau']) . ')</span>';
                                }
                                echo '</label>';
                            }
                        } else {
                            echo '<p>Aucune brebis disponible.</p>';
                        }
                        ?>
                    </div>
                </div>
                <button type="submit">Créer le troupeau</button>
            </form>
        </div>
    </main>
</body>
</html>
<?php
$conn->close();
?>