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
    
    /*
    public function __construct($kurs_id, $kurs_name, $kurs_start, $kurs_ende, $sichtbarkeit, $benutzername) {
        $this->kurs_id = $kurs_id;
        $this->kurs_name = $kurs_name;
        $this->kurs_start = $kurs_start;
        $this->kurs_ende = $kurs_ende;
        $this->sichtbarkeit = $sichtbarkeit;
        $this->benutzername = $benutzername;
    }
     * 
     */
    
    public function __construct($kurs_name, $kurs_start, $kurs_ende, $sichtbarkeit, $benutzername) {
        $this->kurs_id = "";
        $this->kurs_name = $kurs_name;
        $this->kurs_start = $kurs_start;
        $this->kurs_ende = $kurs_ende;
        $this->sichtbarkeit = $sichtbarkeit;
        $this->benutzername = $benutzername;
    }
    
    /**
     * L�dt die Daten des Kurses mit der �bergebenen Id
     * 
     * @param Id des Kurses $id
     * 
     * @return true, falls keine Fehler aufgetreten sind. Sonst false
     */
    
    /*
    public function load($id) {
        $db = new Db_connection();
        $query = "SELECT * FROM kurs WHERE kursid = '".$id."';";
        $result = $db->execute($query);

        $return_array = array();

        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                array_push($return_array, $row);
            }
        } else {
            echo "Kein Ergebnis gefunden!";
        }
        
        foreach ($row as $return_array) {
            $this->kurs_id = $row['kurs_id'];
            $this->kurs_name = $row['kurs_name'];
            $this->kurs_start = $row['kurs_start'];
            $this->kurs_ende = $row['kurs_ende'];
            $this->sichtbarkeit = $row['sichtbarkeit'];
            $this->benutzername = $row['benutzername'];

            return true;
        }

        //Wenn die Methode hier ankommt, dann konnte das Objekt nicht erzeugt werden
        return false;
    }
    
    public function loadKurse($benutzername) {
    	$db = new Db_connection();
    	$query = "SELECT * FROM kurs WHERE benutzername = '".$benutzername."'
    			AND (CURRENT_DATE BETWEEN kurs_start
                        AND kurs_ende);";
    	
    	$result = $db->execute($query);
    
    	$return_array = array();
        
        if (mysqli_num_rows($result) > 0){
            return $result;
        }else{
            return 0;
        }
        
        /*
    	if (mysqli_num_rows($result) > 0) {
    		while ($row = mysqli_fetch_assoc($result)) {
    			array_push($return_array, $row);
    		}
            return $return_array;
    	} else {
    		echo "Kein Ergebnis gefunden!";
                return 0;
    	}*/
    	
    	/*
    	$courseloaded = false;
    	
    	foreach ($row as $return_array) {
    		$this->kurs_id = $row['kurs_id'];
    		$this->kurs_name = $row['kurs_name'];
    		$this->kurs_start = $row['kurs_start'];
    		$this->kurs_ende = $row['kurs_ende'];
    		$this->sichtbarkeit = $row['sichtbarkeit'];
    		$this->benutzername = $row['benutzername'];
    		
    		$courseloaded = true;
    	}
    	
    	if(!$courseloaded){
    		return true;
    	}else{
	  		return false;
 
         * 
         *    	}*/
    
/*    
    }
*/    
    /**
     * Schreibt den aktuellen Kurs in die Datenbank
     */
    
/*    
    public function save(){
        $db = new Db_connection();
	$query = "INSERT INTO kurs (kurs_name, kurs_start, kurs_ende, sichtbarkeit, benutzername) VALUES ('".$this->kurs_name."','".$this->kurs_start."', '".$this->kurs_ende."', '".$this->sichtbarkeit."', '".$this->benutzername."')";
        
	$result = $db->execute($query);
        
        return $result;               
    }

    public function update($kurs_id) {
        $db = new Db_connection();
        $query = "SELECT * FROM kurs WHERE kursid = $1;";
        $result = $db->execute($query);

        //
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
    
    /**
     * Vergibt die 
     */
    /*
    public function nextKurs_Id() {
        $db = new Db_connection();
        $query = "SELECT coalesce(max(kurs_id)+1, 1) from KURS";

        $result = $db->execute($query);

        $count = pg_num_rows($result);

        if ($count == 1) {
            while($row = pg_fetch_row($result) {
                $kursid = $row[0];
            }
            return $kursid;
        } else {
            echo "Fehler bei der Abfrage aufgetreten!";
            mysqli_close($db);
            return 0;
        }
    }
    */
}
