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
	public function saveNew() {
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
	
	public static function loadList($frage_id) {
		$db = new Db_connection();
		$conn = $db->getConnection();
	
		$query = "SELECT * FROM antowrt WHERE pruefung_id = " .$frage_id;
	
		$result = mysqli_query($conn, $query);
	
		if(!$result) {
			// Fehler bei der Datenbankabfrage
			return false;
	
		} else {
			$return_array = array();
			//frage_id, frage_text, punkte, pruefung_id, frage_typ
			while ($row = mysqli_fetch_assoc($result)) {
				$a = new Antowrt(
						$row["antwort_id"],
						$row["antwort_text"],
						$row["frage_id"],
						$row["status"]);
	
				array_push($return_array, $a);
			}
				
			return $return_array;
		}
	}
}