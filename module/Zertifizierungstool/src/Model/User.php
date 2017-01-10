<?php
namespace Zertifizierungstool\Model;

use Zertifizierungstool\Model\Db_connection;

/**
 * @author Michael
 *
 */
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
	
	/** Singleton-Instanz, repräsentiert den aktuellen Benutzer */
	private static $currentUser;
	
	/**
	 * Liefert den aktuellen Benutzer der Seite zurück
	 */
	public static function currentUser() {
		if (!isset(self::$currentUser)) {
			if (isset($_SESSION["currentUser"])) {
				self::$currentUser = unserialize($_SESSION["currentUser"]);
			}else {
 				self::$currentUser = new User();
				
			}
			
		}
		return self::$currentUser;	
	}
	
	/**
	 * Setzt den aktuellen Benutzer
	 */
	public static function setCurrentUser() {
		self::$currentUser = $this;
	}
	
	public function __construct($benutzername="", $passwort="", $vorname="", $nachname="", $geburtsdatum="", $strasse="", $plz="", $ort="", $email="", $email_bestaetigt="", $ist_admin="", $ist_zertifizierer="", $ist_teilnehmer="") {
		
		$db = new Db_connection();
		
		$mysqli= $db->getConnection();
		
		$this->benutzername     = $mysqli->real_escape_string($benutzername);
		$this->passwort         = $mysqli->real_escape_string($passwort);
		$this->vorname          = $mysqli->real_escape_string($vorname);
		$this->nachname         = $mysqli->real_escape_string($nachname);
		$this->geburtsdatum     = $mysqli->real_escape_string($geburtsdatum);
		$this->strasse          = $mysqli->real_escape_string($strasse);
		$this->plz              = $mysqli->real_escape_string($plz);
		$this->ort              = $mysqli->real_escape_string($ort);
		$this->email            = $mysqli->real_escape_string($email);
		$this->email_bestaetigt = $mysqli->real_escape_string($email_bestaetigt);
		$this->ist_admin         = $ist_admin;
		$this->ist_zertifizierer = $ist_zertifizierer;
		$this->ist_teilnehmer    = $ist_teilnehmer;
	}
	
	public function __construct1(){
		
	}
	/**
	 * Lädt die Daten des Benutzers des übergebenen Benutzernamens
	 * @param Benutzername des zu ladenden Benutzers
	 * 
	 */
	public function load($benutzername) {
		$db = new Db_connection();
		
		$mysqli= $db->getConnection();
		
		$benutzername = $mysqli->real_escape_string($benutzername);
		
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
	
	/**
	 * Lädt die Daten des Benutzers der übergebenen E-Mailadresse
	 * @param String E-Mailadresse des zu ladenden Benutzers
	 * @return boolean true falls der Benutzer gefunden wurde
	 * 				   false falls Benutzer mit der übergebenen Mailadresse nicht gefunden wurde
	 */
	public function load_via_email ($email) {
		$db = new Db_connection();
		
		$mysqli= $db->getConnection();
		
		$email = $mysqli->real_escape_string($email);
		
		
		$query = "SELECT * FROM benutzer where email='".$email."';";
		
		$result = $db->execute($query);
		$return_array = array();
		if (mysqli_num_rows($result) > 0) {
			while ($row = mysqli_fetch_assoc($result)) {
				array_push($return_array, $row);
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
		return true;
	}
		else {
			return false;
		}
		
		
		
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
	
	public function getGeburtsdatum() {
		return $this->geburtsdatum; 
	}
	
	public function getStrasse() {
		return $this->strasse;
	}
	
	public function getPLZ() {
		return $this->plz;
	}
	
	public function getOrt() {
		return $this->ort;
	}
	
	public function getEmail() {
		return $this->email;
	}
	
	
	public function istAdmin() {
		if ($this->ist_admin==1){
			return true;
		}
		else{
			return false;
		}
	}
	
	public function istZertifizierer() {
		if ($this->ist_zertifizierer==1){
			return true;
		}
		else{
			return false;
		}
	}
	
	public function istTeilnehmer() {
		if ($this->ist_teilnehmer==1){
			return true;
		}
		else{
			return false;
		}
	}
	

	/**
	 * Prüft ob ein Benutzer mit dem Nutzernamen dieses Objektes in der Datenbank schon
	 * existiert.
	 * @return Gibt true zurück falls er existiert und false falls nicht.
	 */
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
	
	/**
	 * Prüft ob ein Benutzer mit der Mailadresse dieses Objektes in der Datenbank schon
	 * existiert.
	 * @return Gibt true zurück falls er existiert und false falls nicht.
	 */
	public function mailAlreadyExist($email=null) {
		$db = new Db_connection();
		
		if ($email==null) {
			$query = "Select * from benutzer where email='".$this->email."';";
		}else {
			$query = "Select * from benutzer where email='".$email."';";
		}
		$result = $db->execute($query);
		if (mysqli_num_rows($result) == 0) {
			return false;
		}else {
			return true;
		}
	}
	
	
	/**
	 * Schreibt einen neuen Datensatz eines Benutzers in die Datenbank. Dabei wird
	 * vorab geprüft, ob ein Datensatz mit dem Benutzernamen dieses Objektes schon 
	 * existiert.
	 * @return Bei Erfolg oder nicht Erfolg wird ein entsprechender String zurückgeliefert,
	 * der an die View übergeben werden kann.
	 */
	public function register($invite) {
		$db = new Db_connection();
		
		$this->passwort = $this->saltPasswort($this->passwort);
		
		//Formatänderung des Geburtsdatums in Jahr-Monat-Tag (für Datenbank wichtig)
		
		$date = new \DateTime($this->geburtsdatum);
		$this->geburtsdatum=$date->format('Y-m-d');
		
		if (!$this->alreadyExist()){
			if (!$this->mailAlreadyExist()) {
		
			$query = "insert into benutzer (benutzername, passwort, vorname, nachname, geburtsdatum, strasse, plz, ort, email, email_bestaetigt, ist_admin, ist_zertifizierer, ist_teilnehmer) values ('"
	  				.$this->benutzername."', '".$this->passwort."', '".$this->vorname."', '".$this->nachname."', '"
	  				.$this->geburtsdatum."', '".$this->strasse."', '".$this->plz."', '".$this->ort."', '".$this->email."', ".$this->email_bestaetigt.", "
	  				.$this->ist_admin.", ".$this->ist_zertifizierer.", ".$this->ist_teilnehmer.");";
			
			
			$result = $db->execute($query);
			
			if ($invite){
				
				$this->registerMailInvite();
				
			}else {
			
				$m = $this->registerMail();
			
			}
			return "Daten wurden erfolgreich gespeichert. Sie erhalten in K&uuml;rze eine E-Mail. Bitte folgen Sie dem darin enthaltenen Link, um Ihre Registrierung abzuschlie&szlig;en.";
		    
		    
			}else {
			
		return "E-Mail-Adresse ist bereits vergeben! Bitte andere E-Mail-Adresse benutzen!";
		
		}
	} else {
		
		return "Benutzername ist bereits vergeben! Bitte anderen Namen ausw&auml;hlen!";
	}
}
	/**
	 * Verschlüsselt ein Passwort mit bcrypt Algorithmus
	 * @param Passwort in clearform
	 * @param zusätzlicher Saltwert, eigentlich unnötig, da Password_hash selbs salted
	 * @return Verschlüsselter Hashwert des Passworts
	 */
	public function saltPasswort($passwort) {
		return password_hash ($passwort, PASSWORD_DEFAULT);
	}
	/**
	 * Überprüft ob der in der DB gespeicherte Hash des Passworts mit dem Übergebenen
	 * Passwort übereinstimmt. 
	 * @param Eingegebenes Passwort in cleartext
	 * @return Bei übereinstimmung true ansonsten false
	 */
	public function passwortControll ($passwort) {
		$db = new Db_connection();
		
		$mysqli= $db->getConnection();
		
		$passwort = $mysqli->real_escape_string($passwort);
		
		$query = "select passwort from benutzer where benutzername='".$this->benutzername."' and email_bestaetigt = 1;";
		$result = $db->execute($query);
		if (mysqli_num_rows($result) == 0){
			return false;
		}else {
			$row = mysqli_fetch_assoc($result);
			$pruef = password_verify($passwort, $row['passwort']);
			if ($pruef)
				return true;
			else
				return false;
		}
				
	}
	/**
	 * Versendet an die Mailadresse des Objektes eine Mail, zum bestätigen des Accounts
	 * Dazu wird ein Link mit der Route der entsprechenden Action mit zusätzlichem Parameter
	 * des Benutzernames dieses Objektes erstellt
	 */
	public function registerMail () {
		$empfaenger = $this->email;
		$betreff = "Registrierung Zertifizierungstool";
		$from = "user@zerttool.tk";
		$text = "Hallo ".$this->vorname." ".$this->nachname.",\n\nbitte bestaetigen Sie folgenden Link:\n\n https://132.231.36.205/user/registerbest?benutzer=".$this->benutzername;
		$text = wordwrap($text, 70);
		mail ($empfaenger, $betreff, $text); 
	}
	
	/**
	 * Versendet an die Mailadresse des Objektes eine Mail, zum bestätigen des Accounts falls zuvor der Teilnehmer eingeladen wurde
	 * Dazu wird ein Link mit der Route der entsprechenden Action mit zusätzlichem Parameter
	 * des Benutzernames dieses Objektes und der Kurs_id zu der der Teilnehmer eingeladen wurde erstellt
	 */
	public function registerMailInvite() {
		
		$empfaenger = $this->email;
		$betreff = "Registrierung Zertifizierungstool";
		$text = "Hallo ".$this->vorname." ".$this->nachname.",\n\nbitte bestaetigen Sie folgenden Link:\n\n https://132.231.36.205/user/registerbest?benutzer=".$this->benutzername."&kurs_id=".$_SESSION['kurs'];
		$text = wordwrap($text, 70);
		mail ($empfaenger, $betreff, $text);
		
	}
	
	/**
	 * Versendet an die Mailadresse des Objektes eine Mail, falls durch den Passwort-Vergessen-Link
	 * angefordert. Zusätzlich wird eine zufällige Prüfzahl generiert, die in der Datenbank gespeichert
	 * wird. Dies soll zusätzliche Sicherheit gewähren
	 * Prüfzahl wird in Link als Parameter übergeben
	 * 
	 */
	
	public function passwordForgottenMail() {
		
		$db = new Db_connection();
		
		$pruefzahl = mt_rand(0,999999999);
		$query = "update benutzer set pruefzahl =".$pruefzahl." where benutzername='".$this->benutzername."';";
		$result = $db->execute($query);
		
		$empfaenger = $this->email;
		$betreff = "Neues Passwort angefordert für Zertifizierungstool";
		$from = "user@zerttool.tk";
		$text = "Hallo ".$this->vorname." ".$this->nachname.",\n\n wenn Sie ein neues Passwort angefordert haben, folgenden Sie bitte diesem Link:\n\n https://132.231.36.205/user/passwordforgotten?benutzer=".$this->benutzername."&pruefzahl=".$pruefzahl;
		$text = wordwrap($text, 70);
		mail ($empfaenger, $betreff, $text);
				
	}
	/**
	 * Setzt den boolschen Wert email_bestaetigt in der DB von 0 auf 1
	 */
	public function registerbest () {
		$db = new Db_connection();
		$query = "update benutzer set email_bestaetigt=1 where benutzername='".$this->benutzername."';";
		$result = $db->execute($query);
	}
	
	
	/**
	 * Ändert die Werte der Benutzertabelle mit den Übergebenen Werten
	 * @param String $vorname
	 * @param String $nachname
	 * @param String $geburtsdatum
	 * @param String $strasse
	 * @param String $plz
	 * @param String $ort
	 * @param String $email
	 * @return boolean true falls erfolgreich, false falls Datenbankfehler oder E-Mail bereits vorhanden
	 */
	public function update($vorname, $nachname, $geburtsdatum, $strasse, $plz, $ort, $email) {
		$db = new Db_connection();
		
		$mysqli= $db->getConnection();
		
		$vorname = $mysqli->real_escape_string($vorname);
		$nachname = $mysqli->real_escape_string($nachname);
		$geburtsdatum = $mysqli->real_escape_string($geburtsdatum);
		$strasse = $mysqli->real_escape_string($strasse);
		$plz = $mysqli->real_escape_string($plz);
		$ort = $mysqli->real_escape_string($ort);
		$email = $mysqli->real_escape_string($email);
		
		if ($this->mailAlreadyExist($email) && $this->email!=$email){
			return false;
		}else {
		
			$date = new \DateTime($geburtsdatum);
			$geburtsdatum=$date->format('Y-m-d');
			$query = "update benutzer set
					vorname='".$vorname."',
					nachname='".$nachname."',
					geburtsdatum='".$geburtsdatum."',
					strasse='".$strasse."',
					plz='".$plz."',
					ort='".$ort."',
					email='".$email."' where benutzername='".$this->benutzername."';";
		
			$result=$db->execute($query);
			return $result;
		}
	}
	
	/**
	 * Ändert das Passwort des Benutzers in der Datenbank. Passwort wird verschlüsselt.
	 * @param String $passwort Neues Passwort
	 * @return boolean true falls erfolgreich, false falls Datenbankfehler
	 */
	public function  updatePassword($passwort) {
		
			$db = new Db_connection();
			
			$mysqli = $db->getConnection();
			$passwort = $mysqli->real_escape_string($passwort);
			
		
			$passwort = $this->saltPasswort($passwort);
			
			$query = "update benutzer set passwort = '".$passwort."', pruefzahl=NULL where benutzername ='".$this->benutzername."';";
			$result = $db->execute($query);
			return $result;

			
	
	}
	
	/**
	 * Überprüft übergebene Prüfzahl mit Prüfzahl in Datenbank des Benutzers
	 * @param number $pruefzahl Zu überprüfende Prüfzahl
	 * @return boolean true falls Prüfzahl übereinstimmen, false falls nicht
	 */
	public function  check_pruefzahl($pruefzahl) {
	
		$db = new Db_connection();
		
		$mysqli = $db->getConnection();
		$pruefzahl = $mysqli->real_escape_string($pruefzahl);
		
		$query = "select * from benutzer where benutzername='".$this->benutzername."' and pruefzahl=".$pruefzahl.";";
		$result = $db->execute($query);
		if (mysqli_num_rows($result)<1){
			return false;
		}
		else {
			return true;
		}
	}
	
}