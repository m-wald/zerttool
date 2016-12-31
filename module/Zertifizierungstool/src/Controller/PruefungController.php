<?php
namespace Zertifizierungstool\Controller;


use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zertifizierungstool\Model\Pruefung;
use Zertifizierungstool\Model\Kurs;
use Zertifizierungstool\Model\User;
use Zertifizierungstool\Model\Frage;
use Zertifizierungstool\Model\Antwort;
use Zertifizierungstool\Model\Schreibt_pruefung;
use Zertifizierungstool\Model\Beantwortet;

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
		// TODO Prüfen ob Teilnehmer im Kurs eingetragen ist
		// TODO Prüfen, ob Kursende schon erreicht
		// TODO Prüfen, ob letzter Versuch schon 24 Stunden her ist
		// TODO Prüfen, ob Teilnehmer die Prüfung schon bestanden hat oder 3 mal nicht bestanden hat
		
		// Prüfungs-Id aus URL laden
		$pruefung_id = $this->params()->fromRoute('id');
		
		// Eintrag in Tabelle schreibt_pruefung
		$schreibt_pruefung = new Schreibt_pruefung("", $pruefung_id, User::currentUser()->getBenutzername(), time(), 0);
		$schreibt_pruefung->saveNew();
		// TODO Fehler abfangen
		
		// Alle Fragen zur Prüfung laden
		$fragen = Frage::loadList($pruefung_id);
		
		// Für jede Frage:
		foreach ($fragen as $frage) {
			// Alle Antworten laden
			$antworten = Antwort::loadList($frage->getId());
			
			// Für jede Antwort:
			foreach ($antworten as $antwort) {
				// Objekt von "beantwortet" erzeugen mit schreibt_pruefung->getId(), antwort->getId(), beantwortet_status = 0 ->in Db speichern
				// extra-Attribut "edited"? (gesetzt sobal User auf "Weiter" oder so geklickt hat)
				$beantwortet = new Beantwortet("", $schreibt_pruefung->getId(), $antwort->getId(), 0);
				$beantwortet->saveNew();
				// TODO Fehler abfangen
			}
		}
		
		
		// Weiterleiten an FrageController Action answer mit Id der ersten Prüfungsfrage
		header("refresh:0; url = /frage/answer/" .$schreibt_pruefung->getId()); //statt fragen[0]->getId()
	}
	
	/**
	 * Überprüft, ob ein Teilnehmer eine Prüfung bestanden hat
	 */
	public function resultAction() {
		// aus schreibt_pruefung auslesen
			// absteigend nach timestamp sortieren und erster Eintrag ist der richtige
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
			header ("refresh:0; url = /user/login/");
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
		
		// Was wenn Fehler? Zurückleiten auf Übersicht?
		if ($this->pruefung->getTermin() <= time()) {
			array_push($errors, "Der Prüfungszeitraum wurde bereits erreicht. Die Prüfung kann nicht mehr bearbeitet werden!");
		}
		
		return $this->handleForm($_REQUEST, Frage::loadList($this->pruefung->getId()));
	}
	
	
	
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