<?php
namespace Zertifizierungstool\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zertifizierungstool\Model\User;

/*
 * TODO Dokumentation
 */
class UserController extends AbstractActionController
{
	public function registerAction()
	{
		return new ViewModel();
	}
}