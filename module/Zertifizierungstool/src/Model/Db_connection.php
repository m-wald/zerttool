<?php
namespace Zertifizierungstool\Model;

class Db_connection
{
	private $server    	= "localhost";
	private $database 	= "zertifizierungstool";
	private $user 		= "root";
	private $password 	= "zert4tool";
	
	private $conn;
	
	public function __construct() {
		$this->conn = new \mysqli($this->server, $this->user, $this->password, $this->database);
		
		if ($conn->connect_error)
		{
			die("Es konnte keine Verbindung zur Datenbank hergestellt werden: " . $this->conn->connect_error);
		}
	}
	

	public function execute($query) {
		/*
		$conn = new \mysqli($this->server, $this->user, $this->password, $this->database);
		
		if ($conn->connect_error)
		{
			die("Es konnte keine Verbindung zur Datenbank hergestellt werden: " . $this->conn->connect_error);
		}
		*/
		
		$result = mysqli_query($this->$conn, $query);
		
		if (!empty(mysqli_error($this->$conn))) {
			echo mysqli_error($this->$conn);
			echo "<br>" . $query;
			
			return false;
		}
		
		return $result;
	}
	
	public function getConnection() {
		return $this->conn;
	}
}