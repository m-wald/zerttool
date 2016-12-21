<?php
namespace Zertifizierungstool\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zertifizierungstool\Model\Kurs;
use Zertifizierungstool\Model\User;
use Zertifizierungstool\Model\CSV_invite;

class KursController extends AbstractActionController
{   
    public function anlegenAction(){
        
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            
            //Prï¿½fung, ob Kursstartdatum vor -enddatum
            $start  = $_REQUEST["kursstart"];
            $end    = $_REQUEST["kursende"];
            $starttimestamp = strtotime($start);
            $endtimestamp   = strtotime($end);
            
            if($starttimestamp > $endtimestamp){
                return new ViewModel(['error' => 'falsedate']);
            }

            
            //todo Enddatum in der Zukunft abprÃ¼fen?
            
            
            
            //todo Admin legt Kurs an -> Admin ist kein Kursleiter
            /*
            if(User::currentUser()->istAdmin()){
                
            }
            
             * 
             */
            
            $kurs = new Kurs(
                    $_REQUEST["kursname"], 
                    $_REQUEST["kursstart"], 
                    $_REQUEST["kursende"], 
                    $_REQUEST["sichtbarkeit"],
                    User::currentUser()->getBenutzername(),
                    $_REQUEST["beschreibung"]);
            
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
	$kurs = new Kurs("ITM", "01.12.2016", "31.12.2016", 0, "aaa", "abcdefg");
		
	$kurs->save();
		
	return new ViewModel();
    }
    
    /*
    public function ladentestAction(){
        $kurs = new Kurs();
        $laden = $kurs->loadKurse(User::currentUser()->getBenutzername());
        return new ViewModel(['result' => $laden]);  
    }
    */
    
    /*
     * LÃ¤dt Kurse eines Zertifizerers und Ã¼bergibt diese der View
     * @return Kurse eines Zertifizierers
     */
    public function showkurseAction(){
        $kurs = new Kurs();
        /*
         * Wenn Zertifizierer, dann soll er nur seine Kurse angezeigt bekommen.
         * Wenn Admin oder Teilnehmer, dann soll NULL als Parameter Ã¼bergeben werden,
         * damit in der SQL-Query nicht nach dem Benutzernamen gefiltert wird
         */
        if(User::currentUser()->istZertifizierer()){
            $kurseladen = $kurs->loadKurse(User::currentUser()->getBenutzername());
        }elseif((User::currentUser()->istTeilnehmer()) || (User::currentUser()->istAdmin())){
            $kurseladen = $kurs->loadKurse(NULL);
        }
        return new ViewModel(['result' => $kurseladen]); 
    }
    
    /*
     * ÃœberprÃ¼ft ob KursÃ¤nderungen gespeichert werden sollen und ruft die
     * Query zum Speichern der Kursdaten auf 
     * @return Status (Fehler/Erfolg) und Kursdaten 
     */
    public function changedataAction(){
    	$id = $_REQUEST["kurs_id"];
    	$kurs = new Kurs();
    	if(!$kurs->load($id)) $status="Fehler beim Laden des Kurses!";
    	
        if($_REQUEST["speichern"]) {
            $kurs->update($_REQUEST["kursid"], $_REQUEST["kursname"], $_REQUEST["kursstart"], $_REQUEST["kursende"], $_REQUEST["sichtbarkeit"], $_REQUEST["beschreibung"]);
            $kurs = new Kurs(
                    $_REQUEST["kursid"],
                    $_REQUEST["kursname"],
                    $_REQUEST["kursstart"],
                    $_REQUEST["kursende"],
                    $_REQUEST["sichtbarkeit"],
                    $_REQUEST["beschreibung"]); 
            $status = "Erfolgreich geÃ¤ndert."; 
        }
              return new ViewModel(['kurs' => $kurs,
        		'status' => $status]);    
    }
    
