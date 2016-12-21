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
		
		
		$query="insert into benutzer_kurs(benutzername,kurs_id) values('".$benutzer."',".$kurs_id.");";
		
		if($db->execute($query)){
			return true;
		}
		
		else return false;
		
		
	}
	
	
	
	
	
	
	
	
	
	
	
	
}
