<?php

class UserManagementLogin extends RapidEngineResponse
{
	
	function GetResponse( $Application)
	{
		global $RapidEngine;
		
		
		$tpl = new RapidTemplate( "login", $RapidEngine->TemplatePaths);
		$this->h .= $tpl->Merge();
	}
}

?>