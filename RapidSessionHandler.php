<?php

class RapidSessionHandler 
{
	var $DB;
	var $SessionID;

	public function __construct( $db, $name = '') 
	{
		$this->DB = $db;

		session_set_save_handler(
			array($this, "open"),
			array($this, "close"),
			array($this, "read"),
			array($this, "write"),
			array($this, "destroy"),
			array($this, "gc")
		);
		
		if (strlen( $name) > 0)
		{
			session_name( $name);
		}
		
	}
	
	public function setTimeout( $n)
	{
		session_set_cookie_params( $n);
	}	

	public function start()
	{
		session_start();	
	}
	
	public function open($savePath, $sessionName) 
	{
		return true;
	}

	public function close() 
	{
		return true;
	}

	public function read($id) 
	{
		$stm = $this->DB->prepare( 'select value from sessions where session_id = ?');
		$stm->bindParam( 1, $id, PDO::PARAM_STR);
		$stm->execute();
		$data = 0;
		if ($row = $stm->fetch())
		{
			$data = $row['value'];
		}
		return $data;
	}

	public function write($id, $data) 
	{
		$stm = $this->DB->prepare( 'insert into sessions (session_id, value, date_created, last_modified) values (?, ?, Now(), Now()) on duplicate key update value = ?, last_modified = Now()');
		
		$stm->bindParam( 1, $id, PDO::PARAM_STR);
		$stm->bindParam( 2, $data, PDO::PARAM_STR);
		$stm->bindParam( 3, $data, PDO::PARAM_STR);
		$stm->execute();
		return true;
	}

	public function destroy($id) 
	{
		// nada
	}

	public function gc($maxlifetime) 
	{
		// not yet
	}
}

?>