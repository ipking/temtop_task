<?php

namespace ttwms\controller;

use Lite\Exception\RouterException;
use ttwms\ViewBase;


/**
 * @package temtopsys\supply\controller
 */
class PageController extends BaseController{
	public function __call($name, $arguments){
		$file = strtolower($name);
		if(!preg_match('/^[\w\s_]+$/', $file)){
			throw new RouterException('No page found');
		}
		$v = new ViewBase();
		return $v->render("page/$file.php");
	}
}