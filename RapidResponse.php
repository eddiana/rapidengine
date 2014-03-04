<?php

class RapidEngineResponse
{
	//var $h = ''; //html response	
	var $Title = ''; //Page title, if any
	var $Template = ''; //template for response, if any
	var $SkipTemplate = false;
	
	//tags for parent templates
	var $Tags;
	var $Values;
	
	//whitelisted tasks
	var $Tasks;
	
	function __construct()
	{
		$this->Tags = array();
		$this->Values = array();
		$this->Tasks = array();
	}
	
	function GetResponse( $Application)
	{
			
	}
	
	function AddTask( $sFunc)
	{
		$this->Tasks[] = $sFunc;
	}
	
	function TaskAvailable( $sTask)
	{
		return in_array( $sTask, $this->Tasks);
	}
	
	//abstract away linking between response and RapidEngine object
	function GetTemplate( $sName)
	{
		global $RapidEngine;
		$tpl = new RapidTemplate( $sName, $RapidEngine->TemplatePaths, $RapidEngine->DeviceType);
		return $tpl;
	}
	
	function SetTag( $sTag, $sValue)
	{
		$this->Tags[] = $sTag;
		$this->Values[] = $sValue;	
	}
		
}

?>