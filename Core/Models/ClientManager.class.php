<?
/*******************************
Version : 1.0.2.2
Revised : mardi 20 mars 2018, 09:43:29 (UTC+0100)
*******************************/

namespace Core\Admin;
use PDO;
use \Core\Admin;

class ClientManager {
	private $db;
	private $t_client = "client";
	private $t_renewpwd = "renewpwd";
	private $t_reservation = "reservation";
	private $t_rendezvous = "rendezvous";
	private $t_conference = "conference";
	private $max = 12;
	
	protected $errorMessage;
	
	const ERROR_ID = 'Le "ID" doit être un nombre.';
	const ERROR_LOG = 'Le couple identifiant / mot de passe est invalide.';
	const ERROR_EVER = 'Cet identifiant et/ou courriel existe déjà !';
	const ERROR_NOLOG = 'Les champs obligatoires ne doivent pas être vides !';
	const ERROR_NOLOG2 = 'L’identifiant n’existe pas !';
	const ERROR_MAIL = 'Le format de courriel est invalide !';
	const ERROR_PWD = 'Une erreur est survenue. Votre requête ne peut aboutir. Merci de la renouveler.';
	const ERROR_NOLOG3 = 'Votre compte est désactivé.';
	const ERROR_MEL = 'Ce courriel n’existe pas !';
	
	public function __construct($db) { $this->setDb($db); }
	
	// Récupérer une conference en fonction de son ID
	public function get($id) {
		if(!is_numeric($id)&&$id!="new") {
			if(DEBUG) echo ClientManager::ERROR_ID;
			exit;
		}
		$id=intval($id);
		$q=$this->db->prepare('SELECT * FROM `'.$this->t_client.'` WHERE `'.$this->t_client.'`.`id`=:id');
		$q->bindValue(':id', $id, PDO::PARAM_INT);
		$q->execute();
		$donnees = $q->fetch(PDO::FETCH_ASSOC);
		
		return new Client($donnees);
	}
	
	// Filtrer les rendezvous en fonction des listes de sélection "mot clé, type, année"
	public function filtre($filtre){
		$filtred="WHERE 1=1";
		if($filtre){
			if($filtre["nom"])$filtred.=' AND (`nom` LIKE "%'.$filtre["nom"].'%" OR `prenom` LIKE "%'.$filtre["nom"].'%")';
			if($filtre["zip"])$filtred.=' AND `zip` LIKE "%'.$filtre["zip"].'%"';
			if($filtre["vil"])$filtred.=' AND `ville` LIKE "%'.$filtre["vil"].'%"';
			if($filtre["sit"])$filtred.=' AND `site` = "'.$filtre["sit"].'"';
		}
		return $filtred;
	}

	// récupérer les rendezvous par page
	public function getListPaged($page=1, $filtre, $sort="nom", $tri="asc"){
		if($filtre) $filtre=$this->filtre($filtre);

		$q=$this->db->prepare('SELECT * FROM `'.$this->t_client.'` '.$filtre.' ORDER BY `'.$sort.'` '.$tri.' LIMIT '.(($page-1)*$this->max).', '.$this->max);
		$q->execute();
		
		$ref=array();
		while($donnees=$q->fetch(PDO::FETCH_ASSOC)){
			$ref[]=new Client($donnees);
		}
		return $ref;
	}

	// faire s'afficher la liste des pages
	public function nbpage($current,$filtre,$where=""){
		if($filtre) $filtre=$this->filtre($filtre);
		$q=$this->db->prepare('SELECT count(`id`) AS `total` FROM `'.$this->t_client.'`'.$filtre);
		$q->execute();
		
		$nb=$q->fetch(PDO::FETCH_ASSOC);
		$nb=ceil($nb['total']/$this->max);

		if($nb>1){ // s'il n'y a qu'une page, on écrit rien
			// mise en place de la QS
			$tabqs=explode('&',$_SERVER["QUERY_STRING"]);
			for($i=0;$i<count($tabqs);$i++){
				list($var,$val)=explode("=",$tabqs[$i]);
				if($var!="nb"&&$var!="page")$qs.="&".$tabqs[$i];
			}
			//if(strlen($qs)<=0)$qs="&";
			//affichage du nb de page
			return $this->nbpagefront($where, $current, $nb, $qs);
		}
	}

