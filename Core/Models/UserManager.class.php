<?php
/*******************************
Version : 1.0.0.0
Revised : vendredi 4 mai 2018, 10:23:58 (UTC+0200)
 *******************************/
namespace Core\Models;

use PDO;

class UserManager extends Manager {
	protected $table = "user";
	protected $pk = "id";

	/**
	 * Login de l’utilisateur
	 * @param login
	 * @param pwd
	 * @return Boolean
	 */
	public function login($login, $pwd) {
		$q=$this->db->prepare('SELECT * FROM `'.$this->table.'` WHERE `login`=:login AND `pwd`=:pwd');
		$q->bindValue(':login', $login, PDO::PARAM_STR);
		$q->bindValue(':pwd', hash('sha512', $pwd), PDO::PARAM_STR);

		$q->execute();

		$donnees=$q->fetch(PDO::FETCH_ASSOC);

		if($donnees!==false) {
			unset($_SESSION[SHORTNAME.'_user_error']);
			$_SESSION[SHORTNAME.'_user']=new \Core\Models\User($donnees);
			return true;
		}
		// tentative d'identification incorrecte
		else {
			// message d’erreur
			$_SESSION[SHORTNAME.'_user_error']="Identification incorrecte !";
			return false;
		}
	}

}