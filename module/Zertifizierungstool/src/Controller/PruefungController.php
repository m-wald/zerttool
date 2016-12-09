<?php
namespace Zertifizierungstool\Controller;


use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zertifizierungstool\Model\Pruefung;
use Zertifizierungstool\Model\Kurs;
use Zertifizierungstool\Model\User;

/**
 * TODO Dokumentation
 * @author martin
 *
 */
class PruefungController extends AbstractActionController {
	
	public function createAction() {
		// Array, das eventuelle Fehlermeldungen enthält
		$errors = array();
		$result = false;
		
		// Berechtigungsprüfung
		if (!User::currentUser()->istAdmin() && !User::currentUser()->istZertifizierer()) {
			array_push($errors, "Keine Berechtigung!");
		}

		// Erzeugung des Prüfungs-Objekts mit Übergabe der zugehörigen Kurs-Id
		if (isset($this->params()->fromRoute('id'))) {
			$pruefung = new Pruefung($kursid = $this->params()->fromRoute('id'));
		} else {
			array_push($errors, "Es konnte kein Kurs zugeordnet werden!");
		}
				
		
		if ($_REQUEST['speichern']) {
			
			$pruefung = new Pruefung("", 
					$_REQUEST["name"],
					$_REQUEST["termin"], 
					$_REQUEST["kursid"], 
					$_REQUEST["cutscore"] );
			
			// TODO Format des Prüfungstermins überprüfen
			// Prüfungstermin validieren
			//array_push($errors, $this->checkDate($pruefung));
			
			if (empty($errors)) {
				if (!$pruefung->saveNew()) {
					array_push($errors, "Fehler beim Speichern der Prüfung. Bitte erneut versuchen!");
				}else {
					// FrageController->anlegenAction() mit Parameter Prüfungs-Id;
					// oder hier createQuestionAction()
					// Prüfung neu laden, damit Id gesetzt wird
					$result = true;
				}
			}
		}
			
		return new ViewModel([
				'pruefung' => array($pruefung),
				'errors'   => $errors,
				'result'   => $result	
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
		
		if ($kurs->laden($pruefung->getKursId())) {
			if ($pruefung->getTermin() < $kurs->getKurs_start()) {
				$error= "Der Prüfungszeitraum kann erst nach Kursbeginn beginnen!";
			
			}elseif ($pruefung->getTermin() > date_sub($kurs->getKurs_start(), new \DateInterval("P4D"))) {
				$error = "Der Prüfungszeitraum muss spätestens 4 Tage vor Kursende beginnen!";
			}
		}else {
			$error = "Der Kurs wurde nicht in der Datenbank gefunden!";
		}
		
		return $error;
	}
}