	// nb de page en front office
	public function nbpagefront($where,$current,$nbPages,$add=""){
		if($nbPages>1){ // s'il n'y a qu'une page, on écrit rien
			if($current>2) $previous='<a href="'.$where.'?nb=1'.$add.'">« Premier</a> ';
			$previous.='<a href="'.$where.'?nb='.($current-1).$add.'">«</a> ';

			$next=' <a href="'.$where.'?nb='.($current+1).$add.'">»</a>';
			if($current<$nbPages-1) $next.=' <a href="'.$where.'?nb='.$nbPages.$add.'">Dernier »</a>';

			if($current>2) $pageencours=' <span class="point">...</span> ';
			$pageencours.='<a href="'.$where.'?nb='.($current-1).$add.'">'.($current-1).'</a> <span class="active">'.$current.'</span> <a href="'.$where.'?nb='.($current+1).$add.'">'.($current+1).'</a>';
			if($current<=$nbPages-2) $pageencours.=' <span class="point">...</span> ';

			if($current==1) {
				$previous="";
				$pageencours='<span class="active">1</span> <a href="'.$where.'?nb=2'.$add.'">2</a>';
				if($nbPages>2) {
					$pageencours.=' <a href="'.$where.'?nb=3'.$add.'">3</a>';
					if($current<$nbPages+2 && $nbPages>3) $pageencours.=' <span class="point">...</span> ';
				}
			}
			if($current==$nbPages) {
				$next="";
				if($nbPages>2) {
					if($current>2) $pageencours=' <span class="point">...</span> ';
					$pageencours.='<a href="'.$where.'?nb='.($current-2).$add.'">'.($current-2).'</a> '; 
				}
				else $pageencours="";
				$pageencours.='<a href="'.$where.'?nb='.($current-1).$add.'">'.($current-1).'</a> <span class="active">'.$nbPages.'</span>';
			}
			return $previous.$pageencours.$next; 
		}
	}

	// Récupérer la liste des utilisateurs, avec tri
	public function getList($sort="nom", $tri="asc") {
		$persos = array();
		
		$q=$this->db->prepare('SELECT * FROM `'.$this->t_client.'` ORDER BY '.$sort.' '.$tri);
		$q->execute();
		
		while ($donnees = $q->fetch(PDO::FETCH_ASSOC)){
			$persos[] = new Client($donnees);
		}
		return $persos;
	}
	
	// Récupérer la liste des promotions, avec tri
	public function getListDateFront($sort="ordre", $tri="asc", $site="lyon", $dated="0000-00-00") {
		$persos = array();
		
		$q=$this->db->prepare('SELECT * FROM `'.$this->t_client.'` WHERE `site`=:site AND `dated`=:dated AND (`tlj` IS NULL OR `tlj`=0) ORDER BY '.$sort.' '.$tri);
		$q->bindValue(':site', $site, PDO::PARAM_STR);
		$q->bindValue(':dated', $dated, PDO::PARAM_STR);
		$q->execute();
		
		while ($donnees = $q->fetch(PDO::FETCH_ASSOC)){
			$persos[] = new Client($donnees);
		}
		return $persos;
	}

	// Connexion d'un client
	public function login($login, $password) {
		$q=$this->db->prepare('SELECT COUNT(*) FROM `'.$this->t_client.'` WHERE `mail`=:login AND `pwd`=:pwd');
		$q->bindValue(':login', $login, PDO::PARAM_STR);
		$q->bindValue(':pwd', sha1($password), PDO::PARAM_STR);
		$q->execute();
		if($q->fetchColumn()){
			$q=$this->db->prepare('SELECT * FROM `'.$this->t_client.'` WHERE `mail`=:login AND `pwd`=:pwd');
			$q->bindValue(':login', $login, PDO::PARAM_STR);
			$q->bindValue(':pwd', sha1($password), PDO::PARAM_STR);
			$q->execute();
			$donnees = $q->fetch(PDO::FETCH_ASSOC);
			if(!$donnees["active"]) return ClientManager::ERROR_NOLOG3;
			else {
				$_SESSION["fdeuser"]=new Client($donnees);
				return $_SESSION["fdeuser"];
			}
		}
		else return ClientManager::ERROR_LOG;
	}
	
