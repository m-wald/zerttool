<?php
namespace Zertifizierungstool\Controller;


use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zertifizierungstool\Model\User;
use Zertifizierungstool\Auth;


/**
 * Dokumentation
 * 
 * @author
 *
 */
class UserController extends AbstractActionController
{
	
	/*public function loginAction() {
		// Daten aus Request holen
		$benutzername = "waldma";
		$passwort	  = "12345"; 
		
		
		$result = Auth::authenticate($benutzername, $passwort); 
				
	} */
	
	
	public function registerAction()
	{
		if($_SERVER['REQUEST_METHOD'] == 'POST') {
			
			if (User::currentUser()->istAdmin()) {
				
				if ($_REQUEST['rolle']=='a') {
				$user= new User($_REQUEST["benutzername"], $_REQUEST["passwort"], $_REQUEST["vorname"], $_REQUEST["nachname"], $_REQUEST["geburtsdatum"], $_REQUEST["strasse"], $_REQUEST["plz"], $_REQUEST["ort"], $_REQUEST["email"], $_REQUEST["email_bestaetigt"], 1,0,0);
				}
				
				if ($_REQUEST['rolle']=='z') {
					$user= new User($_REQUEST["benutzername"], $_REQUEST["passwort"], $_REQUEST["vorname"], $_REQUEST["nachname"], $_REQUEST["geburtsdatum"], $_REQUEST["strasse"], $_REQUEST["plz"], $_REQUEST["ort"], $_REQUEST["email"], $_REQUEST["email_bestaetigt"], 0,1,0);
				}
				
				if ($_REQUEST['rolle']=='t') {
					$user= new User($_REQUEST["benutzername"], $_REQUEST["passwort"], $_REQUEST["vorname"], $_REQUEST["nachname"], $_REQUEST["geburtsdatum"], $_REQUEST["strasse"], $_REQUEST["plz"], $_REQUEST["ort"], $_REQUEST["email"], $_REQUEST["email_bestaetigt"], 0,0,1);
				}
				
				
			} else {
		
				$user= new User($_REQUEST["benutzername"], $_REQUEST["passwort"], $_REQUEST["vorname"], $_REQUEST["nachname"], $_REQUEST["geburtsdatum"], $_REQUEST["strasse"], $_REQUEST["plz"], $_REQUEST["ort"], $_REQUEST["email"], $_REQUEST["email_bestaetigt"], $_REQUEST["ist_admin"], $_REQUEST["ist_zertifizierer"], $_REQUEST["ist_teilnehmer"]);
			
			}
			
			$result = $user->register();
			
			
			return new ViewModel(['meldung' => $result]);
		}
		
		else {
			if (User::currentUser()->istAdmin()) {
				return new ViewModel(['status' => 'admin']);
			} else {
							
				return new ViewModel();
			}
		}
	}
	
	public function registertestAction()
	{
		$user = new User("michi", "123", "Michael", "Moertl", "1990-11-26", "Nibelungenstrasse","94032", "passau", "moertl05@gw.uni-passau.de", 0, 1, 0, 0);
	
		$user->register();

		return new ViewModel();
	}
	public function anmeldetestAction()
	{
		$user = new User();
		$user->load("michi");
		echo $user->getBenutzername();
		echo $user->saltPasswort("123", $user->getBenutzername());
		$result=$user->passwortControll("123");
		if ($result){
			echo "Erfolgreich";

		}
		else {
			echo "Fehlgeschlagen";
		}
	}
	public function registerbestAction() {
		$user = new User();
		$user->load($_GET['benutzer']);
		$user->registerbest();
		return new ViewModel();
	}
	
	 public function loginAction()
	{
		
		
		if($_SERVER['REQUEST_METHOD'] == 'POST') {
			
			// Vielleicht zuerst die Login-Daten prüfen bevor man alle Daten des Benutzers lädt?
			// Also im Model ne Methode login($benutzername, $passwort), die zuerst die Daten
			// prüft und dann alle Felder befüllt oder load() aufruft
			User::currentUser()->load($_POST['benutzername']);
			
			$result = User::currentUser()->passwortControll($_POST['passwort']);
			if ($result){
				$_SESSION["currentUser"] = serialize(User::currentUser());
				return new ViewModel(['anmeldestatus' => true]);
			}
			else {
				return new ViewModel(['anmeldestatus' => false]);
			}
		}
		 
		
		return new ViewModel();

	}
	
	
	
