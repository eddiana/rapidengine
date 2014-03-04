<?php

class UserManagementSignup extends RapidEngineResponse
{
	
	function GetResponse( $Application)
	{
		global $RapidEngine;
		
		
		$tpl = new RapidTemplate( "signupform", $RapidEngine->TemplatePaths);
		
		
		$this->h = "Signin Up.  Boo-yeah!<br><br>";
		
		$this->h .= $tpl->Merge();
	}
}

?>