	// Envoi d'une demande de mot de passe oublié à un client en fonction de son courriel
	public function forgotPassword($mel){
		$q=$this->db->prepare('SELECT `id` FROM `'.$this->t_client.'` WHERE `mail`=:mel');
		$q->bindValue(':mel',$mel,PDO::PARAM_STR);
		$q->execute();
		$ret=$q->fetchColumn(0);

		if(!$ret)return ClientManager::ERROR_MEL;
		else{
			// TODO : Possible d'utiliser la classe elle même par : $client=$this->get($ret) ?
			$cmanager=new ClientManager($this->db);
			$client=$cmanager->get($ret);
			if($client instanceof Client){
				$cmanager->sendPassword($client,1);
				return $client->id();
			}
			else return ClientManager::ERROR_MEL;
		}
	}
	
	// Envoi d'une demande de renouvellement de mot de passe à un client
	public function sendPassword(Client $client, $what) {
		if(!is_numeric($client->id())) {
			if(DEBUG) echo ClientManager::ERROR_ID;
			exit;
		}
		$q=$this->db->prepare('SELECT COUNT(`mail`) FROM `'.$this->t_client.'` WHERE `id`=:id');
		$q->bindValue(':id', $client->id(), PDO::PARAM_STR);
		$q->execute();
		
		if(!$q->fetchColumn()) return ClientManager::ERROR_NOLOG2;
		else {
			$key=sha1(uniqid(rand(),true));
			$q=$this->db->prepare('INSERT INTO '.$this->t_renewpwd.' (`clecontrol`, `mel`, `type`) VALUES (:key, :mel, "c")');
			$q->bindValue(':key', $key, PDO::PARAM_STR);
			$q->bindValue(':mel', $client->mail(), PDO::PARAM_STR);
			$q->execute();
			
			$tabwhat=array(
				0=>"création de compte client",
				1=>"renouvellement de mot de passe"
			);

			$headers="From: ne-pas-repondre@".EMAILDOMAINE."\r\n";
			$headers.="Content-type: text/plain; charset=utf8"."\r\n";
			$to=$client->mail();
			$site=$client->site();
			$subject="Demande de {$tabwhat[$what]}";
			$websitel=URL;
			$website=WEBSITE;
			$body=<<<BODY
Bonjour,

Une demande de {$tabwhat[$what]} a eu lieu sur le site internet {$website}.

Si vous êtes l'auteur de cette demande et que vous voulez confirmer votre demande, cliquez sur le lien ci-dessous :

http://{$websitel}/$site/password?id={$key}

Si vous n'êtes pas à l'origine de cette demande, merci de l'ignorer et d'effacer ce message.

Cordialement,

Le Webmaster de {$website}   
BODY;
			mail($to,$subject,$body,$headers);
			return 1;
		}
	}
	
