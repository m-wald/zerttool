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
     * Lädt Kurse eines Zertifizerers und übergibt diese der View
     * @return Kurse eines Zertifizierers
     */
    public function showkurseAction(){
        $kurs = new Kurs();
        /*
         * Wenn Zertifizierer, dann soll er nur seine Kurse angezeigt bekommen.
         * Wenn Admin oder Teilnehmer, dann soll NULL als Parameter übergeben werden,
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
     * Überprüft ob Kursänderungen gespeichert werden sollen und ruft die
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
            $status = "Erfolgreich geändert."; 
        }
              return new ViewModel(['kurs' => $kurs,
        		'status' => $status]);    
    }
    
    public function kursviewAction(){
    	$id = $_REQUEST["kurs_id"];
    	$_SESSION['kurs_id']=$id;
    	$kurs = new Kurs();
    	if(!$kurs->load($id)) $status="Fehler beim Laden des Kurses!";
    	//else {$kursview = $kurs->load($id); $status="Kurs wird gleich geladen...";}
    	//return new ViewModel(['kursview'=>$kursview, 'status' => $status]);
        return new ViewModel(['kurs' => $kurs,
        		'status' => $status]);  
    }
    
    
    public function csvinviteAction(){
   	
   
   	if($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['thissite']) {
   		
   		//Upload-Verzeichnis
   		
   		$upload_folder= 'data/uploadsCSV/';
   		$filename=pathinfo($_FILES['datei']['name'],PATHINFO_FILENAME);
   		$extension=strtolower(pathinfo($_FILES['datei']['name'], PATHINFO_EXTENSION));
   		
   		
   		//�berpr�fung der Dateiendung
   		
   		$allowed_extensions=array('csv');
   		
   		if(!in_array($extension, $allowed_extensions)) {
   			return new ViewModel(['meldung' => 'datentyp']);
   		} 
   		
   		//�berpr�fung der Dateigr��e
   		
   		$max_size = 2000000;                                //2 MB (in Byte angegeben)
   		
   		if($_FILES['datei']['size'] > $max_size) {
   				
   			return new ViewModel(['meldung' =>'dateigroesse']);
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
   			
   	  	else{
   			return new ViewModel(['kurs_id' => $_REQUEST['kurs_id']]);
   		}
   } 




public function uploadAction(){
	
	
	
				 
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
		
		$kurs_id = $_REQUEST["kurs_id"];
		
		
				 
		//Upload-Verzeichnis
		//Check ob Verzeichnis mit dem Kurs Id exists
		//Wenn nein - erstellt neues
		$path= 'data/uploadsKurse/';
		$path_new = $path.$kurs_id.'/';
		
		if(!is_dir($path_new)) mkdir($path_new, 0777);
		
		//return new ViewModel(['kurs_id' => $kurs_id]);
		
		echo "Directory am Anfang: ".$path_new."<br>";
	
			
		
		$filename=pathinfo($_FILES['datei']['name'],PATHINFO_FILENAME);
		$extension=strtolower(pathinfo($_FILES['datei']['name'], PATHINFO_EXTENSION));
		
		echo "1: ".$kurs_id."<br>";
		 
		//�berpr�fung der Dateiendung
		 
		$allowed_extensions=array('pdf','word');
		 
		if(!in_array($extension, $allowed_extensions)) {
			return new ViewModel(['meldung' => 'datentyp']);
		}
		
		echo "2: ".$kurs_id."<br>";
		 
		//�berpr�fung der Dateigr��e
		 
		$max_size = 5000000;                                //5 MB (in Byte angegeben)
		 
		if($_FILES['datei']['size'] > $max_size) {
				
			return new ViewModel(['meldung' =>'dateigroesse']);
		}
		
		echo "3: ".$kurs_id."<br>";
		 
		//Pfad zum Upload
		$kurs_id=$_POST["kurs_id"];
		$new_path = $path.$kurs_id.'/'.$filename.'.'.$extension;
		echo "Gespeichert in: ".$new_path."<br>";
		if($path_new!=$new_path) echo "Wieder falsches UploadVerzeichnis!"."<br>";
		if(empty($_POST["kurs_id"])) echo "Kurs_id ist gleich NULL!";
		 
		//Neuer Dateiname falls die Datei bereits existiert
		 
		if(file_exists($new_path)) { //Falls Datei existiert, h�nge eine Zahl an den Dateinamen
			$id = 1;
			do {
				//$kurs_id = $_REQUEST["kurs_id"];
				if(move_uploaded_file($_FILES['datei']['tmp_name'], $path.$kurs_id.'/'.$filename.'_'.$id.'.'.$extension)) {
						
					return new ViewModel(['meldung' => 'erfolgreich']);
				}
				
				//$new_path = $upload_folder.$filename.'_'.$id.'.'.$extension;
				$id++;
			} while(file_exists($new_path));
		}
		
		else {
			//$kurs_id = $_REQUEST["kurs_id"];
			if(move_uploaded_file($_FILES['datei']['tmp_name'], $path.$kurs_id.'/'.$filename.'.'.$extension))
				{
			
				return new ViewModel(['meldung' => 'erfolgreich']);
				}
			}
		 
		//Alles okay, verschiebe Datei an neuen Pfad
		 
		/*if(move_uploaded_file($_FILES['datei']['tmp_name'], $new_path)) {
			
			return new ViewModel(['meldung' => 'erfolgreich']);
			//echo $new_path;
		}*/
		 
		 
	}	 

	else{
		return new ViewModel(['kurs_id' => $kurs_id]);
	}
  }
  
  
    public function showdocumentsAction(){
        $id = $_REQUEST["kurs_id"];
        $name = $_REQUEST["kurs_name"];
            //$kurs = new Kurs();
            //if(!$kurs->load($id)) $status="Fehler beim Laden der Kursdokumente!";
        //Pfad wo die uploads gespeichert wurden
        $path = "data/uploadsKurse/'.$id.'";
        
        //Ordner auslesen und in Variable speichern
        $alldocuments = scandir($path);
        
        return new ViewModel(['path' => $path,
                                'alldocuments' => $alldocuments,
                                'status' => $status,
                                'kursname' => $name]); 
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
	
	
		
	// Gibt die Dateinamen aller Dateien zur�ck
	$names = $upload->getFileName();
	
	// Gibt die Gr��en aller Dateien als Array zur�ck
	// wenn mehr als eine Datei hochgeladen wurde
	$size = $upload->getFileSize();
	
	
	
	
}*/


}


   				
   			
    


	

