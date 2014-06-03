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

require_once("RapidEngineConfig.php");
require_once("RapidTemplate.php");
require_once("RapidResponse.php");
require_once("RapidComponent.php");
require_once("RapidSessionHandler.php");
require_once("RapidApplication.php");
require_once("actions/DefaultRapidResponse.php");

//define various settings


//

function RDebug( $s)
{
	//echo $s . "<br>";
	//error_log( $s);	
}

class RapidEngineClass
{
	public $Options;
	public $Actions;
	public $Components;
	public $ComponentPaths;
	public $ClassPaths;
	public $TemplatePaths;
	public $DefaultTemplate = '';
	public $Databases;
	public $SessionHandler = 0;
	public $Application = 0; //user application object
	public $DeviceType = '';  //for templating
	
	public $ComponentsInitialized = false;
	
	function __construct()
	{
		$this->Options = array();
		$this->Actions = array();
		$this->ComponentPaths = array();
		$this->ClassPaths = array();
		$this->TemplatePaths = array();
		$this->DefaultTemplate = 'main';
		$this->Databases = array();
	}	
	
	public function DefineAction( $action, $class)
	{
		if ((substr( $action, 0, 1) != '/') && (substr( $action, 0, 1) != '@')) 
		{
			$action = '/' . $action;
		}
		
		//RDebug( 'defining action ' . $action);
		$this->Actions[$action] = new RapidEngineAction( $action, $class);	
	}

	public function AddClassPath( $sPath)
	{
		$this->ClassPaths[] = AddTrailingSlash( $sPath);
	}
	
	public function AddComponentPath( $sPath)
	{
		$this->ComponentPaths[] = AddTrailingSlash( $sPath);
	}

	public function AddTemplatePath( $sPath)
	{
		$this->TemplatePaths[] = AddTrailingSlash( $sPath);
	}


	public function UseComponent( $sComponent, $sPath = '')
	{
		//also check for existing, pre req's etc.
		
		$a = array();
		$a['Name'] = $sComponent;
		$a['Object'] = 0;
		$a['Path'] = $sPath;
		$this->Components[] = $a;
	}

	public function AddDatabase( $a)
	{
		//'dbms' (mysql), 'server', 'database', 'user', 'password'
		//'db' is the db object
		$this->Databases[] = $a;	
	
	}
	
	//initializes the first database connection
	public function InitDatabase()
	{
		if (count( $this->Databases) > 0)
		{
			$dbms = $this->Databases[0]['dbms'];
			$server = $this->Databases[0]['server'];
			$database = $this->Databases[0]['database'];
			$user = $this->Databases[0]['user'];
			$pass = $this->Databases[0]['password'];
			
			$this->Databases[0]["db"] = new PDO( "{$dbms}:host={$server};dbname={$database}", $user, $pass); 					
			//RDebug( "dbh: " . $this->Databases[0]["db"]);
		}
		
		
	}