    public function kursviewAction(){
    	$id = $_REQUEST["kurs_id"];
    	$_SESSION['kurs_id']=$id;
    	$kurs = new Kurs();
    	if(!$kurs->load($id)) $status="Fehler beim Laden des Kurses!";
    	$_SESSION['kurs_name']=$kurs->getKurs_name();
    	//else {$kursview = $kurs->load($id); $status="Kurs wird gleich geladen...";}
    	//return new ViewModel(['kursview'=>$kursview, 'status' => $status]);
        return new ViewModel(['kurs' => $kurs,
        		'status' => $status]);  
    }
    
    
    public function csvinviteAction(){
    	
    	// Zugriff auf Action ist nur erlaubt, falls Zertifizierer oder Admin und Zugang über Button in kursview
    	if(User::currentUser()->getBenutzername()==null) {
    		header("refresh:0; url = /user/login");
    		exit;
    	}
    	
    	if(User::currentUser()->istTeilnehmer()==true){
    		header("refresh:0; url = /user/home");
    		exit;
    	}
    	
   	
   
   	if($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['site']=='csvinvite') {
   		
   		//Upload-Verzeichnis
   		
   		$upload_folder= 'data/uploadsCSV/';
   		$filename=pathinfo($_FILES['datei']['name'],PATHINFO_FILENAME);
   		$extension=strtolower(pathinfo($_FILES['datei']['name'], PATHINFO_EXTENSION));
   		
   		
   		//ï¿½berprï¿½fung der Dateiendung
   		
   		$allowed_extensions=array('csv');
   		
   		if(!in_array($extension, $allowed_extensions)) {
   			return new ViewModel(['meldung' => 'datentyp']);
   		} 
   		
   		//ï¿½berprï¿½fung der Dateigrï¿½ï¿½e
   		
   		$max_size = 2000000;                                //2 MB (in Byte angegeben)
   		
   		if($_FILES['datei']['size'] > $max_size) {
   				
   			return new ViewModel(['meldung' =>'dateigroesse']);
   		}
   		
   		//Pfad zum Upload
   		
   		$new_path = $upload_folder.$filename.'.'.$extension;
   		
   		//Neuer Dateiname falls die Datei bereits existiert
   		
   		if(file_exists($new_path)) { //Falls Datei existiert, hï¿½nge eine Zahl an den Dateinamen
   			$id = 1;
   			do {
   				$new_path = $upload_folder.$filename.'_'.$id.'.'.$extension;
   				$id++;
   			} while(file_exists($new_path));
   		}
   		
   		//Alles okay, verschiebe Datei an neuen Pfad
   		
   		if(move_uploaded_file($_FILES['datei']['tmp_name'], $new_path)) {
   			
   			$i=0;	
   			$nomail=array();
   			if (($handle = fopen($new_path, "r")) !== FALSE) {
   				while (($data = fgetcsv($handle, 1000)) !== FALSE) {
   					
   				   		$csv = new CSV_invite();
   				   		if(($csv->insert_data($data[0], $_POST['kurs_id'])) ==false){
   				   			$nomail[$i]=$data;
   				   			$i++;
   				   		}
   				   				
   					}
   				}
   				fclose($handle);
   			}
   			
   			return new ViewModel(['meldung' => 'erfolgreich','fehler' =>$nomail]);
   			}
   			
   	  	elseif(!empty($_SESSION['kurs_id'])){
   	  		
   			return new ViewModel();
   		}
   		//falls direkt auf diese Action zugegriffen wurde, ohne dass ein Kurs ausgewählt wurde!
   		else header("refresh:0; url = /kurs/showkurse");
   		exit;
   } 




public function uploadAction(){	
	
	
	// Zugriff auf Action ist nur erlaubt, falls Zertifizierer oder Admin und Zugang über Button in kursview
	if(User::currentUser()->getBenutzername()==null) {
		header("refresh:0; url = /user/login");
		exit;
	}
	 
	if(User::currentUser()->istTeilnehmer()==true){
		header("refresh:0; url = /user/home");
		exit;
	}
				 
	if($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['thissite']) {
		
		$kurs_id = $_SESSION["kurs_id"];
			
				 
		//Upload-Verzeichnis
		//Check ob Verzeichnis mit dem Kurs Id existiert
		//Wenn nein - erstellt neues
		$path= 'data/uploadsKurse/';
		$path_new = $path.$kurs_id.'/';
		
		if(!is_dir($path_new)) mkdir($path_new, 0777);
		
				
		$filename=pathinfo($_FILES['datei']['name'],PATHINFO_FILENAME);
		$extension=strtolower(pathinfo($_FILES['datei']['name'], PATHINFO_EXTENSION));
		
			 
		//ï¿½berprï¿½fung der Dateiendung
		 
		$allowed_extensions=array('pdf','word');
		 
		if(!in_array($extension, $allowed_extensions)) {
			return new ViewModel(['meldung' => 'datentyp']);
		}
		
				 
		//ï¿½berprï¿½fung der Dateigrï¿½ï¿½e
		 
		$max_size = 5000000;                                //5 MB (in Byte angegeben)
		 
		if($_FILES['datei']['size'] > $max_size) {
				
			return new ViewModel(['meldung' =>'dateigroesse']);
		}		
				 
		//Dateipfad
		
		$new_path = $path_new.$filename.'.'.$extension;
				 
		//Neuer Dateiname falls die Datei bereits existiert
		 
		if(file_exists($new_path)) { //Falls Datei existiert, hï¿½nge eine Zahl an den Dateinamen
			$id = 1;
			do {
				//$kurs_id = $_REQUEST["kurs_id"];
				if(move_uploaded_file($_FILES['datei']['tmp_name'], $path_new.$filename.'_'.$id.'.'.$extension)) {
						
					return new ViewModel(['meldung' => 'erfolgreich']);
				}
				
				//$new_path = $upload_folder.$filename.'_'.$id.'.'.$extension;
				$id++;
			} while(file_exists($new_path));
		}
		
		else {
			//$kurs_id = $_REQUEST["kurs_id"];
			if(move_uploaded_file($_FILES['datei']['tmp_name'], $path_new.$filename.'.'.$extension))
				{
			
				return new ViewModel(['meldung' => 'erfolgreich']);
				}
			}
		 
		//Alles okay, verschiebe Datei an neuen Pfad
		 		 
	}	 

	elseif(!empty($_SESSION['kurs_id'])){
	
		return new ViewModel();
	}
	//falls direkt auf diese Action zugegriffen wurde, ohne dass ein Kurs ausgewählt wurde!
	else header("refresh:0; url = /kurs/showkurse");
	exit;

  }
  
  
    public function showdocumentsAction(){
        $id = $_SESSION['kurs_id'];
       // $id = 12;
        $name = $_SESSION['kurs_name'];
            //$kurs = new Kurs();
            //if(!$kurs->load($id)) $status="Fehler beim Laden der Kursdokumente!";
        //Pfad wo die uploads gespeichert wurden
        $path = "data/uploadsKurse/".$id."/";
        
        //Ordner auslesen und in Variable speichern
        //$alldocuments = scandir($path);
        $alldocuments = array_diff(scandir($path), array('..', '.'));
        
        return new ViewModel([	'path' 			=> $path,
                                'alldocuments' 	=> $alldocuments,
                                'status' 		=> $status,
                                'kursname' 		=> $name]); 
    }
    
    
    public function deleteAction(){
    		
    	if($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['thissite']) {
    		
    		$path		= $_REQUEST["path"];
    		$document	= $_REQUEST["document"];
    		
    		if(is_writeable($path.$document)){
    			if(unlink(realpath($path.$document)))
    					return new ViewModel(['message'=>'Document deleted!']);
    			else 	return new ViewModel(['message'=>'Error by deleting the document!']);
    		}
    		else		return new ViewModel(['message'=>'Access denied!']);
    	
    	}
    }


/*public function upload_multAction(){
	
	$upload = new Zend_File_Transfer_Adapter_Http();
	$files  = $upload->getFileInfo();
	$names = $upload->getFileName();
	$size = $upload->getFileSize();
	$type = $upload->getMimeType();
	
	
	
	foreach($files as $file => $fileInfo) {
		if ($upload->isUploaded($file)) {
			if ($upload->isValid($file)) {
				if ($upload->receive($file)) {
					$info = $upload->getFileInfo($file);
					$tmp  = $info[$file]['tmp_name'];
	
	
		
	// Gibt die Dateinamen aller Dateien zurück
	$names = $upload->getFileName();
	
	// Gibt die Größen aller Dateien als Array zurück
	// wenn mehr als eine Datei hochgeladen wurde
	$size = $upload->getFileSize();
	
	
	
	
}*/


}


   				
   			
    


	

