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
		$query = "insert into eingeladen(email,kurs_id) values ('".$email."',".$kurs_id.");";
		$result = $db->execute($query);
		
		return $result;
	}
	
	
}