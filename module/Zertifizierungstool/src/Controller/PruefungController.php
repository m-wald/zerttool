<?php
namespace Zertifizierungstool\Controller;


use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zertifizierungstool\Model\Pruefung;
use Zertifizierungstool\Model\Kurs;
use Zertifizierungstool\Model\User;
use Zertifizierungstool\Model\Frage;

/**
 * TODO Dokumentation
 * @author martin
 *
 */
class PruefungController extends AbstractActionController {
	
	const pathToHtml	 = 'zertifizierungstool/pruefung/pruefung';
	
	// TODO Prüfungskonstanten zusammenlegen?
	const createPruefung = "Pruefung anlegen";
	const editPruefung   = "Pruefung bearbeiten";
	const createFragen   = "Fragen anlegen";
	const editFragen	 = "Fragen bearbeiten";
	
	public function createAction() {
		// Array, das eventuelle Fehlermeldungen enthält
		$errors = array();
		$result = false;
		$fragen = array();
		
		// Berechtigungsprüfung TODO weiterleitung auf fehlerseite
		if (!User::currentUser()->istAdmin() && !User::currentUser()->istZertifizierer()) {
			array_push($errors, "Keine Berechtigung!");
		}

		$newKursid = $_REQUEST["kursid"];
		
		if (empty($newKursid)) {
			$newKursid = $this->params()->fromRoute('id');
		}
		
		$pruefung = new Pruefung();
		$pruefung->setKursId($newKursid);
				
		
		if ($_REQUEST['speichernPruefung']) {
			
			$pruefung = new Pruefung("", 
					$_REQUEST["name"],
					$_REQUEST["termin"], 
					$_REQUEST["kursid"], 
					$_REQUEST["cutscore"] / 100 );
			
			// TODO Format des Prüfungstermins überprüfen
			// Prüfungstermin validieren
			//array_push($errors, $this->checkDate($pruefung));
			
			if (empty($errors)) {
				if (!$pruefung->saveNew()) {
					array_push($errors, "Fehler beim Speichern der Pr&uuml;fung. Bitte erneut versuchen!");
				}else {
					header ("refresh:0; url = /frage/create/" .$pruefung->getId());
					//$result = true;
				}
			}
		}
			
		$viewModel = new ViewModel([
				'pruefung' => $pruefung,
				'errors'   => $errors,
				'fragen'   => $fragen,
				'mode'	   => PruefungController::createPruefung
		]);
		

		$viewModel->setTemplate(PruefungController::pathToHtml);
		return $viewModel;
		
	}
	
	public function editAction() {
		
		// TODO Prüfen ob Prüfungstermin schon erreicht ist
		// Die Prüfung kann dann nicht mehr bearbeitet werden
		
		// Array, das eventuelle Fehlermeldungen enthält
		$errors = array();
		$result = false;
		
		// Berechtigungsprüfung
		if (!User::currentUser()->istAdmin() && !User::currentUser()->istZertifizierer()) {
			return "Keine Berechtigung";
			//array_push($errors, "Keine Berechtigung!");
		}
		
		/*
		$pruefung_id = $_REQUEST["pruefid"];
		
		if (empty($pruefung_id)) {
			$pruefung_id = $this->params()->fromRoute('id');
		}
		
		$pruefung = new Pruefung();
		$pruefung->load($pruefung_id);
		
		// Überprüfung, ob aktueller Benutzer auch der Kursleiter ist
		$kurs = new Kurs();
		$kurs->load($pruefung->getKursId());
		*/
		
		if (!$_REQUEST['speichernPruefung']) {
			// Formular wurde noch nicht gesendet
			$pruefung_id = $this->params()->fromRoute('id');
			$pruefung = new Pruefung();
			$pruefung->load($pruefung_id);
			$kurs = new Kurs();
			$kurs->load($pruefung->getKursId());
			
			if (!$kurs->getBenutzername() == User::currentUser()->getBenutzername()) {
				array_push($errors, "Keine Berechtigung!");
			}
			
		} else {
				
			$pruefung = new Pruefung(
					$_REQUEST["pruefid"],
					$_REQUEST["name"],
					$_REQUEST["termin"],
					$_REQUEST["kursid"],
					$_REQUEST["cutscore"] / 100 );
				
			// TODO Format des Prüfungstermins überprüfen
			// Prüfungstermin validieren
			//array_push($errors, $this->checkDate($pruefung));
				
			if (empty($errors)) {
				if (!$pruefung->update()) {
					array_push($errors, "Fehler beim Speichern der Pr&uuml;fung. Bitte erneut versuchen!");
				}else {
					header ("refresh:0; url = /frage/create/" .$pruefung->getId());
					//$result = true;
				}
			}
		}
			
		$viewModel = new ViewModel([
				'pruefung' => $pruefung,
				'errors'   => $errors,
				'fragen'   => Frage::loadList($pruefung->getId()),
				'mode'	   => PruefungController::editPruefung
		]);
		
		
		$viewModel->setTemplate(PruefungController::pathToHtml);
		return $viewModel;
	}
	
	//TODO
	public function deleteAction() {
		
	}
	
	/**
	 * Listet alle Prüfungen auf, die zu einem Kurs gehören
	 */
	public function overviewAction() {
		$pruefungen = Pruefung::loadList($this->params()->fromRoute('id'));
		
		if ($pruefungen == false) {
			// Fehler
		}
		
		return new ViewModel(['pruefungen' => $pruefungen]);
	}
	
	/**
	 * Überprüft den Prüfungstermin nach folgenden Kriterien:
	 *  - nach Kursbeginn
	 *  - mindestens 4 Tage vor Kursende
	 *  
	 * @param Die zu überprüfende Prüfung $pruefung
	 * @return Eventuelle Fehlermeldung
	 */
	private function checkDate($pruefung) {
		$error;
		$kurs = new Kurs();
		
		if ($kurs->load($pruefung->getKursId())) {
			if ($pruefung->getTermin() < $kurs->getKurs_start()) {
				$error= "Der Pr&uuml;fungszeitraum kann erst nach Kursbeginn beginnen!";
			
			}elseif ($pruefung->getTermin() > date_sub($kurs->getKurs_ende(), new \DateInterval("P4D"))) {
				$error = "Der Pr&uuml;fungszeitraum muss spätestens 4 Tage vor Kursende beginnen!";
			}
		}else {
			$error = "Der Kurs wurde nicht in der Datenbank gefunden!";
		}
		
		return $error;
	}
}