<?php
session_start();

// Vérification de l'accès administrateur
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

// Récupérer les données des colliers depuis DONNEE_COLLIER
$colliers = [];
$sql = "SELECT ID_donnee, ID_brebis, niveau_batterie, latitude, longitude, date_heure FROM DONNEE_COLLIER";
$result = $conn->query($sql);

if ($result === false) {
    die("Erreur dans la requête SQL : " . $conn->error);
}

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $colliers[] = $row;
    }
} else {
    echo "<!-- Aucune donnée trouvée dans la table DONNEE_COLLIER -->";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Colliers - Berger Lora</title>
    <link rel="stylesheet" href="../css/monstyle.css">
    <link rel="stylesheet" href="../css/troupeau1.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #map { height: 500px; width: 100%; margin-top: 20px; }
        .collier-table { width: 100%; border-collapse: collapse; }
        .collier-table th, .collier-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .collier-table th { background-color: #333; color: white; }
    </style>
</head>
<body>
    <header>
        <h1>Gestion des colliers</h1>
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
    </header>

    <main>
        <div class="container">
            <h2>Localisation des colliers</h2>
            <div id="map"></div>
        </div>

        <div class="container">
            <h2>Liste des colliers</h2>
            <table class="collier-table">
                <thead>
                    <tr>
                        <th>ID Donnée</th>
                        <th>ID Brebis</th>
                        <th>Niveau de batterie</th>
                        <th>Latitude</th>
                        <th>Longitude</th>
                        <th>Date/Heure</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($colliers) > 0): ?>
                        <?php foreach ($colliers as $collier): ?>
                            <tr>
                                <td><?= htmlspecialchars($collier["ID_donnee"]) ?></td>
                                <td><?= htmlspecialchars($collier["ID_brebis"]) ?></td>
                                <td><?= htmlspecialchars($collier["niveau_batterie"]) ?>%</td>
                                <td><?= htmlspecialchars($collier["latitude"] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($collier["longitude"] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($collier["date_heure"]) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6">Aucune donnée de collier trouvée.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        var map = L.map('map').setView([48.8566, 2.3522], 13); // Coordonnées par défaut (Paris)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);

        var colliers = <?php echo json_encode($colliers); ?>;
        var validColliers = colliers.filter(collier => collier.latitude && collier.longitude && !isNaN(collier.latitude) && !isNaN(collier.longitude));
        
        validColliers.forEach(function(collier) {
            L.marker([parseFloat(collier.latitude), parseFloat(collier.longitude)])
        .addTo(map)
                .bindPopup(`Brebis ${collier.ID_brebis}<br>Batterie : ${collier.niveau_batterie}%<br>Date : ${collier.date_heure}`)
                .openPopup();
        });

        if (validColliers.length > 0) {
            map.setView([parseFloat(validColliers[0].latitude), parseFloat(validColliers[0].longitude)], 13);
        }
    </script>
</body>
</html>