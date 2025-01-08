<?php
global $serverBDD, $userBDD, $mdpBDD, $nomBDD;
session_start();
include '_conf.php';

if (isset($_POST['sendcon'])) {
    $var_login = isset($_POST['login']) ? $_POST['login'] : '';
    $var_mdp = isset($_POST['mdp']) ? $_POST['mdp'] : '';

    // Connexion à la base de données
    $connexion = mysqli_connect($serverBDD, $userBDD, $mdpBDD, $nomBDD);
    if ($connexion) {
        // Requête pour vérifier l'utilisateur
        $requete = "SELECT id, login, id_statut, mdp FROM user WHERE login=?";
        $stmt = mysqli_prepare($connexion, $requete);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 's', $var_login);
            mysqli_stmt_execute($stmt);
            $resultat = mysqli_stmt_get_result($stmt);

            // Vérification des identifiants
            if ($donnees = mysqli_fetch_assoc($resultat)) {
                if ($var_mdp === $donnees['mdp']) { // Pas de hachage utilisé dans les données actuelles
                    // Création des variables de session pour l'utilisateur connecté
                    $_SESSION['Sid'] = $donnees['id'];
                    $_SESSION['Slogin'] = $donnees['login'];
                    $_SESSION['Stype'] = $donnees['id_statut'];

                    echo "Connexion réussie. Bienvenue " . htmlspecialchars($donnees['login']);
                } else {
                    echo "Erreur : Mot de passe incorrect.";
                }
            } else {
                echo "Erreur : Utilisateur non trouvé.";
            }

            mysqli_stmt_close($stmt);
        } else {
            echo "Erreur dans la préparation de la requête : " . mysqli_error($connexion);
        }

        mysqli_close($connexion);
    } else {
        echo "Erreur de connexion à la base de données : " . mysqli_connect_error();
    }
}

// Vérification si un utilisateur est connecté
if (isset($_SESSION['Sid'])) {
    echo "<br>Vous êtes connecté en tant que : " . htmlspecialchars($_SESSION['prenom']);
    echo "<br>Type d'utilisateur : " . htmlspecialchars($_SESSION['Stype']);
    echo "<br><a href='perso.php'>Voir vos informations personnelles</a>";
    echo "<form method='POST'><button type='submit' name='logout'>Déconnexion</button></form>";

    // Affichage spécifique selon le type
    switch ($_SESSION['Stype']) {
        case '1':
            echo "<br>PARTIE Prof : Contenu réservé aux professeurs.";
            break;
        case '2':
            echo "<br>PARTIE élève: Contenu réservé aux élèves .";
            break;
        default:
            echo "<br>PARTIE INVITÉ : Contenu par défaut.";
            break;
    }

    if ($_SESSION['Stype'] == 2) { // Si l'utilisateur est un élève (id_statut = 2)
        echo "<li><a href='listeCR.php'>Liste des comptes rendus</a></li>";
        echo "<li><a href='ModCR.php'>Créer/Modifier un compte rendu</a></li>";
        echo "<li><a href='commentaire.php'>Commentaires</a></li>";
    } else {
        // Si l'utilisateur n'est pas un élève, ne pas afficher ces liens
        echo "<p>vous etes professeur.</p>";
    }

} else {
    echo "La connexion est perdue. <a href='index.php'>Reconnectez-vous</a>";
}

// Gestion de la déconnexion
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}
?>
