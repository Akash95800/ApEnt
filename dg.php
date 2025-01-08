<?php

global $serverBDD, $userBDD, $mdpBDD, $nomBDD;
session_start();
include '_conf.php';

// Connexion à la base de données
function connectBDD()
{
    global $serverBDD, $userBDD, $mdpBDD, $nomBDD;
    $connexion = mysqli_connect($serverBDD, $userBDD, $mdpBDD, $nomBDD);
    if (!$connexion) {
        die("Erreur de connexion à la base de données : " . mysqli_connect_error());
    }
    return $connexion;
}

// Vérification de la connexion utilisateur
if (isset($_POST['sendcon'])) {
    $var_login = isset($_POST['login']) ? $_POST['login'] : '';
    $var_mdp = isset($_POST['mdp']) ? $_POST['mdp'] : '';

    $connexion = connectBDD();
    $requete = "SELECT id, login, id_statut, mdp FROM user WHERE login=?";
    $stmt = mysqli_prepare($connexion, $requete);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 's', $var_login);
        mysqli_stmt_execute($stmt);
        $resultat = mysqli_stmt_get_result($stmt);

        if ($donnees = mysqli_fetch_assoc($resultat)) {
            if ($var_mdp === $donnees['mdp']) { // Pas de hachage utilisé dans les données actuelles
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
}

// Vérification si un utilisateur est connecté
if (isset($_SESSION['Sid'])) {
    echo "<br>Vous êtes connecté en tant que : " . htmlspecialchars($_SESSION['Slogin']);
    echo "<br>Type d'utilisateur : " . htmlspecialchars($_SESSION['Stype']);
    echo "<form method='POST'><button type='submit' name='logout'>Déconnexion</button></form>";

    if ($_SESSION['Stype'] == 2) { // Section élève
        if (isset($_POST['validerDate'])) {
            $date_selectionnee = $_POST['date'];
            $connexion = connectBDD();

            // Vérifier si un CR existe pour la date sélectionnée
            $requete = "SELECT * FROM CR WHERE id_user = ? AND date_cr = ?";
            $stmt = mysqli_prepare($connexion, $requete);
            mysqli_stmt_bind_param($stmt, 'is', $_SESSION['Sid'], $date_selectionnee);
            mysqli_stmt_execute($stmt);
            $resultat = mysqli_stmt_get_result($stmt);

            if ($cr = mysqli_fetch_assoc($resultat)) {
                // Si un CR existe, afficher pour modification
                echo "<form method='POST' action=''>";
                echo "<textarea name='contenu'>" . htmlspecialchars($cr['contenu']) . "</textarea>";
                echo "<input type='hidden' name='id_cr' value='" . $cr['id'] . "'>";
                echo "<button type='submit' name='modifierCR'>Modifier</button>";
                echo "</form>";
            } else {
                // Si aucun CR n'existe, proposer l'insertion
                echo "<form method='POST' action=''>";
                echo "<textarea name='contenu' placeholder='Insérez votre compte rendu ici'></textarea>";
                echo "<button type='submit' name='ajouterCR'>Ajouter</button>";
                echo "</form>";
            }

            mysqli_stmt_close($stmt);
            mysqli_close($connexion);
        }

        // Gestion de l'ajout d'un nouveau CR
        if (isset($_POST['ajouterCR'])) {
            $contenu = $_POST['contenu'];
            $date_selectionnee = $_POST['date'];

            $connexion = connectBDD();
            $requete = "INSERT INTO compte_rendu (id_user, date_cr, contenu) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($connexion, $requete);
            mysqli_stmt_bind_param($stmt, 'iss', $_SESSION['Sid'], $date_selectionnee, $contenu);

            if (mysqli_stmt_execute($stmt)) {
                echo "Compte rendu ajouté avec succès.";
            } else {
                echo "Erreur lors de l'ajout du compte rendu.";
            }

            mysqli_stmt_close($stmt);
            mysqli_close($connexion);
        }

        // Gestion de la modification d'un CR existant
        if (isset($_POST['modifierCR'])) {
            $id_cr = $_POST['id_cr'];
            $contenu = $_POST['contenu'];

            $connexion = connectBDD();
            $requete = "UPDATE compte_rendu SET contenu = ? WHERE id = ?";
            $stmt = mysqli_prepare($connexion, $requete);
            mysqli_stmt_bind_param($stmt, 'si', $contenu, $id_cr);

            if (mysqli_stmt_execute($stmt)) {
                echo "Compte rendu modifié avec succès.";
            } else {
                echo "Erreur lors de la modification du compte rendu.";
            }

            mysqli_stmt_close($stmt);
            mysqli_close($connexion);
        }
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

