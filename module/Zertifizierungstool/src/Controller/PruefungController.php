<?php
namespace Zertifizierungstool\Controller;


use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zertifizierungstool\Model\Pruefung;
use Zertifizierungstool\Model\Kurs;
use Zertifizierungstool\Model\User;
use Zertifizierungstool\Model\Frage;
use Zertifizierungstool\Model\Antwort;
use Zertifizierungstool\Model\SchreibtPruefung;
use Zertifizierungstool\Model\Beantwortet;
use Zertifizierungstool\Model\Benutzer_Kurs;

/**
 * Controller, der Aufgaben verarbeitet, die sich auf die Entit�t "Pr�fung" beziehen.
 * Beinhaltet Actions zum Anlegen, Bearbeiten, Absolvieren und L�schen von Pr�fungen.
 * 
 * @author martin
 */
class PruefungController extends AbstractActionController {
	
	const pathToHtml	 = 'zertifizierungstool/pruefung/pruefung';
	
	const PRUEFUNG 		 = "Pruefung";
	const createFragen   = "Fragen anlegen";
	const editFragen	 = "Fragen bearbeiten";
	
	/** Das behandelte Pr�fungs-Objekt */
	private $pruefung;
	
	public function takeAction() {
		$errors = array();
		// Pr�fung laden
		$pruefung_id = $this->params()->fromRoute('id');
		$pruefung = new Pruefung();
		$pruefung->load($pruefung_id);
		
		// Kurs laden
		$kurs = new Kurs();
		$kurs->load($pruefung->getKursId());
		
		
		$benutzer_kurs = new Benutzer_Kurs();
		if (!$benutzer_kurs->alreadyexist(User::currentUser()->getBenutzername(), $pruefung->getKursId())) {
			array_push($errors, 'Fehler: Sie sind nicht im Kurs eingetragen.');
			
		} elseif (strtotime($kurs->getKurs_ende()) < strtotime(date())) {
			array_push($errors, 'Fehler: Der Kurs ist bereits beendet.');
		} else {
		
		// Pr�fen, ob eine Pr�fung im Kurs bereits 3 mal nicht bestanden wurde und der Kurs damit endg�ltig nicht bestanden ist
		$alle_pruefungen = Pruefung::loadList($pruefung->getKursId());
		
		if ($pruefungen == false) {
			// Fehler oder leer
		}
		
		$failed = false;
		foreach ($alle_pruefungen as $p) {
			if (SchreibtPruefung::attempts(User::currentUser()->getBenutzername(), $p->getId()) >= 3) {
				array_push($errors, 'Fehler: Sie haben die Pr&uuml;fung' .$p->getName() .'bereits 3 mal nicht bestanden und sind daher zu keinen Pr&uuml;fungen mehr zugelassen.');
				$failed = true;
			}
		}
			
		// Falls der Benutzer die Pr�fung schon geschrieben hat
		$last_try = new SchreibtPruefung();
		if ($failed == false && $last_try->loadLastTry($pruefung->getId())) {
			
			if ($last_try->getBestanden() == 1) {
				array_push($errors, 'Fehler: Sie haben die Pr&uuml;fung bereits bestanden.');
				
			} else {
				$min_timestamp = strtotime($last_try->getZeitpunkt()) + (60 * 60 * 24);
				if (time() < $min_timestamp) {
					array_push($errors, 'Fehler: Sie k&ouml;nnen die Pr&uuml;fung erst 24 Stunden nach Ihrem letzten Versuch wiederholen.' .strftime('%d.%m.%y %T', $min_timestamp));
				}
			}
		}
		
		if (!empty($errors)) return new ViewModel(['errors' => $errors]);
		

		// Eintrag in Tabelle schreibt_pruefung
		$schreibt_pruefung = new SchreibtPruefung("", $pruefung_id);
		
		if (!$schreibt_pruefung->saveNew()) {
			array_push($errors, "Fehler beim Vorbereiten der Pr&uuml;fung!");
		}
		
		// Alle Fragen zur Pr�fung laden
		$fragen = Frage::loadList($pruefung_id);
		if (!$fragen || empty($fragen)) {
			array_push($errors, "Fehler: Es konnten keine Pr&uuml;fungsfragen geladen werden!");
		}
		
		// F�r jede Frage:
		foreach ($fragen as $frage) {
			// Alle Antworten laden
			$antworten = Antwort::loadList($frage->getId());
			
			// F�r jede Antwort:
			foreach ($antworten as $antwort) {
				// Objekt von "beantwortet" erzeugen und in Db speichern
				// extra-Attribut "edited"? (gesetzt sobal User auf "Weiter" oder so geklickt hat)
				$beantwortet = new Beantwortet("", $schreibt_pruefung->getId(), $antwort->getId(), 0);
				
				if (!$beantwortet->saveNew()) {
					array_push($errors, "Fehler beim Vorbereiten der Pr&uuml;fungsfragen!");
					continue;
				}
			}
		}
		}
		
		if (empty($errors)) {
			// Weiterleiten an FrageController Action answer
			header("refresh:0; url = /frage/answer/" .$schreibt_pruefung->getId());
		}

		return new ViewModel(['errors' => $errors]);
	}
	
