<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/10/17
 * Time: 17:51
 */

namespace ttwms\controller;

use Exception;
use Lite\Core\Result;
use ttwms\CurrentUser;
use ttwms\model\PurchaseOrder;
use ttwms\model\SaleOrder;
use ttwms\model\SysConfig;
use ttwms\model\SysRole;
use ttwms\model\SysUser;
use ttwms\model\WhOutOrder;
use ttwms\ViewBase;
use function Lite\func\array_orderby_keys;

class IndexModController extends BaseController{
	const MOD_CONFIG_SAVE_KEY = 'index_mod_config';
	const MOD_CONFIG_SAVE_NAME_PREFIX = 'user_';
	
	const TIME_CONFIG_SAVE_KEY = 'index_time_config';
	const TIME_CONFIG_SAVE_NAME_PREFIX = 'user_';
	
	private static $mod_list = [
		'IndexMod/platformSaleStatic' => ['销售统计', [SysRole::TYPE_ADMIN]],
		'IndexMod/skuSaleStatic'      => ['SKU销量统计', [SysRole::TYPE_ADMIN]],
		'IndexMod/orderToHandle'      => ['待处理订单', [SysRole::TYPE_ADMIN, SysRole::TYPE_SALE], ['saleorder/SaleOrder/index', ['status'=>SaleOrder::STATUS_PENDING]]],
		'IndexMod/purchaseToHandle'   => ['待处理采购单', [SysRole::TYPE_ADMIN, SysRole::TYPE_PURCHASE], ['purchase/PurchaseOrder/index', ['status' => PurchaseOrder::STATUS_DRAFT]]],
		'IndexMod/outOrderToHandle'   => ['待处理出库单', [SysRole::TYPE_ADMIN, SysRole::TYPE_WAREHOUSE], ['warehouse/outOrder/index', ['status' => WhOutOrder::STATUS_DRAFT]]],
		'IndexMod/calendar'           => ['日历',[SysRole::TYPE_ADMIN, SysRole::TYPE_PURCHASE, SysRole::TYPE_SALE, SysRole::TYPE_WAREHOUSE, SysRole::TYPE_CUSTOMERSERVICE, SysRole::TYPE_FINANCE, SysRole::TYPE_CUSTOMIZE]],
		'IndexMod/clock'              => ['世界时钟', [SysRole::TYPE_ADMIN, SysRole::TYPE_PURCHASE, SysRole::TYPE_SALE, SysRole::TYPE_WAREHOUSE, SysRole::TYPE_CUSTOMERSERVICE, SysRole::TYPE_FINANCE, SysRole::TYPE_CUSTOMIZE]],
		'IndexMod/platformMessage'    => ['平台消息',[SysRole::TYPE_ADMIN, SysRole::TYPE_SALE, SysRole::TYPE_CUSTOMERSERVICE]],
	];
	
	public function __call($method, $arguments){
		$active_mod_list = self::getActiveModList();
		foreach($active_mod_list as $cgi => list($title)){
			if(strcasecmp($cgi, $method)){
				return self::renderMod($method, $title);
			}
		}
		throw new Exception('No support current operation');
	}
	
	public static function getClockSetting(){
		$name = self::TIME_CONFIG_SAVE_NAME_PREFIX.CurrentUser::getUserID();
		$config = SysConfig::getConfigVal(self::TIME_CONFIG_SAVE_KEY, $name);
		if(!$config['mode']){
			$config['mode'] = 'clock';
		}
		if(!$config['list']){
			$config['list'] = [
				['London', '+01:00'],
				['Germany', '+02:00'],
				['New York', '-04:00'],
				['Tokyo', '+09:00'],
			];
		}
		return $config;
	}
	
