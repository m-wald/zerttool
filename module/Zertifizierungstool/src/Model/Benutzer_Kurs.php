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
		
		$mysqli = $db->getConnection();
		
		$benutzer = $mysqli->real_escape_string($benutzer);
		$kurs_id  = $mysqli->real_escape_string($kurs_id);
		
		//Prï¿½fung, ob Benutzer bereits im Kurs ist
		$query="select * from benutzer_kurs where benutzername='".$benutzer."' and kurs_id=".$kurs_id.";";
		
		$result=$db->execute($query);
		
		if(mysqli_num_rows($result)>0){
			return -1;
		}
		//Überprüfung ob der Teilnehmer schon alle benötigten Prüfungen bestanden hat. (Falls Teilnehmer sich aus Kurs ausgetragen hat und sich noch mal einträgt)
		$query_anzahl_pruefungen = "select count(*) from pruefung where kurs_id = ".$kurs_id; //Anzahl der Prüfungen im Kurs
		$query_anzahl_bestandene_pruefungen = "select count(*) from schreibt_pruefung join pruefung using (pruefung_id) where kurs_id =".$kurs_id." and benutzername = '".$benutzer."' and bestanden=1";
		
		$anzahl_pruefungen = $db->execute($query_anzahl_pruefungen);
		$anzahl_bestandene_pruefungen = $db->execute($query_anzahl_bestandene_pruefungen);
		
		$anzahl_pruefungen = mysqli_fetch_row($anzahl_pruefungen);
		$anzahl_bestandene_pruefungen = mysqli_fetch_row($anzahl_bestandene_pruefungen);
		
		
		if ($anzahl_bestandene_pruefungen[0]<$anzahl_pruefungen[0] || $anzahl_pruefungen[0]==0) {
		//Insert der Daten
		
			$query1="insert into benutzer_kurs(benutzername,kurs_id) values('".$benutzer."',".$kurs_id.");";
		
		} else {
			
			$query1="insert into benutzer_kurs(benutzername,kurs_id, bestanden) values('".$benutzer."',".$kurs_id.", 1);";
			
		}
		if($db->execute($query1)){
			return 1;
		}
		//falls Fehler bei Insert auftritt
		else return 0;
		
		
	}
	
        /*
         * Abfrage ob Benutzer schon im Kurs eingetragen ist
         * @author Sergej
         * @return true/false
         */
        
        public function alreadyexist($benutzername, $kursid) {
            $db = new Db_connection();
            $mysqli = $db->getConnection();
            
            $benutzername = $mysqli->real_escape_string($benutzername);
            $kursid = $mysqli->real_escape_string($kursid);
            
            //Prï¿½fung, ob Benutzer bereits im Kurs ist
            $query = "select * from benutzer_kurs where benutzername = '".$benutzername."' and kurs_id = ".$kursid.";";
            $result=$db->execute($query);
            if(mysqli_num_rows($result) > 0){
		return true;
            } else {
                return false;
            }
        }
        
        
        public function signindelete($kursid, $benutzername) {
            $db = new Db_connection();
            $mysqli = $db->getConnection();
            
            $kursid = $mysqli->real_escape_string($kursid);
            $benutzername = $mysqli->real_escape_string($benutzername);
            
            if($this->alreadyexist($benutzername, $kursid)) {
                $query = "delete from benutzer_kurs where benutzername = '".$benutzername."' and kurs_id = ".$kursid.";";
                $result=$db->execute($query);
                return true;
            }
            //Wenn Methode hier ankommt, dann konnte die Zeile nicht gelÃ¶scht werden
            return false;
        }
        
        
        public static function bestanden($id) {
        	$db = new Db_connection();
        	$mysqli = $db->getConnection();
        	
        	$id = $mysqli->real_escape_string($id);
        
        	$query = "UPDATE benutzer_kurs SET bestanden = 1 WHERE kurs_id = " .$id
        				." AND benutzername = " .User::currentUser()->getBenutzername();
        
        	$result = $db->execute($query);
        
        	if(!$result || !mysqli_num_rows($result) > 0) {
        		// Fehler bei der Datenbankabfrage oder keine Frage mit der Id gefunden
        		return false;
        	}
        		
        	return true;
        }
        
}