	/**
	 * �berpr�ft, ob ein Teilnehmer eine Pr�fung und damit ggf. den Kurs bestanden hat
	 */
	public function resultAction() {
		$punkte_gesamt = 0;
		$punkte = 0;
		
		$schreibt_pruefung_id = $this->params()->fromRoute('id');
		$schreibt_pruefung = new SchreibtPruefung();
		$schreibt_pruefung->load($schreibt_pruefung_id);
		
		
		// Alle Fragen zur Pr�fung laden
		$fragen = Frage::loadList($schreibt_pruefung->getPruefungId());
		
		// Alle Fragen durchgehen
		foreach ($fragen as $frage) {
			// Punkte aufsummieren, um m�gliche Gesamtpunktzahl zu ermitteln
			$punkte_gesamt += $frage->getPunkte();
			
			
			
			// Wurde die Frage komplett richtig beantwortet, k�nnen die Punkte addiert werden
			if (FrageController::check($frage->getId(), $schreibt_pruefung_id)) {
				$punkte += $frage->getPunkte();
			}
		}

		// Punktzahl mit ben�tigtem Cutscore vergleichen
		$pruefung = new Pruefung();
		$pruefung->load($schreibt_pruefung->getPruefungId());
		
		if (($punkte / $punkte_gesamt) >= $pruefung->getCutscore()) {
			$schreibt_pruefung->bestanden();
		
			/*
			// Pr�fen ob nun alle Pr�fungen zum Kurs bestanden wurden
			$kurs_bestanden = true;
			$pruefungen = Pruefung::loadList($pruefung->getKursId());
			foreach ($pruefungen as $p) {
				$last_try = new SchreibtPruefung();
				$last_try->loadLastTry($p->getId());
				// TODO Fehler
				if ($last_try->getBestanden() == 0) {
					$kurs_bestanden = false;
				}
			}
			
			if ($kurs_bestanden) {
				Benutzer_Kurs::bestanden($pruefung->getKursId());
			}
			*/
		}
	
		return new ViewModel([
				'schreibt_pruefung'  => $schreibt_pruefung
		]);
	}
	
