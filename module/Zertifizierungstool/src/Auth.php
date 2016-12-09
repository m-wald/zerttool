<?php
namespace Zertifizierungstool;

use Zend\Authentication\Adapter\DbTable\CredentialTreatmentAdapter;
use Zend\Db\Adapter\Adapter as DbAdapter;

class Auth {
	
	private $table 			  = "benutzer";
	private $identityColumn   = "benutzername";
	private $credentialColumn = "passwort";
	private $credentialTreatment = "Password_hash()";
	
	public static function authenticate($benutzername, $passwort) {
		$dbAdapter = new DbAdapter(array(
				'driver'   => 'MySqli',
				'database' =>'localhost/zertifizierungstool'
		));
		
		$authAdapter = new CredentialTreatmentAdapter($dbAdapter, $this->table, $this->identityColumn, $this->credentialColumn, $this->$credentialTreatment);
		
		$authAdapter->setIdentity($benutzername);
		$authAdapter->setCredential($passwort);
		
		// return $authAdapter->authenticate();
		$result = $authAdapter->authenticate();
		
		if (!$result->isValid()) {
			//Authentifizierung fehlgeschlagen
			print_r($result->getMessages());
		}else {
			echo $result->getIdentity();
			echo "Objekt: " . print_r($authAdapter->getResultRowObject());
		}
	}
	
}