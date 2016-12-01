<?php
namespace Zertifizierungstool\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zertifizierungstool\Model\Kurs;
use Zertifizierungstool\Model\User;

class KursController extends AbstractActionController
{
	public function anlegenAction() {
		if (Re('Speichern')) {
			$kursname = getValue NAme;
			
			$kurs = new Kurs();
			$kurs->setName($kursname);
			
			$kurs->speichern();
		}
		
		
		return new ViewModel();
	}
}