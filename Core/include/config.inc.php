<?php
/*******************************
Version : 1.0.0.0
Revised : jeudi 19 avril 2018, 16:54:22 (UTC+0200)
*******************************/

/* Constantes */

// Debug, versioning, ©
define('DEBUG', 0); 			// 0 : pas d'affichage des erreurs, 1 : affichage des erreurs
//error_reporting(E_ALL);

// Constantes
define('URL', $_SERVER['HTTP_HOST']);	// URL du site, ex. : studio-calico.fr
$protocole = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https" : "http";
define('PROTOCOLE', $protocole);
define('CORE_DEFAULT_LANGUAGE','fr'); // Language par défaut pour le Core
define('APP_DEFAULT_LANGUAGE','fr');	// langue par défaut si celle du navigateur n'est pas supportée

define('SOFT', '');		// nom de l'application
define('VERSION', '1.0.0.0');			// version de l'application
define('BUILDDATE', '19/04/2018');		// Date de dernière modification
define('COPYRIGHT', '2018 LNDM');	// Information de copyright
define('EURO',utf8_encode(chr(128)));
// MySQL
define('DBNAME', 'modele');		// nom de la base mysql
define('DBUSER', 'modele');		// utilisateur mysql
define('DBPASS', 'Mi8OBuAkW0TqFcKr');	// mot de passe mysql

// Host
define('NAME','Site');	// Nom qui sera donnée en entête de sujet aux mails, entre [ ]
define('SHORTNAME', 'st');						// Acronyme court pour session

// Mail
define('WEBSITE','Site Web');	// Signature pour "L’équipe" en bas de mail
define('EMAILDOMAINE','site-web.fr'); 		// domaine des mails
define('EMAIL','contact@site-web.fr');	// Mail principal
define('NOREPLY','nepasrepondre@'.EMAILDOMAINE);	// Mail no-reply

// Coordonnées
define('COORDS_NOM','Site Web Dev');	// Nom du propriétaire
define('COORDS_EMAIL','contact@'.EMAILDOMAINE);	// Email du propriétaire

//type d'entrée sortie
define('SALT', '-XT-HJ$YUDYw)QkW|semH/]lWE|IQ7|f,Pg/;x Bx!gf2O=:.I#Cnd`WA1fvN0TR');
define('MAXCOUNTLOGIN',3);