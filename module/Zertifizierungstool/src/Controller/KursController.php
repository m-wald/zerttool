<?php
namespace Zertifizierungstool\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zertifizierungstool\Model\Kurs;
use Zertifizierungstool\Model\User;
use Zertifizierungstool\Model\CSV_invite;
use Zertifizierungstool\Model\Benutzer_Kurs;
use ZendPdf\PdfDocument;
use ZendPdf\Font;
use ZendPdf\Page;
use ZendPdf\Exception;
use ZendPdf\Style;
use ZendPdf\Image;
use Zertifizierungstool\Model\Pruefung;
use Zertifizierungstool\Model\Frage;
use Zertifizierungstool\Model\SchreibtPruefung;

class KursController extends AbstractActionController
{   
    public function createAction(){
    	
    	if(User::currentUser()->getBenutzername()==NULL){
    		header("refresh:0; url= /user/login");
    		exit;
    	}
    	
    	if(User::currentUser()->istTeilnehmer()){
    		header("refresh:0; url= /user/home");
    		exit;
    	}
        
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            
            $currentdate = date('Y-m-d');
            $start  = $_REQUEST["kursstart"];
            $end    = $_REQUEST["kursende"];
            $starttimestamp = strtotime($start);
            $endtimestamp   = strtotime($end);
            $currentdatetimestamp = strtotime($currentdate);
            
            //Pr�fung, ob Kursstartdatum vor -enddatum
            if($starttimestamp > $endtimestamp){
                return new ViewModel(['error' => 'falsedate']);
            }

            //Prüfung, ob Kursende vor dem heutigem Datum 
            if($endtimestamp < $currentdatetimestamp){
                return new ViewModel(['error' => 'endbeforecurrent']);
            }
            
            //Wenn Kursende vor dem Currentdate befinden 
            if($endtimestamp > $currentdatetimestamp) {

                $kurs = new Kurs(
                        NULL,
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
                    return new ViewModel(['error' => 'nichterfolgt']);
            }
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
     * Wenn Zertifizierer, dann Kurse des Zertifizierers laden und der View übergeben
     * Wenn Admin oder Teilnehmer, dann entsprechende Kurse laden und der View übergeben
     * @return Kurse eines Zertifizierers, bzw. je nach Regelung die einsehabren Kurse für Admin und Teilnehmer an die View
     */
    public function showkurseAction(){
    	
    	if(User::currentUser()->getBenutzername()==NULL){
    		header("refresh:0; url= /user/login");
    		exit;
    	}
    	
        $kurs = new Kurs();
        /*
         * Wenn Zertifizierer, dann soll er nur seine Kurse angezeigt bekommen.
         * Wenn Admin oder Teilnehmer, dann soll NULL als Parameter übergeben werden,
         * damit in der SQL-Query nicht nach dem Benutzernamen gefiltert wird
         * 
         * TODO Admin hat doch auch alle Funktionen eines Zertifizierers oder?
         * 		Dann kann er ja theorethisch auch Kurse haben, in denen er Kursleiter ist
         * TODO Es werden momentan ja nur die Kurse geladen und angezeigt, die schon gestartet sind.
         * 		Ein Zertifizierer will aber vielleicht noch vor Kursbeginn irgendwelche Daten �ndern oder
         * 		Teilnehmer wollen auch Kurse sehen, die sie sp�ter belegen wollen.
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
    
    /*
     * zum Anzeigen von Archivierten Kursen
     */
    public function showarchivedkurseAction() {
    	
    	if(User::currentUser()->getBenutzername()==NULL){
    		header("refresh:0; url=/user/login");
    		exit;
    	}
    	
    	if(User::currentUser()->istTeilnehmer()){
    		header("refresh:0; url=/user/home");
    		exit;
    	}
    	
        $kurs = new Kurs();
        $kurseladen = $kurs->loadarchivedKurse(User::currentUser()->getBenutzername());
        return new ViewModel(['result' => $kurseladen]);
    }
    
 
    
    /**
     * Lädt Kurse zu denen sich der Teilnehmer eingetragen hat und übergibt diese
     * @return Kurse des Teilnehmers an die View
     */
    public function showsignedkurseAction() {
    	
    	if(User::currentUser()->getBenutzername()==NULL){
    		header("refresh:0; url=/user/login");
    		exit;
    	}
    	 
    	if(User::currentUser()->istAdmin() || User::currentUser()->istZertifizierer()){
    		header("refresh:0; url=/user/home");
    		exit;
    	}
    	
    	
        $kurs = new Kurs();
        if(User::currentUser()->istTeilnehmer()) {
            $signedkurse = $kurs->loadsignedkurse(User::currentUser()->getBenutzername());
        }
        return new ViewModel(['result' => $signedkurse]);
    }
    
    /*
     * Überprüft ob Kursänderungen gespeichert werden sollen und ruft die
     * Query zum Speichern der Kursdaten auf 
     * @return Status (Fehler/Erfolg) und Kursdaten 
     */
    public function changedataAction(){
    	
    	if(User::currentUser()->getBenutzername()==NULL){
    		header("refresh:0; url=/user/login");
    		exit;
    	}
    	 
    	if(User::currentUser()->istTeilnehmer()){
    		header("refresh:0; url=/user/home");
    		exit;
    	}
    	
    	$id = $_REQUEST["kurs_id"];
        
        //aus archivierte Kurse
        if($_REQUEST["archiv"] == 1) {
            $archiviert = "gesetzt";
        } else {
            $archiviert = "ungesetzt";
        }
       
    	$kurs = new Kurs();
        if(!$kurs->load($id)) {
            return new ViewModel(['error' => 'unabletoload']);
            //$status="Fehler beim Laden des Kurses!";
        }
        
    	$zertladen = $kurs->loadZertifizierer();
        
    	//Zum ändern der Kursdaten von aktuellen Kursen
        if($_REQUEST["speichern"]) {
        	
            $start  = $_REQUEST["kursstart"];
            $end    = $_REQUEST["kursende"];
            $starttimestamp = strtotime($start);
            $endtimestamp   = strtotime($end);
            $today = strtotime(date(d-m-Y));
            
            if($endtimestamp > $starttimestamp && $endtimestamp > $today) {
            
                if($starttimestamp >= $today) {
                    //$status = "Kursdatum nicht änderbar, da Kurs schon begonnen hat!";
                    return new ViewModel(['error' => 'coursealreadystarted']);
                }
                
                $kurs->update($_REQUEST["kurs_id"], $_REQUEST["kursname"], $_REQUEST["kursstart"], $_REQUEST["kursende"], $_REQUEST["sichtbarkeit"], $_REQUEST["beschreibung"]);
                $kurs = new Kurs(
                        $_REQUEST["kurs_id"],
                        $_REQUEST["kursname"],
                        $_REQUEST["kursstart"],
                        $_REQUEST["kursende"],
                        $_REQUEST["sichtbarkeit"],
                        $_REQUEST["beschreibung"]); 
                $status = "erfolgreich geändert"; 
            }
            else {
                //$status = "�berpr�fen Sie bitte Start- und End-Datum des Kurses!";
                return new ViewModel(['error' => 'dateerror']);
            }
        }
        
        //Zum ändern und archivieren der Kursdaten von archivierten Kursen
        if($_REQUEST["übernehmen"]) {
        	
            $start  = $_REQUEST["kursstart"];
            $end    = $_REQUEST["kursende"];
            $starttimestamp = strtotime($start);
            $endtimestamp   = strtotime($end);
            $today = strtotime(date(d-m-Y));
        	
            if($endtimestamp > $starttimestamp && $endtimestamp > $today && $starttimestamp >= $today) {
            $kurs->insert($_REQUEST["kursname"], $_REQUEST["kursstart"], $_REQUEST["kursende"], $_REQUEST["sichtbarkeit"], User::currentUser()->getBenutzername(), $_REQUEST["beschreibung"]);
            $kurs = new Kurs(
                    $_REQUEST["kurs_id"],
                    $_REQUEST["kursname"],
                    $_REQUEST["kursstart"],
                    $_REQUEST["kursende"],
                    $_REQUEST["sichtbarkeit"],
                    $_REQUEST["beschreibung"]); 
            $status = "erfolgreich übernommen"; 
            }
            else {
                //$status = "�berpr�fen Sie bitte Start- und End-Datum des Kurses!";
                return new ViewModel(['error' => 'dateerror']);
            }
        }
        
        return new ViewModel(['kurs' => $kurs, 'result' => $zertladen, 'archiv' => $archiviert, 'status' => $status]);    
    }
    
    
    public function kursviewAction(){
    	
    	if(User::currentUser()->getBenutzername()==NULL){
    		header("refresh:0; url=/user/login");
    		exit;
    	}
    	
    	
    	
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
	    	//falls direkt auf diese Action zugegriffen wurde, ohne dass ein Kurs ausgew�hlt wurde!
	    	else header("refresh:0; url = /kurs/showkurse");
	    	exit;
    }

    
    public function csvinviteAction(){
    	
    	// Zugriff auf Action ist nur erlaubt, falls Zertifizierer oder Admin und Zugang �ber Button in kursview
    	if(User::currentUser()->getBenutzername()==null) {
    		header("refresh:0; url = /user/login");
    		exit;
    	}
    	
    	if(User::currentUser()->istTeilnehmer()){
    		header("refresh:0; url = /user/home");
    		exit;
    	}
    	
   	
   
   	if($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['site']=='csvinvite') {
   		
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
   			
   			   			
   			$nomail=array();
   			$i=0;
   			$falsetype=array();
   			$j=0;
   			if (($handle = fopen($new_path, "r")) !== FALSE) {
   				while (($data = fgetcsv($handle, 1000,";")) !== FALSE) {
   					
   				   		$csv = new CSV_invite();
   				   		
   				   		//falls in der CSV-Datei mehr als eine Spalte bef�llt wird (nur eine E-Mail-Adresse pro Zeile!!)
   				   		$num = count($data);
   				 	
   				   		if($num>1){
   				   			$falsetype[$j]=$data;
   				   			$j++;
   				   			continue;
   				   		}
   				   		
   				   		
   				   		//Pr�fung, ob es sich um E-Mail-Adresse handelt
   				   		
   				   		
   				   		if (!filter_var($data[0], FILTER_VALIDATE_EMAIL)) {
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
   		//falls direkt auf diese Action zugegriffen wurde, ohne dass ein Kurs ausgew�hlt wurde!
   		else header("refresh:0; url = /kurs/showkurse");
   		exit;
    } 

    
    public function enterkursAction() {
   	
   	
   	if(!isset($_REQUEST['kurs_id'])) {
   		
   		if(User::currentUser()->getBenutzername()==NULL){
   			header("refresh:0; url= /user/login");
   			exit;
   		}
   		else{
   			header("refresh:0; url= /user/home");
   			exit;
   		}
   	}
   	
   	
   	
   	
   	/* zeitliche G�ltigkeit des Kurses �berpr�fen
   	 * 2 - Kurs ist abgelaufen
   	 * 0 - Kurs startet in der Zukunft
   	 */
   	
   	
    	$kurs= new Kurs();
    	if ($kurs->active($_REQUEST['kurs_id']) == 0){
    		return new ViewModel(['meldung' =>'dateerror']);
    	}
    	elseif($kurs->active($_REQUEST['kurs_id']) == 2) {
    		return new ViewModel(['meldung' =>'datefuture']);
    	}
   	
    	
    //Fall: Teilnehmer klickt auf Einladungs-Link (ist noch nicht registriert). ABER: Fehlerabfangen f�r den Fall
    //dass sich der Teilnehmer in der Zwischenzeit schon am System registriert hat!
    
    if(isset($_REQUEST['email'])) {
   		$user = new User();
   		if ($user->load_via_email($_REQUEST['email'])) {
   	
   			$_SESSION['kurs']=$_REQUEST['kurs_id'];
   			header("refresh:0; url= /user/login?inviteuser=".$user->getBenutzername());
   			exit;
   	
   		}
   		
   		//Fall: k�nftiger Teilnehmer klickt auf Einladungs-Link (ist noch NICHT REGISTRIERT!!)
   		else {
   	
   			$_SESSION['kurs']=$_REQUEST['kurs_id'];
   			header("refresh:0; url= /user/register?inviteuser=".$_REQUEST['email']);
   			exit;
   	
   		}
   	}
   	
   	//falls Nutzer �ber Kursview in �ffentlich verf�gbaren Kurs eintreten will
   	
   	elseif(isset($_REQUEST['enterpubliccourse'])){
   		$benutzer_kurs=new Benutzer_Kurs();
   		$result = $benutzer_kurs->insert(User::currentUser()->getBenutzername(), $_REQUEST['kurs_id']);
   		
   		if($result == 1) {
   		$_SESSION['kurs']=$_REQUEST['kurs_id'];
   	
   		return new ViewModel(['meldung' => 'erfolgreich']);
   		}
   		if($result == -1){
   			return new ViewModel(['meldung' => 'alreadyexists']);
   		}
   		else return new ViewModel(['meldung' => 'datenbankfehler']);
   	
   	}
   	
   	
   	//Wenn richtiger Benutzer eingeloggt ist, Eintragung in Kurs 
   	
   	elseif(User::currentUser()->getBenutzername() == $_REQUEST['benutzername'] && !isset($_REQUEST['email'])){
   	
   	    	$benutzer_kurs=new Benutzer_Kurs();
   	    	$result = $benutzer_kurs->insert($_REQUEST['benutzername'], $_REQUEST['kurs_id']);
   	    	
   	if($result == 1){
   	    	return new ViewModel(['meldung'=> 'erfolgreich']);
   	}
   	    	
   	    	if($result == -1){
   	    		return new ViewModel(['meldung' => 'alreadyexists']);
   	    	}
   	    	else return new ViewModel(['meldung' => 'datenbankfehler']);
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
	
	
	// Zugriff auf Action ist nur erlaubt, falls Zertifizierer oder Admin und Zugang �ber Button in kursview
	if(User::currentUser()->getBenutzername()==null) {
		header("refresh:0; url = /user/login");
		exit;
	}
	 
	if(User::currentUser()->istTeilnehmer()){
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
		
			 
		//�berpr�fung der Dateiendung
		 
		$allowed_extensions=array('pdf','doc','docx','xls','xlsx');
		 
		if(!in_array($extension, $allowed_extensions)) {
			return new ViewModel(['meldung' => 'datentyp']);
		}
		
				 
		//�berpr�fung der Dateigr��e
		 
		$max_size = 5000000;                                //5 MB (in Byte angegeben)
		 
		if($_FILES['datei']['size'] > $max_size) {
				
			return new ViewModel(['meldung' =>'dateigroesse']);
		}		
				 
		//Dateipfad
		
		$new_path = $path_new.$filename.'.'.$extension;
				 
		//Neuer Dateiname falls die Datei bereits existiert
		 
		if(file_exists($new_path)) { //Falls Datei existiert, h�nge eine Zahl an den Dateinamen
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
	//falls direkt auf diese Action zugegriffen wurde, ohne dass ein Kurs ausgew�hlt wurde!
	else header("refresh:0; url = /kurs/showkurse");
	exit;

    }
  
  
    public function showdocumentsAction(){
    	
    	if(User::currentUser()->getBenutzername()==NULL){
    		header("refresh:0; url= /user/login");
    		exit;
    	}
    	
    	if(!$_POST['site']=="kursview" && !$_SESSION['site'] == 'delete') {
    		header("refresh:0; url= /user/home");
    		exit;
    	}
    	
    	
        $id = $_SESSION['kurs_id'];
        $name = $_SESSION['kurs_name'];
            
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
    
    
    public function docDeleteAction(){
    	
    	if(User::currentUser()->getBenutzername()==NULL) {
    		header("refresh:0; url = /user/login");
    		exit;
    	}
    	
    	if(User::currentUser()->istTeilnehmer()) {
    		header("refresh:0; url = /user/home");
    		exit;
    	}
    	
    	
    		
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
    
    
    
    public function docdownloadAction(){
    	if(User::currentUser()->getBenutzername()==NULL) {
    		header("refresh:0; url = /user/login");
    		exit;
    	}
    	
    	if(isset($_POST['download'])){
    		$path		= $_REQUEST["path"];
    		$document	= $_REQUEST["document"];
    		$extension	= $_REQUEST["extension"]; 
    		
    		if(file_exists($path."/".$document)){
    			header("Content-Type: $extension");  		
    			header("Content-Disposition: attachment; filename=\"$document\"");
    			readfile($path."/".$document);
    		}
    //TODO else return VieModule (error) 
    	}
    }
    
    
    public function signoutkursAction(){
    	
    	if(User::currentUser()->getBenutzername()==NULL) {
    		header("refresh:0; url = /user/login");
    		exit;
    	}
    	
    	
    	
    	
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $benutzer_kurs = new Benutzer_Kurs();
            $id = $_REQUEST['kurs_id'];
            $signout = $benutzer_kurs->signindelete($id, User::currentUser()->getBenutzername());
            
            /*  Wenn das Austragen erfolgreich war, dann Meldung mit entsprechenden Hinweis an View zurückgeben,
             *  ansonsten einen anderen Hinweis zurückgeben.
            */
            if($signout){
                return new ViewModel(['meldung' => 'erfolgreich']);
            }else {
                return new ViewModel(['meldung' => 'fehlerhaft']);
            }
        }
        header("refresh:0; url = /user/home");
        exit;
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

    
    /*
     * Erstellt einen PDF Zertifikat
     * 
     * 
     */

    public function loadCertificateAction(){
		
		$benutzer = User::currentUser()->getBenutzername();
		$vorname = User::currentUser()->getVorname();
		$nachname = User::currentUser()->getNachname();
		
		
		/*
		 *  Button "Meine Zertifikate anzeigen" wird gedruckt
		 *  Funtkion gibt list an View zuruck
		 */
		if($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['pdflist']) {
			$kurs = new Kurs;
			$list = $kurs->certificateList($benutzer);
			return new Viewmodel (['list' => $list]);		
			}
			
			
		else {
	
		/*
		 * Button "Zertifikat" erstellt einen PDF Zertifikat
		 */
		if($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['pdf']) {
			 
			// Zugriff auf Action ist nur erlaubt, falls Zertifizierer oder Admin und Zugang �ber Button in kursview
			if(User::currentUser()->getBenutzername()==null) {
				header("refresh:0; url = /user/login");
				exit;
			}
		
					
			if(isset($_REQUEST["kurs_name"]))		$kurs_name = $_REQUEST["kurs_name"];
			else 									$kurs_name = $_SESSION['kurs_name'];
			
			if(isset($_REQUEST["kurs_id"]))			$kurs_id = $_REQUEST["kurs_id"];
			else 									$kurs_id = $_SESSION['kurs_id'];
			
			$kurs = new Kurs;
			if(!$kurs->checkCourseResult($benutzer, $kurs_id)) {
				return new Viewmodel (['message' => 'access_error']);
				exit;
			}
			
			
			$fileName = $kurs_name.'_'.$vorname.'_'.$nachname;
					
			try{
				// Create new PDF document.
				$pdf = new PdfDocument();
				
				 
				// Add new page
				$pdf->pages[0] = ($page1 = $pdf->newPage('A4'));			
		
				// Set font
				$font = Font::fontWithName(Font::FONT_HELVETICA);
				$page1->setFont($font, 40);		
					 
				//Load Image
				$image = Image::imageWithPath('data/img/logo.png');
				//Draw Image
				$left = 262;
				$bottom = 817;
				$right = 10;
				$top = 750;
				
				//$page1->rotate(0, 0, M_PI/12);
				$page1->drawImage($image, $left, $bottom, $right, $top);
				
								
				$image = Image::imageWithPath('data/img/justdoit.jpg');
				//Draw Image
				$left = 5;
				$bottom = 323;
				$right = 269;
				$top = 700;
			
				$page1->drawImage($image, $left, $bottom, $right, $top);
				
				// Draw text
				$page1->drawText('Zertifikat', 350, 770);
				
				
				$page1->setFont($font, 25);
				$page1->drawText($vorname.' '.$nachname, 280, 650);
				
				$page1->drawLine(280, 640, 500,640);
				
				$page1->setFont($font, 12);
				$page1->drawText('Geboren am: '.User::currentUser()->getGeburtsdatum(), 280, 600);
				$page1->setFont($font, 14);
				$page1->drawText('hat erfolgreich folgender Kurs abgeschlossen:', 280, 570);
				$page1->setFont($font, 25);
				$page1->drawText($kurs_name, 280, 520); 
				$page1->setFont($font, 14);
				$page1->drawText('Kursleiter: ', 280, 470);
				$page1->setFont($font, 22);
				
				$load_kurs = $kurs->load($kurs_id);
				$leiter = $kurs->getBenutzername();
				$user = new User();
				$load_user = $user->load($leiter);
				$vorname = $user->getVorname();
				$nachname = $user->getNachname();
				$leiter_name = $vorname.' '.$nachname;
				
				$page1->drawText($leiter_name, 280, 430);
				
				$date = date('d. M Y');
				$page1->setFont($font, 12);
				$page1->drawText('Passau, den '.$date, 280, 350);
				$page1->drawLine(280, 340, 420, 340);
				
				$image = Image::imageWithPath('data/img/sign.jpg');
				//Draw Image
				$left = 450;
				$bottom = 325;
				$right = 620;
				$top = 400;
					
				$page1->drawImage($image, $left, $bottom, $right, $top);
				// Save document as a new file or rewrite existing document
				//$pdf->save($path.$fileName.$extansion);
		
				header("Content-Disposition: inline; filename=$fileName.pdf");
				header("Content-type: application/x-pdf");
				echo $pdf->render();
				
				return new Viewmodel (['message' => 'success']);
		
			} catch (Exception $e) {
				die ('PDF error: ' . $e->getMessage());
				return new Viewmodel (['message' => 'error']);
			}
		}
			/* 
			else header("refresh:0; url = /kurs/showkurse");
			exit;
		*/
		
	}
    }
	
	
	/** Anzeige der Kursstatistik mit Auflistung aller Pr�fungen dieses Kurses
	 * (Pr�fungsname, Anzahl der Pr�flinge, Bestehensquote der jeweiligen Pr�fung)
	 */ 
	
	public function showstatisticAction() {
		
		/** Berechtigungspr�fung: falls nicht eingeloggt, Weiterleitung zum Login
		 * falls Admin bzw. Zertifizierer, dann nur Anzeige der Kursstatistik, falls man in Kursview auf Button
		 * "Kursstatistik" geklickt hat (hier wird Session mit Kurs-ID gesetzt, die f�r richtige Statistik n�tig ist
		 * ansonsten Weiterleitung zur �bersicht aller selbst verwalteten Kurse
		 * falls Teilnehmer, Weiterleitung zur Home-Seite
		 */
		
		if(User::currentUser()->getBenutzername()==NULL){
			header("refresh:0; url= /user/login");
			exit;
		}
		
		if(User::currentUser()->istTeilnehmer()){
			header("refresh:0; url= /user/home");
			exit;
		}
		
		if((User::currentUser()->istZertifizierer() || User::currentUser()->istAdmin()) && $_POST['site']=='kursview') {
		
		$pruefung = new Pruefung();
		
		$pruefungsliste = $pruefung->loadstatistics($_SESSION['kurs_id']);
		
		
		
		return new ViewModel(['pruefungsliste' => $pruefungsliste]);
		}
		
		else{
			header("refresh:0; url= /kurs/showkurse");
			exit;
		}
		
	}
	
	public function statisticlistquestionsAction() {
		
		if(User::currentUser()->getBenutzername()==NULL){
			header("refresh:0; url= /user/login");
			exit;
		}
		
		if(User::currentUser()->istTeilnehmer()){
			header("refresh:0; url= /user/home");
			exit;
		}
		
		if((User::currentUser()->istZertifizierer() || User::currentUser()->istAdmin()) && $_POST['site']=='showstatistic') {			
				
				$fragen = array();
				$fragen = Frage::loadList($_REQUEST['pruefung_id']);
				$ergebnis = array();
				
				foreach ($fragen as $frage) {
					
					$schreibt_pruefung_list = array();
					$schreibt_pruefung_list = SchreibtPruefung::loadlist($frage->getPruefungId());
					$richtig = 0;
					$beantwortet = 0;
					foreach ($schreibt_pruefung_list as $schreibt_pruefung) {
						
						if (FrageController::check($frage->getId(), $schreibt_pruefung->getId())){
							$richtig++;
						}
						$beantwortet++;
						
					}
					$prozentual_richtig = round(($richtig/$beantwortet)*100,2);
					$inhalt = array($frage->getText(), $beantwortet, $richtig, $prozentual_richtig);
					array_push($ergebnis, $inhalt);
				}
			return new ViewModel(['ergebnis' => $ergebnis, 'pruefung_name' => $_REQUEST['pruefung_name']]);
		
		
		} else{
			header("refresh:0; url= /kurs/showkurse");
			exit;
		}
	}






}

   				
   			
    


	

