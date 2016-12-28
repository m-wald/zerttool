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
		
		if(User::currentUser()->getBenutzername()!=NULL && User::currentUser()->istZertifizierer()){
			header("refresh:0; url= /user/home");
			exit;
		}
		else{
		
		
		if($_SERVER['REQUEST_METHOD'] == 'POST') {
			
			if ($_REQUEST['passwort']==$_REQUEST['passwort2']) {
			
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
			} else {
				
				return new ViewModel(['pw_kontrolle' => 'ungleiche passwoerter']);
			}
			
			if (isset ($_REQUEST['invitemail'])) {
				
				$result = $user->register(true);
			}
			else {
			
				$result = $user->register(false);
			
			}
			
			return new ViewModel(['meldung' => $result]);
			
		}
		
		else {
			if (User::currentUser()->istAdmin()) {
				return new ViewModel(['status' => 'admin']);
			} else {
				
				if (isset($_GET['inviteuser'])) {
					
					return new ViewModel(['email'=> $_GET['inviteuser']]);
					
				}else {
				
				
				
				 return new ViewModel();
				}
			}
		}
		}
	}
	
	
	public function registerbestAction() {
		$user = new User();
		$user->load($_GET['benutzer']);
		$user->registerbest();
		if (isset($_GET['kurs_id'])) {
			
			return new ViewModel(['benutzername'=>$user->getBenutzername(), 'kurs_id'=>$_GET['kurs_id']]);
			
		}
		return new ViewModel();
	}
	
	
	/** Nutzerdaten werden überprüft, bei Richtigkeit wird ein "currentUser" mit den Benutzerdaten befüllt und 
	 * eine Session erstellt  */
	
	 public function loginAction()
	{
		
		if(User::currentUser()->getBenutzername()!=NULL){
			header("refresh:0; url= /user/home");
			exit;
		}
		else{
		
		if($_SERVER['REQUEST_METHOD'] == 'POST') {
						
			$user = new User();
			
			$user->load($_POST['benutzername']);

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
				return new ViewModel(['anmeldestatus' => false]);
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
		}
	}
	
	/** leitet nach erfolgreichem Login auf eine benutzerspezifische Startseite weiter */
	
	public function homeAction() {
		
		if(User::currentUser()->getBenutzername()==NULL){
			header("refresh:0 url= /user/login");
			exit;
		}
		else{
		return new ViewModel(['benutzername' => User::currentUser()->getBenutzername()]); 
		
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
			
			User::currentUser()->update($_REQUEST["vorname"], $_REQUEST["nachname"], $_REQUEST["geburtsdatum"], $_REQUEST["strasse"], $_REQUEST["plz"], $_REQUEST["ort"], $_REQUEST["email"]);
			User::currentUser()->load(User::currentUser()->getBenutzername());
			$_SESSION["currentUser"] = serialize(User::currentUser());			
			return new ViewModel(['status' => "erfolgreich"]);
		}
		else {
				return new ViewModel(['benutzerdaten' => array(User::currentUser()->getBenutzername(),User::currentUser()->getVorname(), User::currentUser()->getNachname(), User::currentUser()->getGeburtsdatum(), User::currentUser()->getStrasse(), User::currentUser()->getPLZ(), User::currentUser()->getOrt(), User::currentUser()->getEmail())]);
		}
		}
	
	}
	
	public function changepasswordAction() {
		
		if(User::currentUser()->getBenutzername()==NULL){
			header("refresh:0; url= /user/login");
			exit;
		}
		else{
		
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
	}
	
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
			$user->passwordForgottenMail();
			return new ViewModel(['status' => 'mail']);
			
		} 
		
		else if (isset($_POST['email'])){
				
			$user = new User();
			$user->load_via_email($_POST['email']);
			$user->passwordForgottenMail();
			return new ViewModel(['status' => 'mail']);
				
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
	
	
}