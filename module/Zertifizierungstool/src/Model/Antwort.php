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
	
	public function update() {
		$db = new Db_connection();
		$conn = $db->getConnection();
	
		$query = "UPDATE antwort SET"
				." antwort_text = '" .$this->text ."'"
				.", frage_id = " .$this->frage_id
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