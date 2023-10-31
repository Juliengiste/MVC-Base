<?php
/*******************************
Version : 1.0.0.0
Revised : jeudi 19 avril 2018, 16:55:14 (UTC+0200)
*******************************/

// Debug
$timestamp_debut = microtime(true);
ini_set("xdebug.var_display_max_children", -1);
ini_set("xdebug.var_display_max_data", -1);
ini_set("xdebug.var_display_max_depth", -1);

define('DR',  $_SERVER["DOCUMENT_ROOT"]);

// inclusion des paramètres par défaut
if(is_file(DR."Core/include/config.inc.php")) require(DR."Core/include/config.inc.php");
else var_dump(DR);

// Chargement automatique des classes
require_once(__DIR__."/../SplClassLoader.php");
$CoreLoader = new SplClassLoader('Core', DR);
$CoreLoader->register();

// Initialisation de la connexion à la base de données
$pdo = Core\Database\myPDO::getInstance();

$_GET = Core\Classes\Utils::secureFullGet();
$_POST = Core\Classes\Utils::secureFullPost();

// Encodage des IDs de sessions pour empècher les "session hijacking attacks"
session_start();
if (isset($_SESSION["secure_session"])) {
	if ($_SESSION["secure_session"]!=sha1("Je suis le maître des clefs. ".(isset($_SERVER["HTTP_USER_AGENT"])?$_SERVER["HTTP_USER_AGENT"]:$_SERVER["REMOTE_ADDR"])." Est-ce vous le cerbère de la grande porte ?")) {
		session_destroy();
		header("location: /index.php");
	}
}
else $_SESSION["secure_session"]=sha1("Je suis le maître des clefs. ".(isset($_SERVER["HTTP_USER_AGENT"])?$_SERVER["HTTP_USER_AGENT"]:$_SERVER["REMOTE_ADDR"])." Est-ce vous le cerbère de la grande porte ?");

// Choix de la langue
Core\Classes\Utils::defaultLocalization();