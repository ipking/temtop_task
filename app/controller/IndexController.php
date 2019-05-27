<?php
namespace ttwms\controller;

use Lite\Core\Config;
use Lite\Core\Result;
use Lite\Core\Router;
use Lite\Exception\BizException;
use ttwms\CurrentUser;
use ttwms\model\PurchaseReceipt;
use ttwms\model\SysUser;
use function Temtop\array_trim;
use ttwms\model\TransitDeliveryOrder;


/**
 * Class IndexController
 * @package ttwms\controller
 */
class IndexController extends BaseController{
	/**
	 * @param $search
	 * @param string $post
	 * @return array
	 * @throws \Lite\Exception\Exception
	 */
	public function index($search,$post=''){
		$status_map = array(
			PurchaseReceipt::STATUS_PUBLISHED => '预到入库',
			PurchaseReceipt::STATUS_ARRIVAL => '待收货',
			PurchaseReceipt::STATUS_RECEIVED => '待质检',
			PurchaseReceipt::STATUS_CHECKED => '待上架',
		);
		
		$p_list = PurchaseReceipt::find('status in ?',array_keys($status_map))->map('id','status');
		$purchase_receipt = [];
		foreach($p_list as $status){
			$purchase_receipt[$status] += 1;
		}
		$t_list = TransitDeliveryOrder::find('status = ?',TransitDeliveryOrder::STATUS_NEW)->map('id','status');
		$transit_delivery_order = [];
		foreach($t_list as $status){
			$transit_delivery_order[$status] += 1;
		}
		
		return [
			'todo_receipt'=>$purchase_receipt,
			'todo_transit'=>$transit_delivery_order,
			'status_map'=>$status_map,
		];
	}
	
	/**
	 * @param $kw
	 * @return array
	 * @throws \Lite\Exception\Exception
	 */
	private static function getLocalMenus($kw){
		/**
		 * 校验权限，转换URI
		 * @param $uri
		 * @return string
		 */
		$convert_uri = function($uri){
			if($uri && !CurrentUser::getUrlAccessFlag($uri)){
				return Router::getUrl($uri);
			}
			return null;
		};
		$cfg = Config::get('nav');
		$ret = [];
		foreach($cfg as list($root_title, $uri, $subs, $abs)){
			if($uri && $convert_uri($uri)){
				$ret[] = [
					'title' => $root_title,
					'abs'   => $abs ?: '',
					'url'   => $convert_uri($uri)
				];
			}
			$last_sub_title = '';
			foreach($subs as list($sub_title, $sub_uri, $ss, $sub_abs)){
				if($sub_uri && $convert_uri($sub_uri)){
					$ret[] = [
						'title' => $root_title.' / '.($last_sub_title ? $last_sub_title.' / ' : '').$sub_title,
						'abs'   => $sub_abs ?: '',
						'url'   => $convert_uri($sub_uri)
					];
				}
				$last_sub_title = $sub_title;
			}
		}
		if($kw){
			$ret = array_filter($ret, function($item) use ($kw){
				if(stripos($item['title'], $kw) !== false || stripos($item['abs'], $kw) !== false){
					return true;
				}
				return false;
			}, ARRAY_FILTER_USE_BOTH);
		}
		foreach($ret as $k=>$item){
			$ret[$k]['title'] = '<i></i>'.$ret[$k]['title'];
		}
		return $ret;
	}
	

	
	/**
	 * @param $get
	 * @param $post
	 * @return string
	 * @throws \Exception
	 */
	public function login($get, $post){
		if($post){
			$post = array_trim($post);
			$user = SysUser::find("account =? and password=? and status=?",$post['username'],SysUser::getPassWord($post['password']),SysUser::STATUS_ENABLED)->one();
			if(!$user->id){
				throw new BizException("用户名或密码错误或账户已禁用");
			}
			CurrentUser::instance()->login($user);
			return new Result('登录成功', true, null, Router::getUrl(''));
		}
	}
	
	/**
	 * 退出登录
	 * @return Result
	 */
	public function logout(){
		CurrentUser::instance()->logout();
		return new Result('成功退出登录', true);
	}
	
	public function loginByToken($get,$post){
		$username =$post['username'];
		$token = $post['token'];
		$user = SysUser::find("account=?", $username)->oneOrFail();
	
		if($user && md5($user->id.$user->password) == $token ){
			CurrentUser::instance()->login($user);
			return new Result('登录成功', true, null, Router::getUrl(''));
		}
	}
	
}