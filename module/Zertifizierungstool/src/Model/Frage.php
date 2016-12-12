<?php
namespace Zertifizierungstool\Model;

use Zertifizierungstool\Model\Db_connection;

class Frage {
	
	private $id;
	private $text;
	private $punkte;
	private $pruefung_id;
	private $typ;
	
	public function __construct($id = "", $text = "", $punkte = "", $pruefung_id = "" ) {
		$this->id 		   = $id;
		$this->text  	   = $text;
		$this->punkte 	   = $punkte;
		$this->pruefung_id = $pruefung_id;
	}
	public function saveNew() {
		$db = new Db_connection();
		
		$query = "INSERT INTO frage (frage_id, frage_text, punkte, pruefung_id, frage_typ) VALUES ("
				.$this->id	. ", '"
				.$this->text 	. "', "
				.$this->punkte . ", "
				.$this->pruefung_id . ", '"
				.$this->typ . "')" ;
		
		$result = $db->execute($query);
		
		if(!$result) {
			// Fehler bei der Datenbankabfrage
			return false;
				
		} else {
			// Id des eben eingefügten Datensatzes auslesen und im Objekt setzen
			$this->id = mysqli_insert_id($db->getConnection());
			return true;
		}
	}
	
	public function getId()   		{return $this->id;}
	public function getText() 		{return $this->text;}
	public function getPunkte() 	{return $this->punkte;}
	public function getPruefungId() {return $this->pruefung_id;}
	public function getTyp()		{return $this->typ;}
}