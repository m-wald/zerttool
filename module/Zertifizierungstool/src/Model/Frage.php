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
	public function saveNew() {
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
			$this->id = mysqli_insert_id($db->getConnection());
			return true;
		}
	}
	
	public static function loadList($pruefung_id) {
		$db = new Db_connection();
		
		$query = "SELECT * FROM frage WHERE pruefung_id = " .$pruefung_id;
		
		$result = $db->execute($query);
		
		if($result == false) {
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
	
	public function getId()   		{return $this->id;}
	public function getText() 		{return $this->text;}
	public function getPunkte() 	{return $this->punkte;}
	public function getPruefungId() {return $this->pruefung_id;}
	public function getTyp()		{return $this->typ;}
}