	// Envoi d'un nouveau mot de passe à un client
	public function renewPassword($id) {
		$q=$this->db->prepare('SELECT COUNT(*) FROM `'.$this->t_renewpwd.'` WHERE `clecontrol`=:id');
		$q->bindValue(':id', $id, PDO::PARAM_STR);
		$q->execute();
		if(!$q->fetchColumn()) return ClientManager::ERROR_PWD;
		else {
			$q=$this->db->prepare('SELECT * FROM `'.$this->t_renewpwd.'` WHERE `clecontrol`=:id');
			$q->bindValue(':id', $id, PDO::PARAM_STR);
			$q->execute();
			$donnees = $q->fetch(PDO::FETCH_ASSOC);
			$q=$this->db->prepare('SELECT COUNT(*) FROM `'.$this->t_client.'` WHERE `mail`=:mail');
			$q->bindValue(':mail',$donnees["mel"], PDO::PARAM_STR);
			$q->execute();
			if(!$q->fetchColumn()) return ClientManager::ERROR_PWD;
			else {
				$q=$this->db->prepare('SELECT * FROM `'.$this->t_client.'` WHERE `mail`=:mail');
				$q->bindValue(':mail',$donnees["mel"], PDO::PARAM_STR);
				$q->execute();
				$donnees = $q->fetch(PDO::FETCH_ASSOC);
				$cit=new Client($donnees);
				$pwd=\Core\Utils::genpwd(10);
				$q=$this->db->prepare('UPDATE `'.$this->t_client.'` SET `pwd`=:pwd, `active`=1 WHERE `id`=:id');
				$q->bindValue(':pwd',sha1($pwd));
				$q->bindValue(':id',$cit->id());
				$q->execute();
				
				$q=$this->db->prepare('DELETE FROM `'.$this->t_renewpwd.'` WHERE `clecontrol`=:id OR TIME_TO_SEC(TIMEDIFF(NOW(),ts)) > 3600');
				$q->bindValue(':id',$id);
				$q->execute();
				
				$websitel=EMAILDOMAINE;
				$website=WEBSITE;

				$headers="From: ne-pas-repondre@{$websitel}"."\r\n";
				$headers.="Content-type: text/plain; charset=utf8"."\r\n";
				$to=$cit->mail();
				$subject="Renouvellement de mot de passe";
				$body=<<<BODY
Bonjour,

Vous avez confirmé une demande de renouvellement de mot de passe sur le site {$website}.

Voici vos nouvelles coordonnées de connexion, actives dès maintenant :

Identifiant  : {$cit->mail()}
Mot de passe : {$pwd}

Cordialement,

Le Webmaster de {$website}   
BODY;
				mail($to,$subject,$body,$headers);
				return 1;
			}
		}
	}

	public function digitevent(Client $client){
		// détermination des liens images du badge en fonction de la ville
		if(strtoupper($client->site())=="LYON"){
			$sponsor="https://s3-us-west-2.amazonaws.com/digi-projects/Lyon-p2+logos.jpg";
			$logo="https://s3-us-west-2.amazonaws.com/digi-projects/Lyon-p1.jpg";
			$info="https://s3-us-west-2.amazonaws.com/digi-projects/Lyon-p3+infos.jpg";
		}
		elseif(strtoupper($client->site())=="SAINT-ETIENNE"){
			$sponsor="https://s3-us-west-2.amazonaws.com/digi-projects/St+Etienne-p2+logos.jpg";
			$logo="https://s3-us-west-2.amazonaws.com/digi-projects/Loire-p1.jpg";
			$info="https://s3-us-west-2.amazonaws.com/digi-projects/St+Etienne-p3+infos.jpg";
		}
		elseif(strtoupper($client->site())=="ROANNE"){
			$sponsor="https://s3-us-west-2.amazonaws.com/digi-projects/Roanne-p2+logos.jpg";
			$logo="https://s3-us-west-2.amazonaws.com/digi-projects/Loire-p1.jpg";
			$info="https://s3-us-west-2.amazonaws.com/digi-projects/Roanne-p3+infos.jpg";
		}

		// formation de la requête à envoyer
		$req=array(
			'guests'=>array(
				array(
					'name' => $client->nom(),
					'firstname' => $client->prenom(),
					'guestemail' => $client->mail(),
					'mobilephone' => $client->digitEventMP(),
					'registrationStatus' => "confirmed",
					'59ce54d000773800042da76e' => $client->societe(),
					'59ce521400773800042da72d' => $client->site(),
					'59ce54e700773800042da773' => "visiteur",
					'59ce448dab86610004f38961' => $sponsor,
					'59ce44a9ab86610004f38977' => $info,
					'59ce44c8ab86610004f3898f' => $logo,
				),
			),
			'api_key'=>'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6IjU5YjI0ZDc0NjAwYzI3MDAwNGE4NmZhYSIsImlhdCI6MTUwNDg1Nzc0MX0.8eTPe-uS72LZDndvY09tSpKo9IxZnwiWiUdVkReM22w', // = token
		);
		$req=json_encode($req);

		// envoi à DigitEvent
		$eventId='59cba9d567c207000498432d';

		$ch = curl_init('https://api.digitevent.com/v2/event/'.$eventId.'/guests/create');
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$req);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json','Connection: Close'));
		 
