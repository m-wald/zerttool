	<?php
namespace Zertifizierungstool\Model;

use Zertifizierungstool\Model\Db_connection;

/**
 * @author Martin
 *
 */
class Schreibt_pruefung {
	
	private $id;		
	private $pruefung_id;
	private $benutzername;
	private $zeitpunkt;
	private $bestanden;

	public function __construct($id = "", $pruefung_id = "", $benutzername = "", $zeitpunkt = "", $bestanden = "") {
		$this->id			= $id;
		$this->pruefung_id	= $pruefung_id;
		$this->benutzername	= $benutzername;
		$this->zeitpunkt    = $zeitpunkt;
		$this->bestanden 	= $bestanden;
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
		
		// TODO lieber mit $this...
		$query = "INSERT INTO schreibt_pruefung (pruefung_id, benutzername, zeitpunkt, bestanden) VALUES ("
					.$this->pruefung_id	. ", '"
					.$this->benutzername. "', '"
					.$this->zeitpunkt ."', "
					.$this->bestanden .")" ;
		
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
	public function update() {
		
	}
	
	/**
	 * Befüllt die Attribute des aktuellen Objekts mit den entsprechenden Daten aus der Datenbank.
	 * 
	 * @param $id Die id des Eintrags in der Datenbank, der geladen werden soll.
	 * @return boolean false, falls ein Fehler aufgetreten ist. Sonst true.
	 */
	public function load($id) {
		$db = new Db_connection();
		
		$query = "SELECT * FROM schreibt_pruefung WHERE schreibt_pruefung_id = " .$id;
		
		$result = $db->execute($query);
		
		if(!$result || mysqli_num_rows($result) != 1) {
			// Fehler bei der Datenbankabfrage oder keine Prüfung mit der Id gefunden
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
	
	// Getter methods
	public function getId() 	  		{return $this->id;}	
	public function getPruefungId() 	{return $this->pruefung_id;}
	public function getBenutzername()	{return $this->benutzername;}
	public function getZeitpunkt()   	{return $this->zeitpunkt;}
	public function getBestanden() 		{return $this->bestanden;}
}