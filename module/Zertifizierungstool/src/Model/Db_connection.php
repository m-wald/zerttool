<?php
namespace Zertifizierungstool\Model;

class Db_connection
{
	private $server    	= "localhost";
	private $database 	= "zertifizierungstool";
	private $user 		= "root";
	private $password 	= "zert4tool";	
	
	public function execute($query) {
		$conn = new \mysqli($this->server, $this->user, $this->password, $this->database);
		
		if ($conn->connect_error)
		{
			die("Es konnte keine Verbindung zur Datenbank hergestellt werden: " . $this->conn->connect_error);
		}
		
		$result = mysqli_query($conn, $query);
		$return_array = array();
		print_r($result);
		
		if (mysqli_num_rows($result) > 0) {
			while ($row = mysqli_fetch_assoc($result)) {
				array_push($return_array, $row);
			}
		} else {
			echo "Kein Ergebnis gefunden.";
		}
		
		return $return_array;
	}
}