	/** löscht vorhandene Cookies und beendet anschließend die aktuelle Session */
	
	public function logoutAction() {
		
		
		if (ini_get("session.use_cookies")) {
			$params = session_get_cookie_params();
			setcookie(session_name(), '', time() - 42000, $params["path"],
					$params["domain"], $params["secure"], $params["httponly"]
					);
		}
		
		session_destroy();
		
	}
	
	/** leitet nach erfolgreichem Login auf eine benutzerspezifische Startseite weiter */
	
	public function homeAction() {
		
		
		return new ViewModel(['benutzername' => User::currentUser()->getBenutzername()]); 
		
			
	}
	
	
	/** liest aktuelle Benutzerdaten aus und übergibt diese an ein Formular. Darin können die Daten dann geändert werden und in der Datenbank aktualisiert werden. */
	
	public function changedataAction() {
		
		if($_SERVER['REQUEST_METHOD'] == 'POST') {
			
			User::currentUser()->update($_REQUEST["vorname"], $_REQUEST["nachname"], $_REQUEST["geburtsdatum"], $_REQUEST["strasse"], $_REQUEST["plz"], $_REQUEST["ort"], $_REQUEST["email"]);
			User::currentUser()->load(User::currentUser()->getBenutzername());
			$_SESSION["currentUser"] = serialize(User::currentUser());			
			return new ViewModel(['status' => "erfolgreich"]);
		}
		else {
				return new ViewModel(['benutzerdaten' => array(User::currentUser()->getBenutzername(),User::currentUser()->getVorname(), User::currentUser()->getNachname(), User::currentUser()->getGeburtsdatum(), User::currentUser()->getStrasse(), User::currentUser()->getPLZ(), User::currentUser()->getOrt(), User::currentUser()->getEmail())]);
		}
	
	}
	
	public function changepasswordAction() {
		
		if($_SERVER['REQUEST_METHOD'] == 'POST') {
			if ($_REQUEST['newPasswort1'] == $_REQUEST['newPasswort2']) {
				$result = User::currentUser()->passwortControll($_REQUEST['passwort']);
				if ($result) {
					$resultUpdate = User::currentUser()->updatePassword($_REQUEST['newPasswort1']);
					if ($resultUpdate) {
						return new ViewModel(['status' => 'erfolgreich']);
					}
					else {
						return new ViewModel (['status' => 'datenbankfehler']);
					}
				}
				else {
					return new ViewModel (['status' => 'altes passwort falsch']);
				}
			} else {
				return new ViewModel (['status' => 'ungleiche passwoerter']);
			}
		}else {
			
			return new ViewModel();
			
		}
		
	}
	
	public function passwordforgottenAction() {
		
		if (isset($_GET['benutzer'])) {
			
			return new ViewModel(['benutzer' => $_GET['benutzer']]);
			
		} else if (isset($_POST['benutzermail'])){
			
			$user = new User();
			$user->load($_POST['benutzermail']);
			$user->passwordForgottenMail();
			return new ViewModel(['status' => 'mail']);
			
		} else if (isset($_POST['newPasswort1'])) {
			
			if ($_POST['newPasswort1']==$_POST['newPasswort2']) {
				$user = new User();
				$user->load($_POST['benutzer']);
				$user->updatePassword($_POST['newPasswort1']);
				return new ViewModel(['status'=>'erfolgreich']);
			}
			else {
				return new ViewModel(['status'=>'ungleiche passwoerter', 'benutzer'=>$_POST['benutzer']]);
			}
			
		} else {
			
			return new ViewModel();
			
		}
		

		
	}
	
	public function loeschenAction() {
		
	}
}