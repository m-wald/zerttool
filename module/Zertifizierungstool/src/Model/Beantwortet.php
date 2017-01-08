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
	 * F�gt die Daten des aktuellen Objekts als neuen Datensatz in der Datenbank.
	 * Setzt auch die Id des Objekts mit dem Wert, der von der DB automatisch zugeteilt wurde.
	 * 
	 * @return boolean false, falls ein Fehler aufgetreten ist. Sonst true.
	 */
	public function saveNew() {
		$db = new Db_connection();
		$conn = $db->getConnection();
		
		$query = "INSERT INTO beantwortet (schreibt_pruefung_id, antwort_id, beantwortet_status) VALUES ("
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
			// Id des eben eingef�gten Datensatzes auslesen und im Objekt setzen
			$this->id = mysqli_insert_id($conn);
			return true;
		}
	}
	
	public function load($schreibt_pruefung, $antwort) {
		$db = new Db_connection();
	
		$query = "SELECT * FROM beantwortet WHERE "
					."schreibt_pruefung_id = " .$schreibt_pruefung
					." AND antwort_id = "	   .$antwort;
	
		$result = $db->execute($query);
	
		if(!$result || !mysqli_num_rows($result) > 0) {
			// Fehler bei der Datenbankabfrage oder keine Frage mit der Id gefunden
			return false;
		}
			
		$row = mysqli_fetch_assoc($result);
	
		$this->id		= $row["beantwortet_id"];
		$this->schreibt_pruefung_id 	= $row["schreibt_pruefung_id"];
		$this->antwort_id  = $row["antwort_id"];
		$this->status = $row["status"];
	
		return true;
	}
	
	//TODO zusammenfassen
	/**
	 * Setzt in der DB "True" als abgebene Antwort
	 */
	public static function setTrue($schreibt_pruefung, $antwort) {
		$db = new Db_connection();
		$conn = $db->getConnection();
		
		$query = "UPDATE beantwortet SET status = 1 WHERE "
					."schreibt_pruefung_id = " .$schreibt_pruefung
					." AND antwort_id = "	   .$antwort;
				
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
					." AND antwort_id = "	   .$antwort;
									
		$result = mysqli_query($conn, $query);
									
		if (is_bool($result) && $result == false) {
			echo $query;
			echo '<br>' .mysqli_error($conn);
			return false;
		} else {
			return true;
		}
	}
	
	public function getStatus() {
		return $this->status;
	}
}