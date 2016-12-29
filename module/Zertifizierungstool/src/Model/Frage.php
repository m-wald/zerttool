<?php
namespace Zertifizierungstool\Model;

use Zertifizierungstool\Model\Db_connection;

class Frage {
	
	private $id;
	private $text;
	private $punkte;
	private $pruefung_id;
	private $typ;
	
	public function __construct($id = "", $text = "", $punkte = "", $pruefung_id = "", $typ = "" ) {
		$this->id 		   = $id;
		$this->text  	   = $text;
		$this->punkte 	   = $punkte;
		$this->pruefung_id = $pruefung_id;
		$this->typ		   = $typ;
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
		$conn = $db->getConnection();
		
		$query = "INSERT INTO frage (frage_text, punkte, pruefung_id, frage_typ) VALUES ('"
				.$this->text 	. "', "
				.$this->punkte . ", "
				.$this->pruefung_id . ", '"
				.$this->typ . "')" ;
		
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
	
	private function update() {
		$db = new Db_connection();
		$conn = $db->getConnection();
		
		$query = "UPDATE frage SET"
				." frage_text = '" .$this->text ."'"
				.", punkte = "     .$this->punkte
				.", pruefung_id = " .$this->pruefung_id
				.", frage_typ = '"   .$this->typ
		
				."' WHERE frage_id = " .$this->id;
		
		$result = mysqli_query($conn, $query);
		
		if (is_bool($result) && $result == false) {
			echo $query;
			echo '<br>' .mysqli_error($conn);
			return false;
		} else {
			return true;
		}
	}
	
	public function load($id) {
		$db = new Db_connection();
		
		$query = "SELECT * FROM frage WHERE frage_id = " .$id;
		
		$result = $db->execute($query);
		
		if(!$result || !mysqli_num_rows($result) > 0) {
			// Fehler bei der Datenbankabfrage oder keine Frage mit der Id gefunden
			return false;
		}
			
		$row = mysqli_fetch_assoc($result);
		
		$this->id		= $id;
		$this->text 	= $row["frage_text"];
		$this->punkte   = $row["punkte"];
		$this->pruefung_id  = $row["pruefung_id"];
		$this->typ = $row["frage_typ"];
		
		return true;
	}
	
	public static function loadList($pruefung_id) {
		$db = new Db_connection();
		$conn = $db->getConnection();
		
		$query = "SELECT * FROM frage WHERE pruefung_id = " .$pruefung_id;
		
		$result = mysqli_query($conn, $query);
		
		if(!$result) {
			// Fehler bei der Datenbankabfrage
			return false;
		
		} else {
			$return_array = array();
			//frage_id, frage_text, punkte, pruefung_id, frage_typ
			while ($row = mysqli_fetch_assoc($result)) {
				$f = new Frage(
						$row["frage_id"],
						$row["frage_text"],
						$row["punkte"],
						$row["pruefung_id"],
						$row["frage_typ"]);
				
				array_push($return_array, $f);
			}
			
			return $return_array;
		}
	}
	
	public static function delete($id) {
		$db = new Db_connection();
		$conn = $db->getConnection();
		
		$query = "DELETE FROM frage WHERE frage_id = " .$id;
		
		$result = mysqli_query($conn, $query);
		
		if (is_bool($result) && $result == false) {
			echo $query;
			echo '<br>' .mysqli_error($conn);
			return false;
		} else {
			return true;
		}
	}
	
	public function getId()   		{return $this->id;}
	public function getText() 		{return $this->text;}
	public function getPunkte() 	{return $this->punkte;}
	public function getPruefungId() {return $this->pruefung_id;}
	public function getTyp()		{return $this->typ;}
}