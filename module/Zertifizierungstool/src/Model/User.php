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
		
		$result = $db->execute($query);
		$this->benutzername = $result['benutzername'];
		$this->vorname		= $result['vorname'];
		$this->nachname		= $result['nachname'];
		// Fehler prüfen
	}
}