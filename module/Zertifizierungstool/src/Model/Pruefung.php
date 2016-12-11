<?php
namespace Zertifizierungstool\Model;

use Zertifizierungstool\Model\Db_connection;

class Pruefung {
	
	private $id;
	private $name;
	private $termin;
	private $kurs_id;
	private $cutscore;
	
	public function __construct($id = "", $name = "", $termin = "", $kursid = "", $cutscore = "") {
		$this->id		= $id;
		$this->name		= $name;
		$this->termin	= $termin;
		$this->kurs_id  = $kursid;
		$this->cutscore = $cutscore;
	}
	
	public function saveNew() {
		$db = new Db_connection();
		$conn = $db->getConnection();
		
		$query = "INSERT INTO pruefung (pruefung_name, pruefung_ab, kurs_id, cutscore) VALUES ('"
					.$this->name	. "', '"
					.$this->termin 	. "', "
					.$this->kurs_id . ", '"
					.$this->cutscore . "')" ;
		
		$result = mysqli_query($conn, $query);
				
		if(!$result) {
			// Fehler bei der Datenbankabfrage
			return false;
			
		} else {
			// Id des eben eingefügten Datensatzes auslesen und im Objekt setzen
			$this->id = mysqli_insert_id($conn);
			return true;
		}
	}
	
	public function load($id) {
		$db = new Db_connection();
		
		$query = "SELECT * FROM pruefung WHERE pruefung_id = " .$this->id;
		
		$result = mysqli_query($db->getConnection(), $query);
		
		if(!$result || !mysqli_num_rows($result) > 0) {
			// Fehler bei der Datenbankabfrage oder keine Prüfung mit der Id gefunden
			return false;
		}

		$row = mysqli_fetch_assoc($result);
			
		$this->id		= $id;
		$this->name 	= $row["pruefung_name"];
		$this->termin   = $row["pruefung_ab"];
		$this->kurs_id  = $row["kurs_id"];
		$this->cutscore = $row["cutscore"];
		
		return true;
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
		return $this->cutscore;
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