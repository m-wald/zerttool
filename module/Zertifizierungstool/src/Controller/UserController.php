<?php
namespace Zertifizierungstool\Controller;


use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zertifizierungstool\Model\User;
use Zertifizierungstool\Model\Kurs;

/**
 * Dokumentation
 * 
 * @author
 *
 */
class UserController extends AbstractActionController
{
	/**
	 * Author: Michael´s
	 * Registrierung eines Benutzers. Admin kann Admin, Zertifizierer und Teilnehmer anlegen.
	 * Nicht registrierte Benutzer können einen Teilnehmer-Account erstellen.
	 */
	public function registerAction()
	{
		
		if(User::currentUser()->getBenutzername()!=NULL && User::currentUser()->istZertifizierer()){
			header("refresh:0; url= /");
			exit;
		}
		else{
		
		$user = new User();
		
		if($_SERVER['REQUEST_METHOD'] == 'POST') {
			
			

			//Falls ein Admin einen Benutzer anlegt wird hier geprüft, welche Rolle vergeben wurde
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
				//Falls Teilnehmer sich selbst registriert
				$user= new User($_REQUEST["benutzername"], $_REQUEST["passwort"], $_REQUEST["vorname"], $_REQUEST["nachname"], $_REQUEST["geburtsdatum"], $_REQUEST["strasse"], $_REQUEST["plz"], $_REQUEST["ort"], $_REQUEST["email"], $_REQUEST["email_bestaetigt"], $_REQUEST["ist_admin"], $_REQUEST["ist_zertifizierer"], $_REQUEST["ist_teilnehmer"]);
			
			}
				
			//Fehlerausgabe bei falschen bestätigtem Passwort
			
			//Überprüfung ob Passwort und bestätigtes Passwort übereinstimmen
			if ($_REQUEST['passwort']==$_REQUEST['passwort2']) {
				$currentdate = date('Y-m-d');
				if((Kurs::validateDate($_REQUEST["geburtsdatum"], 'Y-m-d') || (Kurs::validateDate($_REQUEST["geburtsdatum"], 'd.m.Y'))) && (strtotime($_REQUEST["geburtsdatum"])<=strtotime($currentdate))) {
					
					
					//Bei Registrierung über invite, wird ein angepasster Registrierungslink
					//generiert (Kurs_id wird mit übergeben) 
					if (isset ($_REQUEST['invitemail'])) {
						
						$result = $user->register(true);
					}
					else {
					
						$result = $user->register(false);
						
					}
					
					return new ViewModel(['meldung' => $result, 'user' => $user]);
				
			
			}
			
				else {
					
					if (User::currentUser()->istAdmin()) {
						//Falls Admin wird dies wieder an die View übergeben, email falls Registrierung über invitemail
						return new ViewModel(['datum' => 'datum', 'status' => 'admin', 'email'=> $_REQUEST['email'], 'user' => $user]);
					
					} else {
						return new ViewModel(['datum' => 'datum', 'email'=> $_REQUEST['email'], 'user' => $user]);
					}
					
				}
			
			}
		
			else {
				if (User::currentUser()->istAdmin()) {
					//Falls Admin wird dies wieder an die View übergeben, email falls Registrierung über invitemail
					return new ViewModel(['pw_kontrolle' => 'ungleiche passwoerter','status' => 'admin', 'email'=> $_REQUEST['email'], 'user' => $user]);
						
				} else {
					if (isset($_REQUEST['invitemail'])) {
						return new ViewModel(['pw_kontrolle' => 'ungleiche passwoerter', 'emailinvitation'=> $_REQUEST['email'], 'user' => $user]);
					}else {
						return new ViewModel(['pw_kontrolle' => 'ungleiche passwoerter', 'email'=> $_REQUEST['email'], 'user' => $user]);
					}
				}
			}
		}
		//Ausgabe des Formulars, bei erstem Aufruf der Seite
		else {
			if (User::currentUser()->istAdmin()) {
				return new ViewModel(['status' => 'admin', 'user' => $user]);
			} else {
				
				//Falls per invite Seite aufgerufen wird, wird die Mailadresse vorbelegt
				if (isset($_GET['inviteuser'])) {
					
					return new ViewModel(['emailinvitation'=> $_GET['inviteuser'], 'user' => $user]);
					
				}else {
				
				
				
				 return new ViewModel(['user' => $user]);
				}
			}
		}
		}
	}
	
	
	/**
	 * Author Michael´s
	 * Setzt den boolschen Wert "bestätigt" des Benutzers von 0 auf 1 
	 */
	public function registerbestAction() {
		if (isset ($_GET['benutzer'])) {
			$user = new User();
			$user->load($_GET['benutzer']);
			if (!$user->istBestaetigt()){
				if ($user->check_pruefzahl($_GET['pruefzahl'])){
					$user->registerbest();
					if (isset($_GET['kurs_id'])) {
						
						return new ViewModel(['benutzername'=>$user->getBenutzername(), 'kurs_id'=>$_GET['kurs_id']]);
						
					}
					return new ViewModel();
				}
				else {
					return new ViewModel(['pruefzahl'=>'']);
				}
			}else {
				if (isset($_GET['kurs_id'])) {
					return new ViewModel(['benutzername'=>$user->getBenutzername(), 'kurs_id'=>$_GET['kurs_id'], 'kursbeitritt' => '']);
				}else {
					return new ViewModel(['bestaetigt'=>'']);
				}
			}
		} else {
			header("refresh:0; url=/user/login");
			exit;
		}
	}
	
	
	/** Nutzerdaten werden überprüft, bei Richtigkeit wird ein "currentUser" mit den Benutzerdaten befüllt und 
	 * eine Session erstellt  */
	
	 public function loginAction()
	{
		
		if(User::currentUser()->getBenutzername()!=NULL){
			header("refresh:0; url= /");
			exit;
		}
		else{
		
		if($_SERVER['REQUEST_METHOD'] == 'POST') {
						
			$user = new User();
			
			$load_status=$user->load($_POST['benutzername']);
			
			if ($load_status){

				$result = $user->passwortControll($_POST['passwort']);
				if ($result){
					User::currentUser()->load($_POST['benutzername']);
					$_SESSION["currentUser"] = serialize(User::currentUser());
					if (isset ($_POST['inviteuser'])) {
						
						header("refresh:0; url=/kurs/enterkurs?benutzername=".$_POST['inviteuser']."&kurs_id=".$_SESSION['kurs']);
						exit;
					
					
					
					}
					return new ViewModel(['anmeldestatus' => true]);
				}
				else {
					if (isset($_POST['inviteuser'])){
						
						return new ViewModel(['anmeldestatus' => false, 'inviteuser'=>$_POST['inviteuser']]);
						
					}else {
					
						return new ViewModel(['anmeldestatus' => false]);
					}
				}
			}else {
				if (isset($_POST['inviteuser'])){
				
					return new ViewModel(['benutzer_nicht_gefunden' => '', 'inviteuser'=>$_POST['inviteuser']]);
				
				}else {
				
					return new ViewModel(['benutzer_nicht_gefunden' => '']);
				}
			}
		}
		 
		if (isset($_GET['inviteuser'])) {
			
			return new ViewModel(['inviteuser'=>$_GET['inviteuser']]);
			
		}
		
		return new ViewModel();
		
		}

	}
	
	
	
	/** löscht vorhandene Cookies und beendet anschließend die aktuelle Session */
	
	public function logoutAction() {
		
		if(User::currentUser()->getBenutzername()==NULL){
			header("refresh:0; url= /user/login");
			exit;
		
		}
		else {
			
			
		if (ini_get("session.use_cookies")) {
			$params = session_get_cookie_params();
			setcookie(session_name(), '', time() - 42000, $params["path"],
					$params["domain"], $params["secure"], $params["httponly"]
					);
		}
		session_destroy();
		
		return new ViewModel();
		}
	}
	
	
	
	
	/** liest aktuelle Benutzerdaten aus und übergibt diese an ein Formular. Darin können die Daten dann geändert werden und in der Datenbank aktualisiert werden. */
	
	public function changedataAction() {
		
		if(User::currentUser()->getBenutzername()==NULL){
			header("refresh:0; url= /user/login");
			exit;
		}
		
		
		else {
			if($_SERVER['REQUEST_METHOD'] == 'POST') {
				$currentdate = date('Y-m-d');
				if ((Kurs::validateDate($_REQUEST["geburtsdatum"], 'Y-m-d') || (Kurs::validateDate($_REQUEST["geburtsdatum"], 'd.m.Y'))) && (strtotime($_REQUEST["geburtsdatum"])<=strtotime($currentdate))) {
				
					$result = User::currentUser()->update($_REQUEST["vorname"], $_REQUEST["nachname"], $_REQUEST["geburtsdatum"], $_REQUEST["strasse"], $_REQUEST["plz"], $_REQUEST["ort"], $_REQUEST["email"]);
					if ($result){
						User::currentUser()->load(User::currentUser()->getBenutzername());
						$_SESSION["currentUser"] = serialize(User::currentUser());			
						return new ViewModel(['status' => "erfolgreich"]);
					}else {
						
						return new ViewModel(['benutzerdaten' => array(User::currentUser()->getBenutzername(),User::currentUser()->getVorname(), User::currentUser()->getNachname(), User::currentUser()->getGeburtsdatum(), User::currentUser()->getStrasse(), User::currentUser()->getPLZ(), User::currentUser()->getOrt(), User::currentUser()->getEmail()), 'status' => 'email']);
						
					}
				} else {
					return new ViewModel(['benutzerdaten' => array(User::currentUser()->getBenutzername(),User::currentUser()->getVorname(), User::currentUser()->getNachname(), User::currentUser()->getGeburtsdatum(), User::currentUser()->getStrasse(), User::currentUser()->getPLZ(), User::currentUser()->getOrt(), User::currentUser()->getEmail()), 'status' => 'invaliddate']);
				}
			}	
			else {
					return new ViewModel(['benutzerdaten' => array(User::currentUser()->getBenutzername(),User::currentUser()->getVorname(), User::currentUser()->getNachname(), User::currentUser()->getGeburtsdatum(), User::currentUser()->getStrasse(), User::currentUser()->getPLZ(), User::currentUser()->getOrt(), User::currentUser()->getEmail())]);
			}
		}
	
	}
	
	/**
	 * Änderung des Passworts, altes Passwort wird benötigt
	 */
	public function changepasswordAction() {
		
		if(User::currentUser()->getBenutzername()==NULL){
			header("refresh:0; url= /user/login");
			exit;
		}
		else{
		
		if($_SERVER['REQUEST_METHOD'] == 'POST') {
			
			//Überprüfung ob neues Passwort mit bestätigtem PW übereinstimmen
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
	}
	
	/**
	 * Änderung des PW falls PW vergessen wurde, über E-Mail
	 * Versendet an die Mail-Adresse des Benutzers einen Link,
	 * zum ändern des PW. Zusätzlich wird eine zufällige Prüfzahl generiert, die in 
	 * der Datenbank gespeichert und per Mail übergeben wird.
	 */
	public function passwordforgottenAction() {
		
		if (isset($_GET['benutzer'])) {
			$user = new User();
			$user->load($_GET['benutzer']);
			$result = $user->check_pruefzahl($_GET['pruefzahl']);
			if ($result) {
				
				return new ViewModel(['benutzer' => $_GET['benutzer'], 'pruefzahl' => $_GET['pruefzahl']]);
				
			}
			else {
			
				return new ViewModel(['status'=>'falsche pruefzahl']);
				
			}
			
		} else if (isset($_POST['benutzername'])){
			
			$user = new User();
			$user->load($_POST['benutzername']);
			if ($user->istBestaetigt()) {
				$user->passwordForgottenMail();
				return new ViewModel(['status' => 'mail']);
			}
			else {
				return new ViewModel(['status' => 'nicht bestaetigt']);
			}
			
		} 
		
		else if (isset($_POST['email'])){
				
			$user = new User();
			$user->load_via_email($_POST['email']);
			if ($user->istBestaetigt()) {
				$user->passwordForgottenMail();
				return new ViewModel(['status' => 'mail']);
			}
			else {
				return new ViewModel(['status' => 'nicht bestaetigt']);
			}
		}
		
		else if (isset($_POST['newPasswort1'])) {
			
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
	
	public function impressumAction(){
		return new ViewModel();
	}
	
}