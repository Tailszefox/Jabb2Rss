<?php
// Page de modifications des flux RSS d'un utilisateur

session_start();

if(isset($_POST['rss']))
{
	include('./lastRSS.php');
	include('./sql.php');
	$lastRss = new lastRSS;
	$rss = array_unique($_POST['rss']);
	
	// On récupère les flux RSS auquel l'utilisateur est abonné pour le moment
	$rssActuels = SelectQueryMultiple('SELECT * FROM abos JOIN rss ON abos.id_rss = rss.id WHERE abos.id_compte = "'.$_SESSION['id'].'"');
	
	$rssAdressesActuelles = array();
	
	// On vérifie d'abord quels abonnements sont à supprimer
	foreach($rssActuels as $r)
	{
		// Le flux actuel n'est pas dans la liste envoyée par l'utilisateur, l'abonnement est supprimé
		if(!in_array($r['adresse'], $rss))
		{
			UpdateQuery('DELETE FROM abos WHERE id_compte = "'.$_SESSION['id'].'" and id_rss = "'.$r['id'].'"');
			echo '<p>Abonnement à ' .$r['adresse']. ' résilié.</p>';
		}
		else
		{
			$rssAdressesActuelles[] = $r['adresse'];
		}
	}
	
	// Pour chacun des flux demandés par l'utilisateur
	foreach($rss as $r)
	{
		if(!empty($r))
		{
			// L'utilisateur n'est pas abonné au flux, on rajoute l'abonnement
			if(!in_array($r, $rssAdressesActuelles))
			{
				$idRss = SelectQuery('SELECT id FROM rss WHERE adresse = "'.$r.'"', false);
				
				// Le flux n'existe pas du tout, on l'ajoute à la base
				if(empty($idRss))
				{
					$lastRss->cache_dir = './rss';
					$lastRss->cache_time = 3600;
					$lastRss->stripHTML = true;
					$lastRss->CDATA = 'content';
					$lastRss->cp = 'UTF-8';
					$flux = $lastRss->get($r);
					
					$items = $flux['items'];
					
					$requete = 'INSERT INTO rss(id, adresse, last_entry) VALUES("", "'.$r.'", "'.$items[0]['guid'].'")';
					$idRss = InsertQuery($requete, false);
				}
				else
				{
					$idRss = $idRss[0];
				}
				
				$requete = 'INSERT INTO abos(id_compte, id_rss) VALUES("'.$_SESSION['id'].'", "'.$idRss.'")';
				InsertQuery($requete, false);
				echo '<p>Abonnement à ' .$r. ' ajouté.</p>';
			}
		}
	}
	
	// Suppression des flux RSS qui n'ont plus aucun abonné
	$rssObsoletes = SelectQueryMultiple('SELECT * FROM rss LEFT JOIN abos ON abos.id_rss = rss.id WHERE id_compte IS NULL');
	
	foreach($rssObsoletes as $r)
	{
		UpdateQuery('DELETE FROM rss WHERE id = "'.$r['id'].'"', false);
	}
	
	echo '<p>Modifications enregistrées.</p>';

}
?>
