<?php
namespace Zertifizierungstool\Controller;


use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zertifizierungstool\Model\Pruefung;
use Zertifizierungstool\Model\User;
use Zertifizierungstool\Model\Frage;
use Zertifizierungstool\Model\Antwort;

/**
 * Controller, der Aufgaben verarbeitet, die sich auf die Entität "Frage" und "Antwort" beziehen.
 * Beinhaltet Actions zum Anlegen, Bearbeiten, Beantworten und Löschen von Fragen und Antworten.
 *
 * @author martin
 */
class FrageController extends AbstractActionController {
	
	private $frage;
	
	public function answerAction() {
		// Frage laden
		$frage = new Frage();
		// TODO$frage->load()
		// Alle Antworten zu dieser Frage laden
		$antworten = Antwort::loadList($frage->getId());
		// Alle Fragen zu frage->getPruefungId() laden
		$fragen = Frage::loadList($frage->getPruefungId());
		// Array nach Id sortieren
		array_multisort($fragen);
		// Ermitteln der nächsten Id nach der aktuellen im Array
			// Was bei letzter Id?
		// Nachdem Formular angesendet wurde:
			// Entsprechenden Eintrag in beantwortet ändern
			// Ermitteln der nächsten Prüfung im Array
			
		return new ViewModel([
				'frage'		=> $frage,
				'antworten' => $antworten,
				'nextId'	=> $nextId,
		]);
	}
	private function handleForm($request, $mode) {
		// TODO Prüfen ob Prüfungstermin schon erreicht ist
		// Die Prüfung kann dann nicht mehr bearbeitet werden
		
		// Array, das mit eventuellen Fehlermeldungen gefüllt wird
		$errors = array();
		
		// Diesen Teil doch in die Methoden?
		$this->frage = new Frage();
		$pruefung = new Pruefung();
		if ($mode == PruefungController::editFragen) {
			$frageid = $_REQUEST["id"];
			
			if (empty($frageid)) {
				$frageid = $this->params()->fromRoute('id');
			}
			$this->frage->load($frageid);
			$pruefungid = $this->frage->getPruefungId();
			
		}elseif ($mode == PruefungController::createFragen) {
			$pruefungid = $_REQUEST["pruefung_id"];
			if (empty($pruefungid)) {
				$pruefungid = $this->params()->fromRoute('id');
			}
		}
			
		if (!$pruefung->load($pruefungid)) {
			array_push($errors, "Fehler beim Laden der Prüfung!");
		}
		
		if (isset($request['speichernFrage'])) {
			// Neues Frage-Objekt mit den Daten aus dem gesendeten Formular erzeugen und in der DB speichern bzw. aktualisieren
			$this->frage = new Frage(
							$request["id"],
							$request["frage_text"],
							$request["punkte"],
							$request["pruefung_id"],
							$request["frage_typ"]);
		
			if (!$this->frage->save()) {
				array_push($errors, "Fehler beim Speichern der Frage. Bitte erneut versuchen!");
				
			}else {
				// Frage konnte gespeichert werden -> Speichern der zugehöregen Antwort(en)
				switch ($request["frage_typ"]) {
					case "TF":
						$status = 0;
						if ($request["tf"] == "true") {
							$status = 1;
						}
						
						$antwort = new Antwort($request["antwort_id"], "", $this->frage->getId(), $status);
						
						if (!$antwort->save()) array_push($errors, "Fehler beim Speichern der Antwort. Bitte erneut versuchen!");
	
						break;
		
					case "MC":
						$index = 1;
						while (!empty($request["antwort_text" .$index])) {
							$status = 0;
							if ($request["antwort_checked" .$index]) {
								$status = 1;
							}
		
							$antwort = new Antwort(
										$request["antwort_id" .$index],
										$request["antwort_text" .$index],
										$this->frage->getId(),
										$status);
			
							if (!$antwort->save()) array_push($errors, "Fehler beim Speichern der Antwort. Bitte erneut versuchen!");
							
							$index++;
							}
						break;
				}
			}
	
			if (empty($errors)) {
				header ("refresh:0; url = /frage/create/" .$this->frage->getPruefungId());
			}
		}
		
		$viewModel = new ViewModel([
				'pruefung' => $pruefung,
				'fragen'   => Frage::loadList($pruefung->getId()), // Fragen laden -> Was bei Fehler?
				'errors'   => $errors,
				'mode'	   	  => $mode,
				'frageToEdit' => $this->frage
		]);
		
		$viewModel->setTemplate(PruefungController::pathToHtml);
		return $viewModel;
	}
	
	public function createAction() {
		// Berechtigungsprüfung
		if (!User::currentUser()->istAdmin() && !User::currentUser()->istZertifizierer()) {
			header ("refresh:0; url = /user/login/");
		}
		
		return $this->handleForm($_REQUEST, PruefungController::createFragen);
	}
	