		$res = curl_exec($ch);
		error_log(('Retour inscription DigitEvent : '.$res));
		curl_close($ch);

		$r=json_decode($res); // décodage json en retour
		$guest=$r->guests[0]; // récupération de l'objet en 1ère position du tableau puisque ajout 1 par 1

		// enregistrement du badge dans la bdd
		$q=$this->db->prepare('UPDATE `'.$this->t_client.'` SET `badge`=:badge WHERE `id`=:idclient');
		$q->bindValue(':badge',$guest->secureId,PDO::PARAM_STR);
		$q->bindValue(':idclient',$client->id(),PDO::PARAM_INT);
		$q->execute();

		// création de la campagne mail
		if($client->site()=="LYON"){
			$campaignId='59d34eb4a362da0004f49279';
		}
		elseif($client->site()=="SAINT-ETIENNE"){
			$campaignId='59d38c63d61e61000482d260';
		}
		elseif($client->site()=="ROANNE"){
			$campaignId='59d3909bd4a888000407fbec';
		}

		$req=array(
			'api_key'=>'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6IjU5YjI0ZDc0NjAwYzI3MDAwNGE4NmZhYSIsImlhdCI6MTUwNDg1Nzc0MX0.8eTPe-uS72LZDndvY09tSpKo9IxZnwiWiUdVkReM22w', // = token
		);
		$req=json_encode($req);

