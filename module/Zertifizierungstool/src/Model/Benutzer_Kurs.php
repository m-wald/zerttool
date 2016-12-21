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
	
	
	public function insert($benutzer,$kurs_id) {
		
		$db=new Db_connection();
		
		//Prüfung, ob Benutzer bereits im Kurs ist
		$query="select * from benutzer_kurs where benutzername='".$benutzer."' and kurs_id=".$_kurs_id.";";
		
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
	
	
	
	
	
	
	
	
	
	
	
	
}
