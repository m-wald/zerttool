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