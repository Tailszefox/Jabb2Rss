<?php
// Crée un formulaire contennat la liste des flux de l'utilisateur

session_start();

if(isset($_POST['adresse']) && isset($_POST['motdepasse']))
{
	include('./sql.php');
	$donnees = protect($_POST);
	
	// Vérification de l'authentification
	$existe = SelectQuery('SELECT *  FROM comptes WHERE adresse = "'.$_POST['adresse'].'" and motdepasse = "'.md5($_POST['motdepasse']).'"', false);
	if(sizeof($existe) == 0)
	{
		echo '<p>Votre adresse ou votre mot de passe est invalide. Veuillez les vérifier.</p>
		<p>Si vous ne possèdez pas de compte, créez-le en utilisant le formulaire de gauche.</p>';
		return;
	}
	
	// Mise en session de l'ID de l'utilisateur
	$_SESSION['id'] = $existe['id'];
	
	// Récupération des abonnements
	$rss = SelectQueryMultiple('SELECT * FROM abos JOIN rss ON abos.id_rss = rss.id WHERE abos.id_compte = "'.$existe['id'].'"');
	
	echo '<p>Modifier vos flux RSS</p>
	<p>Pour vous désinscrire d\'un flux, effacez son adresse ou remplacez-la par une autre.</p>
	<div id="rssBis">';
	
	foreach($rss as $r)
	{
		echo '<input type="text" size="40" name="rss[]" class="adresseFluxBis" value="'.$r['adresse'].'"/> <span class="ajoutFluxBis">+</span>';
	}
	
	if(sizeof($rss) == 0)
	{
		echo '<input type="text" size="40" name="rss[]" class="adresseFluxBis" value="'.$r['adresse'].'"/> <span class="ajoutFluxBis">+</span>';
	}
	
	echo '</div><p><input type="submit" /></p><p><input type="button" value="Se désinscrire" name="desinscription" /></p>';
}
?>
