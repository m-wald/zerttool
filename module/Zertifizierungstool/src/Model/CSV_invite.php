<?php
namespace Zertifizierungstool\Model;

use Zertifizierungstool\Model\Db_connection;


class CSV_invite {
	
	private $email;
	private $kurs_id;
	
	
	public function insert_data($email,$kurs_id) {
		
		$this->email   = $email;
		$this->kurs_id = $kurs_id;
		
		
		//Daten in DB schreiben
		
		$db = new Db_connection();
		
		$query= "select * from eingeladen where email = '".$email."' and kurs_id = ".$kurs_id.";";
		$result=$db->execute($query);
		
		if (mysqli_num_rows($result)>0) {
			return false;
		}
		else {
		$query1 = "insert into eingeladen(email,kurs_id) values ('".$email."',".$kurs_id.");";
		$result1 = $db->execute($query1);
		
		return $result1;
		}
	}
	
	
}