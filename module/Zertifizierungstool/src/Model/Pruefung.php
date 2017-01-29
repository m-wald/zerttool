<?php
namespace Zertifizierungstool\Model;

use Zertifizierungstool\Model\Db_connection;

/**
 * Objekte dieser Klasse repräsentieren die Entität "Prüfung" aus der Datenbank.
 * Die Klasse enthält Methoden zum Lesen, Verändern und Löschen von Datensätzen aus der Tabelle "pruefung"
 * 
 * @author Martin
 *
 */
class Pruefung {
	
	/** Tabellenfeld "pruefung_id" */
	private $id;		
	private $name;
	private $termin;
	private $kurs_id;
	private $cutscore;
	private $anzahlmitgeschrieben;
	private $bestehensquote;
	private $durchschnitt_versuche;
	
	
	public function __construct($id = "", $name = "", $termin = "", $kursid = "", $cutscore = "", $anzahlmitgeschrieben="", $bestehensquote="", $durchschnitt_versuche="") {
		
		$db = new Db_connection();
		$mysqli = $db->getConnection();
		
		
		
		$this->id						= $mysqli->real_escape_string($id);
		$this->name						= $mysqli->real_escape_string($name);
		$this->termin					= $mysqli->real_escape_string($termin);
		$this->kurs_id 					= $mysqli->real_escape_string($kursid);
		$this->cutscore 				= $mysqli->real_escape_string($cutscore);
		$this->anzahlmitgeschrieben 	= $mysqli->real_escape_string($anzahlmitgeschrieben);
		$this->bestehensquote 			= $mysqli->real_escape_string($bestehensquote);
		$this->durchschnitt_versuche	= $mysqli->real_escape_string($durchschnitt_versuche);
		
	}
	
	/**
	 * Speichert das aktuelle Objekt in der Datenbank.
	 * Falls das Objekt bereits in der Datenbank existiert, wird der entsprechende
	 * Datensatz aktualisiert. Ansonsten wird ein neuer Datensatz angelegt.
	 */
	public function save() {
		if (empty($this->id)) {
			return $this->saveNew();
		}else {
			return $this->update();
		}
	}
	
