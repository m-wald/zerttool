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
            
            //Pr�fung, ob Kursstartdatum vor -enddatum
            $start  = $_REQUEST["kursstart"];
            $end    = $_REQUEST["kursende"];
            $starttimestamp = strtotime($start);
            $endtimestamp   = strtotime($end);
            
            if($starttimestamp > $endtimestamp){
                return new ViewModel(['error' => 'falsedate']);
            }

            
            //todo Enddatum in der Zukunft abprüfen?
            
            
            
            //todo Admin legt Kurs an -> Admin ist kein Kursleiter
            /*
            if(User::currentUser()->istAdmin()){
                
            }
            
             * 
             */
            $user = new User();
            $user -> currentUser();
            
            $kurs = new Kurs(
                    $_REQUEST["kursname"], 
                    $_REQUEST["kursstart"], 
                    $_REQUEST["kursende"], 
                    $_REQUEST["sichtbarkeit"],
                    User::currentUser()->getBenutzername());
            
            unset($createkurs);
            $createkurs = $kurs->save();
            
            if(isset($createkurs))
            	return new ViewModel(['message' => 'erfolgt']);
            else 
            	return new ViewModel(['error' => 'nichtangelegt']);
            
            
	}
		
		
	return new ViewModel();
	      
        
    }
    
    public function anlegentestAction()
    {
	$kurs = new Kurs("ITM", "01.12.2016", "31.12.2016", 0, "aaa");
		
	$kurs->save();
		
	return new ViewModel();
    }
    
	    
    public function changedataAction() {
   			
        if($_SERVER['REQUEST_METHOD'] == 'POST') {

        //Kurs::currentKurs()->update($_REQUEST["kurs_name"], $_REQUEST["kurs_start"], $_REQUEST["kurs_ende"], $_REQUEST["sichtbarkeit"]);
        Kurs::currentKurs()->load(User::currentKurs()->getKurs_id());
        $_SESSION["currentKurs"] = serialize(User::currentKurs());
        return new ViewModel(['status' => "erfolgreich"]);
        }
        else {
        return new ViewModel(['kursdaten' => array(User::currentKurs()->getBenutzername(),User::currentUser()->getVorname(), User::currentUser()->getNachname(), User::currentUser()->getGeburtsdatum(), User::currentUser()->getStrasse(), User::currentUser()->getPLZ(), User::currentUser()->getOrt(), User::currentUser()->getEmail())]);
        }	
   }
   
   public function uebersichtAction(){
       
   }
    
}

