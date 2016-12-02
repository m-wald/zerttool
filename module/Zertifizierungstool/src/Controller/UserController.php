<?php
namespace Zertifizierungstool\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zertifizierungstool\Model\User;
use Zertifizierungstool\Form\LoginForm;

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
	public function register_testAction()
	{
		$user = new User("michi", 123, "Michael", "Moertl", "26.11.", "Nibelungenstrasse", "passau", "mich@mo.bla", 0, 1, 0, 0);
		
		$user->register();
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