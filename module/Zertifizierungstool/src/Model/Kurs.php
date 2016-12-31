<?php

namespace Zertifizierungstool\Model;

use Zertifizierungstool\Model\Db_connection;

class Kurs {

    private $kurs_id;
    private $kurs_name;
    private $kurs_start;
    private $kurs_ende;
    private $sichtbarkeit;
    private $benutzername;
    private $teilnehmerzahl;
    
    public function __construct($kurs_name, $kurs_start, $kurs_ende, $sichtbarkeit, $benutzername, $beschreibung) {
        $this->kurs_id = "";
        $this->kurs_name = $kurs_name;
        $this->kurs_start = $kurs_start;
        $this->kurs_ende = $kurs_ende;
        $this->sichtbarkeit = $sichtbarkeit;
        $this->benutzername = $benutzername;
        $this->beschreibung = $beschreibung;
    }
    
    /**
     * L�dt die Daten des Kurses mit der �bergebenen Id
     * 
     * @param Id des Kurses $id
     * 
     * @return true, falls keine Fehler aufgetreten sind. Sonst false
     */
    
    public function load($id) {
        $db = new Db_connection();
        $query = "SELECT * FROM kurs join teilnehmerzahl using (kurs_id) WHERE kurs_id = ".$id.";";
        $result = $db->execute($query);       

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
       
                $this->kurs_id = $row['kurs_id'];
                $this->kurs_name = $row['kurs_name'];
                $this->kurs_start = $row['kurs_start'];
                $this->kurs_ende = $row['kurs_ende'];
                $this->sichtbarkeit = $row['sichtbarkeit'];
                $this->benutzername = $row['benutzername'];
                $this->beschreibung = $row['beschreibung'];
                $this->teilnehmerzahl = $row['teilnehmerzahl'];
                
                return true;
        }

