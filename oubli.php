<form method="post" action="oubli.php">
    Insérer votre mail : <input type="email" name="mail" required>
    <input type="submit" value="Confirmer">
</form>

<?php
if (isset($_POST['mail'])) {
    $lemail = $_POST['mail'];
    echo "Le formulaire a été envoyé avec comme email la valeur : ($lemail) <br>";

    // Inclure les informations de connexion à la BDD
    include '_conf.php';
    global $serverBDD, $nomBDD, $userBDD, $mdpBDD;

    // On établit la connexion
    $conn = new mysqli($serverBDD, $userBDD, $mdpBDD, $nomBDD);

    // Vérifier la connexion
    if ($conn->connect_error) {
        die('Erreur de connexion : ' . $conn->connect_error);
    }
    echo "Connexion réussie <br>";

    // Préparer la requête pour éviter les injections SQL
    $requete = $conn->prepare("SELECT login, mdp FROM user WHERE mail = ?");
    $requete->bind_param("s", $lemail);
    $requete->execute();
    $resultat = $requete->get_result();

    if ($resultat->num_rows > 0) {
        while ($donnees = $resultat->fetch_assoc()) {
            $login = $donnees['login'];
            $mdp = $donnees['mdp'];
            echo "Login : " . htmlspecialchars($login) . " | Mot de passe : " . htmlspecialchars($mdp);
        }
    } else {
        echo "Aucun utilisateur trouvé avec cet email.";
    }

    $passwordhash = md5($mdp);

    var_dump($mdp);
    var_dump($passwordhash);


    // Fermer la connexion
    $requete->close();
    $conn->close();
}
    ?>
    <p>Veuillez insérer votre email pour continuer.</p>












