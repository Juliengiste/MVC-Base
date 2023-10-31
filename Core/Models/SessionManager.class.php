<?php
/*******************************
Version : 1.0.0.0
Revised : vendredi 4 mai 2018, 10:23:58 (UTC+0200)
 *******************************/
namespace Core\Models;

use PDO;

class SessionManager extends Manager {
	protected $table = "session";
	protected $pk = "S_ID";

	/**
	 * Retourne l'ensemble des sessions d'une action
	 * @param int $id id de l'action
	 * @return array|bool Array d'Objets Session
     */
	public function getSessionsAction($args=array()) {
		$defaults = array(
			'orderby' => 'S_ID', 'order' => 'DESC', 'post_per_page' => '', 'page' => 1,
			'SStatut' => 'NOT_F', 'SAction_ID' => 0
		);
		$r=array_merge($defaults, $args);

		$where="WHERE 1=1"; $limit="";

		if($r['SAction_ID']>0) $where.= " AND `SAction_ID`=:id";

		if($r['SStatut']!="") {
			if(substr($r['SStatut'],0,3)=="NOT") $where.=' AND (SStatut!="'.substr($r['SStatut'],4,strlen($r['SStatut'])).'")';
			elseif(!is_array($r['SStatut'])) $where.=' AND (SStatut="'.$r['SStatut'].'")';
		}

		if($r['post_per_page']!="") $limit="LIMIT ".(($r['page']-1)*POSTPERPAGE).",".$r['post_per_page'];

		$q=$this->db->prepare('SELECT * FROM `'.$this->table.'` '.$where.' ORDER BY `'.$r['orderby'].'` '.$r['order'].' '.$limit);

		if($r['SAction_ID']>0) $q->bindValue(':id', $r['SAction_ID'], PDO::PARAM_INT);
		//$q->debugDumpParams();
		$q->execute();

		$sessions = [];
		while($donnees = $q->fetch(PDO::FETCH_ASSOC)){
			$sessions[] = new Session($donnees);
		}

		if(count($sessions)>0) return $sessions;
		return false;
	}

   public function save(array $sessions)
   {
      try {
         //on vérifie que la transactio n'a pas déjà été ouverte par l'action
         if(!$this->db->inTransaction()) {
         	$this->db->beginTransaction();
         	$transactionStartsHere = true;
         }

         $nb_sessions = 0;
         foreach ($sessions as $session) :

            if ($session instanceof Session) :
               if($session->S_ID>0) {
                  $req = "UPDATE `session` set `SAction_ID` = :SAction_ID, `SStatut` = :SStatut, `SCarif_ID` = :SCarif_ID, `SDateDeb` = :SDateDeb, `SDateFin` = :SDateFin, `SDateComment` = :SDateComment,`SDateDebInscription` = :SDateDebInscription, `SDateFinInscription` = :SDateFinInscription, `SInscriptionComment` = :SInscriptionComment, `SSTAdresse_ID` = :SSTAdresse_ID WHERE `S_ID` = :S_ID";
	               $req = $this->db->prepare($req);
	               $req->bindValue(':S_ID', $session->S_ID, PDO::PARAM_INT);
               }
               else {
                  $req = "INSERT INTO `session`
      (`S_ID`, `SAction_ID`, `SStatut`, `SCarif_ID`, `SDateDeb`, `SDateFin`, `SDateComment`,`SDateDebInscription`,`SDateFinInscription`, `SInscriptionComment`, `SSTAdresse_ID`) values
      (:S_ID, :SAction_ID, :SStatut, :SCarif_ID, :SDateDeb, :SDateFin, :SDateComment, :SDateDebInscription, :SDateFinInscription, :SInscriptionComment, :SSTAdresse_ID)";
	               $req = $this->db->prepare($req);
	               $req->bindValue(':S_ID', null, PDO::PARAM_INT);
               }

               //Requete formation initiale
               $req->bindValue(':SAction_ID', $session->SAction_ID, PDO::PARAM_INT);
               $req->bindValue(':SStatut', $session->SStatut, PDO::PARAM_STR);
               $req->bindValue(':SCarif_ID', $session->SCarif_ID, PDO::PARAM_STR);
               $req->bindValue(':SDateDeb', $session->SDateDeb, PDO::PARAM_STR);
               $req->bindValue(':SDateFin', $session->SDateFin, PDO::PARAM_STR);
               $req->bindValue(':SDateComment', $session->SDateComment, PDO::PARAM_STR);
               $req->bindValue(':SDateDebInscription', $session->SDateDebInscription, PDO::PARAM_STR);
               $req->bindValue(':SDateFinInscription', $session->SDateFinInscription, PDO::PARAM_STR);
               $req->bindValue(':SInscriptionComment', $session->SInscriptionComment, PDO::PARAM_STR);
               $req->bindValue(':SSTAdresse_ID', $session->SSTAdresse_ID, PDO::PARAM_INT);
               
               if($req->execute()) $nb_sessions++;
               else {
                  throw new \Exception("Impossible d'enregistrer la session");
                  return false;
               }
            endif; //instance of session
         endforeach; //sessions

         if(isset($transactionStartsHere)) $this->db->commit();

         return $nb_sessions;
      } catch (\Exception $e) {
	      $emailManager = new Email();
	      $emailManager->alerteAdminError($e);
	      $this->db->rollBack();
	      if(isset($transactionStartsHere)) $this->db->rollBack();
	      return false;
      }

      return false;
   }
	
	/**
	 * Suppression des sessions liées à une action
	 * @param $Action_ID
	 */
	public function deleteFromAction($Action_ID) {
	   $this->db->query("DELETE FROM `".$this->table."` WHERE `SAction_ID`=".$Action_ID);
   }
   
   public function archiveSessionsEchues($days = 45) {
		//echo "UPDATE `".$this->table."` SET 'SStatut'='F' WHERE `SStatut`='V' AND `SDateFin`<='".date("Y-m-d", strtotime("-".$days." days"))."'";
	   $this->db->query("UPDATE `".$this->table."` SET `SStatut`='F' WHERE `SStatut`='V' AND `SDateFin`<='".date("Y-m-d", strtotime("-".$days." days"))."'");
   }

}