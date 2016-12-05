<?php
namespace Zertifizierungstool\Controller;


use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zertifizierungstool\Model\User;

/**
 * Dokumentation
 * 
 * @author martin waldmann
 *
 */
class UserController extends AbstractActionController
{
	public function registerAction()
	{
		if($_SERVER['REQUEST_METHOD'] == 'POST') {
			
		
		
		$user= new User($_REQUEST["benutzername"], $_REQUEST["passwort"], $_REQUEST["vorname"], $_REQUEST["nachname"], $_REQUEST["geburtsdatum"], $_REQUEST["strasse"], $_REQUEST["plz"], $_REQUEST["ort"], $_REQUEST["email"], $_REQUEST["email_bestaetigt"], $_REQUEST["ist_admin"], $_REQUEST["ist_zertifizierer"], $_REQUEST["ist_teilnehmer"]);
		
		$result = $user->register();
		
		
		return new ViewModel(['meldung' => $result]);
		}
		
		else
			return new ViewModel;
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
			$user = new User();
			$user->load($_POST['benutzername']);
			
			$result=$user->passwortControll($_POST['passwort']);
			if ($result){
			//hier Session-Management!!
				
				return new ViewModel(['anmeldestatus' => true]);
			}
			else {
				return new ViewModel(['anmeldestatus' => false]);
			}
		}
		
		
		return new ViewModel();

	}
	
	public function loeschenAction() {
		
	}
}