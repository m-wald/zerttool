<?php
namespace Zertifizierungstool\Model;

use Zertifizierungstool\Model\Db_connection;

class Antwort {
	
	private $id;
	private $text;
	private $frage_id;
	private $status;
	
	public function __construct($id = "", $text = "", $frage_id = "", $status = "" ) {
		$this->id 	    = $id;
		$this->text 	= $text;
		$this->frage_id = $frage_id;
		$this->status 	= $status;
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
	
	public function getId() 	 {return $this->id;}
	public function getText() 	 {return $this->text;}
	public function getFrageId() {return $this->frage_id;}
	public function getStatus()  {return $this->status;}
}