<?php
namespace Zertifizierungstool\Model;

use Zertifizierungstool\Model\Db_connection;

class User
{
	private $benutzername;
	private $passwort;
	private $vorname;
	private $nachname;
	private $geburtsdatum;
	private $strasse;
	private $plz;
	private $ort;
	private $email;
	private $email_confirmed;
	private $is_admin;
	private $is_zertifizierer;
	private $is_teilnehmer;
	
	public function __construct($benutzername, $passwort, $vorname, $nachname, $geburtsdatum, $strasse, $plz, $ort, $email, $email_confirmed, $is_admin, $is_zertifizierer, $is_teilnehmer) {
		$this->benutzername     = $benutzername;
		$this->passwort         = $passwort;
		$this->vorname          = $vorname;
		$this->nachname         = $nachname;
		$this->geburtsdatum     = $geburtsdatum;
		$this->strasse          = $strasse;
		$this->plz              = $plz;
		$this->ort              = $ort;
		$this->email            = $email;
		$this->email_confirmed  = $email_confirmed;
		$this->is_admin         = $is_admin;
		$this->is_zertifizierer = $is_zertifizierer;
		$this->is_teilnehmer    = $is_teilnehmer;
	}
	
	public function __construct1(){
		
	}
	
	public function load($benutzername) {
		$db = new Db_connection();
		
		$query = "SELECT * FROM benutzer";
		
		$result = $db->execute($query);
		
		foreach ($result as $row) {	
			$this->benutzername     = $row['benutzername'];
			$this->vorname		    = $row['vorname'];
			$this->nachname		    = $row['nachname'];
			$this->geburtsdatum     = $row['geburtsdatum'];
			$this->strasse          = $row['strasse'];
			$this->plz              = $row['plz'];
			$this->ort              = $row['ort'];
			$this->email            = $row['email'];
			$this->email_confirmed  = $row['email_bestaetigt'];
			$this->is_admin         = $row['ist_admin'];
			$this->is_zertifizierer = $row['ist_zertifizierer'];
			$this->is_teilnehmer    = $row['ist_teilnehmer'];
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
	
	public function alreadyExist() {
		$db = new Db_connection();
		
		$query = "Select * from benutzer where benutzername='".$this->benutzername."';";
		$result = $db->executeinsert($query);
		if (mysqli_num_rows($result) == 0) {
			return false;
		}else {
			return true;
		}
	}
	
	public function register() {
		$db = new Db_connection();
		echo $this->benutzername;
		echo $this->vorname;
		$this->passwort = $this->saltPasswort($this->passwort, $this->benutzername);
		
		if (!$this->alreadyExist()){
		$query = "insert into benutzer (benutzername, passwort, vorname, nachname, geburtsdatum, strasse, plz, ort, email, email_bestaetigt, ist_admin, ist_zertifizierer, ist_teilnehmer) values ('"
				.$this->benutzername."', '".$this->passwort."', '".$this->vorname."', '".$this->nachname."', '"
				.$this->geburtsdatum."', '".$this->strasse."', '".$this->plz."', '".$this->ort."', '".$this->email."', ".$this->email_confirmed.", "
				.$this->is_admin.", ".$this->is_zertifizierer.", ".$this->is_teilnehmer.");";
		
		
		$result = $db->executeinsert($query);
	    echo "Registriert";
		}else {
		echo "Benutzer schon registriert";
		}
	}
	public function saltPasswort($passwort, $salt) {
		return hash ('sha256', $passwort . $salt);
	}
	public function passwortControll ($passwort) {
		$db = new Db_connection();
		$passwort = $this->saltPasswort($passwort, $this->benutzername);
		$query = "select * from benutzer where benutzername='".$this->benutzername."' and passwort='".$passwort."';";
		$result = $db->executeinsert($query);
		if (mysqli_num_rows($result) > 0){
			return true;
		}else {
			return false;
		}
				
	}
	
}