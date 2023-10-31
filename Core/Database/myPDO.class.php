<?php
/*******************************
Version : 1.0.0.0
Revised : jeudi 19 avril 2018, 14:40:02 (UTC+0200)
*******************************/

namespace Core\Database;

use PDO; //pour éviter qu'il ne rentre dans l'autoload

class myPDO extends PDO {
	private static $instance;

	public static function getInstance() {
		if (!self::$instance) {
			try {
				self::$instance = new myPDO('mysql:host=localhost;dbname='.DBNAME, DBUSER, DBPASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8", PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
			} catch (\PDOException $e) {
				\Core\Classes\Utils::handleError('Impossible de se connecter à la base : ' . $e->getMessage(), 0);
			}
		}

		return self::$instance;
	}
}