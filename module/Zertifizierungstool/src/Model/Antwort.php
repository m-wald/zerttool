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
}