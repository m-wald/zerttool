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
{   /*
     * Ein neuer Kurs wird angelegt und das Objekt Kurs an die View übergeben.
     */
    public function createAction(){
    	
    	if(User::currentUser()->istTeilnehmer() || User::currentUser()->getBenutzername()==NULL){
    		header("refresh:0; url= /");
    		exit;
    	}else{
        
        $kurs = new Kurs();
        
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            
            $currentdate = date('Y-m-d');
            $start  = $_REQUEST["kursstart"];
            
            /*
             * Falls der Benutzer Firefox benutzt, dann soll das Datum überprüft werden und bei einer
             * fehlerhaften Eingabe einen String an die View übergeben 
             */
             
            
            //Überprüfung des Datumsformats, sowie der Korrektheit dessen
            if($kurs->validateDate($start, 'Y-m-d') || $kurs->validateDate($start, 'd.m.Y')) {
                    //mach nix
            }else{
                return new ViewModel(['error' => 'invaliddate', 'kurs' => $kurs]);
            }
            
            $end    = $_REQUEST["kursende"];
            
            //Überprüfung des Datumsformats, sowie der Korrektheit dessen
            if($kurs->validateDate($end, 'Y-m-d') || $kurs->validateDate($end, 'd.m.Y')) {
                    //mach nix
            }else{
                return new ViewModel(['error' => 'invaliddate', 'kurs' => $kurs]);
            }
            
            /*
             * Falls der Benutzer Firefox benutzt, dann soll das Datum überprüft werden und bei einer
             * fehlerhaften Eingabe einen String an die View übergeben 
             */
                        
            
            $starttimestamp = strtotime($start);
            $endtimestamp   = strtotime($end);
            $currentdatetimestamp = strtotime($currentdate);
            
            //Pr�fung, ob Kursstartdatum vor -enddatum
            if($starttimestamp > $endtimestamp && $currentdatetimestamp > $starttimestamp){
                
                $kurs = new Kurs(
                        NULL,
                        $_REQUEST["kursname"], 
                        NULL, 
                        NULL, 
                        $_REQUEST["sichtbarkeit"],
                        User::currentUser()->getBenutzername(),
                        $_REQUEST["beschreibung"]);
                
                return new ViewModel(['error' => 'falsedate', 'kurs' => $kurs]);
            }
            
            //Prüfung, ob Kursstart in der Vergangenheit liegt
            if($currentdatetimestamp > $starttimestamp){
                
                $kurs = new Kurs(
                        NULL,
                        $_REQUEST["kursname"], 
                        NULL, 
                        NULL, 
                        $_REQUEST["sichtbarkeit"],
                        User::currentUser()->getBenutzername(),
                        $_REQUEST["beschreibung"]);
                
                return new ViewModel(['error' => 'startinpast', 'kurs' => $kurs]);
            }
            
            //Pueft, ob Enddatum in 4 Tage nach dem Kursbeginn liegt
           
            $fourdays_afterstart = strtotime(date('Y-m-d', strtotime($start. ' + 4 days')));
            
            if(($fourdays_afterstart) > $endtimestamp){
            
            	$kurs = new Kurs(
            			NULL,
            			$_REQUEST["kursname"],
            			$_REQUEST["kursstart"],
            			NULL,
            			$_REQUEST["sichtbarkeit"],
            			User::currentUser()->getBenutzername(),
            			$_REQUEST["beschreibung"]);
            
            	return new ViewModel(['error' => '4days', 'kurs' => $kurs]);
            }

            //Prüfung, ob Kursende vor dem heutigem Datum 
            if($endtimestamp <= $currentdatetimestamp){
                
                $kurs = new Kurs(
                        NULL,
                        $_REQUEST["kursname"], 
                        NULL, 
                        NULL, 
                        $_REQUEST["sichtbarkeit"],
                        User::currentUser()->getBenutzername(),
                        $_REQUEST["beschreibung"]);
                
                return new ViewModel(['error' => 'endbeforecurrent', 'kurs' => $kurs]);
            }
            
            /* Wenn Kursende nach dem Currentdate befindet, dann soll das Objekt Kurs mit den 
             * Benutzereingaben befüllt werden und die Methode zum Speichern des Kurses in der Datenbank
             * soll aufgerufen werden
             */
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
                    return new ViewModel(['message' => 'erfolgt', 'kurs' => $kurs]);
                else 
                    return new ViewModel(['error' => 'nichterfolgt', 'kurs' => $kurs]);
            }
	}	
	return new ViewModel(['kurs' => $kurs]); 
        }
    }
    
    /**
     * Wenn Zertifizierer, dann Kurse des Zertifizierers laden und der View übergeben
     * Wenn Admin oder Teilnehmer, dann entsprechende Kurse laden und der View übergeben
     * @return Kurse eines Zertifizierers, bzw. je nach Regelung die einsehbaren Kurse für Admin und Teilnehmer an die View
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
         */
        if(User::currentUser()->istZertifizierer()){
            $kurseladen = $kurs->loadKurse(User::currentUser()->getBenutzername());
        }elseif((User::currentUser()->istTeilnehmer()) || (User::currentUser()->istAdmin())){
            $kurseladen = $kurs->loadKurse(NULL);
        }
        return new ViewModel(['result' => $kurseladen]); 
    }
    
    /*
     * Zum Anzeigen von Archivierten Kursen. Es wird die entsprechende Methode aufgerufen
     */
    public function showarchivedkurseAction() {
    	
    	if(User::currentUser()->getBenutzername()==NULL){
    		header("refresh:0; url=/user/login");
    		exit;
    	}
    	
    	if(User::currentUser()->istTeilnehmer()){
    		header("refresh:0; url= /");
    		exit;
    	}
    	
        $kurs = new Kurs();
        if((User::currentUser()->istAdmin()))
        	$kurseladen = $kurs->loadarchivedKurse(NULL);
        else
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
    	 
    	if(User::currentUser()->istZertifizierer()){
    		header("refresh:0; url= /");
    		exit;
    	}
    	
    	
        $kurs = new Kurs();
        if(User::currentUser()->istTeilnehmer() || User::currentUser()->istAdmin()) {
            $signedkurse = $kurs->loadsignedkurse(User::currentUser()->getBenutzername());
        }
        return new ViewModel(['result' => $signedkurse]);
    }
    
    /*
     * Es sollen nur die Kurse an die View zurückgegeben werden, die der Zertifizierer bzw. Admin auch
     * angelegt hat.
     */
    public function showcreatedkurseAction(){
    	if(User::currentUser()->getBenutzername()==NULL){
    		header("refresh:0; url=/user/login");
    		exit;
    	}
        
        if(User::currentUser()->istTeilnehmer()) {
                header("refresh:0; url= /");
    		exit;
        }
    	
        if(User::currentUser()->istZertifizierer() || User::currentUser()->istAdmin()) {
                $kurs = new Kurs();
                if(!$kurs->loadcreatedkurse(User::currentUser()->getBenutzername()))
                        return new ViewModel(['error' => 'error']);
                else
                $createdkurse = $kurs->loadcreatedkurse(User::currentUser()->getBenutzername());
    	}
    	return new ViewModel(['result' => $createdkurse]);
    }
    
    /*
     * Zum Speichern von geänderten Kursdaten, bzw. zum übernehmen der Daten eines 
     * archivierten Kurses
     */
    public function changedataAction(){
    	
    	if(User::currentUser()->getBenutzername()==NULL){
    		header("refresh:0; url=/user/login");
    		exit;
    	}
    	 
    	if(User::currentUser()->istTeilnehmer()){
    		header("refresh:0; url= /");
    		exit;
    	}
    	
        if(!isset($_REQUEST['kurs_id'])) {
                header("refresh:0; url= /");
                exit;
        }
        
    	$id = $_REQUEST["kurs_id"];
        
        /* Wenn der Benutzer einen archivierten Kurs in showarchivedkurse auswählt hat 
         * Überprüfung ob die Variable "archiv" den Wert "1" besitzt
         */
        if($_REQUEST["archiv"] == 1) {
            $archiviert = "gesetzt";
        } else {
            $archiviert = "ungesetzt";
        }
       
    	$kurs = new Kurs();
        
        //Prüfung ob der Kurs geladen werden konnte
        if(!$kurs->load($id)) {
            return new ViewModel(['error' => 'unabletoload', 'kurs' => $kurs]);
        }
        
        //Abspeichern der ursprünglichen Kursstartdaten aus dem Objekt
        $kursstartalt = $kurs->getKurs_start();
        $starttimestampalt = strtotime($kursstartalt);
        
    	$zertladen = $kurs->loadZertifizierer();
    	
    	//Variable n�tig, um in der Pr�fungs�bersicht den Kursnamen anzeigen zu k�nnen
    	$_SESSION['kurs_name']=$kurs->getKurs_name();
        
    	/*
         * Zum Ändern der Kursdaten von aktuellen Kursen
         */
        if($_REQUEST["speichern"]) {
        	
            $start  = $_REQUEST["kursstart"];
            $end    = $_REQUEST["kursende"];
            $starttimestamp = strtotime($start);
            $endtimestamp   = strtotime($end);
            $currentdate = strtotime(date('Y-m-d'));
            
            //Überprüfung des Datumsformats, sowie der Korrektheit dessen
            if($kurs->validateDate($start, 'Y-m-d') || $kurs->validateDate($start, 'd.m.Y')) {
                    //mach nix
            }else{
                return new ViewModel(['error' => 'invaliddate', 'kurs' => $kurs]);
            }
            
            //Überprüfung des Datumsformats, sowie der Korrektheit dessen
            if($kurs->validateDate($end, 'Y-m-d') || $kurs->validateDate($end, 'd.m.Y')) {
                    //mach nix
            }else{
                return new ViewModel(['error' => 'invaliddate', 'kurs' => $kurs]);
            }
            
            // Enddatum muss größer wie Startdatum sein, Enddatum muss größer wie das heutige Datum sein 
            if($endtimestamp > $starttimestamp && $endtimestamp > $currentdate) {
                
                if($currentdate > $starttimestamp) {
                    return new ViewModel(['error' => 'startinpast', 'kurs' => $kurs]);
                }
                
                if($starttimestamp != $starttimestampalt) {
                    // Kursstart darf nur geändert werden solange der Kurs noch nicht begonnen hat
                    if($starttimestampalt <= $currentdate) {

                        return new ViewModel(['error' => 'coursealreadystarted', 'kurs' => $kurs]);
                    }
                }
                
                
                $fourdays_afterstart = strtotime(date('Y-m-d', strtotime($start. ' + 4 days')));
                
                //Prüfung ob der Kurs mindestens vier Tage andauert.
                if(($fourdays_afterstart) > $endtimestamp){

                    return new ViewModel(['error' => 'fourdays', 'kurs' => $kurs]);
                }
                
                //Methode update wird aufgerufen und die Kursdaten werden in der Datenbank geändert
                $kurs->update($_REQUEST["kurs_id"], $_REQUEST["kursname"], $_REQUEST["kursstart"], $_REQUEST["kursende"], $_REQUEST["sichtbarkeit"], $_REQUEST["benutzername"], $_REQUEST["beschreibung"]);
                $kurs = new Kurs(
                        $_REQUEST["kurs_id"],
                        $_REQUEST["kursname"],
                        $_REQUEST["kursstart"],
                        $_REQUEST["kursende"],
                        $_REQUEST["sichtbarkeit"],
                        $_REQUEST["benutzername"],
                        $_REQUEST["beschreibung"]); 
                $status = "erfolgreich geändert"; 
            }
            else {
                return new ViewModel(['error' => 'dateerror', 'kurs' => $kurs]);
            }
        }
        
        //Zum Ändern der Kursdaten von archivierten Kursen
        if($_REQUEST["übernehmen"]) {	
            
            $start  = $_REQUEST["kursstart"];
            $end    = $_REQUEST["kursende"];
            $starttimestamp = strtotime($start);
            $endtimestamp   = strtotime($end);
            $currentdate = strtotime(date('Y-m-d'));
            
            //Überprüfung des Datumsformats, sowie der Korrektheit dessen
            if($kurs->validateDate($start, 'Y-m-d') || $kurs->validateDate($start, 'd.m.Y')) {
                    //mach nix
            }else{
                return new ViewModel(['error' => 'invaliddate', 'archiv' => 'gesetzt', 'kurs' => $kurs]);
            }
            
            //Überprüfung des Datumsformats, sowie der Korrektheit dessen
            if($kurs->validateDate($end, 'Y-m-d') || $kurs->validateDate($end, 'd.m.Y')) {
                    //mach nix
            }else{
                return new ViewModel(['error' => 'invaliddate', 'archiv' => 'gesetzt', 'kurs' => $kurs]);
            }
            
            // Enddatum muss größer wie Startdatum sein, Enddatum muss größer wie das heutige Datum sein 
            if($endtimestamp > $starttimestamp && $endtimestamp > $currentdate) {
                
                if($currentdate > $starttimestamp) {
                    return new ViewModel(['error' => 'startinpast', 'archiv' => 'gesetzt', 'kurs' => $kurs]);
                }
                
                $fourdays_afterstart = strtotime(date('Y-m-d', strtotime($start. ' + 4 days')));
                
                //Prüfung ob der Kurs mindestens vier Tage andauert.
                if(($fourdays_afterstart) > $endtimestamp){

                    return new ViewModel(['error' => 'fourdays', 'archiv' => 'gesetzt', 'kurs' => $kurs]);
                }
                
                //Es wird ein neuer Kurs in der Datenbank erzeugt, wobei der alte bestehen bleibt
                $kurs->insert($_REQUEST["kursname"], $_REQUEST["kursstart"], $_REQUEST["kursende"], $_REQUEST["sichtbarkeit"], $_REQUEST["benutzername"], $_REQUEST["beschreibung"]);
                $kurs = new Kurs(
                        $_REQUEST["kurs_id"],
                        $_REQUEST["kursname"],
                        $_REQUEST["kursstart"],
                        $_REQUEST["kursende"],
                        $_REQUEST["sichtbarkeit"],
                        $_REQUEST["benutzername"],
                        $_REQUEST["beschreibung"]); 
                $status = "erfolgreich übernommen"; 
            }
            else {
                
                return new ViewModel(['error' => 'dateerror', 'archiv' => 'gesetzt', 'kurs' => $kurs]);
            }
        }
        
        //Nur wenn der Nutzer von showarchivedkurse auf die Seite kommt, dann wird auch archiv mit archiviert beschrieben
        return new ViewModel(['kurs' => $kurs, 'result' => $zertladen, 'archiv' => $archiviert, 'status' => $status]);    
    }
    
    /*
     * Zum Anzeigen der Detailansicht eines Kurses
     */
    public function kursviewAction(){
    	
    	if(User::currentUser()->getBenutzername()==NULL){
    		header("refresh:0; url=/user/login");
    		exit;
    	}
    	
    	if(!isset($_REQUEST['kurs_id'])) {
                header("refresh:0; url= /");
                exit;
        }
    	
    	if((isset($_POST["back"]) && !empty($_POST["kurs_id"])) || $_POST['site']=="showstatistic")
    		$id = $_POST["kurs_id"];
    	else 
    		$id = $_REQUEST["kurs_id"];
    	
    	$_SESSION['kurs_id']=$id;
    	$kurs = new Kurs();
        $benutzer_kurs = new Benutzer_Kurs();
        
        //Wenn der Kurs nicht geladen werden kann, dann soll eine Meldung an die View übergeben werden
    	if(!$kurs->load($id)) {
            $status="errorloadingcourse";
            return new ViewModel(['status' => $status]);
        }
        
    	$_SESSION['kurs_name']=$kurs->getKurs_name();
        
        return new ViewModel(['kurs' => $kurs,
                        'benutzer_kurs' => $benutzer_kurs]);
           
    }

    
    public function singleinviteAction() {
    	
    	if(User::currentUser()->getBenutzername()==null) {
    		header("refresh:0; url = /user/login");
    		exit;
    	}
    	 
    	if(User::currentUser()->istTeilnehmer()==true){
    		header("refresh:0; url = /");
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
    		header("refresh:0; url = /");
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
   			$success=array();
   			$k=0;
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
   				   			$success[$k]=$data;
   				   			$k++;
   				   		}
   				   				
   					}
   				}
   				fclose($handle);
   			}
   			
   			return new ViewModel(['success'=>$success, 'fehler' =>$nomail, 'falsetype'=>$falsetype]);
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
   			header("refresh:0; url= /");
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
   	    	$_SESSION['kurs']=$_REQUEST['kurs_id'];
   	    	
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

    /*
     * Methode zum hochladen von Kursdaten
     */
    public function uploadAction(){	
	
	
	// Zugriff auf Action ist nur erlaubt, falls Zertifizierer oder Admin und Zugang �ber Button in kursview
	if(User::currentUser()->getBenutzername()==null) {
		header("refresh:0; url = /user/login");
		exit;
	}
	 
	if(User::currentUser()->istTeilnehmer()){
		header("refresh:0; url = /");
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
		 
		$allowed_extensions=array('pdf','doc','docx','xls','xlsx', 'jpeg', 'png');
		 
		if(!in_array($extension, $allowed_extensions)) {
			return new ViewModel(['meldung' => 'datentyp']);
		}
		
				 
		//�berpr�fung der Dateigr��e
		 
		$max_size = 1024*1024*2;                                //2 MB (in Byte angegeben)
               		 
                if($_FILES['datei']['size'] > $max_size) {
				
			return new ViewModel(['meldung' => 'dateigroesse']);
		}		
				 
		//Dateipfad
		
		$new_path = $path_new.$filename.'.'.$extension;
				 
		//Neuer Dateiname falls die Datei bereits existiert
		
                if(file_exists($new_path))
                {
                        //Falls Datei existiert, hänge eine Zahl an den Dateinamen
                        $id = 1;
                        do
                        {
                                $new_path = $path_new.$filename.'_'.$id.'.'.$extension;
                                $id++;
                        }while(file_exists($new_path));
                }
                //Alles okay, verschiebe Datei an neuen Pfad
                $sent = move_uploaded_file($_FILES['datei']['tmp_name'], $new_path);

                if($sent)
                {
                    return new ViewModel(['meldung' => 'erfolgreich']);
                }else{
                    return new ViewModel(['meldung' => 'uploadfehlerhaft']);
                }
                //Alles okay, verschiebe Datei an neuen Pfad
		 		 
	}	 

	elseif(!empty($_SESSION['kurs_id'])){
	
		return new ViewModel();
	}//falls direkt auf diese Action zugegriffen wurde, ohne dass ein Kurs ausgew�hlt wurde!
	else {
            header("refresh:0; url = /kurs/showkurse");
            exit;
        }

    }
  
    /*
     * Zum Anzeigen der bisher hochgeladenen Kursdokumente
     */
    public function showdocumentsAction(){
    	
    	if(User::currentUser()->getBenutzername()==NULL){
    		header("refresh:0; url= /user/login");
    		exit;
    	}
    	
    	if(!$_POST['site']=="kursview" && !$_SESSION['site'] == 'delete') {
    		header("refresh:0; url= /");
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
                                'kursname' 		=> $name]); 
    }
    
    
    public function docDeleteAction(){
    	
    	if(User::currentUser()->getBenutzername()==NULL) {
    		header("refresh:0; url = /user/login");
    		exit;
    	}
    	
    	if(User::currentUser()->istTeilnehmer()) {
    		header("refresh:0; url = /");
    		exit;
    	}
    	
    	
    		
    	if($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['thissite']) {
    		
    		$path		= $_REQUEST["path"];
    		$document	= $_REQUEST["document"];
    		
    		if(is_writeable($path.$document)){
    			if(unlink(realpath($path.$document)))
    					return new ViewModel(['message'=>'Document deleted']);
    			else 	return new ViewModel(['message'=>'Error deleting']);
    		}
    		else		return new ViewModel(['message'=>'Access denied']);
    	
    	}
    }
    
    
    /*
     * Bereits hochgeladene Dokumente sollen von den Benutzern heruntergeladen werden
     */
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
    
    	}
    }
    
    /*
     * Benutzer soll sich aus dem Kurs austragen können
     */
    public function signoutkursAction(){
    	
    	if(User::currentUser()->getBenutzername()==NULL) {
    		header("refresh:0; url = /user/login");
    		exit;
    	}
    	
        if($_POST['site'] == 'checksignoutkurs') {
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
        header("refresh:0; url = /");
        exit;
    }
    
    
    //Abfrage, ob sich Benutzer auch sicher austragen will!!
    
    public function checksignoutkursAction(){
    	
    	if(User::currentUser()->getBenutzername()==NULL){
    		header("refresh: 0; url= /user/login");
    		exit;
    	}
    	
    	if(!$_POST['site']=="kursview" && !$_POST['site']=="showsignedkurse"){
    		header("refresh: 0; url= /");
    		exit;
    	}
    	
    	return new ViewModel(['site'=>$_POST['site']]);
    		
    	}
    	
    

   
    /*
     * Erstellt einen PDF Zertifikat
     * 
     * 
     */

    public function loadcertificateAction(){
		
                if(User::currentUser()->getBenutzername()==NULL) {
                        header("refresh:0; url = /user/login");
                        exit;
                }
               
                if(User::currentUser()->istZertifizierer()) {
                        header("refresh:0; url = /");
                        exit;
                }
                
		$benutzer = User::currentUser()->getBenutzername();
		$vorname = User::currentUser()->getVorname();
		$nachname = User::currentUser()->getNachname();
		
                /*
                 * Wenn der Akteur seine Zertifikate über die Startseite auswählt
                 * Funktion gibt list an View zurück
                 */
                if(isset($_GET['pdflist'])) {
                        $kurs = new Kurs;
			$list = $kurs->certificateList($benutzer);
			return new Viewmodel (['list' => $list]);
                }
		/*
		 *  Button "Meine Zertifikate anzeigen" wird gedruckt
		 *  Funtkion gibt list an View zuruck
		 */
		elseif($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['pdflist'])) {
			$kurs = new Kurs;
			$list = $kurs->certificateList($benutzer);
			return new Viewmodel (['list' => $list]);		 
                }	
		elseif($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['pdf']) {
	
		/*
		 * Button "Zertifikat" erstellt einen PDF Zertifikat
		 */
		
			 
			// Zugriff auf Action ist nur erlaubt, falls Zertifizierer oder Admin und Zugang �ber Button in kursview
			if(User::currentUser()->getBenutzername()==null) {
				header("refresh:0; url = /user/login");
				exit;
			}
		
					
			if(isset($_REQUEST["kurs_name"]))		$kurs_name = $_REQUEST["kurs_name"];
			else 									$kurs_name = $_SESSION['kurs_name'];
			
			if(isset($_REQUEST["kurs_id"]))			$kurs_id = $_REQUEST["kurs_id"];
			else 									$kurs_id = $_SESSION['kurs_id'];
			
			
			if(!Kurs::checkCourseResult(User::currentUser()->getBenutzername(), $kurs_id)) {
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
					 
				//Load Uni Logo
				$image = Image::imageWithPath('data/img/logo.png');
				//Draw Image
				$left = 262;
				$bottom = 817;
				$right = 10;
				$top = 750;				
				
				$page1->drawImage($image, $left, $bottom, $right, $top);
				
						
				// Draw text Zertifikat
				$page1->drawText('Zertifikat', 350, 770);
				
				//Name und Vorname des Teilnehmers
				$page1->setFont($font, 25);
				$page1->drawText($vorname.' '.$nachname, 80, 650);
				// Draw line
				$page1->drawLine(80, 640, 320,640);
				
				//Info �ber Teilnehmer und Kurs: Geburtsdatum, Kursbezeichnung, Kursleiter
				$page1->setFont($font, 12);
				$geburtstag = date('d. M Y', strtotime(User::currentUser()->getGeburtsdatum()));
				$page1->drawText('Geboren am: '.$geburtstag, 80, 600);
				$page1->setFont($font, 14);
				$page1->drawText('hat erfolgreich folgenden Kurs abgeschlossen:', 80, 570);
				$page1->setFont($font, 25);
				$page1->drawText($kurs_name, 80, 520); 
				$page1->setFont($font, 14);
				$page1->drawText('Kursleiter: ', 80, 470);
				$page1->setFont($font, 22);
				//Kursleiter wird definiert
				$kurs = new Kurs;
				$load_kurs = $kurs->load($kurs_id);
				$leiter = $kurs->getBenutzername();
				$user = new User();
				$load_user = $user->load($leiter);
				$vorname = $user->getVorname();
				$nachname = $user->getNachname();
				$leiter_name = $vorname.' '.$nachname;
				
				$page1->drawText($leiter_name, 80, 430);
				//Ort, Datum
				$page1->setFont($font, 12);
				$page1->drawText('Passau, den '.date('d. M Y', strtotime($kurs->getKurs_ende())), 80, 350);
				$page1->drawLine(80, 340, 220, 340);
				
				//Unterschrift
				$image = Image::imageWithPath('data/img/sign.jpg');
				//Draw Image
				$left = 350;
				$bottom = 325;
				$right = 520;
				$top = 400;
					
				$page1->drawImage($image, $left, $bottom, $right, $top);
				// Save document as a new file
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
                    header("refresh:0; url= /");
                    exit;
            }

            if((User::currentUser()->istZertifizierer() || User::currentUser()->istAdmin()) && $_POST['site']=='kursview' || $_POST['site']=='statisticlistquestions') {

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
                    header("refresh:0; url= /");
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

   				
   			
    


	

