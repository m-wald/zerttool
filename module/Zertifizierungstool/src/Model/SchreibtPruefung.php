<?php
namespace Zertifizierungstool\Model;

use Zertifizierungstool\Model\Db_connection;

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
		//$db = new Db_connection();
		//$conn = $db->getConnection();
	
		$datetime = new \DateTime();
		$datetime->format('U = Y-m-d H:i:s');
		
		
		$query = "INSERT INTO schreibt_pruefung (pruefung_id, benutzername, zeitpunkt, bestanden) VALUES ("
					.$this->pruefung_id .", '"
					.User::currentUser()->getBenutzername() ."', '"
					.strftime('Y-m-d H:i:s') ."'";
		/*
		$query = "INSERT INTO schreibt_pruefung (pruefung_id, benutzername, zeitpunkt, bestanden) VALUES ("
				.$this->pruefung_id	. ", '"
				.$this->benutzername. "', '"
				.$this->zeitpunkt ."', "
				.$this->bestanden .")" ;
		
		/*
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
}