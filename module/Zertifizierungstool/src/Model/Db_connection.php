<?php

class Db_connection
{
	private $server    	= "localhost";
	private $database 	= "zertifizierungstool";
	private $user 		= "root";
	private $password 	= "zert4tool";
	
	public function getConnection() {
		$conn = new \mysqli($this->server, $this->user, $this->password, $this->database);
		
		if ($conn->connect_error)
		{
			die("Es konnte keine Verbindung zur Datenbank hergestellt werden: " . $this->conn->connect_error);
		}
		
		return $conn;
	}
	

	public function execute($query) {
		$conn = new \mysqli($this->server, $this->user, $this->password, $this->database);
		
		if ($conn->connect_error)
		{
			die("Es konnte keine Verbindung zur Datenbank hergestellt werden: " . $this->conn->connect_error);
		}

		$result = mysqli_query($conn, $query);
		
		if (!empty(mysqli_error($conn))) {
			echo mysqli_error($conn);
			echo "<br>" . $query;
			
			return false;
		}
		
		return $result;
	}
	
	/**
	 * Führt die übergebene Datenbankabfrage aus.
	 * Gedacht für "INSERT"-Befehle und gibt die Id des eingefügten Datensatzes zurück.
	 * @param Query-String $query
	 * @return false, falls ein Fehler aufgetreten ist, sonst die Id
	 */
	public function insert($query) {
		$conn = new \mysqli($this->server, $this->user, $this->password, $this->database);
		
		if ($conn->connect_error)
		{
			die("Es konnte keine Verbindung zur Datenbank hergestellt werden: " . $this->conn->connect_error);
		}
		
		mysqli_query($conn, $query);
		
		if (!empty(mysqli_error($conn))) {
			echo mysqli_error($conn);
			echo "<br>" . $query;
				
			return false;
		}
		
		return mysqli_insert_id($conn);
	}

}