<?php
namespace Zertifizierungstool\Model;

use Zertifizierungstool\Model\Db_connection;

class Antwort {
	
	private $id;
	private $text;
	private $frage_id;
	private $typ;
	private $status;
	
	public function __construct($id = "", $text = "", $frage_id = "", $typ = "", $status = "" ) {
		$this->id 	    = $id;
		$this->text 	= $text;
		$this->frage_id = $frage_id;
		$this->typ 		= $typ;
		$this->status 	= $status;
	}
	public function saveNew() {
		$db = new Db_connection();
		
		$query = "INSERT INTO antwort (antwort_id, antwort_text, frage_id, antworttyp, status) VALUES ("
				.$this->id	. ", '"
				.$this->text 	. "', "
				.$this->frage_id . ", '"
				.$this->typ . "', "
				.$this->status . ") ";
	
		$result = $db->execute($query);
		
		// TODO fehler
	}
}