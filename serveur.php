<?php
include('./sql.php');
include('./XMPPHP/XMPP.php');
include ('./lastRSS.php');

// Récupère tous les flux RSS
$rss = SelectQueryMultiple('SELECT * FROM rss');

$lastRss = new lastRSS;
$lastRss->cache_dir = './rss';
$lastRss->cache_time = 600;
$lastRss->stripHTML = true;
$lastRss->CDATA = 'content';
$lastRss->cp = 'UTF-8';

// Connexion à Jabber
$conn = new XMPPHP_XMPP($config[3], $config[4], $config[5], $config[6], 'xmpphp', 'jabber.org', $printlog=False);

echo date('d/m/y H:i:s') . "\n";

$conn->connect();
$conn->processUntil('session_start');

// Pour chacun des flux
foreach($rss as $r)
{
	$messages = array();
	
	if($flux = $lastRss->get($r['adresse']))
	{
		echo 'Chargement de ' . $r['adresse'] . "\n";
		
		// Les articles vont du plus récent au plus ancien, on inverse
		$items = array_reverse($flux['items']);
		
		// On récupère le GUID de l'entrée la plus récente qu'on a envoyé avant
		$lastGuid = $r['last_entry'];
		
		// Pour chaque article du flux
		foreach($items as $item)
		{
			if(isset($item['guid']))
				$guid = $item['guid'];
			else
				$guid = $item['link'];

			$messages[] = $item['title'] . "\n" . $item['link'];
			
			// Si on tombe sur l'entrée qu'on a déjà envoyée, on recommence à partir de là
			if($guid == $lastGuid)
				$messages = array();
		}
		
		// Récupère la liste des abonnés à ce flux RSS
		$abonnes = SelectQueryMultiple('SELECT * FROM comptes JOIN abos ON abos.id_compte = comptes.id WHERE abos.id_rss = "'.$r['id'].'"');
		
		
		// S'il y a au moins une nouveauté
		if(count($messages) > 0)
		{
			// Pour chacun des abonnés à ce flux
			foreach($abonnes as $a)
			{
				echo 'Envoie des flux à '.$a['adresse']. "\n";
				$conn->message($a['adresse'], $flux['title']);
				foreach($messages as $m)
				{
					$conn->message($a['adresse'], $m);
				}
			}
			
			// On met à jour le GUID de la dernière entrée récupérée
			UpdateQuery('UPDATE rss SET last_entry = "'.$guid.'" WHERE id = "'.$r['id'].'"');
		}
	}
}

// Deconnection de Jabber
$conn->disconnect();
?>
