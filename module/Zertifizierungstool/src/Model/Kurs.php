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
        $this->kurs_id = $kurs_id;
        $this->kurs_name = $kurs_name;
        $this->kurs_start = $kurs_start;
        $this->kurs_ende = $kurs_ende;
        $this->sichtbarkeit = $sichtbarkeit;
        $this->benutzername = $benutzername;
    }
    
    public function load($benutzername){
        $db = new Db_connection();
        
        $db->execute("SELECT * FROM kurs WHERE benutzername = $1;");
        
        
        
        
    }
}