	public function Dispatch( $override_action = '')
	{
		global $RapidEngineDefaultActionPath;
		

		//are we reading the action from the request, or having one manually passed?
		if (strlen( $override_action) > 0)
		{
			$action = $override_action;
		}
		else
		{
			//get the uri requested, minus any get params
			$a = explode( '?', $_SERVER['REQUEST_URI']);
			//$action = substr( $a[0], 1);
			$action = $a[0];
			
			//override with get?  will prob be removed
			if (strlen( $_GET['action']) > 0)
			{
				$action = '/' . $_GET['action'];
			}
			if (strlen($action) < 1)
			{
				$action = '/';
			}
		}
		
		//add local template paths so they are the end of the list
		$this->AddTemplatePath( AddTrailingSlash( dirname(__FILE__)) . 'templates');
		
		//
		$this->InitDatabase();
		$db = GetDB();


		//init sessions
		$this->SessionHandler = new RapidSessionHandler( $db, 'whatever');
		$this->SessionHandler->setTimeout( 30 * 24 * 60 * 60);
		$this->SessionHandler->start();
		
		//initiate components
		$this->InitializeComponents();
		
		
		//init application
		if ($this->Application)
		{
			if (! $this->Application->Initiated)
			{
				$this->Application->Init();
			}
		}
		
		//create descended response object
		//assign its base properties
		//call its GetResponse() method
		
		RDebug( "Action " . $action);
			
		//do we have a handler for this action?
		if ( array_key_exists( $action, $this->Actions))
		{
			$c = $this->Actions[$action];
			RDebug( "found");
		}
		else
		{
			//is there a 404 handler for the missing request?
			if ( array_key_exists( '@404', $this->Actions))
			{
				$c = $this->Actions['@404'];
			}
			else
			{			
				$c = $this->Actions['/'];
			}	
		}

		
		//secondary task?
		$task = $_GET['task'] . '';
		
		$sClassFile = $this->FindClassFile( $c->Class);
		RDebug("class $sClassFile");


		if (strlen( $sClassFile) > 0)
		{
			require_once( $sClassFile);
			
			$cc = $c->Class;
			
			$r = new $cc();
				
			$h = '';
			//running a task or the default response?
			if (strlen( $task) > 0)
			{
				//is it white listed?
				if ($r->TaskAvailable( $task))
				{
					$h = $r->$task( 0);					
				}
				else
				{
					//error?
					$Rebug( "task {$task} is not available for this class");
				}	
			}
			else
			{
				//are we calling the default response, or a pre-defined method?
				if (strlen( $c->Method) > 0)
				{
					$m = $c->Method;
					$h = $r->$m( 0);	
				}
				else
				{					
					$h = $r->GetResponse( 0);
				}
			}
			
			//check errors
			//load main template
			if (!$r->SkipTemplate)
			{
				if (strlen( $r->Template) > 0)
				{
					$tpl = new RapidTemplate( $r->Template, $this->TemplatePaths, $this->DeviceType);
				}
				else
				{
					$tpl = new RapidTemplate( 'main', $this->TemplatePaths, $this->DeviceType);
				}
				if ($tpl->Loaded)
				{
					$tpl->SetTag( 'BODY', $h);
					//$tpl->SetTag( 'TITLE', $r->Title);
					
					for ($ii = 1; $ii <= count( $r->Tags); $ii++)
					{
						$tpl->SetTag( $r->Tags[$ii-1], $r->Values[$ii-1]);
					}
					
					if ($this->Application)
					{
						foreach ($this->Application->Tags as $t => $v)
						{
							$tpl->SetTag($t, $v);
						}
					}
					
					$h = $tpl->Merge();
				}
			}
			else
			{
			}			
			
			return $h;
		}	
		else
		{
			//init and return the default RapidEngine response	
		}
			
			
	}
	
	public function Redirect( $url)
	{
		header("Location: " . $url);
		die();
	}
	
	public function FindClassFile( $sClass)
	{
		global $RapidEngineDefaultActionPath;
		
		foreach( $this->ClassPaths as $p)
		{
			$sFile = AddTrailingSlash( $p) . $sClass . ".php";
			if (file_exists( $sFile))
			{
				return $sFile;	
			}
		}
		
		$sFile = $RapidEngineDefaultActionPath . $sClass . ".php";
		if (file_exists( $sFile))
		{
			return $sFile;	
		}
		
		return '';
		
	}
	
	public function InitializeComponents()
	{
		if ($this->ComponentsInitialized)
		{
			return;
		}
		
		if (count( $this->Components))
		{
			foreach ($this->Components as $c)
			{
				if (strlen( $c['Path']) > 0)
				{
					$sPath = AddTrailingSlash( $c['Path']). AddTrailingSlash( $c['Name']) . $c['Name'] . ".php";
				}
				else
				{
					$sPath = AddTrailingSlash( dirname(__FILE__)) . AddTrailingSlash( 'components') . AddTrailingSlash( $c['Name']) . $c['Name'] . ".php";
				}
				if (file_exists( $sPath))
				{
					RDebug( $sPath);
					require_once( $sPath);
					
					$on = $c['Name'];
					
					$c['Object'] = new $on();
				}
				
			}
		}
		$this->ComponentsInitialized = true;
	}
	

}

global $RapidEngine;
$RapidEngine = new RapidEngineClass();

class RapidEngineAction
{
	public $Action = '';
	public $Class = '';  //should be a class descended from RapidEngineResponse
	public $Method = ''; //specific method to execute
	public $Component = "";  //if a plugin component, sor specifying paths
	
	function __construct( $action, $class, $comp = "")
	{
		$this->Action = $action;

		if (strpos( $class, '->') === false)
		{
			$this->Class = $class;			
		}
		else
		{
			$a = explode( '->', $class);
			$this->Class = $a[0];
			$this->Method = $a[1];
		}
		
		$this->Component = $comp;
	}
	
	
}



//add base action
$RapidEngine->DefineAction( "/", "DefaultRapidResponse");


function GetDB()
{
	global $RapidEngine;
	return $RapidEngine->Databases[0]["db"];
}

function GetApp()
{
	global $RapidEngine;
	return $RapidEngine->Application;
}

function GetEngine()
{
	global $RapidEngine;
	return $RapidEngine;
}

//util functions to be moved
function AddTrailingSlash( $s)
{
	if (substr( $s, -1) <> '/')
	{
		return $s . '/';	
	}	
	else
		return $s;
}


?>