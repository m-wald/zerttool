<?php
namespace Zertifizierungstool\Model;

use Zertifizierungstool\Model\Db_connection;

class Pruefung {
	
	private $id;
	private $name;
	private $termin;
	private $kurs_id;
	private $cutscore;
	
	public function __construct($kursid) {
		$this->name		= "";
		$this->termin	= "";
		$this->kurs_id  = $kursid;
		$this->cutscore = 0.5;
	}
	
	public function anlegen() {
		$db = new Db_connection();
		
		$query = "INSERT INTO pruefung (pruefung_id, pruefung_name, pruefung_ab, kurs_id, cutscore) VALUES (".
					$this->id 		. ", ";
					$this->name 	. ", ";
					$this->termin 	. ", ";
					$this->kurs_id 	. ", ";
					$this->cutscore . ")" ;
					
		$result = $db->execute($query);
	}
	
	
	// Getter methods
	public function getId() {
		return $this->id;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function getTermin() {
		return $this->termin;
	}
	
	public function getKursId() {
		return $this->kurs_id;
	}
	
	public function getCutscore() {
		$this->cutscore;
	}
	
	// Setter methods
	public function setId($id) {
		$this->id = $id;
	}
	
	public function setName($name) {
		$this->name = $name;
	}
	
	public function setTermin($termin) {
		$this->termin = $termin;
	}
	
	public function setKursId($kursId) {
		$this->kurs_id = $kursId;
	}
	
	public function setCutscore($cutscore) {
		$this->cutscore = $cutscore;
	}

}