<?php
namespace Zertifizierungstool\Controller;


use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zertifizierungstool\Model\Pruefung;
use Zertifizierungstool\Model\User;

class FrageController extends AbstractActionController {
	
	public function createAction() {
		// Array, das eventuelle Fehlermeldungen enthält
		$errors = array();
		
		// Berechtigungsprüfung
		if (!User::currentUser()->istAdmin() && !User::currentUser()->istZertifizierer()) {
			array_push($errors, "Keine Berechtigung!");
		}
		
		// Erzeugung des Frage-Objekts mit Übergabe der zugehörigen Prüfungs-Id
		if (isset($this->params()->fromRoute('id'))) {
			$pruefung = new Pruefung($kursid = $this->params()->fromRoute('id'));
		} else {
			array_push($errors, "Der Frage konnte keine Prüfung zugeordnet werden!");
		}
	}
}