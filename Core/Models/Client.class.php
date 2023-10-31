<?
/*******************************
Version : 1.0.2.2
Revised : jeudi 26 octobre 2017, 15:23:58 (UTC+0200)
*******************************/

namespace Core\Admin;

class Client {
	protected $id;
	protected $mail;
	protected $pwd;
	protected $nom;
	protected $prenom;
	protected $societe;
	protected $zip;
	protected $ville;
	protected $sm;
	protected $profil;
	protected $statut;
	protected $site;
	protected $badge;
	protected $commentaire;
	protected $networking;
	protected $active;

	public function __construct ($donnees = array()) {
		if(!empty($donnees)) $this->hydrate($donnees); // Initialisation, si y'a un paramètre, on hydrate l'objet
	}
	
	public function hydrate(array $donnees){
		foreach ($donnees as $key => $value){
			$method = 'set'.ucfirst($key); // On récupère le nom du setter correspondant à l'attribut
			if (method_exists($this, $method)){ // Si le setter correspondant existe, on l'appelle
				$this->$method($value);
			}
		}
	}
	
	public function setId($param){
		$this->id = (int) $param;
	}
	public function setMail($param){
		$this->mail = (string) $param;
	}
	public function setPwd($param){
		$this->pwd = (string) $param;
	}
	public function setNom($param){
		$this->nom = (string) $param;
	}
	public function setPrenom($param){
		$this->prenom = (string) $param;
	}
	public function setSociete($param){
		$this->societe = (string) $param;
	}
	public function setZip($param){
		$this->zip = (string) $param;
	}
	public function setVille($param){
		$this->ville = (string) $param;
	}
	public function setSm($param){
		$this->sm = (string) $param;
	}
	public function setProfil($param){
		$this->profil = (string) $param;
	}
	public function setStatut($param){
		$this->statut = (string) $param;
	}
	public function setBadge($param){
		$this->badge = (string) $param;
	}
	public function setSite($param){
		$this->site = (string) $param;
	}
	public function setCommentaire($param){
		$this->commentaire = (string) $param;
	}
	public function setNetworking($param){
		$this->networking = (int) $param;
	}
	public function setActive($param){
		$this->active = (string) $param;
	}
		
	public function id() { return $this->id; }
	public function mail() { return $this->mail; }
	public function pwd() { return $this->pwd; }
	public function nom() { return $this->nom; }
	public function prenom() { return $this->prenom; }
	public function societe() { return $this->societe; }
	public function zip() { return $this->zip; }
	public function ville() { return $this->ville; }
	public function sm() { return $this->sm; }
	public function profil() { return $this->profil; }
	public function statut() { return $this->statut; }
	public function badge() { return $this->badge; }
	public function site() { return $this->site; }
	public function commentaire() { return $this->commentaire; }
	public function networking() { return $this->networking; }
	public function active() { return $this->active; }

	public function digitEventMP(){
		$t=str_replace(array(".","-"," ","/",",","="), "", $this->sm);
		if(strpos($t, "33")===0) return "+".$t;
		elseif(strpos($t, "0033")===0) return "+".ltrim($t,'0');
		elseif(strpos($t, "+33")===false) return "+33".ltrim($t,"0");
		else return $t;
	}
}
?>