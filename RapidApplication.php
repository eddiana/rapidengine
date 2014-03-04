<?php 

class RapidApplication
{
	var $Initiated = false;
	
	//tags for parent templates
	var $Tags;

	/*
	 *  Called before dispatch.  Descendents should initialize common resources here
	 */
	function Init()
	{
		
	}
	
	function SetTag( $sTag, $sValue)
	{
		$this->Tags[$sTag] = $sValue;
	}
	
}


?>