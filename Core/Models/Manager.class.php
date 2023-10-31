<?php
/*******************************
 * Version : 1.0.0.0
 * Revised : vendredi 4 mai 2018, 10:23:58 (UTC+0200)
 *******************************/

namespace Core\Models;

use PDO;
use Core\Classes\Utils;

abstract class Manager {
	protected $db;
	protected $prefix;
	protected $table;
	protected $pk;
	protected $metier;
	
	protected $errorMessage;
	
	/* pagination */
	protected $max = 10;
	
	function max() { return $this->max; }
	
	public function __construct() {
		$this->db = \Core\Database\myPDO::getInstance();
		$this->metier = '\Core\Models\\' . ucfirst($this->table);
	}
	
	/**
	 * Récupérer une formation en fonction de son ID
	 * @param int $id
	 * @return Formation
	 */
	public function get($id = 0) {
		
		$q = $this->db->prepare('SELECT * FROM `' . $this->table . '` WHERE `' . $this->table . '`.`' . $this->pk . '`=:id');
		$q->bindValue(':id', $id, PDO::PARAM_INT);
		$q->execute();
		//$q->debugDumpParams();
		$donnees = $q->fetch(PDO::FETCH_ASSOC);
		
		if ($donnees !== false) return new $this->metier($donnees);
		return false;
	}
	
	// Récupérer la liste des clients, avec tri
	public function getList($sort="name", $tri="asc") {
		$list = array();
		
		$q=$this->db->prepare('SELECT * FROM `'.$this->table.'` ORDER BY '.$sort.' '.$tri);
		$q->execute();
		
		while ($donnees = $q->fetch(PDO::FETCH_ASSOC)){
			$list[] = new $this->metier($donnees);
		}
		return $list;
	}
	
	protected function delete(int $id) {
		$this->db->query("DELETE FROM `" . $this->table . "` WHERE `" . $this->prefix . "_ID`=" . $id);
	}

}