<?php
// Vérifie qu'un URL pointe vers un flux RSS valide

include ('./lastRSS.php');
$lastRss = new lastRSS;
$lastRss->cache_dir = './rss';
$lastRss->cache_time = 3600;
$lastRss->stripHTML = true;
$lastRss->CDATA = 'content';
$lastRss->cp = 'UTF-8';
$flux = $lastRss->get(trim($_POST['url']));

// Si $flux est null, lastRSS n'a pas réussi à parser, le flux est invalide
if($flux)
	echo '1';
else
	echo '0';
?>
