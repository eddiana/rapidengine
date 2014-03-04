<?php

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