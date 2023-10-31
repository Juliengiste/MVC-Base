<?php
/*******************************
 * Version : 1.0.0.0
 * Revised : jeudi 19 avril 2018, 16:57:06 (UTC+0200)
 *******************************/

namespace Core\View;

use Core\Classes\Utils;
use Core\Models\User;

class View {
	protected $tplDir = 'template';
	protected $tplExt = '.tpl.php';
	protected $tplInc = '.inc.php';
	protected $_GET = '';
	protected $_POST = '';
	protected $template;
	
	protected $public_pages = array(
		// 'accueil' => array('nom' => "Générateur de mot de passe"),
	);
	
	public function __construct($tpl = null) {
		$this->setTemplate($tpl);
	}
	
	public function setTemplate($tpl) {
		$this->template = $tpl;
	}
	
	public function render() {
		global $lang;
		/*
		 * Les fichiers xml sont publics
		 */
		// if (isset($_GET['page']) && $_GET['page'] == "xml") {
			// $this->renderTemplate('template/pages/xml_fiche.tpl.php');
			// exit();
		// }
		/*
		 * On vérifie l'accès aux pdf normalement protégés, si on a la bonne clé on affiche (lien avec site bourgogne greta)
		 */
		if (isset($_GET['page']) && $_GET['page'] == "pdf") {
			$key = hash('sha256', "yopyop-nop-rtn-ahahahaha" . date("Ymd") . SALT);
			if (isset($_POST['cle']) && $_POST['cle'] == $key) {
				$this->renderTemplate('template/pages/pdf_fiche.tpl.php');
			}
			elseif (isset($_POST['cle'])) return "";
		}
		// récupération des paramètres GET (page et lang éventuellement)
		$check = $this->checkTemplate();
		if (isset($_GET['page']) && ($_GET['page'] == "pdf" || $_GET['page'] == "csv-liste" || ($_GET['page'] == "annuaire" && isset($_GET['sspage']) && $_GET['sspage'] == "csv") || $_GET['page'] == "pdf-catalogue")) {
			$this->renderTemplate($check);
		}
		else {
			//on fait une temporisation pour éviter d'avoir le header lancé en cas de redirection 404/403
			ob_start();
			$this->renderTemplate('meta', 1);
			$this->renderTemplate('header', 1);
			$this->renderTemplate($check);
			$this->renderTemplate('footer', 1);
			ob_end_flush();
		}
	}
	
	public function checkTemplate() {
		global $pdo;
		
		// vérification de l’existance de la page demandée
		if (isset($_GET["page"]) && isset($_GET["sspage"]) && isset($_GET["w"]))
			$tplFile = DR . $this->tplDir . "/pages/" . $_GET["page"] . "_" . $_GET["sspage"] . $this->tplExt;        // avec sspage
		elseif (isset($_GET["page"]) && isset($_GET["sspage"]) && isset($_GET["detail"]))
			$tplFile = DR . $this->tplDir . "/pages/" . $_GET["detail"] . $this->tplExt;        // avec detail
		elseif (isset($_GET["page"]))
			$tplFile = DR . $this->tplDir . "/pages/" . $_GET["page"] . $this->tplExt;            // juste page
		else {
			$tplFile = DR . $this->tplDir . "/pages/accueil" . $this->tplExt;                                    // si aucune page de précisée alors retour accueil
			$_GET['page']="accueil";
		}

		// la page existe, on vérifie si on peut l'afficher
		if (isset($tplFile) && is_file($tplFile) && is_readable($tplFile)) {
			// Check session et autorisation
			$personne = new User();
			$pmanager = new \Core\Models\UserManager($pdo);
			
			if ($personne->isConnected($pmanager)) {
				$personne = $_SESSION[SHORTNAME.'_user']; //on reattribue a notre personne les nouvelles valeurs
				// $page = isset($_GET['page']) ? $_GET['page'] : 'accueil';
				// if ($personne->hasRight("acces", $page)) {
					// return substr($tplFile, strpos($tplFile, DR . $this->tplDir . "/pages/"));
					return $tplFile;
				// }
				// else { // connecté mais sans droit
					// $this->redirect403();
				// }
			}
			else { // non connecté
				if(isset($_GET['page'])&&array_key_exists($_GET['page'], $this->public_pages)) return $tplFile;
				else $this->redirectLogin($_SERVER['REQUEST_URI']);
			}
		}
		else { // la page n'existe pas
			$this->redirect404(Utils::handleError('Probleme au chargement du template : ' . $tplFile, 'E_USER_NOTICE' ));
		}
	}
	
	public function renderTemplate($template = null, $part = null) {
		global $pdo, $lang;
		
		if (isset($template)) {
			if ($part)
				$tplFile = DR . $this->tplDir . "/include/" . $template . $this->tplInc;                        // template (for included page)
			else
				$tplFile = $template;
			
			require $tplFile;
		}
		else exit();
	}
	
	/**
	 * Redirection Login
	 * Header correct et chargement du template
	 */
	public function redirectLogin($origine) {
		$this->renderTemplate('meta', 1);
		$this->renderTemplate('header', 1);
		
		require DR . $this->tplDir . "/include/login" . $this->tplInc;
		
		$this->renderTemplate('footer', 1);
		
		// page non trouvée
		exit();
	}
	
	/**
	 * Redirection 403
	 * Header correct et chargement du template
	 */
	public function redirect403() {
		ob_end_clean();
		header('HTTP/1.0 403 Forbidden');
		
		$this->renderTemplate('meta', 1);
		$this->renderTemplate('header', 1);
		
		require DR . $this->tplDir . "/include/403" . $this->tplInc;
		
		$this->renderTemplate('footer', 1);
		// page non trouvée
		exit();
	}
	
	/**
	 * Redirection 404
	 * Header correct et chargement du template
	 */
	public function redirect404($error) {
		ob_end_clean();
		header('HTTP/1.0 404 Not Found');
		
		$this->renderTemplate('meta', 1);
		$this->renderTemplate('header', 1);
		
		require DR . $this->tplDir . "/include/404" . $this->tplInc;
		if (isset($error)) echo $error;
		
		$this->renderTemplate('footer', 1);
		// page non trouvée
		exit();
	}
	
	/*
	public function isTemplate($tpl) {
		return $this->template===$tpl;
	}
	*/
}