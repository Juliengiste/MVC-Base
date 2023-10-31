<?php
/*******************************
 * Version : 1.0.0.0
 * Revised : jeudi 26 octobre 2017, 15:23:58 (UTC+0200)
 *******************************/

namespace Core\Models;

use Core\Classes\Utils;

class User extends Metier {
	protected $allowed_properties = [
		'id',
		'login',
		'pwd',
	];
	protected $pk_name = 'id'; //Primary Key
	//page accessible à tous les statuts connectés
	// protected $pagesForAll = array("accueil", "rechercher", "resultat", "consulter", "pdf", "liste", "csv-liste", "boite-a-outils", "pdf-catalogue", "annuaire", "annuaire-liste");
	
	/**
	 * Check login
	 * @param none
	 * @return Boolean
	 */
	public function isConnected($pmanager = false) {
		// La session est déjà ouverte
		if (isset($_SESSION[SHORTNAME.'_user'])) {
			// On demande la déconnexion
			if (isset($_GET['logout'])) {
				$_SESSION = array();
				if (ini_get("session.use_cookies")) {
					$params = session_get_cookie_params();
					setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
				}
				session_destroy();
				//on supprime le paramètres GET/retour accueil car si on se reconnecte on est déco direct
				header('Location:/');
				exit();
			} // on reste connecté
			else return true;
		} // POST des identifiants/pwd
		elseif (isset($_POST['login_login']) && (isset($_POST['login_pwd']))) {
			return $pmanager->login($_POST['login_login'], $_POST['login_pwd']);
		} // Affichage d'une page sans être connecté ni avoir envoyé d'identifiant (pas d'erreur mais demande de login)
		else {
			unset($_SESSION[SHORTNAME.'_user_error']);
			return false;
		}
	}
	
	/**
	 * Check rights
	 * @param none
	 * @return Boolean
	 */
/*	public function hasRight($type = "acces", $valeur = "") {
		if (!$this->isConnected()) return false;
		
		switch ($type) :
			//consultation d'une page (expl : /rechercher/)
			case 'acces' :
				//si la page est accessible à tous
				if (in_array($valeur, $this->pagesForAll)) return true;
				//si la page est accessible à certains utilisateur on vérifie en bdd PSAcces
				elseif (strpos($this->PSAcces, $valeur) !== false) return true;
				break;
			
			//enregistrement d'une fiche (expl : /editer/)
			case 'save':
				//$valeur =statut fiche
				//var_dump($valeur);
				if ($this->PSStatut < 3) return false;
				elseif ($this->PSStatut == 3 && ($valeur == "B" || $valeur == "A")) return true;
				//elseif ($this->PSStatut == 7 && $valeur == "A") return false;
				elseif ($this->PSStatut >= 7) return true;
				break;
			
			//edition d'une fiche (expl : /editer/60000/)
			case 'editer':
				if ($this->PSStatut == 3 && $valeur->FPCreate_ID == $this->P_ID) return true;
				elseif ($this->PSStatut == 7 && ($valeur->FPCreate_ID == $this->P_ID || $valeur->FPReferent_ID == $this->P_ID)) return true;
				elseif ($this->PSStatut == 9) return true;
				return false;
				break;
			
			//envoie d'une alerte recrutement
			case 'sendAlerte':
				if ($this->PAlerte != 1) return false;
				elseif($this->PStatut_ID==9) return true;
				elseif($valeur!="" && $valeur==$this->PRattachement_ID) return true;
				return false;
				break;
		
		endswitch;
		
		return false;
	}*/
	
	public function __toString() {
		return 'Objet ' . __CLASS__;
	}
}