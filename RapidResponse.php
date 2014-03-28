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