<?php

/*
	Base class for components

*/

class RapidComponent
{
	var $Name = '';
	
	function DefineAction( $action, $responseclass)
	{
		global $RapidEngine;
		
		$RapidEngine->DefineAction( $action, $responseclass, $this->Name);
		
	}

}


?>