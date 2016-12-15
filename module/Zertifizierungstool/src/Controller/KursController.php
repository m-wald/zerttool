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
    
	    
    /*public function changedataAction() {
   			
        if($_SERVER['REQUEST_METHOD'] == 'POST') {

        //Kurs::currentKurs()->update($_REQUEST["kurs_name"], $_REQUEST["kurs_start"], $_REQUEST["kurs_ende"], $_REQUEST["sichtbarkeit"]);
        Kurs::currentKurs()->load(User::currentKurs()->getKurs_id());
        $_SESSION["currentKurs"] = serialize(User::currentKurs());
        return new ViewModel(['status' => "erfolgreich"]);
        }
        else {
        return new ViewModel(['kursdaten' => array(User::currentKurs()->getBenutzername(),User::currentUser()->getVorname(), User::currentUser()->getNachname(), User::currentUser()->getGeburtsdatum(), User::currentUser()->getStrasse(), User::currentUser()->getPLZ(), User::currentUser()->getOrt(), User::currentUser()->getEmail())]);
        }	
   }*/
   
   public function changedataAction(){
    if(User::currentUser()->getBenutzername()==NULL){
		header("refresh:0; url= /user/login");
		exit;
    }

    else{
    		$kursdaten = array();
    		$user= User::currentUser()->getBenutzername();
         	$kursdaten = loadKurse($user);
         	
        }
        return new ViewModel(['kursarray' => 'kursdaten']);
    }
   
   
   
   public function csvinviteAction(){
   	
   
   	if($_SERVER['REQUEST_METHOD'] == 'POST') {
   		
   		//Upload-Verzeichnis
   		
   		$upload_folder= 'data/uploadsCSV/';
   		$filename=pathinfo($_FILES['datei']['name'],PATHINFO_FILENAME);
   		$extension=strtolower(pathinfo($_FILES['datei']['name'], PATHINFO_EXTENSION));
   		
   		
   		//�berpr�fung der Dateiendung
   		
   		$allowed_extensions=array('csv');
   		
   		if(!in_array($extension, $allowed_extensions)) {
   			die("<ausgabe>Ung&uuml;ltige Dateiendung. Nur CSV-Dateien sind erlaubt<!/ausgabe>");
   		} 
   		
   		//�berpr�fung der Dateigr��e
   		
   		$max_size = 1024*1024;                                   //1 MB
   		
   		if($_FILES['datei']['size'] > $max_size) {
   				
   			die("Bitte keine Dateien groesser 1MB hochladen");
   		}
   		
   		//Pfad zum Upload
   		
   		$new_path = $upload_folder.$filename.'.'.$extension;
   		
   		//Neuer Dateiname falls die Datei bereits existiert
   		
   		if(file_exists($new_path)) { //Falls Datei existiert, h�nge eine Zahl an den Dateinamen
   			$id = 1;
   			do {
   				$new_path = $upload_folder.$filename.'_'.$id.'.'.$extension;
   				$id++;
   			} while(file_exists($new_path));
   		}
   		
   		//Alles okay, verschiebe Datei an neuen Pfad
   		
   		move_uploaded_file($_FILES['datei']['tmp_name'], $new_path); 
   		
   		echo "erfolgreich";
   	} 
   	else{
   		return new ViewModel();
   	}
   }
}
   				
   			
    


	

