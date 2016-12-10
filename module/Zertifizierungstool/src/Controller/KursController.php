<?php
namespace Zertifizierungstool\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zertifizierungstool\Model\Kurs;
use Zertifizierungstool\Model\User;

class KursController extends AbstractActionController
{
    public function anlegenAction(){
        
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            
            //Prüfung, ob Kursstartdatum vor -enddatum
            if($_REQUEST("kursstart") > $_REQUEST("kursende")){
                $error = "Das Kursendedatum liegt vor dem Kursstartdatum!";
                return new ViewModel(['error' => $error]);
            }
            
            //todo Enddatum in der Zukunft abprüfen?
            
            
            $kurs = new Kurs(
                    $_REQUEST["kursname"], 
                    $_REQUEST["kursstart"], 
                    $_REQUEST["kursende"], 
                    $_REQUEST["sichtbarkeit"],
                    User::currentUser());
            
            $createkurs = $kurs->save();
            
            return new ViewModel(['message' => $createkurs]);
	}
		
	else
            return new ViewModel();
        
    }
}