        //Wenn die Methode hier ankommt, dann konnte das Objekt nicht erzeugt werden
        return false;
    }
    
    /**
     * Lädt alle Kurse, die dem übergebenen Benutzernamen zugeordent sind
     * Wenn Zertifizierer, dann sollen alle aktiven Kurse ausgegeben werden für die der Zertifizierer
     * zuständig ist. Wenn Admin, dann sollen alle aktiven Kurse ausgegeben werden.
     * Wenn Teilnehmer, dann sollen alle aktiven öffentlichen Kurse ausgegeben werden. 
     * @param Benutzername. Wenn NULL übergeben wird, dann handelt es sich um einen Admin oder
     *      Teilnehmer
     * @return Array mit allen Kursen, ansonsten 0.
     */
    public function loadKurse($benutzername) {
    	$db = new Db_connection();
        if(User::currentUser()->istZertifizierer()){
            $query = "SELECT * FROM kurs WHERE benutzername = '".$benutzername."'
                            AND (CURRENT_DATE <= kurs_ende);";
        }elseif((User::currentUser()->istAdmin()) && ($benutzername == NULL)){
            $query = "SELECT * FROM kurs WHERE (CURRENT_DATE <= kurs_ende);";
	}elseif((User::currentUser()->istTeilnehmer()) && ($benutzername == NULL)){
            $query = "SELECT * FROM kurs WHERE (CURRENT_DATE <= kurs_ende) AND sichtbarkeit = 1;";
	}
    	
    	$result = $db->execute($query);
    
    	
        
        if (mysqli_num_rows($result) > 0){
        	// TODO Ihr �bergebt hier direkt das Ergebnis der Datenbankabfrage.
        	// Besser w�rs, wenn ihr f�r jeden Datensatz ein neues Objekt von der Klasse "Kurs" anlegt und in ein Array speichert.
        	// Am Ende k�nnt ihr dann das Array zur�ckgeben und in der View dann einfach die getter-Methoden f�r das jeweilige Objekt aufrufen.
        	// #Objektorientierung ;)
        /*if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                    array_push($return_array, $row);
            }
            
            foreach ($return_array as $row) {	
                $this->kurs_id      = $row['kurs_id'];
                $this->kurs_name    = $row['kurs_name'];
                $this->kurs_start   = $row['kurs_start'];
                $this->kurs_ende    = $row['kurs_ende'];
                $this->sichtbarkeit = $row['sichtbarkeit'];
                $this->benutzername = $row['benutzername'];
            }
            
            return $return_array;
        */  
            return $result;
        } else {
            //kein Ergebnis gefunden
            return 0;
                //echo "Kein Ergebnis gefunden.";
        }

        
            
        /*    return $result;
        }else{
            return 0;
        }*/
    }
    
    
    
    /*
     * Pr�ft ob der User den KUrs bestanden hat
     */    
    
    public function kursResult($benutzername, $kurs_id){
    	$db = new Db_connection();
    	if((User::currentUser()->istTeilnehmer()) && ($benutzername == NULL)){
    		$query = "SELECT bestanden FROM benutzer_kurs WHERE benutzername = '".$benutzername."' 
    					AND kurs_id = ".$kurs_id." ;";
    		$result = $db->execute($query);    		 
    		
    		if ($result == 1) 	return true;
    		else 				return false;
    	}
    }
    
    /*
     * List von alle bestandene Kurse vom Benutzer
     */
    
    public function pdfList($benutzername){
    	$db = new Db_connection();
    	if((User::currentUser()->istTeilnehmer())){
    		$query = "SELECT kurs_id, kurs_name FROM benutzer_kurs WHERE benutzername = '".$benutzername."' AND bestanden = 1
    				JOIN kurs ON kurs.kurs_id = benutzer_kurs.kurs_id
    				;";
    		$result = $db->execute($query);
    	
    		if (mysqli_num_rows($result) > 0) 	return true;
    		else 								return false;
    	}
    }
    
    
    /*
     * Lädt die archivierten Kurse
     */
    public function loadarchivedKurse($benutzername) {
        $db = new Db_connection();
        if(User::currentUser()->istZertifizierer() || User::currentUser()->istAdmin()){
            $query = "SELECT * FROM kurs WHERE benutzername = '".$benutzername."'
                            AND (CURRENT_DATE >= kurs_ende + 30);";
        }    	
    	$result = $db->execute($query);
        
        if (mysqli_num_rows($result) > 0){
            return $result;
        } else {
            //kein Ergebnis gefunden
            return 0;
        }
    }
    
    /*
     * Lädt alle Kurse zu denen sich der Teilnehmer eingeschrieben hat.
     * @param Benutzername des Teilnehmers
     * @return Array mit allen Kursen, bzw. 0, falls noch keine Daten existieren
     */
    public function loadsignedkurse($benutzername) {
        $db = new Db_connection();
        if(User::currentUser()->istTeilnehmer()) {
            $query = "SELECT * FROM benutzer_kurs JOIN kurs USING (kurs_id) WHERE benutzer_kurs.benutzername = '".$benutzername."';";
            $result = $db->execute($query);
        }

        if (mysqli_num_rows($result) > 0){
            return $result;
        }else{
            return 0;
        }
    }
    
   
    /**
     * Speichert einen neuen Kurs in die Datenbank
     * @return führt die Query aus
     */
    
    public function save(){
        $db = new Db_connection();
	$query = "INSERT INTO kurs (kurs_name, kurs_start, kurs_ende, sichtbarkeit, benutzername, beschreibung) VALUES 
			('".$this->kurs_name."','".$this->kurs_start."', '".$this->kurs_ende."', '".$this->sichtbarkeit."', '".$this->benutzername."', '".$this->beschreibung."')";
        
	$result = $db->execute($query);
        
        return $result;               
    }
    
    /*
     * Speichert die geänderten Kursdaten in der Datenbank ab
     * @param übergebene Bestandteile des Kurses
     * @return führt die Query aus
     */
    
    public function update($kursid, $kursname, $kursstart, $kursende, $sichtbarkeit, $beschreibung) {
        $db = new Db_connection();
        $query = "UPDATE kurs SET 
                    kurs_name = '".$kursname."',
                    kurs_start = '".$kursstart."',
                    kurs_ende = '".$kursende."',
                    sichtbarkeit = '".$sichtbarkeit."',
                    beschreibung = '".$beschreibung."' where kurs_id = '".$kursid."';";
        $result = $db->execute($query);
        return $result;
    }
    
    /*
     *  TODO:::::::Archivierter Kurs soll in die Datenbank eingepflegt werden
     */
    public function insert($kursname, $kursstart, $kursende, $sichtbarkeit, $benutzername, $beschreibung) {
        $db = new Db_connection();
	$query = "INSERT INTO kurs (kurs_name, kurs_start, kurs_ende, sichtbarkeit, benutzername, beschreibung) VALUES ('".$kursname."','".$kursstart."', '".$kursende."', '".$sichtbarkeit."', '".$benutzername."', '".$beschreibung."')";
        
	$result = $db->execute($query);
        
        return $result;  
    }
    
    /**
     * Pr�ft anhand des aktuellen Datums, ob das Kurs_Ende erreicht wurde.
     * @return true falls noch aktiv, false falls nicht
     */
    public function active($kurs_id) {
    	$db = new Db_connection();
    	
    	$query_future = "select 1 from kurs where (CURRENT_DATE < kurs_start) and kurs_id=".$kurs_id;
    	$result_future= $db->execute($query_future);
    	
    	$query_current = "select 1 from kurs where (CURRENT_DATE BETWEEN kurs_start AND kurs_ende) and kurs_id=".$kurs_id;
    	$result_current= $db->execute($query_current);
    	
    	if (mysqli_num_rows($result_future)>0) {
    		return 2;
    	}
    	elseif (mysqli_num_rows($result_current)>0)   		
    			return 1;
    	else return 0;
    	
    }

    
    /*
     * Laedt alle bestehende Zertifizierern
     */	
    public function loadZertifizierer(){
    	$db = new Db_connection();
    	 
    	$query = "SELECT benutzername FROM benutzer WHERE ist_zertifizierer = 1;";
    	$result = $db->execute($query);
    	    	
    	if (mysqli_num_rows($result) > 0){
    		return $result;
    	}else{
    		return 0;
    	}
    }
    	
   

    function getKurs_id() {
        return $this->kurs_id;
    }

    function getKurs_name() {
        return $this->kurs_name;
    }

    function getKurs_start() {
        return $this->kurs_start;
    }

    function getKurs_ende() {
        return $this->kurs_ende;
    }

    function getSichtbarkeit() {
        return $this->sichtbarkeit;
    }

    function getBenutzername() {
        return $this->benutzername;
    }
    
    function getBeschreibung() {
        return $this->beschreibung;
    }
    
    function getTeilnehmerzahl() {
    	return $this->teilnehmerzahl;
    }

    function setKurs_id($kurs_id) {
        $this->kurs_id = $kurs_id;
    }

    function setKurs_name($kurs_name) {
        $this->kurs_name = $kurs_name;
    }

    function setKurs_start($kurs_start) {
        $this->kurs_start = $kurs_start;
    }

    function setKurs_ende($kurs_ende) {
        $this->kurs_ende = $kurs_ende;
    }

    function setSichtbarkeit($sichtbarkeit) {
        $this->sichtbarkeit = $sichtbarkeit;
    }

    function setBenutzername($benutzername) {
        $this->benutzername = $benutzername;
    }
    
    function setBeschreibung($beschreibung) {
        $this->beschreibung = $beschreibung;
    }
}
