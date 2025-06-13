<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Position des brebis</title>
    <link rel="stylesheet" href="../css/monstyle.css">
<link rel="stylesheet" href="../css/position.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>
<body>
    <h1>Position des Brebis</h1>
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

<section class="recherche-troupeaux">

  <div class="recherche">
    <h2>Catégories</h2>

    <label for="site-search">Recherche :</label>
    <input type="search" id="site-search" name="q" />
    <button>Rechercher</button>
  </div>
<br>
  <h2>Suivre les troupeaux</h2>
  <ul class="liste-troupeaux">
    <li><a href="troupeau1.php">Troupeau 1</a></li>
    <li><a href="#">Troupeau 2</a></li>
    <li><a href="#">Troupeau 3</a></li>
  </ul>
</section>

</body>
</html>