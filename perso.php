<?php
global $serverBDD, $userBDD, $mdpBDD, $nomBDD;
session_start();
include '_conf.php';

if (isset($_SESSION['Sid'])) {
    $connexion = mysqli_connect($serverBDD, $userBDD, $mdpBDD, $nomBDD);
    if ($connexion) {
        $requete = "SELECT * FROM user WHERE id=?";
        $stmt = mysqli_prepare($connexion, $requete);
        mysqli_stmt_bind_param($stmt, 'i', $_SESSION['Sid']);
        mysqli_stmt_execute($stmt);
        $resultat = mysqli_stmt_get_result($stmt);

        if ($donnees = mysqli_fetch_assoc($resultat)) {
            echo "<h1>Informations personnelles</h1>";
            echo "Nom : " . htmlspecialchars($donnees['nom']) . "<br>";
            echo "Prénom : " . htmlspecialchars($donnees['prenom']) . "<br>";
            echo "Email : " . htmlspecialchars($donnees['mail']) . "<br>";
            echo "Téléphone : " . htmlspecialchars($donnees['tel']) . "<br>";
            echo "date de naissance: " . htmlspecialchars($donnees['dateN']) . "<br> ";

        }

        mysqli_stmt_close($stmt);
        mysqli_close($connexion);
    }
} else {
    echo "Vous n'êtes pas connecté. <a href='index.php'>Connectez-vous</a>";
}
?>
