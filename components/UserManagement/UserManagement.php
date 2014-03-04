<?php

class UserManagement extends RapidComponent
{
	function __construct()
	{
		global $RapidEngine;
		
		$this->Name = "UserManagement";
		RDebug( $this->Name . " init");
				
		//component requirements
		//$RapidEngine->UseComponent( "DatabaseSessions");
		
		$RapidEngine->AddClassPath( AddTrailingSlash( dirname(__FILE__)));
		$RapidEngine->AddTemplatePath( AddTrailingSlash( dirname(__FILE__)) . AddTrailingSlash( 'templates'));

		$this->DefineAction( "signup", "UserManagementSignup");
		$this->DefineAction( "login", "UserManagementLogin");
		
	}

}

$UM = new UserManagement();

function GetUM()
{
	return $UM;	
}



?>