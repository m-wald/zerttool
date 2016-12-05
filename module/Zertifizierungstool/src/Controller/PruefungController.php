<?php
namespace Zertifizierungstool\Controller;


use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zertifizierungstool\Model\Pruefung;
use Zertifizierungstool\Model\Kurs;

/**
 * TODO Dokumentation
 * @author martin
 *
 */
class PruefungController extends AbstractActionController {
	
	function anlegenAction() {
		// TODO Berechtigungsprüfung
		
		$pruefung = new Pruefung($this->params()->fromRoute('id'));
		
		
		
		if ($_REQUEST['speichern']) {
			// Array, das eventuelle Fehlermeldungen enthält
			$errors = array();
			
			// Pruefung-Objekt mit Daten aus Request-Array füllen
			$pruefung->setId($_REQUEST["id"]);
			$pruefung->setName($_REQUEST["name"]);
			$pruefung->setTermin($_REQUEST["termin"]);
			$pruefung->setCutscore($_REQUEST["cutscore"]);
			$pruefung->setKursId($_REQUEST["kursid"]);
			
			// Termin der Prüfung muss nach Kursbeginn liegen und mindestens 4 Tage vor Kursende
				// Kurs laden, zu dem die Prüfung gehört	
				$kurs = new Kurs();
				if (!$kurs->laden($pruefung->getKursId())) {
					// Fehlermeldung: Der Kurs wurde nicht in der Datenbank gefunden
				}
				
				// Termin überprüfen
				if ($pruefung->getTermin() < $kurs->getKurs_start()) {
					array_push($errors, "Der Prüfungszeitraum kann erst nach Kursbeginn beginnen!");
					
				}elseif ($pruefung->getTermin() > date_sub($kurs->getKurs_start(), new \DateInterval("P4D"))) {
					array_push($errors, "Der Prüfungszeitraum muss spätestens 4 Tage vor Kursende beginnen!");
				}

			// Falls keine Fehler => FrageController->anlegenAction() mit Parameter Prüfungs-Id;
		}
		
		return new ViewModel([
				'pruefung' => array($pruefung),
				'errors'   => $errors
		]);
	}
}