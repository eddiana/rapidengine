<?php

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
}

class RapidEngineClass
{
	var $Options;
	var $Actions;
	var $Components;
	var $ComponentPaths;
	var $ClassPaths;
	var $TemplatePaths;
	var $DefaultTemplate = '';
	var $Databases;
	var $SessionHandler = 0;
	var $Application = 0; //user application object
	var $DeviceType = '';  //for templating
	
	var $ComponentsInitialized = false;
	
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
	
	function DefineAction( $action, $class)
	{

		$this->Actions[$action] = new RapidEngineAction( $action, $class);	
	}

	function AddClassPath( $sPath)
	{
		$this->ClassPaths[] = AddTrailingSlash( $sPath);
	}
	
	function AddComponentPath( $sPath)
	{
		$this->ComponentPaths[] = AddTrailingSlash( $sPath);
	}

	function AddTemplatePath( $sPath)
	{
		$this->TemplatePaths[] = AddTrailingSlash( $sPath);
	}


	function UseComponent( $sComponent, $sPath = '')
	{
		//also check for existing, pre req's etc.
		
		$a = array();
		$a['Name'] = $sComponent;
		$a['Object'] = 0;
		$a['Path'] = $sPath;
		$this->Components[] = $a;
	}

	function AddDatabase( $a)
	{
		//'dbms' (mysql), 'server', 'database', 'user', 'password'
		//'db' is the db object
		$this->Databases[] = $a;	
	
	}
	
	//initializes the first database connection
	function InitDatabase()
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

	function Dispatch( $action)
	{
		global $RapidEngineDefaultActionPath;
		
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
			
		if ( array_key_exists( $action, $this->Actions))
		{
			$c = $this->Actions[$action];
			RDebug( "found");
		}
		else
		{
			$c = $this->Actions['/'];	
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
				$h = $r->GetResponse( 0);
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
	
	function Redirect( $url)
	{
		header("Location: " . $url);
		die();
	}
	
	function FindClassFile( $sClass)
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
	
	function InitializeComponents()
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
	var $Action = '';
	var $Class;  //should be a class descended from RapidEngineResponse
	var $Component = "";  //if a plugin component, sor specifying paths
	
	function __construct( $action, $class, $comp = "")
	{
		$this->Action = $action;
		$this->Class = $class;
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