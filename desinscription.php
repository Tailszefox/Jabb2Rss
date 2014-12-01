<?php
// Page de désinscription d'un abonné

session_start();

include('./sql.php');

// Suppression du compte et des abonnements

UpdateQuery('DELETE FROM comptes WHERE id = "'.$_SESSION['id'].'"');
UpdateQuery('DELETE FROM abos WHERE id_compte = "'.$_SESSION['id'].'"');

// Recherche et suppressions des flux RSS obsolètes (qui n'ont plus d'abonnés)

$rssObsoletes = SelectQueryMultiple('SELECT * FROM rss LEFT JOIN abos ON abos.id_rss = rss.id WHERE id_compte IS NULL');

foreach($rssObsoletes as $r)
{
	UpdateQuery('DELETE FROM rss WHERE id = "'.$r['id'].'"', true);
}

?>
