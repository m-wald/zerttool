<?php
namespace Zertifizierungstool\Model;

use Zertifizierungstool\Model\Db_connection;

class Antwort {
	
	private $id;
	private $text;
	private $frage_id;
	private $status;
	
	public function __construct($id = "", $text = "", $frage_id = "", $status = "" ) {
		
		$db = new Db_connection();
		$mysqli= $db->getConnection();
		
		$this->id 	    = $mysqli->real_escape_string($id);
		$this->text 	= $mysqli->real_escape_string($text);
		$this->frage_id = $mysqli->real_escape_string($frage_id);
		$this->status 	= $mysqli->real_escape_string($status);
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
	
	private function saveNew() {
		$db = new Db_connection();
		
		$query = "INSERT INTO antwort (antwort_text, frage_id, status) VALUES ('"
				.$this->text 	. "', "
				.$this->frage_id . ", "
				.$this->status . ") ";
	
		$result = $db->execute($query);
		
		if($result == false) {
			// Fehler bei der Datenbankabfrage
			return false;
		
		} else {
			return true;
		}
	}
	
	private function update() {
		$db = new Db_connection();
		$conn = $db->getConnection();
	
		$query = "UPDATE antwort SET"
				." antwort_text = '" .$this->text
				."', frage_id = " .$this->frage_id
				.", status = "   .$this->status
	
				." WHERE antwort_id = " .$this->id;
	
		$result = mysqli_query($conn, $query);
	
		if (is_bool($result) && $result == false) {
			echo $query;
			echo '<br>' .mysqli_error($conn);
			return false;
		} else {
			return true;
		}
	}
	
	public static function delete($id) {
		$db = new Db_connection();
		$conn = $db->getConnection();
		
		$id = $conn->real_escape_string($id);
		
		$query = "DELETE FROM antwort WHERE antwort_id = " .$id;
		
		$result = mysqli_query($conn, $query);
		
		if (is_bool($result) && $result == false) {
			echo $query;
			echo '<br>' .mysqli_error($conn);
			return false;
		} else {
			return true;
		}
	}
	
	public static function loadList($frage_id) {
		$db = new Db_connection();
		$conn = $db->getConnection();
		
		$frage_id = $conn->real_escape_string($frage_id);
	
		$query = "SELECT * FROM antwort WHERE frage_id = " .$frage_id;
	
		$result = mysqli_query($conn, $query);
	
		if(!$result) {
			// Fehler bei der Datenbankabfrage
			return false;
	
		} else {
			$return_array = array();
			while ($row = mysqli_fetch_assoc($result)) {
				$a = new Antwort(
						$row["antwort_id"],
						$row["antwort_text"],
						$row["frage_id"],
						$row["status"]);
	
				array_push($return_array, $a);
			}
				
			return $return_array;
		}
	}
	
	public function load($id) {
		$db = new Db_connection();
		$mysqli = $db->getConnection();
	
		$id = $mysqli->real_escape_string($id);
	
	
		$query = "SELECT * FROM antwort WHERE antwort_id = " .$id;
	
		$result = $db->execute($query);
	
		if(!$result || mysqli_num_rows($result) != 1) {
			// Fehler bei der Datenbankabfrage oder keine Prüfung mit der Id gefunden
			return false;
		}
			
		$row = mysqli_fetch_assoc($result);
	
		$this->id		= $id;
		$this->text   = $row["antwort_text"];
		$this->frage_id  = $row["frage_id"];
		$this->status = $row["status"];
	
		return true;
	}
	
	public function getId() 	 {return $this->id;}
	public function getText() 	 {return $this->text;}
	public function getFrageId() {return $this->frage_id;}
	public function getStatus()  {return $this->status;}
}