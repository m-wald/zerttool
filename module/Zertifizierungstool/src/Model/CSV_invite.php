<?php
namespace Zertifizierungstool\Model;

use Zertifizierungstool\Model\Db_connection;
use Zertifizierungstool\Model\Kurs;
use Zertifizierungstool\Model\User;

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
	
	public function inviteMail($email, $kurs_id) {
		
		$kurs = new Kurs();
		$kurs->load($kurs_id);
		$user = new User();
		if ($user->load_via_email($email)) {
			
			$empfaenger = $email;
			$betreff = "Einladung zu Kurs ".$kurs->getKurs_name();
			$text = "Hallo ".$user->getVorname()." ".$user->getNachname().",\n\n Sie wurden in den Kurs ".$kurs->getKurs_name()." eigeladen.\n
					Bitte folgen Sie diesem Link um sich einzutragen:\n\n
					132.231.36.205/kurs/enterkurs?benutzername=".$user->getBenutzername()."&kurs_id=".$kurs_id;
			$text = wordwrap($text, 70);
			mail ($empfaenger, $betreff, $text);
		}else {
			
			$empfaenger = $email;
			$betreff = "Einladung zu Kurs ".$kurs->getKurs_name();
			$text = "Hallo zukünftiger Teilnehmer,\n\n Sie wurden in den Kurs ".$kurs->getKurs_name()." eigeladen.\n
					Für diese E-Mail-Adresse wurde bisher keine Registrierung festgestellt.
					Bitte folgen Sie diesem Link um sich zu registrieren:\n\n
					www.zerttool.tk/kurs/enterkurs?email=".$email."&kurs_id=".$kurs_id."
					\n\n Sie werden nach der Registrierung automatisch in den Kurs eingetragen.";
					
			$text = wordwrap($text, 70);
			mail ($empfaenger, $betreff, $text);
			
		}

		
	}
	
	
}