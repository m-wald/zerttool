<?php
namespace Zertifizierungstool\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zertifizierungstool\Model\Kurs;
use Zertifizierungstool\Model\User;
use Zertifizierungstool\Model\CSV_invite;
use Zertifizierungstool\Model\Benutzer_Kurs;

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
    
    /**
     * Wenn Zertifizierer, dann Kurse des Zertifizierers laden und der View Ã¼bergeben
     * Wenn Admin oder Teilnehmer, dann entsprechende Kurse laden und der View Ã¼bergeben
     * @return Kurse eines Zertifizierers, bzw. je nach Regelung die einsehabren Kurse fÃ¼r Admin und Teilnehmer an die View
     */
    public function showkurseAction(){
        $kurs = new Kurs();
        /*
         * Wenn Zertifizierer, dann soll er nur seine Kurse angezeigt bekommen.
         * Wenn Admin oder Teilnehmer, dann soll NULL als Parameter Ã¼bergeben werden,
         * damit in der SQL-Query nicht nach dem Benutzernamen gefiltert wird
         * 
         * TODO Admin hat doch auch alle Funktionen eines Zertifizierers oder?
         * 		Dann kann er ja theorethisch auch Kurse haben, in denen er Kursleiter ist
         * TODO Es werden momentan ja nur die Kurse geladen und angezeigt, die schon gestartet sind.
         * 		Ein Zertifizierer will aber vielleicht noch vor Kursbeginn irgendwelche Daten ändern oder
         * 		Teilnehmer wollen auch Kurse sehen, die sie später belegen wollen.
         * 		Deswegen mein Vorschlag: Alle Kurse laden, die noch nicht beendet sind.
         * Sind aber nur Ideen, also falls ihr da anderer Meinung seid, gebt Bescheid ;)
         */
        if(User::currentUser()->istZertifizierer()){
            $kurseladen = $kurs->loadKurse(User::currentUser()->getBenutzername());
        }elseif((User::currentUser()->istTeilnehmer()) || (User::currentUser()->istAdmin())){
            $kurseladen = $kurs->loadKurse(NULL);
        }
        return new ViewModel(['result' => $kurseladen]); 
    }
    
    /**
     * LÃ¤dt Kurse zu denen sich der Teilnehmer eingetragen hat und Ã¼bergibt diese
     * @return Kurse des Teilnehmers an die View
     */
    public function showsignedkurseAction() {
        $kurs = new Kurs();
        if(User::currentUser()->istTeilnehmer()) {
            $signedkurse = $kurs->loadsignedkurse(User::currentUser()->getBenutzername());
        }
        return new ViewModel(['result' => $signedkurse]);
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
    	if(isset($_POST["back"]) && !empty($_POST["kurs_id"]))
    		$id = $_POST["kurs_id"];
    	else 
    		$id = $_REQUEST["kurs_id"];
    	
    	$_SESSION['kurs_id']=$id;
    	$kurs = new Kurs();
        $benutzer_kurs = new Benutzer_Kurs();
    	if(!$kurs->load($id)) $status="Fehler beim Laden des Kurses!";
    	$_SESSION['kurs_name']=$kurs->getKurs_name();
        return new ViewModel(['kurs' => $kurs,
        		'status' => $status,
                        'benutzer_kurs' => $benutzer_kurs]);  
    }
    
    public function singleinviteAction() {
    	
    	if(User::currentUser()->getBenutzername()==null) {
    		header("refresh:0; url = /user/login");
    		exit;
    	}
    	 
    	if(User::currentUser()->istTeilnehmer()==true){
    		header("refresh:0; url = /user/home");
    		exit;
    	}
    	
    	if($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['site']=='singleinvite') {
    		
    		$csv = new CSV_invite();
    		if ($csv->insert_data($_REQUEST['email'], $_SESSION['kurs_id'])) {
    			
    			$csv->inviteMail($_REQUEST['email'], $_SESSION['kurs_id']);
    			
    			return new ViewModel(['erfolgreich' => $_REQUEST['email']]);
    			
    		}else {
    			
    			return new ViewModel(['fehler' =>$_REQUEST['email']]);
    			
    		}
    		
    	}
    	elseif(!empty($_SESSION['kurs_id'])){
    	
    		return new ViewModel();
    	}
	    	//falls direkt auf diese Action zugegriffen wurde, ohne dass ein Kurs ausgewï¿½hlt wurde!
	    	else header("refresh:0; url = /kurs/showkurse");
	    	exit;
    }
    	
    
    
    
    
    
    public function csvinviteAction(){
    	
    	// Zugriff auf Action ist nur erlaubt, falls Zertifizierer oder Admin und Zugang ï¿½ber Button in kursview
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
   			
   			   			
   			$nomail=array();
   			$i=0;
   			$falsetype=array();
   			$j=0;
   			if (($handle = fopen($new_path, "r")) !== FALSE) {
   				while (($data = fgetcsv($handle, 1000,";")) !== FALSE) {
   					
   				   		$csv = new CSV_invite();
   				   		
   				   		//falls in der CSV-Datei mehr als eine Spalte befüllt wird (nur eine E-Mail-Adresse pro Zeile!!)
   				   		$num = count($data);
   				 	
   				   		if($num>1){
   				   			$falsetype[$j]=$data;
   				   			$j++;
   				   			continue;
   				   		}
   				   		
   				   		
   				   		//Prüfung, ob es sich um E-Mail-Adresse handelt
   				   		if (!filter_var($data, FILTER_VALIDATE_EMAIL)) {
   				   			$falsetype[$j]=$data;
   				   			$j++;
   				   			continue;
   				   		}
   				   		
   				   		
   				   		if(($csv->insert_data($data[0], $_POST['kurs_id'])) ==false){
   				   			$nomail[$i]=$data;
   				   			$i++;
   				   		}
   				   		else {
   				   			$csv->inviteMail($data[0], $_SESSION['kurs_id']);
   				   		}
   				   				
   					}
   				}
   				fclose($handle);
   			}
   			
   			return new ViewModel(['meldung' => 'erfolgreich','fehler' =>$nomail, 'falsetype'=>$falsetype]);
   			}
   			
   	  	elseif(!empty($_SESSION['kurs_id'])){
   	  		
   			return new ViewModel();
   		}
   		//falls direkt auf diese Action zugegriffen wurde, ohne dass ein Kurs ausgewï¿½hlt wurde!
   		else header("refresh:0; url = /kurs/showkurse");
   		exit;
    } 

    public function enterkursAction() {
   	// zeitliche Gï¿½ltigkeit des Kurses ï¿½berprï¿½fen
   	
    	$kurs= new Kurs();
    	if (!$kurs->active($_REQUEST['kurs_id'])){
    		return new ViewModel(['meldung' =>'dateerror']);
    	}
   	
    	
    //Fall: Teilnehmer klickt auf Einladungs-Link (ist noch nicht registriert). ABER: Fehlerabfangen für den Fall
    //dass sich der Teilnehmer in der Zwischenzeit schon am System registriert hat!
    
    if(isset($_REQUEST['email'])) {
   		$user = new User();
   		if ($user->load_via_email($_REQUEST['email'])) {
   	
   			$_SESSION['kurs']=$_REQUEST['kurs_id'];
   			header("refresh:0; url= /user/login?inviteuser=".$user->getBenutzername());
   			exit;
   	
   		}
   		
   		//Fall: künftiger Teilnehmer klickt auf Einladungs-Link (ist noch NICHT REGISTRIERT!!)
   		else {
   	
   			$_SESSION['kurs']=$_REQUEST['kurs_id'];
   			header("refresh:0; url= /user/register?inviteuser=".$_REQUEST['email']);
   			exit;
   	
   		}
   	}
   	
   	//falls Nutzer über Kursview in öffentlich verfügbaren Kurs eintreten will
   	
   	elseif(isset($_REQUEST['enterpubliccourse'])){
   		$benutzer_kurs=new Benutzer_Kurs();
   		$benutzer_kurs->insert(User::currentUser()->getBenutzername(), $_REQUEST['kurs_id']);
   	
   		return new ViewModel(['meldung' => 'erfolgreich']);
   	
   	}
   	
   	
   	//Wenn richtiger Benutzer eingeloggt ist, Eintragung in Kurs 
   	
   	elseif(User::currentUser()->getBenutzername() == $_REQUEST['benutzername'] && !isset($_REQUEST['email'])){
   	
   	    	$benutzer_kurs=new Benutzer_Kurs();
   	    	$benutzer_kurs->insert($_REQUEST['benutzername'], $_REQUEST['kurs_id']);
   	
   	    	return new ViewModel(['meldung'=> 'erfolgreich']);
   	    }
   	
   	    
   	    
   	    //Wenn falscher Benutzer eingeloggt ist 
   	    
   	elseif(User::currentUser()->getBenutzername()!= NULL) {
   	
   	    	return new Viewmodel(['meldung'=> 'falseuser']);
   	    }
   	   
   	    
   	   // Falls eingeladener Teilnehmer noch nicht eingeloggt ist
   	
   	else {
   	    	$_SESSION['kurs']=$_REQUEST['kurs_id'];
   	    	header("refresh:0; url= /user/login?inviteuser=".$_REQUEST['benutzername']);
   	    	exit;
   	    }
   	
   	
    }
   
   
   



    public function uploadAction(){	
	
	
	// Zugriff auf Action ist nur erlaubt, falls Zertifizierer oder Admin und Zugang ï¿½ber Button in kursview
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
	//falls direkt auf diese Action zugegriffen wurde, ohne dass ein Kurs ausgewï¿½hlt wurde!
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
    
    public function signoutkursAction(){
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $benutzer_kurs = new Benutzer_Kurs();
            $id = $_REQUEST['kurs_id'];
            $signout = $benutzer_kurs->signindelete($id, User::currentUser()->getBenutzername());
            
            /*  Wenn das Austragen erfolgreich war, dann Meldung mit entsprechenden Hinweis an View zurÃ¼ckgeben,
             *  ansonsten einen anderen Hinweis zurÃ¼ckgeben.
            */
            if($signout){
                return new ViewModel(['meldung' => 'erfolgreich']);
            }else {
                return new ViewModel(['meldung' => 'fehlerhaft']);
            }
        }
        return new ViewModel();
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
	
	
		
	// Gibt die Dateinamen aller Dateien zurï¿½ck
	$names = $upload->getFileName();
	
	// Gibt die Grï¿½ï¿½en aller Dateien als Array zurï¿½ck
	// wenn mehr als eine Datei hochgeladen wurde
	$size = $upload->getFileSize();
	
	
	
	
}*/


}


   				
   			
    


	

