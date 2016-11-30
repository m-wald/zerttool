<?php
namespace Zertifizierungstool\Model;

class Db_connection
{
	private $server    	= "localhost";
	private $database 	= "zertifizierungstool";
	private $user 		= "root";
	private $password 	= "zert4tool";
	
	private $conn		= NULL;
	private $result;
	
	public function connect()
	{
		if ($this->conn == NULL) {
			$this->conn = new \mysqli($this->server, $this->user, $this->password, $this->database);
		}
		
		if ($this->conn->connect_error)
		{
			die("Es konnte keine Verbindung zur Datenbank hergestellt werden: " . $this->conn->connect_error);	
		}
		
		return $this->conn;
	}
	
	public function execute($query) {
		if ($this->conn == NULL) {
			$this->connect();
		}
		$this->result = $this->conn->query($query);
	}
	
	public function nextRow() {
		return mysqli_fetch_array($this->result);
	}
}