		$ch = curl_init('https://api.digitevent.com/v2/event/'.$eventId.'/campaign/'.$campaignId.'/guest/'.$guest->_id);
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$req);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json','Connection: Close'));
		 
		$res = curl_exec($ch);
		error_log('Retour confirmation DigitEvent : '.$res);
		curl_close($ch);
	}
	
	// Activation d'un compte client
	public function activation($id){
		$q=$this->db->prepare('SELECT COUNT(*) FROM `'.$this->t_renewpwd.'` WHERE `clecontrol`=:id');
		$q->bindValue(':id', $id, PDO::PARAM_STR);
		$q->execute();
		if(!$q->fetchColumn()) return ClientManager::ERROR_PWD;
		else {
			$q=$this->db->prepare('SELECT * FROM `'.$this->t_renewpwd.'` WHERE `clecontrol`=:id');
			$q->bindValue(':id', $id, PDO::PARAM_STR);
			$q->execute();
			$donnees = $q->fetch(PDO::FETCH_ASSOC);
			$q=$this->db->prepare('SELECT COUNT(*) FROM `'.$this->t_client.'` WHERE `mail`=:mail');
			$q->bindValue(':mail',$donnees["mel"], PDO::PARAM_STR);
			$q->execute();
			if(!$q->fetchColumn()) return ClientManager::ERROR_PWD;
			else {
				$q=$this->db->prepare('SELECT * FROM `'.$this->t_client.'` WHERE `mail`=:mail');
				$q->bindValue(':mail',$donnees["mel"], PDO::PARAM_STR);
				$q->execute();
				$donnees = $q->fetch(PDO::FETCH_ASSOC);
				$cit=new Client($donnees);
				$q=$this->db->prepare('UPDATE `'.$this->t_client.'` SET `active`=1 WHERE `id`=:id');
				$q->bindValue(':id',$cit->id());
				$q->execute();
				$cit->setActive(1);
				
				$q=$this->db->prepare('DELETE FROM `'.$this->t_renewpwd.'` WHERE `clecontrol`=:id OR TIME_TO_SEC(TIMEDIFF(NOW(),ts)) > 3600');
				$q->bindValue(':id',$id);
				$q->execute();
				
				$websitel=strtolower(WEBSITE);
				$website=WEBSITE;

				$headers="From: ne-pas-repondre@".EMAILDOMAINE."\r\n";
				$headers.="Content-type: text/plain; charset=utf8"."\r\n";
				$to=$cit->mail();
				$subject="Activation de compte client";
				$body=<<<BODY
Bonjour,

Vous avez activé votre compte sur le site {$website}.

Cordialement,

Le Webmaster de {$website}   
BODY;
				mail($to,$subject,$body,$headers);

				// On vient d'activer le compte, donc on envoi les données à DigitEvent
				$this->digitevent($cit);

				return 1;
			}
		}
	}

	// Ajout d'un client
	public function add(Client $client) {
/*		if($client->login()==""||$client->nom()==""||$client->prenom()==""||$client->mail()==""||$client->adr1()==""||$client->zip()==""||$client->tel()==""||$client->ville()==""||$client->pays()=="") return ClientManager::ERROR_NOLOG;
		elseif(!ereg("^.+@.+\\..+$",$client->mail())) return ClientManager::ERROR_MAIL;
		elseif($client->password()!=$client->cpassword()||strlen($client->password())<1) return ClientManager::ERROR_NOLOG4;
		else {*/
			$q=$this->db->prepare('SELECT COUNT(`mail`) FROM `'.$this->t_client.'` WHERE `mail`=:mail');
			$q->bindValue(':mail', $client->mail(), PDO::PARAM_STR);
			$q->execute();
			if(!$q->fetchColumn()){
				$q=$this->db->prepare('INSERT INTO `'.$this->t_client.'` (`id`, `societe`, `nom`, `prenom`, `mail`, `pwd`, `zip`, `ville`, `sm`, `profil`, `statut`, `site`, `commentaire`, `networking`, `active`) VALUES (NULL, :societe, :nom, :prenom, :mail, :pwd, :zip, :ville, :sm, :profil, :statut, :site, :commentaire, :networking, :active)');
				$q->bindValue(':societe', $client->societe(), PDO::PARAM_STR);
				$q->bindValue(':nom', $client->nom(), PDO::PARAM_STR);
				$q->bindValue(':prenom', $client->prenom(), PDO::PARAM_STR);
				$q->bindValue(':mail', $client->mail(), PDO::PARAM_STR);
				$q->bindValue(':pwd', sha1($client->pwd()), PDO::PARAM_STR);
				$q->bindValue(':zip', $client->zip(), PDO::PARAM_STR);
				$q->bindValue(':ville', $client->ville(), PDO::PARAM_STR);
				$q->bindValue(':sm', $client->sm(), PDO::PARAM_STR);
				$q->bindValue(':profil', $client->profil(), PDO::PARAM_STR);
				$q->bindValue(':statut', $client->statut(), PDO::PARAM_STR);
				$q->bindValue(':site', $client->site(), PDO::PARAM_STR);
				$q->bindValue(':commentaire', $client->commentaire(), PDO::PARAM_STR);
				$q->bindValue(':networking', $client->networking(), PDO::PARAM_INT);
				$q->bindValue(':active', $client->active(), PDO::PARAM_STR);
				$q->execute();
				$lid=$this->db->lastInsertId();
				$client->setId($lid);
				
				// Envoi mot de passe defini par le client au lieu de l'envoi vers la page de génération de mot de passe
				// ClientManager::sendPassword($client,0);

				// si ajout du compte client depuis l'interface d’admin, pas d'envoi de mot de passe par mail
				if(!$_SESSION["fdeadmin"]){
					$key=sha1(uniqid(rand(),true));
					$q=$this->db->prepare('INSERT INTO '.$this->t_renewpwd.' (`clecontrol`, `mel`, `type`) VALUES (:key, :mel, "c")');
					$q->bindValue(':key', $key, PDO::PARAM_STR);
					$q->bindValue(':mel', $client->mail(), PDO::PARAM_STR);
					$q->execute();
					
					$websitel=URL;
					$website=WEBSITE;
					$headers="From: ne-pas-repondre@".EMAILDOMAINE."\r\n";
					$headers.="Content-type: text/plain; charset=utf8"."\r\n";
					$to=$client->mail();
					$login=$client->mail();
					$pwd=$client->pwd();
					$site=$client->site();
					$subject="=?UTF-8?B?".base64_encode("Demande de création de compte client")."?=";
					$body=<<<BODY
Bonjour,

Une demande de création de compte client a eu lieu sur le site internet {$website}.

Vos paramètres sont :
- Identifiant : {$login}
- Mot de passe : {$pwd}

Ce mot de passe n'est pas stocké en clair dans notre base de données et nous ne pourrons pas vous le redonner. En cas de perte, générer un nouveau mot de passe depuis la page de connexion à votre compte.

Si vous êtes l'auteur de cette demande et que vous voulez activer votre compte, cliquez sur le lien ci-dessous :

http://{$websitel}/{$site}/inscription?activation={$key}

Si vous n'êtes pas à l'origine de cette demande, merci de l'ignorer et d'effacer ce message.

Cordialement,

Le Webmaster de {$website}   
BODY;
					mail($to,$subject,$body,$headers);

					// mail à l'admin
					$bodyadmin=<<<BADMIN
Bonjour,

Un compte vient d'être créé. A voir sur http://{$websitel}/admin/client-crud.php?id={$lid}

Le système de gestion   
BADMIN;
					// mail(EMAIL,"=?UTF-8?B?".base64_encode("[".WEBSITE."] Création de compte client")."?=",$bodyadmin,"From: ne-pas-repondre@".$websitel."\r\n"."Content-type: text/plain; charset=utf8"."\r\n");

				}
				else {
					// else la création du compte à lieu depuis l'admin, on inscrit automatiquement chez DigitEvent
					if($client->active()) $this->digitevent($client);
				}
				return $lid;
			}
			else return ClientManager::ERROR_EVER;
		//}
	}
	
	// Mise à jour d'un client
	public function updateClient(Client $client) {
		if(strlen($client->pwd())>0) $q=$this->db->prepare('UPDATE `'.$this->t_client.'` SET `societe`=:societe, `nom`=:nom, `prenom`=:prenom, `pwd`=:pwd, `sm`=:sm, `mail`=:mail, `profil`=:profil, `statut`=:statut, `zip`=:zip, `ville`=:ville, `commentaire`=:commentaire, `networking`=:networking WHERE id=:id');
		else $q=$this->db->prepare('UPDATE `'.$this->t_client.'` SET `societe`=:societe, `nom`=:nom, `prenom`=:prenom, `sm`=:sm, `mail`=:mail, `profil`=:profil, `statut`=:statut, `zip`=:zip, `ville`=:ville, `commentaire`=:commentaire, `networking`=:networking WHERE id=:id');
		$q->bindValue(':id', $client->id(), PDO::PARAM_INT);
		$q->bindValue(':societe', $client->societe(), PDO::PARAM_STR);
		$q->bindValue(':nom', $client->nom(), PDO::PARAM_STR);
		$q->bindValue(':prenom', $client->prenom(), PDO::PARAM_STR);
		if(strlen($client->pwd())>0)$q->bindValue(':pwd', sha1($client->pwd()), PDO::PARAM_STR);
		$q->bindValue(':sm', $client->sm(), PDO::PARAM_STR);
		$q->bindValue(':mail', $client->mail(), PDO::PARAM_STR);
		$q->bindValue(':zip', $client->zip(), PDO::PARAM_STR);
		$q->bindValue(':ville', $client->ville(), PDO::PARAM_STR);
		$q->bindValue(':profil', $client->profil(), PDO::PARAM_STR);
		$q->bindValue(':statut', $client->statut(), PDO::PARAM_STR);
		$q->bindValue(':commentaire', $client->commentaire(), PDO::PARAM_STR);
		$q->bindValue(':networking', $client->networking(), PDO::PARAM_INT);
		$q->execute();
		
		$_SESSION["client"]=$client;

		$lid=$client->id();
		
/*				$websitel=strtolower(WEBSITE);

		// mail à l'admin
		if(!$_SESSION["user"]){
			$bodyadmin=<<<BADMIN
Bonjour,

Un compte vient d'être modifié. A voir sur http://www.{$websitel}/admin/tdb-cli-crud.php?id={$lid}

Le système de gestion   
BADMIN;
			mail(BT_MAILC."@".BT_MAILD,"=?UTF-8?B?".base64_encode("[".WEBSITE."] Modification de compte client")."?=",$bodyadmin,"From: ne-pas-repondre@".$websitel."\r\n"."Content-type: text/plain; charset=utf8"."\r\n");
		}*/
		return $lid;
	}
	
	// Mise à jour d'un client
	public function update(Client $client) {
		$oldclient=$this->get($client->id());
		// à la modification du compte depuis l'admin, on inscrit chez DigitEvent si le active passe de rien à 1
		if($oldclient->active()!=$client->active()&&$client->active()==1)$this->digitevent($client);

		if(strlen($client->pwd())>0) $q=$this->db->prepare('UPDATE `'.$this->t_client.'` SET `societe`=:societe, `nom`=:nom, `prenom`=:prenom, `pwd`=:pwd, `sm`=:sm, `mail`=:mail, `profil`=:profil, `statut`=:statut, `zip`=:zip, `ville`=:ville, `commentaire`=:commentaire, `active`=:active WHERE id=:id');
		else $q=$this->db->prepare('UPDATE `'.$this->t_client.'` SET `societe`=:societe, `nom`=:nom, `prenom`=:prenom, `sm`=:sm, `mail`=:mail, `profil`=:profil, `statut`=:statut, `zip`=:zip, `ville`=:ville, `commentaire`=:commentaire, `networking`=:networking, `active`=:active WHERE id=:id');
		$q->bindValue(':id', $client->id(), PDO::PARAM_INT);
		$q->bindValue(':societe', $client->societe(), PDO::PARAM_STR);
		$q->bindValue(':nom', $client->nom(), PDO::PARAM_STR);
		$q->bindValue(':prenom', $client->prenom(), PDO::PARAM_STR);
		if(strlen($client->pwd())>0)$q->bindValue(':pwd', sha1($client->pwd()), PDO::PARAM_STR);
		$q->bindValue(':sm', $client->sm(), PDO::PARAM_STR);
		$q->bindValue(':mail', $client->mail(), PDO::PARAM_STR);
		$q->bindValue(':zip', $client->zip(), PDO::PARAM_STR);
		$q->bindValue(':ville', $client->ville(), PDO::PARAM_STR);
		$q->bindValue(':profil', $client->profil(), PDO::PARAM_STR);
		$q->bindValue(':statut', $client->statut(), PDO::PARAM_STR);
		$q->bindValue(':commentaire', $client->commentaire(), PDO::PARAM_STR);
		$q->bindValue(':networking', $client->networking(), PDO::PARAM_STR);
		$q->bindValue(':active', $client->active(), PDO::PARAM_STR);
		$q->execute();

		// 1ère activation depuis l’admin
		$client=$this->get($client->id());
		if(!$client->badge()&&$client->active()) $this->digitevent($client);
		
		return $client->id();
	}

	public function updateComClient($comclient, $id){
		$q=$this->db->prepare('UPDATE `'.$this->t_client.'` SET `commentaire`=:commentaire WHERE id=:id');
		$q->bindValue(':id', $id, PDO::PARAM_INT);
		$q->bindValue(':commentaire', $comclient, PDO::PARAM_STR);
		$q->execute();
	}
		
	// Suppression d'un client
	public function delete($id) {
		if(!is_numeric($id)) {
			if(DEBUG) echo ClientManager::ERROR_ID;
			exit;
		}
		$q=$this->db->prepare('DELETE FROM `'.$this->t_client.'` WHERE `id`=:id');
		$q->bindValue(':id', $id, PDO::PARAM_INT);
		$q->execute();

		// suppression des réservations conférences/tables rondes de l’utilisateur
		$q=$this->db->prepare('DELETE FROM `'.$this->t_reservation.'` WHERE `idclient`=:id');
		$q->bindValue(':id', $id, PDO::PARAM_INT);
		$q->execute();
		// suppression des réservations rdv expert de l’utilisateur
		$q=$this->db->prepare('DELETE FROM `'.$this->t_rendezvous.'` WHERE `idclient`=:id');
		$q->bindValue(':id', $id, PDO::PARAM_INT);
		$q->execute();
	}
	
	// Mise en place de la connexion avec la base de données
	public function setDb($db) {
		$this->db = $db;
	}
	
	// Retour d'une ligne de texte si "echo <classe>"
	public function __toString () {
		return "Classe de gestion des accès bdd pour les utilisateurs";
	}
}
?>