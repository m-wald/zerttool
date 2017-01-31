<?php
namespace Zertifizierungstool\Model;

use Zertifizierungstool\Model\Db_connection;
use Zend\Crypt\PublicKey\Rsa\PublicKey;

/**
 * @author Martin
 *
 */
class SchreibtPruefung {
	
	private $id;		
	private $pruefung_id;
	private $benutzername;
	private $zeitpunkt;
	private $bestanden;

	public function __construct($id = "", $pruefung_id = "", $benutzername = "", $zeitpunkt = "", $bestanden = "") {
		
		$db = new Db_connection();
		$mysqli = $db->getConnection();
		
		$this->id			= $mysqli->real_escape_string($id);
		$this->pruefung_id	= $mysqli->real_escape_string($pruefung_id);
		$this->benutzername	= $mysqli->real_escape_string($benutzername);
		$this->zeitpunkt    = $mysqli->real_escape_string($zeitpunkt);
		$this->bestanden 	= $mysqli->real_escape_string($bestanden);
	}
	
	/**
	 * Fügt die Daten des aktuellen Objekts als neuen Datensatz in der Datenbank.
	 * Setzt auch die Id des Objekts mit dem Wert, der von der DB automatisch zugeteilt wurde.
	 *
	 * @return boolean false, falls ein Fehler aufgetreten ist. Sonst true.
	 */
	public function saveNew() {
		$db = new Db_connection();
		$conn = $db->getConnection();
		
		$query = "INSERT INTO schreibt_pruefung (pruefung_id, benutzername, zeitpunkt, bestanden) VALUES ("
				.$this->pruefung_id	. ", '"
				.User::currentUser()->getBenutzername(). "', '"
				.strftime('%F %T', time()) ."', "
				."0)" ;
		
		$result = mysqli_query($conn, $query);
	
		if(!empty(mysqli_error($conn))) {
			// Fehler bei der Datenbankabfrage
			echo mysqli_error($conn);
			echo "<br>" . $query;
			return false;
					
		} else {
			// Id des eben eingefügten Datensatzes auslesen und im Objekt setzen
			$this->id = mysqli_insert_id($conn);
			return true;
		}
	}
	
	public function load($id) {
		$db = new Db_connection();
		$mysqli = $db->getConnection();
		
		$id = $mysqli->real_escape_string($id);
	
		$query = "SELECT * FROM schreibt_pruefung WHERE schreibt_pruefung_id = " .$id;
	
		$result = $db->execute($query);
	
		if(!$result || !mysqli_num_rows($result) > 0) {
			// Fehler bei der Datenbankabfrage oder keine Frage mit der Id gefunden
			return false;
		}
			
		$row = mysqli_fetch_assoc($result);
	
		$this->id		= $id;
		$this->pruefung_id 	= $row["pruefung_id"];
		$this->benutzername   = $row["benutzername"];
		$this->zeitpunkt  = $row["zeitpunkt"];
		$this->bestanden = $row["bestanden"];
	
		return true;
	}
	
	public static function loadlist ($pruefung_id) {
		
		$db = new Db_connection();
		$mysqli = $db->getConnection();
		
		$pruefung_id = $mysqli->real_escape_string($pruefung_id);
		
		$query = "SELECT * FROM schreibt_pruefung WHERE pruefung_id = " .$pruefung_id;
		
		$result = $db->execute($query);
		
		if(!$result) {
			// Fehler bei der Datenbankabfrage
			return false;
		
		} else {
			$return_array = array();
			//frage_id, frage_text, punkte, pruefung_id, frage_typ
			while ($row = mysqli_fetch_assoc($result)) {
				$schreibtPruefung = new SchreibtPruefung(
						$row["schreibt_pruefung_id"],
						$row["pruefung_id"],
						$row["benutzername"],
						$row["zeitpunkt"],
						$row["bestanden"]);
		
				array_push($return_array, $schreibtPruefung);
			}
				
			return $return_array;
		}
		
	}
	
	public function bestanden() {
		$this->bestanden = 1;
		$db = new Db_connection();
		
		$query = "UPDATE schreibt_pruefung SET bestanden = 1 WHERE schreibt_pruefung_id = " .$this->id;
		
		$result = $db->execute($query);
		
		if(!$result || !mysqli_num_rows($result) > 0) {
			// Fehler bei der Datenbankabfrage oder keine Frage mit der Id gefunden
			return false;
		}
			
		return true;
	}
	
	public function loadLastTry($pruefung_id) {
		$db = new Db_connection();
		$mysqli = $db->getConnection();
		
		$pruefung_id = $mysqli->real_escape_string($pruefung_id);
		
		// Den Zeitpunkt des letzten Versuchs ermitteln
		$query = "SELECT schreibt_pruefung_id FROM aktuellster_Versuch "
				 ."WHERE pruefung_id = " .$pruefung_id
				 ." AND benutzername = '" .User::currentUser()->getBenutzername() ."'";
		
		$result = $db->execute($query);
		
		if(!$result || mysqli_num_rows($result) != 1) {
			// Fehler bei der Datenbankabfrage oder der User hat die Prüfung noch nicht geschrieben
			return false;
		}
		
		$row = mysqli_fetch_assoc($result);
		
		$this->load($row['schreibt_pruefung_id']);
		
		return true;	
	}
	
	/**
	 * Gibt zurück, wie oft sich ein Benutzer an einer bestimmten Prüfung bereits versucht hat.
	 */
	public static function attempts($benutzername, $pruefung_id) {
		$db = new Db_connection();
		$query = "SELECT count(schreibt_pruefung_id) AS anzahl FROM schreibt_pruefung "
				."WHERE benutzername = '" .$benutzername
				."' AND pruefung_id = "   .$pruefung_id
				." GROUP BY benutzername, pruefung_id";
		
		$result = $db->execute($query);
		
		if(!$result || mysqli_num_rows($result) != 1) {
			// Fehler bei der Datenbankabfrage oder keine Frage mit der Id gefunden
			return false;
		}
		
		$row = mysqli_fetch_assoc($result);
		
		return $row['anzahl'];
	}
	
	/*
	 * Gibt zurück Datum von der letzten bestandenen Prüfung in einem Kurs
	 */
	public function lastExam($benutzername, $kurs_id){
		$db = new Db_connection();
		$query = "SELECT zeitpunkt FROM schreibt_pruefung
					WHERE zeitpunkt IN (SELECT MAX(zeitpunkt) FROM schreibt_pruefung
					JOIN pruefung USING (pruefung_id)
					WHERE benutzername = '".$benutzername."' AND kurs_id = ".$kurs_id.")
				";
		$result = $db->execute($query);
		if(!$result || mysqli_num_rows($result) != 1) {
			// Fehler bei der Datenbankabfrage oder keine Frage mit der Id gefunden
			return false;
		}
		$row = mysqli_fetch_assoc($result);
		
		return $row['zeitpunkt'];
		
	}
	
	public function getId() { return $this->id; }
	public function getPruefungId() { return $this->pruefung_id; }
	public function getBenutzername() { return $this->benutzername; }
	public function getZeitpunkt() { return $this->zeitpunkt; }
	public function getBestanden() { return $this->bestanden; }
}