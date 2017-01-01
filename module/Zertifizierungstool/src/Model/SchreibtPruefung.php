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
}