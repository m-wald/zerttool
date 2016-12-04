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
		
		$user = new User();
		
		$user->load('waldma');
		
		return new ViewModel([
				'benutzer' => array($user),
		]);
		
		
	}
	
	public function registertestAction()
	{
		$user = new User("michi", "123", "Michael", "Moertl", "1990-11-26", "Nibelungenstrasse","94032", "passau", "mich@mo.bla", 0, 1, 0, 0);
	
		$user->register();
		return new ViewModel();
	}
	public function anmeldetestAction()
	{
		$user = new User();
		$user->load("michi");
		$result=$user->passwortControll("123");
		if ($result){
			echo "Erfolgreich";
		}
		else {
			echo "Fehlgeschlagen";
		}
	}
	
	public function loginAction()
	{
		
		$benutzername = Request::getValue("benutzername");
		
		$user = new User();
		if($user->login($benutzername, $passwort) == true){
			
		}
	}
	
	public function loeschenAction() {
		
	}
}