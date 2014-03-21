<?php
/*
 * @package		RapidEngine
* @copyright	(c) 2014 Ed Diana
* @license		MIT License
*
* Copyright (C) 2013-14 Ed Diana
*
* Permission is hereby granted, free of charge, to any person obtaining a copy of this software and
* associated documentation files (the "Software"), to deal in the Software without restriction,
* including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense,
* and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so,
* subject to the following conditions:
*
* The above copyright notice and this permission notice shall be included in all copies or substantial
* portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
* INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR
* PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE
* FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE,
* ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

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