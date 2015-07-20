<?php
require("config.php");
mysql_connect($mysql_hostname, $mysql_username, $mysql_password);

if(!mysql_select_db('jabb2rss'))
{
	mysql_query('CREATE DATABASE `jabb2rss` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;');
	mysql_select_db('jabb2rss');
	
	mysql_query('CREATE TABLE IF NOT EXISTS `abos` (
  `id_compte` int(11) NOT NULL,
  `id_rss` int(11) NOT NULL,
  PRIMARY KEY (`id_compte`,`id_rss`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;');
	mysql_query('CREATE TABLE IF NOT EXISTS `comptes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `adresse` text NOT NULL,
  `motdepasse` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;');
	mysql_query('CREATE TABLE IF NOT EXISTS `rss` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `adresse` text NOT NULL,
  `last_entry` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;');
}

// Applique les protections usuelles sur les éléments d'un tableau
function protect($array)
{
	$newArray = array();

	foreach($array as $key => $a)
	{
		if(is_array($a))
		{
			$newArray[$key] = protect($a);
		}
		else
		{
			$newA = $a;
			if(get_magic_quotes_gpc())
			{
				$newA = stripslashes($newA);
			}
			$newA = htmlspecialchars($newA);
			$newA = mysql_real_escape_string($newA);
			$newA = trim($newA);
			$newArray[$key] = $newA;
		}
	}

	return $newArray;
}

// Requête de sélection unique
// Renvoie un tableau de colonnes
function SelectQuery($query, $display = FALSE)
{
	if($display == TRUE)
		echo $query;
	
	$result = mysql_query($query) or die('MySQL Error : '. mysql_error() .' '. $query);
	
	if(mysql_num_rows($result) == 0)
		$arrayResult = array();
	else
		$arrayResult = mysql_fetch_array($result);
	
	return $arrayResult;
}

// Requête de sélection multiples
// Renvoie un tableau de lignes
function SelectQueryMultiple($query, $display = FALSE)
{
	if($display == TRUE)
		echo $query;
	
	$result = mysql_query($query) or die('MySQL Error : '. mysql_error() .' '. $query);
	
	if(mysql_num_rows($result) == 0)
		$arrayResult = array();
	else
	{
		$arrayResult = array();
		while($row = mysql_fetch_array($result))
		{
			$arrayResult[] = $row;
		}
	}
	
	return $arrayResult;
}

// Requête d'insertion
// Renvoie le nouvel ID
function InsertQuery($query, $display = FALSE)
{
	if($display == TRUE)
		echo $query;
	
	mysql_query($query) or die('MySQL Error : '. mysql_error() .' '. $query);
	return mysql_insert_id();
}

// Requête de mise à jour et de suppression
// Renvoie de le nombre de lignes affectées
function UpdateQuery($query, $display = FALSE)
{
	if($display == TRUE)
		echo $query;
	
	mysql_query($query) or die('MySQL Error : '. mysql_error() .' '. $query);
	return mysql_affected_rows();
}
?>
