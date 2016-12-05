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

    public function __construct($kurs_id, $kurs_name, $kurs_start, $kurs_ende, $sichtbarkeit, $benutzername) {
        $this->kurs_id      = $kurs_id;
        $this->kurs_name    = $kurs_name;
        $this->kurs_start   = $kurs_start;
        $this->kurs_ende    = $kurs_ende;
        $this->sichtbarkeit = $sichtbarkeit;
        $this->benutzername = $benutzername;
    }
    
    public function __construct() {
    }

    /**
     * Lädt die Daten des Kurses mit der übergebenen Id
     * 
     * @param Id des Kurses $id
     * 
     * @return true, falls keine Fehler aufgetreten sind. Sonst false
     */
    public function laden($id) {
        $db = new Db_connection();
        $result = $db->execute("SELECT * FROM kurs WHERE benutzername = $1;");
        
        while ($row = mysqli_fetch_assoc($result)) {
        	$this->kurs_id      = $row['kurs_id'];
        	$this->kurs_name    = $row['kurs_name'];
        	$this->kurs_start   = $row['kurs_start'];
        	$this->kurs_ende    = $row['kurs_ende'];
        	$this->sichtbarkeit = $row['sichtbarkeit'];
        	$this->benutzername = $row['benutzername'];
        	
        	return true;
        }
        
        return false;
    }

    public function update($kurs_id) {
        
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