	public function clockSetting($get, $post){
		if($post){
			$list = [];
			$mode = $post['mode'];
			foreach($post['timezone'] as $k => $tz){
				$list[] = [$post['city'][$k], $tz];
			}
			if(!$list){
				return new Result('请选择城市或地区');
			}
			$config = [
				'mode' => $mode,
				'list' => $list
			];
			$name = self::TIME_CONFIG_SAVE_NAME_PREFIX.CurrentUser::getUserId();
			SysConfig::updateConfigVal(self::TIME_CONFIG_SAVE_KEY, $name, $config);
			return $this->getCommonResult(true);
		}
	}
	
	public static function getActiveModList(){
		return self::$mod_list;
	}
	
	private static function getModsAvailable(){
		$mods = [];
		$current_types = CurrentUser::getUser()->role_types;
		foreach(self::$mod_list as $cgi => list($title, $default_roles)){
			if(CurrentUser::getUser()->is_root == SysUser::IS_ROOT_YES || array_intersect($current_types, $default_roles)){
				$mods[$cgi] = self::$mod_list[$cgi];
			}
		}
		return $mods;
	}
	
	/**
	 * 获取当前用户启用模块列表
	 * @return array[[cgi=>active]]
	 */
	public static function getModsChecked(){
		$name = self::MOD_CONFIG_SAVE_NAME_PREFIX.CurrentUser::getUserID();
		$mods_config = SysConfig::getConfigVal(self::MOD_CONFIG_SAVE_KEY, $name);
		$current_types = CurrentUser::getUser()->role_types;
		$mods_available = self::getModsAvailable();
		
		$ms_checked = [];
		foreach($mods_config ?: [] as $cgi => $checked){
			if($checked){
				$ms_checked[] = $cgi;
			}
		}
		$mods_available = array_orderby_keys($mods_available, $ms_checked);
		$mods = [];
		foreach($mods_available as $cgi => list($title, $default_roles)){
			if($mods_config[$cgi] || (!isset($mods_config[$cgi]) && (CurrentUser::getUser()->is_root == SysUser::IS_ROOT_YES || array_intersect($current_types, $default_roles)))){
				$mods[$cgi] = $mods_available[$cgi];
			}
		}
		
		return $mods;
	}
	
	public function removeMod($get){
		$name = self::MOD_CONFIG_SAVE_NAME_PREFIX.CurrentUser::getUserID();
		$org_ms = SysConfig::getConfigVal(self::MOD_CONFIG_SAVE_KEY, $name);
		$org_ms[$get['cgi']] = false;
		SysConfig::updateConfigVal(self::MOD_CONFIG_SAVE_KEY, $name, $org_ms);
		return $this->getCommonResult(true);
	}
	
	public function modOptions($get, $post){
		$mods_available = self::getModsAvailable();
		
		if($post){
			$save_mods = $post['save_mods'];
			$ms = [];
			$mods_available = array_orderby_keys($mods_available, array_keys($save_mods));
			foreach($mods_available as $cgi => $m){
				$ms[$cgi] = !!$save_mods[$cgi];
			}
			$name = self::MOD_CONFIG_SAVE_NAME_PREFIX.CurrentUser::getUserID();
			SysConfig::updateConfigVal(self::MOD_CONFIG_SAVE_KEY, $name, $ms);
			return $this->getCommonResult(true);
		}
		
		$mods_checked = self::getModsChecked();
		$mods_available = array_orderby_keys($mods_available, array_keys($mods_checked));
		
		return [
			'mods_checked'   => $mods_checked,
			'mods_available' => $mods_available
		];
	}
	
	/**
	 * @param $method
	 * @param string $title
	 * @return string
	 * @throws \Exception
	 */
	private static function renderMod($method, $title = ''){
		$method = preg_replace('/.*?\:\:/', '', $method);
		$view = new ViewBase(['title' => $title]);
		$tpl = 'indexmod/'.strtolower($method).'.php';
		$html = $view->render($tpl, true, ViewBase::REQ_IFRAME);
		return new Result('succ', true, $html);
	}
}