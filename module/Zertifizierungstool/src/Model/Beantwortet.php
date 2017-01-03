	<?php
namespace Zertifizierungstool\Model;

use Zertifizierungstool\Model\Db_connection;

/**
 * @author Martin
 *
 */
class Beantwortet {
	
	private $id;		
	private $schreibt_pruefung_id;
	private $antwort_id;
	private $status;

	public function __construct($id = "", $schreibt_pruefung_id = "", $antwort_id = "", $status = "") {
		$this->id			= $id;
		$this->schreibt_pruefung_id	= $schreibt_pruefung_id;
		$this->antwort_id	= $antwort_id;
		$this->status    	= $status;
	}
	
	/**
	 * Fügt die Daten des aktuellen Objekts als neuen Datensatz in der Datenbank.
	 * Setzt auch die Id des Objekts mit dem Wert, der von der DB automatisch zugeteilt wurde.
	 * 
	 * @return boolean false, falls ein Fehler aufgetreten ist. Sonst true.
	 */
	public function saveNew() {
		/*
		$db = new Db_connection();
		$conn = $db->getConnection();
		
		$query = "INSERT INTO beantwortet (schreibt_pruefung_id, antwort_id, status) VALUES ("
					.$this->schreibt_pruefung_id	. ", "
					.$this->antwort_id	. ", "
					.$this->status.")" ;
		
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
		*/
	}
	
	/**
	 * Setzt in der DB "True" als abgebene Antwort
	 */
	public static function setTrue($schreibt_pruefung, $antwort) {
		$db = new Db_connection();
		$conn = $db->getConnection();
		
		$query = "UPDATE beantwortet SET status = 1 WHERE "
					."schreibt_pruefung_id = " .$schreibt_pruefung
					."AND antwort_id = "	   .$antwort;
		
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
	 * Setzt in der DB "False" als abgebene Antwort
	 */
	public static function setFalse($schreibt_pruefung, $antwort) {
		$db = new Db_connection();
		$conn = $db->getConnection();
	
		$query = "UPDATE beantwortet SET status = 1 WHERE "
				."schreibt_pruefung_id = " .$schreibt_pruefung
				."AND antwort_id = "	   .$antwort;
	
				$result = mysqli_query($conn, $query);
					
				if (is_bool($result) && $result == false) {
					echo $query;
					echo '<br>' .mysqli_error($conn);
					return false;
				} else {
					return true;
				}
	}
}