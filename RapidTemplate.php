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

class RapidTemplate
{
	var $Loaded = false;
	var $TemplateFile = '';//file name
	var $Template = ''; //file contents
	var $DeviceType = '';  //none, 'tablet', 'phone', etc, for device variant templates
	var $Tags = '';
	var $Values = '';
	var $TemplatePaths;
	
	function __construct( $sFile, $aTemplatePaths, $sDeviceType = '')
	{
		$this->Tags = array();
		$this->Values = array();
		$this->TemplatePaths = $aTemplatePaths; //should be array
		$this->DeviceType = $sDeviceType;
		
		$this->Load( $sFile);
	}	
	
	function Load( $sFile)
	{
		$n = count( $this->TemplatePaths);
		for ($i = 1; $i <= $n; $i++)
		{
			if (strlen($this->DeviceType) > 0)
			{
				$sPath = $this->TemplatePaths[$i-1] . $sFile . '.' . $this->DeviceType . '.tpl';
				
				if (file_exists( $sPath))
				{
					$this->Template = file_get_contents( $sPath);
					$this->TemplateFile = $sFile;
					$this->Loaded = true;
					break;
				}
			}
			
			$sPath = $this->TemplatePaths[$i-1] . $sFile . '.tpl';
			if (file_exists( $sPath))
			{
				$this->Template = file_get_contents( $sPath);
				$this->TemplateFile = $sFile;
				$this->Loaded = true;
				break;
			}			
			
		}	
	}
	
	function SetTag( $sTag, $sValue)
	{
		$this->Tags[ $sTag] = $sValue;
	}
	
	function Merge()
	{
		$s = $this->Template;
		foreach ($this->Tags as $t => $v)
		{
			$p = '{{' . $t . '}}';
			$s = str_replace( $p, $v, $s);
		}
		return $s;
	}
	
}

?>