	/*
	public function createAction() {
		// Prüfen ob Prüfungstermin schon erreicht ist
		// Die Prüfung kann dann nicht mehr bearbeitet werden
		
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
				'pruefung' => $pruefung,
				'fragen'   => Frage::loadList($pruefung->getId()),
				'errors'   => $errors,
				'mode'	   => PruefungController::createFragen
		]);
		
		$viewModel->setTemplate(PruefungController::pathToHtml);
		return $viewModel;
	}
	*/
	
	public function edit2Action() {
		// Berechtigungsprüfung
		if (!User::currentUser()->istAdmin() && !User::currentUser()->istZertifizierer()) {
			array_push($errors, "Keine Berechtigung!");
		}
		
		return $this->handleForm($_REQUEST, PruefungController::editFragen);
	}
	public function editAction() {
		// Prüfen ob Prüfungstermin schon erreicht ist
		// Die Prüfung kann dann nicht mehr bearbeitet werden
		
		// Array, das eventuelle Fehlermeldungen enthält
		$errors = array();
		
		// Berechtigungsprüfung
		if (!User::currentUser()->istAdmin() && !User::currentUser()->istZertifizierer()) {
			array_push($errors, "Keine Berechtigung!");
		}
		
		// Erzeugung des Frage-Objekts mit Übergabe der zugehörigen Prüfungs-Id
		$frageid = $_REQUEST["id"];
		
		if (empty($frageid)) {
			$frageid = $this->params()->fromRoute('id');
		}
		
		$frage = new Frage();
		$frage->load($frageid);
		
		$pruefung = new Pruefung();		
		if (!$pruefung->load($frage->getPruefungId())) {
			array_push($errors, "Fehler beim Laden der Prüfung!");
		}		
		
		if ($_REQUEST['speichernFrage']) {
				
			$frage = new Frage(
					$_REQUEST["id"],
					$_REQUEST["frage_text"],
					$_REQUEST["punkte"],
					$_REQUEST["pruefung_id"],
					$_REQUEST["frage_typ"]);
				
			if (empty($errors)) {
				if (!$frage->update()) {
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
							
						$antwort = new Antwort($_REQUEST["antwort_id"], "", $frage->getId(), $status);
						if (empty($_REQUEST["id"])) {
							if (!$antwort->saveNew()) {
								array_push($errors, "Fehler beim Speichern der Antwort. Bitte erneut versuchen!");
							}
						} else {
							if (!$antwort->update()) {
								array_push($errors, "Fehler beim Speichern der Antwort. Bitte erneut versuchen!");
							}
						}
						
							
						break;
		
					case "MC":
						$index = 1;
						while (!empty($_REQUEST["antwort_text" .$index])) {
							$status = 0;
							if ($_REQUEST["antwort_checked" .$index]) {
								$status = 1;
							}
		
							$antwort = new Antwort($_REQUEST["antwort_id" .$index],
									$_REQUEST["antwort_text" .$index],
									$frage->getId(),
									$status);
		
							if (empty($_REQUEST["id"])) {
								if (!$antwort->saveNew()) {
									array_push($errors, "Fehler beim Speichern der Antworten. Bitte erneut versuchen!");
								}
							} else {
								if (!$antwort->update()) {
									array_push($errors, "Fehler beim Speichern der Antworten. Bitte erneut versuchen!");
								}
							}
		
							$index++;
						}
						break;
				}
			}
			
			if (empty($errors)) {
				header ("refresh:0; url = /frage/create/" .$frage->getPruefungId());
			}
		}
		
		$viewModel = new ViewModel([
				'pruefung' => $pruefung,
				'fragen'   => Frage::loadList($pruefung->getId()),
				'errors'   => $errors,
				// Fragen laden -> Was bei Fehler?
				'mode'	   => PruefungController::editFragen,
				'frageToEdit' => $frage
		]);
		
		$viewModel->setTemplate(PruefungController::pathToHtml);
		return $viewModel;
	}
	
	public function deleteAction() {
		// TODO Berechtigungsprüfungen
		// TODO Prüfen ob Prüfungstermin schon erreicht ist
		// Die Prüfung kann dann nicht mehr bearbeitet werden
		$frage_id_toDelete = $this->params()->fromRoute('id');
		$frage = new Frage();
		$frage->load($frage_id_toDelete);
		
		$antwortenToDelete = Antwort::loadList($frage_id_toDelete);
		
		foreach ($antwortenToDelete as $antwort) {
			Antwort::delete($antwort->getId());
			// TODO Fehler abfangen
		}
		
		Frage::delete($frage_id_toDelete);
		// TODO Fehler abfangen
		
		header ("refresh:5; url = /frage/create/" .$frage->getPruefungId());
	}
	
	public function deleteAntwortAction() {
		Antwort::delete($antwort->getId());
		header ("refresh:5; url = /frage/create/" .$this->params()->fromRoute('id'));
	}
}