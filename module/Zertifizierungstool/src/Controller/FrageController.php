<?php
namespace Zertifizierungstool\Controller;


use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zertifizierungstool\Model\Pruefung;
use Zertifizierungstool\Model\User;
use Zertifizierungstool\Model\Frage;

class FrageController extends AbstractActionController {
	
	public function createAction() {
		// Array, das eventuelle Fehlermeldungen enthält
		$errors = array();
		
		// Berechtigungsprüfung
		if (!User::currentUser()->istAdmin() && !User::currentUser()->istZertifizierer()) {
			array_push($errors, "Keine Berechtigung!");
		}
		
		// Erzeugung des Frage-Objekts mit Übergabe der zugehörigen Prüfungs-Id
		$pruefungid = $_REQUEST["pruefung_id"];
		
		if (empty($pruefungid)) {
			$pruefungid = $this->params()->fromRoute('id');
		}
		
		$pruefung = new Pruefung();
		
		if (!$pruefung->load($pruefungid)) {
			array_push($errors, "Fehler beim Laden der Prüfung!");
		}
		
		// Alle bereits angelegten Fragen zu dieser Prüfung laden
		$fragen = Frage::loadList($pruefungid);
		
		if ($fragen == false) {
			array_push($errors, "Fehler beim Laden der Prüfungsfragen!");
		}
		
		if ($_REQUEST['speichern']) {
			
			$frage = new Frage("",
					$_REQUEST["frage_text"],
					$_REQUEST["punkte"],
					$_REQUEST["pruefung_id"],
					$_REQUEST["frage_typ"]);
			
			if (empty($errors)) {
				if (!$frage->saveNew()) {
					array_push($errors, "Fehler beim Speichern der Frage. Bitte erneut versuchen!");
				}
			}
		}
		
		
		return new ViewModel([
				'pruefung' => array($pruefung),
				'fragen'   => $fragen,
				'errors'   => $errors
		]);
		
	}
}