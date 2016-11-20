<?php
namespace Zertifizierungstool\Model;

class User
{
	public $benutzername;
	public $passwort;
	public $email;
	
	public function load() {
		$this->benutzername = "Lehner";
	}
}