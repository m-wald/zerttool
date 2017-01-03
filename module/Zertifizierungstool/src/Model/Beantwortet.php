<?php
namespace Zertifizierungstool\Model;

use Zertifizierungstool\Model\Db_connection;

/**
 * @author Martin
 *
 */
class Beantwortet2 {
	
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

	}
}