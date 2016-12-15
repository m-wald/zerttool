<?php
namespace Zertifizierungstool\Controller;


use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zertifizierungstool\Model\Pruefung;
use Zertifizierungstool\Model\User;
use Zertifizierungstool\Model\Frage;
use Zertifizierungstool\Model\Antwort;

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
		
		if (is_bool($fragen)) {
			array_push($errors, "Fehler beim Laden der Prüfungsfragen!");
		}

		
		if ($_REQUEST['speichernFrage']) {
			
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
			if (empty($errors)) {
				switch ($_REQUEST["frage_typ"]) {
					case "TF":
						$status = 0;
						if ($_REQUEST["tf"] == "true") {
							$status = 1;
						}
					
						$antwort = new Antwort("", "", $frage->getId(), $status);
						if (!$antwort->saveNew()) {
							array_push($errors, "Fehler beim Speichern der Antwort. Bitte erneut versuchen!");
						}
					
						break;
				
					case "MC":
						$index = 1;
						while (!empty($_REQUEST["antwort_text" .$index])) {
							$status = 0;
							if ($_REQUEST["antwort_checked" .$index]) {
								$status = 1;
							}
						
							$antwort = new Antwort("",
									$_REQUEST["antwort_text" .$index],
									$frage->getId(),
									$status);
						
							if (!$antwort->saveNew()) {
								array_push($errors, "Fehler beim Speichern der Antworten. Bitte erneut versuchen!");
							}
						
							$index++;
						}
						break;
					
					default: break;
				}
			}	
		}
	
		$viewModel = new ViewModel([
				'pruefung' => array($pruefung),
				'fragen'   => $fragen,
				'errors'   => $errors,
				'mode'	   => array(PruefungController::createFragen)
		]);
		
		$viewModel->setTemplate(PruefungController::pathToHtml);
		return $viewModel;
	}
	
	public function editAction() {
		
	}
}