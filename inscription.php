<?php
// Page d'inscription d'un nouvel utilisateur

if(isset($_POST['adresse']) && isset($_POST['motdepasse']) && isset($_POST['rss']))
{
	include('./sql.php');
	include('./XMPPHP/XMPP.php');
	
	// Applications des protections usuelles
	$donnees = protect($_POST);
	
	// Vérification de la non-existence de l'utilisateur
	$existe = SelectQuery('SELECT id FROM comptes WHERE adresse = "'.$donnees['adresse'].'"');
	
	if(empty($existe))
	{
		// Ajout dans la base
		$requete = 'INSERT INTO comptes(id, adresse, motdepasse) VALUES("", "'.$donnees['adresse'].'", "'.md5($donnees['motdepasse']).'")';
		
		$idPersonne = InsertQuery($requete);
		$rss = array_unique($donnees['rss']);
		
		// Création d'un objet de flux RSS
		include ('./lastRSS.php');
		$lastRss = new lastRSS;
		
		// Pour chacun des flux RSS demandés par l'utilisateur
		foreach($rss as $r)
		{
			$r = trim($r);
			
			// On vérifie quele flux n'existe pas déjà dans la base
			$idRss = SelectQuery('SELECT id FROM rss WHERE adresse = "'.$r.'"', false);
			
			// Le flux n'existe pas, on le rajoute
			if(empty($idRss))
			{
				$lastRss->cache_dir = './rss';
				$lastRss->cache_time = 3600;
				$lastRss->stripHTML = true;
				$lastRss->CDATA = 'content';
				$lastRss->cp = 'UTF-8';
				$flux = $lastRss->get($r);
				
				$items = $flux['items'];
				
				// Récupération du dernier objet pour envoyer les updates à partir de celui-ci
				$requete = 'INSERT INTO rss(id, adresse, last_entry) VALUES("", "'.$r.'", "'.$items[0]['guid'].'")';
				$idRss = InsertQuery($requete, false);
			}
			else
			{
				// Le flux existe, on récupère son ID
				$idRss = $idRss[0];
			}
			
			// Ajout de l'abonnement au flux
			$requete = 'INSERT INTO abos(id_compte, id_rss) VALUES("'.$idPersonne.'", "'.$idRss.'")';
			InsertQuery($requete, false);
		}
		
		// Envoi d'un message de confirmation à l'utilisateur
		$conn = new XMPPHP_XMPP($config[3], $config[4], $config[5], $config[6], 'xmpphp', 'jabber.org', $printlog=False);
		$conn->connect();
		$conn->processUntil('session_start');
		$conn->message($donnees['adresse'], 'Merci de votre inscription au service Jabb2Rss ! Utilisez désormais le formulaire de droite pour vous connecter, modifier vos flux RSS ou vous désinscrire.');
		$conn->disconnect();
		
		echo 'Votre inscription a bien été prise en compte. Vérifiez que vous avez bien reçu un message de confirmation.';
	}
	else
	{
		echo 'Votre adresse existe déjà. Utilisez le formulaire de droite pour vous identifier.';
	}
}
?>
