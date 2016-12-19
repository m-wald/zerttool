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
    
    public function load($id) {
        $db = new Db_connection();
        $query = "SELECT * FROM kurs WHERE kurs_id = ".$id.";";
        $result = $db->execute($query);       

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
       
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
    
    /*
     * Lädt alle Kurse, die dem übergebenen Benutzernamen zugeordent sind
     * @param Benutzername des Zertifizierers
     * @return Array mit allen Kursen, ansonsten 0.
     */
    
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
    }
   
    /**
     * Speichert einen neuen Kurs in die Datenbank
     * @return führt die Query aus
     */
    
    public function save(){
        $db = new Db_connection();
	$query = "INSERT INTO kurs (kurs_name, kurs_start, kurs_ende, sichtbarkeit, benutzername) VALUES ('".$this->kurs_name."','".$this->kurs_start."', '".$this->kurs_ende."', '".$this->sichtbarkeit."', '".$this->benutzername."')";
        
	$result = $db->execute($query);
        
        return $result;               
    }
    
    /*
     * Speichert die geänderten Kursdaten in der Datenbank ab
     * @param übergebene Bestandteile des Kurses
     * @return führt die Query aus
     */
    
    public function update($kursid, $kursname, $kursstart, $kursende, $sichtbarkeit) {
        $db = new Db_connection();
        $query = "UPDATE kurs SET 
                    kurs_name = '".$kursname."',
                    kurs_start = '".$kursstart."',
                    kurs_ende = '".$kursende."',
                    sichtbarkeit = '".$sichtbarkeit."' where kurs_id = '".$kursid."';";
        $result = $db->execute($query);
        return $result;
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
}
