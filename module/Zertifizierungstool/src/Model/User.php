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
	private $email_bestaetigt;
	private $ist_admin;
	private $ist_zertifizierer;
	private $ist_teilnehmer;
	
	public function __construct($benutzername, $passwort, $vorname, $nachname, $geburtsdatum, $strasse, $plz, $ort, $email, $email_bestaetigt, $ist_admin, $ist_zertifizierer, $ist_teilnehmer) {
		$this->benutzername     = $benutzername;
		$this->passwort         = $passwort;
		$this->vorname          = $vorname;
		$this->nachname         = $nachname;
		$this->geburtsdatum     = $geburtsdatum;
		$this->strasse          = $strasse;
		$this->plz              = $plz;
		$this->ort              = $ort;
		$this->email            = $email;
		$this->email_bestaetigt = $email_bestaetigt;
		$this->ist_admin         = $ist_admin;
		$this->ist_zertifizierer = $ist_zertifizierer;
		$this->ist_teilnehmer    = $ist_teilnehmer;
	}
	
	public function __construct1(){
		
	}
	
	public function load($benutzername) {
		$db = new Db_connection();
		
		$query = "SELECT * FROM benutzer where benutzername='".$benutzername."';";
		
		$result = $db->execute($query);
		$return_array = array();
		if (mysqli_num_rows($result) > 0) {
			while ($row = mysqli_fetch_assoc($result)) {
				array_push($return_array, $row);
			}
		} else {
			echo "Kein Ergebnis gefunden.";
		}
		
		foreach ($return_array as $row) {	
			$this->benutzername     = $row['benutzername'];
			$this->vorname		    = $row['vorname'];
			$this->nachname		    = $row['nachname'];
			$this->geburtsdatum     = $row['geburtsdatum'];
			$this->strasse          = $row['strasse'];
			$this->plz              = $row['plz'];
			$this->ort              = $row['ort'];
			$this->email            = $row['email'];
			$this->email_bestaetigt  = $row['email_bestaetigt'];
			$this->ist_admin         = $row['ist_admin'];
			$this->ist_zertifizierer = $row['ist_zertifizierer'];
			$this->ist_teilnehmer    = $row['ist_teilnehmer'];
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
		$result = $db->execute($query);
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
  				.$this->geburtsdatum."', '".$this->strasse."', '".$this->plz."', '".$this->ort."', '".$this->email."', ".$this->email_bestaetigt.", "
  				.$this->ist_admin.", ".$this->ist_zertifizierer.", ".$this->ist_teilnehmer.");";
		
		
		$result = $db->execute($query);
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
		$query = "select * from benutzer where benutzername='".$this->benutzername."' and passwort='".$passwort."' and email_bestaetigt = 1;";
		$result = $db->execute($query);
		if (mysqli_num_rows($result) > 0){
			return true;
		}else {
			return false;
		}
				
	}
	public function registerMail () {
		$empfaenger = $this->email;
		$betreff = "Registrierung Zertifizierungstool";
		$from = "user@zerttool.tk";
		$text = "Sehr geehrte Damen und Herren, bitte bestaetigen Sie folgenden Link: www.zerttool.tk/user/registerbest?benutzer=".$this->benutzername;
		$text = wordwrap($text, 70);
		mail ($empfaenger, $betreff, $text); 
	}
	public function registerbest () {
		$db = new Db_connection();
		$query = "update benutzer set email_bestaetigt=1 where benutzername='".$this->benutzername."';";
		$result = $db->execute($query);
	}
	
}