	/**
	 * Fügt die Daten des aktuellen Objekts als neuen Datensatz in der Datenbank.
	 * Setzt auch die Id des Objekts mit dem Wert, der von der DB automatisch zugeteilt wurde.
	 * 
	 * @return boolean false, falls ein Fehler aufgetreten ist. Sonst true.
	 */
	private function saveNew() {
		$db = new Db_connection();
		$conn = $db->getConnection();
		
		$query = "INSERT INTO pruefung (pruefung_name, pruefung_ab, kurs_id, cutscore) VALUES ('"
					.$this->name	. "', '"
					.strftime('%F', strtotime($this->termin)) 	. "', "
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
	
	/**
	 * Aktualisiert den Datensatz mit der ID des aktuellen Objekts.
	 * 
	 * @return boolean false, falls ein Fehler aufgetreten ist. Sonst true.
	 */
	private function update() {
		$db = new Db_connection();
		$conn = $db->getConnection();
		
		$query = "UPDATE pruefung SET"
					." pruefung_name = '" .$this->name ."'"
					.", pruefung_ab = '"  .strftime('%F', strtotime($this->termin)) ."'"
					.", cutscore = "      .$this->cutscore
		
				." WHERE pruefung_id = " .$this->id;
		
		$result = mysqli_query($conn, $query);
		
		if (is_bool($result) && $result == false) {
			echo $query;
			echo '<br>' .mysqli_error($conn);
			return false;
		} else {
			return true;
		}
	}
	
	/**
	 * Befüllt die Attribute des aktuellen Objekts mit den entsprechenden Daten aus der Datenbank.
	 * 
	 * @param $id Die id des Eintrags in der Datenbank, der geladen werden soll.
	 * @return boolean false, falls ein Fehler aufgetreten ist. Sonst true.
	 */
	public function load($id) {
		$db = new Db_connection();
		$mysqli = $db->getConnection();
		
		$id = $mysqli->real_escape_string($id);
		
		
		$query = "SELECT * FROM pruefung WHERE pruefung_id = " .$id;
		
		$result = $db->execute($query);
		
		if(!$result || mysqli_num_rows($result) != 1) {
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
	
	/**
	 * Lädt alle Prüfungen, die zu einem bestimmten Kurs gehören und speichert diese in einem Array.
	 * 
	 * @param $kurs_id Id des Kurses, dessen Prüfungen geladen werden sollen.
	 * @return boolean false, falls ein Fehler aufgetreten ist. Sonst das befüllte Array.
	 */
	public static function loadList($kurs_id) {
		$db = new Db_connection();
		$conn = $db->getConnection();
		
		$kurs_id = $conn->real_escape_string($kurs_id);
	
		$query = "SELECT * FROM pruefung WHERE kurs_id = " .$kurs_id;
	
		$result = mysqli_query($conn, $query);
	
		if(!$result) {
			// Fehler bei der Datenbankabfrage
			return false;
	
		} else {
			$return_array = array();
			while ($row = mysqli_fetch_assoc($result)) {
				$p = new Pruefung(
						$row["pruefung_id"],
						$row["pruefung_name"],
						$row["pruefung_ab"],
						$row["kurs_id"],
						$row["cutscore"]);
	
				array_push($return_array, $p);
			}
	
			return $return_array;
		}
	}
	
	
	/** lädt alle Prüfungen eines Kurses und liefert die Anzahl der Absolventen und die Bestehensquote mit **/
	
	public static function loadstatistics($kurs_id) {
		$db = new Db_connection();
		$conn = $db->getConnection();
		
		$kurs_id = $conn->real_escape_string($kurs_id);
	
		$query = "SELECT pruefung_id, pruefung_name, pruefung_ab, cutscore, kurs_id, anzahl_mitgeschrieben, bestehensquote, durchschnitt_versuche FROM pruefung natural join anzahl_mitgeschrieben natural join bestehensquote natural join durchschnitt_versuche where kurs_id=".$kurs_id."
				  union all select pruefung_id, pruefung_name, pruefung_ab, cutscore, kurs_id, 0, 0, 0 from pruefung where kurs_id=".$kurs_id." and pruefung_id not in (select pruefung_id from anzahl_mitgeschrieben) order by pruefung_id;";
	
		$result = mysqli_query($conn, $query);
	
		if(!$result) {
			// Fehler bei der Datenbankabfrage
			return false;
	
		
			}
			$return_array = array();
			while ($row = mysqli_fetch_assoc($result)) {
				$p = new Pruefung(
						$row["pruefung_id"],
						$row["pruefung_name"],
						$row["pruefung_ab"],
						$row["kurs_id"],
						$row["cutscore"],
						$row["anzahl_mitgeschrieben"],
						$row["bestehensquote"],
						$row["durchschnitt_versuche"]);
				
						
	
				array_push($return_array, $p);
			}
	
			return $return_array;
		}
	
		public static function delete($id) {
			$db = new Db_connection();
			$conn = $db->getConnection();
		
			$id = $conn->real_escape_string($id);
		
			$query = "DELETE FROM pruefung WHERE pruefung_id = " .$id;
		
			$result = mysqli_query($conn, $query);
		
			if (is_bool($result) && $result == false) {
				echo $query;
				echo '<br>' .mysqli_error($conn);
				return false;
			} else {
				return true;
			}
		}
	
	
	
	
	
	// Getter methods
	public function getId() 	  {return $this->id;}	
	public function getName() 	  {return $this->name;}
	public function getTermin()   {return $this->termin;}
	public function getKursId()   {return $this->kurs_id;}
	public function getCutscore() {return $this->cutscore;}
	public function getAnzahlMitgeschrieben() {return $this->anzahlmitgeschrieben;}
	public function getBestehensquote() {return $this->bestehensquote;}
	public function getDurchschnittVersuche() {return $this->durchschnitt_versuche;}
	
	
	// Setter methods
	public function setId($id) {
		
		$db = new Db_connection();
		$mysqli = $db->getConnection();
		
		$id = $mysqli->real_escape_string($id);
		$this->id = $id;
	}
	
	public function setName($name) {
		
		$db = new Db_connection();
		$mysqli = $db->getConnection();
		
		$name = $mysqli->real_escape_string($name);
		$this->name = $name;
	}
	
	public function setTermin($termin) {
		
		$db = new Db_connection();
		$mysqli = $db->getConnection();
		
		$termin = $mysqli->real_escape_string($termin);
		$this->termin = $termin;
	}
	
	public function setKursId($kursId) {
		
		$db = new Db_connection();
		$mysqli = $db->getConnection();
		
		$kursId = $mysqli->real_escape_string($kursId);
		$this->kurs_id = $kursId;
	}
	
	public function setCutscore($cutscore) {
		
		$db = new Db_connection();
		$mysqli = $db->getConnection();
		
		$cutscore = $mysqli->real_escape_string($cutscore);
		$this->cutscore = $cutscore;
	}

}