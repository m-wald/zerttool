<?php
namespace Zertifizierungstool\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

//use Zertifizierungstool\Model\User;

class UserController extends AbstractActionController
{
	public function indexAction()
	{
		//$user = new User();
		
		return new ViewModel([
				'benutzer' => "Lehner" //$user->benutzername,
		]);
	}

	public function registerAction()
	{
	}

	public function editAction()
	{
	}
}