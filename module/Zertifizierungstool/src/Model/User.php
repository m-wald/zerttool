<?php
namespace Zertifizierungstool\Model;

use Zertifizierungstool\Model\Db_connection;

class User
{
	private $benutzername;
	private $passwort;
	private $vorname;
	private $nachname;
	
	public function load($benutzername) {
		$db = new Db_connection();
		
		$query = "SELECT * FROM benutzer WHERE benutzername = " . $benutzername;
		
		$db->execute($query);
		$row = $db->nextRow();
		$this->benutzername = $row['benutzername'];
		$this->vorname		= $row['vorname'];
		$this->nachname		= $row['nachname'];
		// Fehler prüfen
	}
	
	public function getBenutzername($param) {
		return $this->benutzername;
	}
	
	public function getVorname($param) {
		return $this->vorname;
	}
	
	public function getNachname($param) {
		return $this->nachname;
	}
}