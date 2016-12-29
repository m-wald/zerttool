<?php
namespace Zertifizierungstool\Controller;


use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zertifizierungstool\Model\Pruefung;
use Zertifizierungstool\Model\Kurs;
use Zertifizierungstool\Model\User;
use Zertifizierungstool\Model\Frage;

/**
 * Controller, der Aufgaben verarbeitet, die sich auf die Entität "Prüfung" beziehen.
 * Beinhaltet Actions zum Anlegen, Bearbeiten, Absolvieren und Löschen von Prüfungen.
 * 
 * @author martin
 */
class PruefungController extends AbstractActionController {
	
	const pathToHtml	 = 'zertifizierungstool/pruefung/pruefung';
	
	const PRUEFUNG 		 = "Pruefung";
	const createFragen   = "Fragen anlegen";
	const editFragen	 = "Fragen bearbeiten";
	
	/** Das behandelte Prüfungs-Objekt */
	private $pruefung;
	
	public function takeExamAction() {
		// Prüfung mit Id aus URL laden
		// Eintrag in Tabelle schreibt_pruefung
			//pruefung-getId(), currentUser()-getBenutzername(), aktueller timestamp, 0(nicht bestanden)
		
		// Alle Fragen zur Prüfung laden
		// Für jede Frage:
			// Alle Antworten laden
			// Für jede Antwort:
				// Objekt von "beantwortet" erzeugen mit schreibt_pruefung->getId(), antwort->getId(), beantwortet_status = 0 ->in Db speichern
				// extra-Attribut "edited" (gesetzt sobal User auf "Weiter" oder so geklickt hat)
		
		// Weiterleiten an FrageController Action answer mit Id der ersten Prüfungsfrage
	}
	
	/**
	 * Überprüft, ob ein Teilnehmer eine Prüfung bestanden hat
	 */
	public function resultAction() {
		// Abgebene Antworten prüfen und evtl "bestanden" in schreibt_pruefung auf 1 setzen
		// Mit cutscore vergleichen
		// Anbieten Zertifikat runterzuladen
	}
	
	/**
	 * Verarbeitet das Formular zum Anlegen und Bearbeiten von Prüfungen
	 * @param $request Daten aus Request-Array
	 * @param array $fragen Evtl. bereits angelegte Fragen
	 * @return \Zend\View\Model\ViewModel
	 */
	private function handleForm($request, $fragen = array()) {
		// Array, das eventuelle Fehlermeldungen enthält
		$errors = array();
		
		if (isset($request['speichernPruefung'])) {
		
		$this->pruefung = new Pruefung(
				$request["pruefid"],
				$request["name"],
				$request["termin"],
				$request["kursid"],
				$request["cutscore"] / 100 );
		
		// TODO Format des Prüfungstermins überprüfen
		// Prüfungstermin validieren
		//array_push($errors, $this->checkDate($pruefung));
			
		if (empty($errors)) {
			if ($this->pruefung->save()) {
				header ("refresh:0; url = /frage/create/" .$this->pruefung->getId());
			}else {
				array_push($errors, "Fehler beim Speichern der Pr&uuml;fung. Bitte erneut versuchen!");
			}
		}
		}

		$viewModel = new ViewModel([
				'pruefung' => $this->pruefung,
				'errors'   => $errors,
				'fragen'   => $fragen,
				'mode'	   => PruefungController::PRUEFUNG
		]);
		
		
		$viewModel->setTemplate(PruefungController::pathToHtml);
		return $viewModel;
	}
	
	/**
	 * Legt die Kopfdaten einer neuen Prüfung in der Datenbank an.
	 * @return \Zend\View\Model\ViewModel
	 */
	public function createAction() {
		// Berechtigungsprüfung TODO weiterleitung auf fehlerseite
		if (!User::currentUser()->istAdmin() && !User::currentUser()->istZertifizierer()) {
			//header ("refresh:0; url = /user/login/");
		}
		
		if (empty($_REQUEST["kursid"])) {
			$newKursid = $this->params()->fromRoute('id');	
		}else {
			$newKursid = $_REQUEST["kursid"];
		}
		
		$this->pruefung = new Pruefung();
		$this->pruefung->setKursId($newKursid);
		
		return $this->handleForm($_REQUEST);
	}
	
	/**
	 * Bearbeitet die Kopfdaten einer Prüfung.
	 * @return \Zend\View\Model\ViewModel
	 */
	public function editAction() {
		// Berechtigungsprüfung TODO weiterleitung auf fehlerseite
		if (!User::currentUser()->istAdmin() && !User::currentUser()->istZertifizierer()) {
			header ("refresh:0; url = /user/login/");
		}
		
		if (empty($_REQUEST["pruefid"])) {
			$pruefung_id = $this->params()->fromRoute('id');
		}else {
			$pruefung_id = $_REQUEST["pruefid"];
		}
		
		$this->pruefung = new Pruefung();
		$this->pruefung->load($pruefung_id);
		
		$kurs = new Kurs();
		$kurs->load($this->pruefung->getKursId());
		
		if (!$kurs->getBenutzername() == User::currentUser()->getBenutzername()) {
			header ("refresh:0; url = /user/login/");
		}
		
		if ($this->pruefung->getTermin() >= new Date()) {
			array_push($errors, "Der Prüfungszeitraum wurde bereits erreicht. Die Prüfung kann nicht mehr bearbeitet werden!");
		}
		
		return $this->handleForm($_REQUEST, Frage::loadList($this->pruefung->getId()));
	}
	
	/*
	public function createAction() {
		// Array, das eventuelle Fehlermeldungen enthält
		$errors = array();
		$result = false;
		$fragen = array();
		
		// Berechtigungsprüfung weiterleitung auf fehlerseite
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
			
			// Format des Prüfungstermins überprüfen
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
		
		// Prüfen ob Prüfungstermin schon erreicht ist
		// Die Prüfung kann dann nicht mehr bearbeitet werden
		
		// Array, das eventuelle Fehlermeldungen enthält
		$errors = array();
		$result = false;
		
		// Berechtigungsprüfung
		if (!User::currentUser()->istAdmin() && !User::currentUser()->istZertifizierer()) {
			return "Keine Berechtigung";
			//array_push($errors, "Keine Berechtigung!");
		}

		
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
			
			if ($pruefung->getTermin() >= new Date()) {
				array_push($errors, "Der Prüfungszeitraum wurde bereits erreicht. Die Prüfung kann nicht mehr bearbeitet werden!");
			}
			
		} else {
				
			$pruefung = new Pruefung(
					$_REQUEST["pruefid"],
					$_REQUEST["name"],
					$_REQUEST["termin"],
					$_REQUEST["kursid"],
					$_REQUEST["cutscore"] / 100 );
				
			// Format des Prüfungstermins überprüfen
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
	*/
	
	//TODO
	/**
	 * Löscht eine Prüfung.
	 */
	public function deleteAction() {
		
	}
	
	/**
	 * Listet alle Prüfungen auf, die zu einem Kurs gehören
	 */
	public function overviewAction() {
		$kursid = $this->params()->fromRoute('id');
		$pruefungen = Pruefung::loadList($kursid);
		
		if ($pruefungen == false) {
			// Fehler
		}
		
		return new ViewModel([
				'pruefungen' => $pruefungen,
				'kursid'	 => $kursid
		]);
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