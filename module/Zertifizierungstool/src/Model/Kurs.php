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
    
    /*
    public function __construct($kurs_name, $kurs_start, $kurs_ende, $sichtbarkeit, $benutzername, $beschreibung) {
        $this->kurs_id = "";
        $this->kurs_name = $kurs_name;
        $this->kurs_start = $kurs_start;
        $this->kurs_ende = $kurs_ende;
        $this->sichtbarkeit = $sichtbarkeit;
        $this->benutzername = $benutzername;
        $this->beschreibung = $beschreibung;
    }*/
    
    public function __construct($kurs_id, $kurs_name, $kurs_start, $kurs_ende, $sichtbarkeit, $benutzername, $beschreibung, $teilnehmeranzahl) {
		
    	$db = new Db_connection();
    	
    	$mysqli= $db->getConnection();
    	
    	
    	
    	$this->kurs_id          = $mysqli->real_escape_string($kurs_id);
        $this->kurs_name        = $mysqli->real_escape_string($kurs_name);
        $this->kurs_start       = $mysqli->real_escape_string($kurs_start);
        $this->kurs_ende        = $mysqli->real_escape_string($kurs_ende);
        $this->sichtbarkeit     = $mysqli->real_escape_string($sichtbarkeit);
        $this->benutzername     = $mysqli->real_escape_string($benutzername);
        $this->beschreibung     = $mysqli->real_escape_string($beschreibung);
        $this->teilnehmerzahl   = $mysqli->real_escape_string($teilnehmeranzahl);
		
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
        
        $mysqli = $db->getConnection();
        $id = $mysqli->real_escape_string($id);
        
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
    	
    	$mysqli = $db->getConnection();
    	
    	$benutzername = $mysqli->real_escape_string($benutzername);
    	
    	
        if(User::currentUser()->istZertifizierer()){
        	$query = "SELECT * FROM kurs WHERE (CURRENT_DATE <= kurs_ende);";
           	// $query = "SELECT * FROM kurs WHERE benutzername = '".$benutzername."'
             //               AND (CURRENT_DATE <= kurs_ende);";
        }elseif((User::currentUser()->istAdmin()) && ($benutzername == NULL)){
            $query = "SELECT * FROM kurs WHERE (CURRENT_DATE <= kurs_ende);";
	}elseif((User::currentUser()->istTeilnehmer()) && ($benutzername == NULL)){
            $query = "SELECT * FROM kurs WHERE (CURRENT_DATE <= kurs_ende) AND sichtbarkeit = 1;";
	}
    	
    	$result = $db->execute($query);
    
        if (mysqli_num_rows($result) > 0) {
            $return_array = array();
            while ($row = mysqli_fetch_assoc($result)) {
                    $kurse = new Kurs(
                            $row["kurs_id"],
                            $row["kurs_name"],
                            $row["kurs_start"],
                            $row["kurs_ende"],
                            $row["sichtbarkeit"],
                            $row["benutzername"]);
                    
                    array_push($return_array, $kurse);
            }
                        
            return $return_array;
         
        } else {
            //kein Ergebnis gefunden
            return 0;
        }
    }
    
    
    
    /*
     * Pr�ft ob der User den KUrs bestanden hat
     */    
    /*
    public function checkCourseResult($benutzername, $kurs_id){
    	$db = new Db_connection();
    	
    	$mysqli = $db->getConnection();
    	
    	$benutzername = $mysqli->real_escape_string($benutzername);
    	$kurs_id = $mysqli->real_escape_string($kurs_id);
    	
    	
    	if(User::currentUser()->istTeilnehmer() || User::currentUser()->istAdmin()){
    		$query = "SELECT bestanden FROM benutzer_kurs WHERE benutzername = '".$benutzername."' 
    					AND kurs_id = ".$kurs_id." ;";
    		$result = $db->execute($query);    		 
    		
    		if (mysqli_fetch_all($result))	 	return true;
    		else 								return false;
    	}
    }
    */
    
    /*
     * List von alle bestandene Kurse vom Benutzer
     */
    
    public function certificateList($benutzername){
    	$db = new Db_connection();
    	
    	$mysqli = $db->getConnection();
    	
    	$benutzername = $mysqli->real_escape_string($benutzername);
    	
    	if(User::currentUser()->istTeilnehmer() || User::currentUser()->istAdmin()){
    		$query = "SELECT benutzer_kurs.kurs_id, kurs.kurs_name FROM benutzer_kurs
    				JOIN kurs ON kurs.kurs_id = benutzer_kurs.kurs_id
    				WHERE benutzer_kurs.benutzername = '".$benutzername."' AND bestanden = 1
    				AND kurs.kurs_ende <= CURDATE()
    						;";
    		$result = $db->execute($query);
    	
    		/*if (mysqli_num_rows($result) > 0) 	return $result;
    		else 								return 0;
                 * 
                 */
                
                if (mysqli_num_rows($result) > 0) {
                    $return_array = array();
                    while ($row = mysqli_fetch_assoc($result)) {
                            $kurse = new Kurs(
                                    $row["kurs_id"],
                                    $row["kurs_name"],
                                    $row["kurs_start"],
                                    $row["kurs_ende"],
                                    $row["sichtbarkeit"],
                                    $row["benutzername"]);

                            array_push($return_array, $kurse);
                    }

                    return $return_array;
         
                } else {
                    //kein Ergebnis gefunden
                    return 0;
                }
    	}
    }
    
    
    /*
     * Lädt die archivierten Kurse
     */
    public function loadarchivedKurse($benutzername) {
        $db = new Db_connection();
        
        $mysqli = $db->getConnection();
        
        $benutzername = $mysqli->real_escape_string($benutzername);
        
        
        if(User::currentUser()->istZertifizierer() || User::currentUser()->istAdmin()){
            $query = "SELECT * FROM kurs WHERE benutzername = '".$benutzername."'
                            AND (CURRENT_DATE > kurs_ende);";
        }    	
    	$result = $db->execute($query);
        
        if (mysqli_num_rows($result) > 0) {
            $return_array = array();
            while ($row = mysqli_fetch_assoc($result)) {
                    $kurse = new Kurs(
                            $row["kurs_id"],
                            $row["kurs_name"],
                            $row["kurs_start"],
                            $row["kurs_ende"],
                            $row["sichtbarkeit"],
                            $row["benutzername"]);
                    
                    array_push($return_array, $kurse);
            }    
            return $return_array;
    
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
        
        $mysqli = $db->getConnection();
        
        $benutzername = $mysqli->real_escape_string($benutzername);
        
        
        if(User::currentUser()->istTeilnehmer() || User::currentUser()->istAdmin()) {
            $query = "SELECT * FROM benutzer_kurs JOIN kurs USING (kurs_id) WHERE benutzer_kurs.benutzername = '".$benutzername."' and (CURRENT_DATE between kurs_start and kurs_ende);";
            $result = $db->execute($query);
        }
        
        if (mysqli_num_rows($result) > 0) {
            $return_array = array();
            while ($row = mysqli_fetch_assoc($result)) {
                    $kurse = new Kurs(
                            $row["kurs_id"],
                            $row["kurs_name"],
                            $row["kurs_start"],
                            $row["kurs_ende"],
                            $row["sichtbarkeit"],
                            $row["benutzername"]);
                    
                    array_push($return_array, $kurse);
            }    
            return $return_array;
    
        } else {
            //kein Ergebnis gefunden
            return 0;
        }
    }
    
   
    /**
     * Speichert einen neuen Kurs in die Datenbank
     * @return führt die Query aus
     */
    
    public function save(){
        $db = new Db_connection();
        
        $date_start = new \DateTime($this->kurs_start);
        $date_ende = new \DateTime($this->kurs_ende);
        
        $this->kurs_start = $date_start->format('Y-m-d');
        $this->kurs_ende = $date_ende->format('Y-m-d');
        
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
    
    public function update($kursid, $kursname, $kursstart, $kursende, $sichtbarkeit, $benutzername ,$beschreibung) {
        $db = new Db_connection();
        
        $mysqli = $db->getConnection();
        
        $kursid = $mysqli->real_escape_string($kursid);
        $kursname = $mysqli->real_escape_string($kursname);
        $kursstart = $mysqli->real_escape_string($kursstart);
        $kursende = $mysqli->real_escape_string($kursende);
        $sichtbarkeit = $mysqli->real_escape_string($sichtbarkeit);
        $benutzername = $mysqli->real_escape_string($benutzername);
        $beschreibung = $mysqli->real_escape_string($beschreibung);
        
        $date_start = new \DateTime($kursstart);
        $date_ende = new \DateTime($kursende);
        
        $kursstart = $date_start->format('Y-m-d');
        $kursende = $date_ende->format('Y-m-d');
        
        $query = "UPDATE kurs SET 
                    kurs_name = '".$kursname."',
                    kurs_start = '".$kursstart."',
                    kurs_ende = '".$kursende."',
                    sichtbarkeit = '".$sichtbarkeit."',
                    benutzername = '".$benutzername."',
                    beschreibung = '".$beschreibung."' where kurs_id = '".$kursid."';";
        $result = $db->execute($query);
        return $result;
    }
    
    /*
     *  TODO:::::::Archivierter Kurs soll in die Datenbank eingepflegt werden
     */
    public function insert($kursname, $kursstart, $kursende, $sichtbarkeit, $benutzername, $beschreibung) {
        $db = new Db_connection();
        
        $mysqli = $db->getConnection();
        
        
        $kursname = $mysqli->real_escape_string($kursname);
        $kursstart = $mysqli->real_escape_string($kursstart);
        $kursende = $mysqli->real_escape_string($kursende);
        $sichtbarkeit = $mysqli->real_escape_string($sichtbarkeit);
        $benutzername = $mysqli->real_escape_string($benutzername);
        $beschreibung = $mysqli->real_escape_string($beschreibung);
        
        
	$query = "INSERT INTO kurs (kurs_name, kurs_start, kurs_ende, sichtbarkeit, benutzername, beschreibung) VALUES ('".$kursname."','".$kursstart."', '".$kursende."', '".$sichtbarkeit."', '".$benutzername."', '".$beschreibung."')";
        
	$result = $db->execute($query);
        
        return $result;  
    }
    
    /**
     * Pr�ft anhand des aktuellen Datums, ob das Kurs_Ende erreicht wurde.
     * @return 2 falls der Kurs erst in der Zukunft anf�ngt
     * @return 1 falls der Kurs noch aktiv, false falls nicht
     */
    public function active($kurs_id) {
    	$db = new Db_connection();
    	
    	$mysqli = $db->getConnection();
    	
    	$kurs_id = $mysqli->real_escape_string($kurs_id);
    	
    	
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
    	    	
    	 if (mysqli_num_rows($result) > 0) {
            $return_array = array();
            while ($row = mysqli_fetch_assoc($result)) {
                    $kurse = new Kurs(
                            $row["kurs_id"],
                            $row["kurs_name"],
                            $row["kurs_start"],
                            $row["kurs_ende"],
                            $row["sichtbarkeit"],
                            $row["benutzername"]);
                    
                    array_push($return_array, $kurse);
            }    
            return $return_array;
    
        } else {
            //kein Ergebnis gefunden
            return 0;
        }
    }
    
   /* function istKursleiter($zertifizierer, $kurs_id){
    	$db = new Db_connection();
    	$mysqli = $db->getConnection();
    	
    	$kurs_id = $mysqli->real_escape_string($kurs_id);
    	$zertifizierer = $mysqli->real_escape_string($zertifizierer); 
    	
    	$query = "SELECT 1 FROM kurs WHERE kurs_id=".$kurs_id." 
    				AND benutzername='".$zertifizierer."'
    			";
    	$result = $db->execute($query);
    	if(mysqli_num_rows($result) > 0){
    		return true;
    	} else {
    		return false;
    	}
    }
    	
   */

    function getKurs_id() {
        return $this->kurs_id;
    }

    function getKurs_name() {
        return $this->kurs_name;
    }

    function getKurs_start() {
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'Firefox')){
			$date = new \DateTime($this->kurs_start);
			$this->kurs_start=$date->format('d.m.Y');
        }
        return $this->kurs_start;
    }

    function getKurs_ende() {
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'Firefox')){
			$date = new \DateTime($this->kurs_ende);
			$this->kurs_ende=$date->format('d.m.Y');
        }
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
    	
    	$db = new Db_connection();
    	$mysqli = $db->getConnection();
    	
    	$kurs_id = $mysqli->real_escape_string($kurs_id);
        $this->kurs_id = $kurs_id;
    }

    function setKurs_name($kurs_name) {
    	
    	$db = new Db_connection();
    	$mysqli = $db->getConnection();
    	
    	$kurs_name = $mysqli->real_escape_string($kurs_name);
        $this->kurs_name = $kurs_name;
    }

    function setKurs_start($kurs_start) {
    	
    	$db = new Db_connection();
    	$mysqli = $db->getConnection();
    	
    	$kurs_start = $mysqli->real_escape_string($kurs_start);
        $this->kurs_start = $kurs_start;
    }

    function setKurs_ende($kurs_ende) {
    	
    	$db = new Db_connection();
    	$mysqli = $db->getConnection();
    	
    	$kurs_ende = $mysqli->real_escape_string($kurs_ende);
        $this->kurs_ende = $kurs_ende;
    }

    function setSichtbarkeit($sichtbarkeit) {
    	
    	$db = new Db_connection();
    	$mysqli = $db->getConnection();
    	
    	$sichtbarkeit = $mysqli->real_escape_string($sichtbarkeit);
        $this->sichtbarkeit = $sichtbarkeit;
    }

    function setBenutzername($benutzername) {
    	
    	$db = new Db_connection();
    	$mysqli = $db->getConnection();
    	
    	$benutzername = $mysqli->real_escape_string($benutzername);
        $this->benutzername = $benutzername;
    }
    
    function setBeschreibung($beschreibung) {
    	
    	$db = new Db_connection();
    	$mysqli = $db->getConnection();
    	
    	$beschreibung = $mysqli->real_escape_string($beschreibung);
        $this->beschreibung = $beschreibung;
    }
    
    function checkDate($_datum) {
        /*
            Test ob es sich um ein Datum handelt. String in 3 Bestandteile zerlegen. Bestandteile aufteilen.
            Prüfen ob die Bestandteile jeweils die gültige Anzahl an Ziffern besitzen.
        */

        $_anzahldatumstring = strlen($_datum);

        if($_anzahldatumstring === 10)
        {
                //Datum enthält 10 Zeichen
        }else
        {
                return false;
        }

        //Das Datum in drei Bestandteile aufteilen, wobei der Punkt als Trennelement benutzt wird
        $delimiters = array(".", "-");
        $_trenndatum = Kurs::multiexplode($delimiters, $_datum);
        //$_trenndatum = explode(".", $_datum, 3);

        //Bestandteile in 3 Variablen abspeichern
        $_tag = $_trenndatum[0]; 
        $_mon = $_trenndatum[1];
        $_jahr = $_trenndatum[2];

        //Bestandteile in 3 Variablen abspeichern und diese dann auf die Anzahl der Zeichen überprüfen
        $_tagzeichen = strlen($_trenndatum[0]);
        $_monzeichen = strlen($_trenndatum[1]);
        $_jahrzeichen = strlen($_trenndatum[2]);

        //Schaut nach wie viele Tage der übergebene Monat im übergebenen Jahr hat 
        $_anzahltageimmonat = cal_days_in_month(CAL_GREGORIAN, $_mon, $_jahr);

        if($_tag < 1 || $_tag > $_anzahltageimmonat)
        {
               return false;
        }

        //Wenn erfasster Monat weniger als 1 oder weniger als 12 ist, dann gib einen Fehler aus.
        if($_mon <1 || $_mon > 12)
        {
                return false;
        }

        //Prüfe ob das jeweilige Bestandteil des Datums eine ganze Zahl ist
        //Wenn es eine ganze Zahl ist und die Anzahl an Zeichen korrekt sind, dann ist die einzelne Bedingung wahr
        //Wenn alle Bedingungen zutreffen ist der ganze Ausdruck wahr
        if((ctype_digit($_trenndatum[0]) && $_tagzeichen == 2) && (ctype_digit($_trenndatum[1]) && $_monzeichen == 2) && (ctype_digit($_trenndatum[2]) && $_jahrzeichen == 4))
        {
                //mache nichts - String ist ein Datum
        }else
        {
                return false;
        }
        //Wenn die Methode hier angekommen ist, dann handelt es sich bei dem String um ein Datum
        return true;
    }
    
    function multiexplode ($delimiters,$string) {
        $ary = explode($delimiters[0],$string);
        array_shift($delimiters);
        if($delimiters != NULL) {
            foreach($ary as $key => $val) {
                 $ary[$key] = multiexplode($delimiters, $val);
            }
        }
        return  $ary;
    }
    
}
