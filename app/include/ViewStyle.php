<?php

namespace ttwms;



use Lite\Core\Config;

class ViewStyle{
	/**
	 * @param array $lessFile
	 * @param string $style_color
	 * @return string
	 * @throws \Exception
	 */
	public static  function getStyle($lessFile,$style_color){
		if(!$lessFile){
			return "";
		}
		$styleList = Config::get("style");
		$styleConfig =$styleList[$style_color];
		if(!$styleConfig){
			return "";
		}
		$less = new \lessc();
		$less->setVariables($styleConfig);
		$style_css ="";
		
		foreach($lessFile as $file){
			$inputString = @file_get_contents($file);
			$style_css .= $less->compile($inputString);
		}
		return "<style>{$style_css}</style>";
	}
}