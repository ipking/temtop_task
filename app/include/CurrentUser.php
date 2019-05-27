<?php

namespace ttwms;

use Lite\Component\AccessAdapter;
use Lite\Core\Router;
use Lite\Exception\BizException;
use ttwms\model\SysAccess;
use ttwms\model\SysRole;
use ttwms\model\SysRoleAuth;
use ttwms\model\SysUser;


session_start();

class CurrentUser extends AccessAdapter{
	const __CUSTOMER_SESSION_KEY__ = '__customer_session__';
	/**
	 * 权限白名单列表
	 * @var array
	 */
	private static $white_list = array(
		'index/login',
		'index/logout',
		'index/loginByToken'
	);
	
	public function __construct($config){
		parent::__construct($config);
	}
	
	protected static $instance = array();
	
	/**
	 * @param array $config
	 * @return array|\Lite\Component\AccessAdapter
	 */
	public static function instance(array $config = array()){
		if(!self::$instance){
			self::$instance = new static($config);
		}
		return self::$instance;
	}
	
	
	public static function setCaptcha($code){
		$_SESSION['captcha'] = $code;
	}
	
	/**
	 * get captcha
	 * @return mixed
	 */
	public static function getCaptcha(){
		return $_SESSION['captcha'];
	}
	
	/**
	 * @param $uri
	 * @return string
	 */
	public static function getUrlAccessFlag($uri){
		$auth_tail_flag = 'NOACCESS';
		list($ctrl, $action) = self::resolveUri($uri);
		
		if(!self::isAuthAction(Router::patchControllerFullName($ctrl), $action ?: Router::getDefaultAction())){
			return $auth_tail_flag;
		}
		return '';
	}
	
	/**
	 * 检测URI是否为白名单
	 * @param $controller
	 * @param $action
	 * @return bool
	 */
	private static function inWhiteList($controller, $action){
		foreach(self::$white_list as $uri){
			if(self::uriCheck($controller, $action, $uri)){
				return true;
			}
		}
		return false;
	}
	
	/**
	 * 解析URI
	 * @param $uri
	 * @return array
	 */
	private static function resolveUri($uri){
		$ctrl = $action = '';
		if(strpos($uri, '/') !== false){
			$tmp = explode('/', trim($uri, '/'));
			$action = array_pop($tmp);
			$ctrl = join('/', $tmp);
		} else if($uri == '*'){
			$ctrl = '*';
			$action = '*';
		} else if($uri){
			$ctrl = $uri;
		}
		return [$ctrl, $action];
	}
	
	public static function checkUriAccess($uri){
		list($c, $a) = self::resolveUri($uri);
		$a = $a ?: Router::getDefaultAction();
		return self::isAuthAction($c, $a);
	}
	
	/**
	 * @param $controller
	 * @param $action
	 * @return bool
	 * @throws \Lite\Exception\BizException
	 * @throws \Lite\Exception\Exception
	 */
	public static function checkAuth($controller, $action){
		if(!$controller || !$action){
			return true;
		}
		if(!self::inWhiteList($controller, $action) && !self::instance()->isLogin()){
			$url = Router::getUrl('index/login');
			$html = <<<EOT
					<!doctype html>
					<html lang="en">
					<head>
						<meta charset="UTF-8">
						<title>Document</title>
						<script>top.location.href="$url";</script>
					</head>
					<body>
						正在登录系统...
					</body>
					</html>
EOT;
			die($html);
		} else{
			$result = self::isAuthAction($controller, $action);
			if(!$result){
				throw new BizException('您没有当前页面访问权限');
			}
		}
		return true;
	}
	
	/**
	 * 检测uri是否匹配controller与action
	 * @param $controller
	 * @param $action
	 * @param $uri
	 * @return bool
	 */
	private static function uriCheck($controller, $action, $uri){
		$ctrl = Router::resolveNameFromController($controller ?: Router::$DEFAULT_CONTROLLER);
		$action = $action ?: Router::$DEFAULT_ACTION;
		
		list($c, $a) = self::resolveUri($uri);
		$c = $c ?: Router::resolveNameFromController(Router::$DEFAULT_CONTROLLER);
		$a = $a ?: Router::$DEFAULT_ACTION;
		if($c == '*'){
			return true;
		}
		
		if(strcasecmp($c, $ctrl) === 0 && ($a == '*' || strcasecmp($a, $action) === 0)){
			return true;
		}
		return false;
	}
	
	/**
	 * 判断当前用户是否对action具有权限
	 * 优先顺序：
	 * 1.全局通用白名单
	 * 2.自定义黑名单
	 * 3.自定义白名单
	 * 4.角色默认白名单
	 * @param $controller
	 * @param $action
	 * @return bool
	 */
	public static function isAuthAction($controller, $action){
		
		if(self::inWhiteList($controller, $action)){
			return true;
		}
		
		
		if(self::ignoreAction($controller,$action)){
			return true;
		}
		/**
		 * @var \ttwms\model\SysUser $u
		 */
		$u = self::getUser();
		if(in_array($u->role->type,[SysRole::TYPE_ADMIN])){
			return true;
		}
		
		//获取当前用户角色拥有的权限
		$role_list = SysRoleAuth::find('role_id = ? ',$u->role->id)->all();
		//需要控制的则需配置了权限才可访问
		foreach($role_list ?: [] as $role){
			if( self::uriCheck($controller, $action, $role['uri'])){
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * 所有需要控制的权限
	 * 不属于需要控制的URI登录态即可访问,如首页index/index
	 * @param $controller
	 * @param $action
	 * @return bool
	 * @throws \Lite\Exception\Exception
	 */
	public static function ignoreAction($controller,$action){
		$act_list = SysAccess::getAccessList();
		
		foreach($act_list as $uri => $item){
			if(self::uriCheck($controller, $action, $uri)){
				return false;
			}
		}
		return true;
	}
	
	public static function checkFieldAccess($get_class, $field){
		return true;
	}

	
	/**
	 * 以用户信息登录
	 * @param array|mixed $user_info
	 */
	public function login($user_info){
		session_start();
		$uid = $this->getIdFromUserInfo($user_info);
		$this->loginById($uid);
	}
	
	/**
	 * 用户退出登录
	 * @return bool
	 */
	public function logout()
	{
		return parent::logout();
	}
	
	
	public static function getUserToken(){
		$u = self::instance()->getLoginInfo();
		return $u['token'];
	}
	
	/**
	 * @return array(
	 *              'id' => '123645465445212111',
	 *              'name' => 'xxxx',
	 *              'role_list' => array()
	 * )
	 */
	public static function getUser(){
		$u = self::instance()->getLoginInfo();
		return $u;
	}
	
	public static function getUserId(){
		return self::instance()->getLoginUserId();
	}
	
	public static function getUserName(){
		
		$u = CurrentUser::instance()->getLoginInfo();
		return $u['name'];
	}
	
	public static function getUserRoles(){
		$u = self::instance()->getLoginInfo();
		return $u['role_list']?:[];
	}
	
	
	
	
	/**
	 * 从登陆信息中获取token
	 * @param $user
	 * @return mixed
	 */
	protected function getIdFromUserInfo($user){
		return $user['id'];
	}
	
	protected function getUserInfoFromId($uid){
		return SysUser::find('id = ?',$uid)->one();
	}
	

	
}