	/**
	 * Verarbeitet das Formular zum Anlegen und Bearbeiten von Pr�fungen
	 * @param $request Daten aus Request-Array
	 * @param array $fragen Evtl. bereits angelegte Fragen
	 * @return \Zend\View\Model\ViewModel
	 */
	private function handleForm($request, $fragen = array()) {
		// Array, das eventuelle Fehlermeldungen enth�lt
		$errors = array();
		
		if (isset($request['speichernPruefung'])) {
		// Der Benutzer hat das Formular abgesendet
		
			// Neues Pr�fungs-Objekt mit den Daten aus dem Formular erzeugen
			$this->pruefung = new Pruefung(
					$request["pruefid"],
					$request["name"],
					$request["termin"],
					$request["kursid"],
					$request["cutscore"] / 100 );
		
			// Format des Pr�fungstermins �berpr�fen
			if (strtotime($this->pruefung->getTermin()) == false) {
				array_push($errors, "Ung&uuml;ltiges Datums-Format beim Pr&uuml;fungstermin!");
			}
			// Pr�fungstermin validieren -> Muss nach Kursbeginn und mind. 4 Tage vor Kursende liegen
			$kurs = new Kurs();		
			if ($kurs->load($this->pruefung->getKursId())) {
				$start = strtotime($kurs->getKurs_start());
				$ende  = strtotime($kurs->getKurs_ende());
				$termin = strtotime($this->pruefung->getTermin());
				
				if ($termin < $start) {
					array_push($errors, "Der Pr&uuml;fungszeitraum kann erst nach Kursbeginn starten!");
					
				}else {
					// Datum ermitteln, zu dem die Pr�fung sp�testens verf�gbar sein muss
					$latest_date = new \DateTime(strftime('%F', $ende));
					$latest_date->modify('-4 days');
					if ($termin > strtotime($latest_date->format('Y-m-d'))) {
						array_push($errors, "Der Pr&uuml;fungszeitraum muss mindestens 4 Tage vor Kursende starten! Also sp&auml;testens am " .$latest_date->format('d.m.Y'));
					}
				}
			}else {
				array_push($errors, "Der Kurs wurde nicht in der Datenbank gefunden!");
			}
			
			// Falls bisher keine Fehler aufgetreten sind, versuchen die Pr�fung zu speichern
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
	 * Legt die Kopfdaten einer neuen Pr�fung in der Datenbank an.
	 * @return \Zend\View\Model\ViewModel
	 */
	public function createAction() {
		if (empty($_REQUEST["kursid"])) {
			$newKursid = $this->params()->fromRoute('id');	
		}else {
			$newKursid = $_REQUEST["kursid"];
		}
		
		$this->pruefung = new Pruefung();
		$this->pruefung->setKursId($newKursid);
		
		$kurs = new Kurs();
		$kurs->load($this->pruefung->getKursId());
		
		if (!User::currentUser()->istAdmin() && !$kurs->getBenutzername() == User::currentUser()->getBenutzername()) {
			header ("refresh:0; url = /");
			exit;
		}
		
		return $this->handleForm($_REQUEST);
	}
	
	/**
	 * Bearbeitet die Kopfdaten einer Pr�fung.
	 * @return \Zend\View\Model\ViewModel
	 */
	public function editAction() {
		if (empty($_REQUEST["pruefid"])) {
			$pruefung_id = $this->params()->fromRoute('id');
		}else {
			$pruefung_id = $_REQUEST["pruefid"];
		}
		
		$this->pruefung = new Pruefung();
		$this->pruefung->load($pruefung_id);
		
		$kurs = new Kurs();
		$kurs->load($this->pruefung->getKursId());
		
		if (!User::currentUser()->istAdmin() && !$kurs->getBenutzername() == User::currentUser()->getBenutzername()) {
			header ("refresh:0; url = /");
			exit;
		}
		
		if (strtotime($this->pruefung->getTermin()) <= time()) {
			header ("refresh:0; url = /");
		}
		
		return $this->handleForm($_REQUEST, Frage::loadList($this->pruefung->getId()));
	}
	
	
	/**
	 * L�scht eine Pr�fung.
	 */
	public function deleteAction() {
		$pruefung_id_toDelete = $this->params()->fromRoute('id');
		$pruefung = new Pruefung();
		$pruefung->load($pruefung_id_toDelete);
		
		if ($pruefung->getTermin() <= time()) {
			header ("refresh:0; url = /");
		}
		
		$kurs = new Kurs();
		$kurs->load($pruefung->getKursId());
		
		if (!$kurs->getBenutzername() == User::currentUser()->getBenutzername()) {
			header ("refresh:0; url = /");
			exit;
		}
		
		
		
		$fragen_to_delete = Frage::loadList($pruefung->getId());
		
		foreach ($fragen_to_delete as $frage) {
			$antworten_to_delete = Antwort::loadList($frage->getId());
			foreach ($antworten as $antwort) {
				Antwort::delete($antwort->getId());
			}
			
			Frage::delete($frage->getId());
		}
		
		Pruefung::delete($pruefung->getId());
		
		header ("refresh:0; url = /pruefung/overview/" .$pruefung->getKursId());
	}
	
	/**
	 * Listet alle Pr�fungen auf, die zu einem Kurs geh�ren
	 */
	public function overviewAction() {
		$error = '';
		$kursid = $this->params()->fromRoute('id');
		
		$kurs = new Kurs();
		$kurs->load($kursid);
		
		$benutzer_kurs = new Benutzer_Kurs();
		// User muss Admin sein
		// oder Zertifizierer und Kursleiter
		// oder Teilnehmer und eingetragen im Kurs
		if (!User::currentUser()->istAdmin() && 
				(!User::currentUser()->istZertifizierer() && $kurs->getBenutzername() == User::currentUser()->getBenutzername()) &&
				(!User::currentUser()->istTeilnehmer() && $benutzer_kurs->alreadyexist(User::currentUser()->getBenutzername(), $kursid))) {
			header ("refresh:0; url = /");
		
		} else {
		
		// Pr�fen, ob eine Pr�fung im Kurs bereits 3 mal nicht bestanden wurde und der Kurs damit endg�ltig nicht bestanden ist
		if ($benutzer_kurs->alreadyexist(User::currentUser()->getBenutzername(), $kursid)) {
			$pruefungen = Pruefung::loadList($kursid);
			
			if ($pruefungen == false) {
				// Fehler oder leer
			}
			
			$failed = false;
			foreach ($pruefungen as $p) {
				if (SchreibtPruefung::attempts(User::currentUser()->getBenutzername(), $p->getId()) >= 3) {
					$error = 'Fehler: Sie haben die Pr&uuml;fung' .$p->getName() .'bereits 3 mal nicht bestanden und sind daher zu keinen Pr&uuml;fungen mehr zugelassen.';
					$failed = true;
				}
			}
		}
		
		}
		
		
		return new ViewModel([
				'pruefungen' => $pruefungen,
				'kursid'	 => $kursid,
				'error'		 => $error
		]);
	}
}