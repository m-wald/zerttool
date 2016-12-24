<?php
namespace Zertifizierungstool\Model;

use Zertifizierungstool\Model\Db_connection;

/**
 * @author Michael's
 *
 */

class Benutzer_Kurs {
	
	private $benutzer;
	private $kurs_id;
	private $bestanden;
	
	
	/**
	 * Methode zum Eintragen eines Benutzers in einen bestimmten Kurs
	 * @param $benutzer Benutzer der in Kurs zugeordnet werden soll
	 * @param $kurs_id Kurs dem ein Benutzer zugeordnet werden soll
	 * @return 1 falls Insert funktioniert hat, -1 falls der Benutzer schon dem entsprechenden
	 * 		   Kurs zugeordnet wurde, 0 falls beim Insertversuch ein Datenbankfehler auftritt
	 */
	public function insert($benutzer,$kurs_id) {
		
		$db=new Db_connection();
		
		//Pr�fung, ob Benutzer bereits im Kurs ist
		$query="select * from benutzer_kurs where benutzername='".$benutzer."' and kurs_id=".$kurs_id.";";
		
		$result=$db->execute($query);
		
		if(mysqli_num_rows($result)>0){
			return -1;
		}
		
		//Insert der Daten
		
		$query1="insert into benutzer_kurs(benutzername,kurs_id) values('".$benutzer."',".$kurs_id.");";
		
		if($db->execute($query1)){
			return 1;
		}
		//falls Fehler bei Insert auftritt
		else return 0;
		
		
	}
	
        /*
         * Abfrage ob Benutzer schon im Kurs eingetragen ist
         * @author Sergej
         * @params true/false
         */
        public function signintesting($kursid, $benutzername) {
            $db = new Db_connection();
            
            //Pr�fung, ob Benutzer bereits im Kurs ist
            $query="select * from benutzer_kurs where benutzername='".$benutzername."' and kurs_id=".$kursid.";";
            $result=$db->execute($query);
            if(mysqli_num_rows($result) == 1){
		return true;
            } else {
                return false;
            }
        }
	
	
	
	
	
	
	
	
	
	
}
