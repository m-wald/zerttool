<?php
namespace Zertifizierungstool\Model;

class Db_connection
{
	private $server    	= "localhost";
	private $database 	= "zertifizierungstool";
	private $user 		= "root";
	private $password 	= "zert4tool";	
	
	private $result;
	
	public function execute($query) {
		$conn = new \mysqli($this->server, $this->user, $this->password, $this->database);
		
		if ($conn->connect_error)
		{
			die("Es konnte keine Verbindung zur Datenbank hergestellt werden: " . $this->conn->connect_error);
		}
		
		$this->result = $conn->query($query);
	}
	
	public function nextRow() {
		return mysqli_fetch_assoc($this->result);
	}
}