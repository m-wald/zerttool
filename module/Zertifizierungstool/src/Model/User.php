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
		
		$query = "SELECT * FROM benutzer";
		
		$result = $db->execute($query);
		
		foreach ($result as $row) {	
			$this->benutzername = $row['benutzername'];
			$this->vorname		= $row['vorname'];
			$this->nachname		= $row['nachname'];
		}
		
		// Fehler prüfen
	}
	
	public function getBenutzername() {
		return $this->benutzername;
	}
	
	public function getVorname() {
		return $this->vorname;
	}
	
	public function getNachname() {
		return $this->nachname;
	}
}