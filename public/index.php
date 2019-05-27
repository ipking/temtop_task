<?php

use Lite\Core\Application;
use Lite\Core\Config;
use ttwms\business\WMSClientAuth;
use ttwms\model\LogApiRequestError;

include_once 'ttlib/Litephp/bootstrap.php';
include_once 'ttlib/temtop/autoload.php';
include_once 'TtSdk/src/autoload.inc.php';
include_once 'performance.inc.php';

include_once dirname(dirname(__DIR__)).'/ttwms/lib/autoload.inc.php';
if(!function_exists('dump')){
	function dump(){
		return call_user_func_array('Lite\func\dump', func_get_args());
	}
}
$path_info = $_SERVER['PATH_INFO']?:$_SERVER['REDIRECT_PATH_INFO'];
$path = trim($path_info, '/');
list($api,$class, $act) = explode('/', $path);
if($api != 'api'){
	Application::init('ttwms');
	die;
}

//api 接口
Application::init('ttwms', null, Application::MODE_CLI);

$code = $_GET['customerId'];
$token = $_GET['token'];
try{
	$class = ucfirst($class);
	$file = Config::get('app/path')."$api/$class.php";
	$class_full = Application::getNamespace()."\\$api\\$class";
	if(!is_file($file)){
		throw new Exception('api no found:'.$file);
	}
	
	include $file;
	
	if(!class_exists($class_full)){
		throw new Exception('class no found:'.$class_full);
	}
	T::log('get', json_encode($_GET),'api');
	$client_id = WMSClientAuth::decodeAuthorization($code,$token);
	
	$instance = new $class_full($client_id);
	if(!method_exists($instance, $act)){
		throw new Exception('api action no found:'.$act);
	}
	
	if($rsp = $instance->$act()){
		exit($rsp);
	}
	throw new Exception('server error');
} catch(\Exception $e){
	echo $result = json_encode( array(
		'errorCode' => $e->getCode()?:1,
		'errorMsg'  => $e->getMessage(),
		'data'      => array(
			'ack'          => 'N',
			'documentCode' => '',
			'errors'       => array(array(
				'code'     => $e->getCode()?:1,
				'codeNote' => $e->getMessage()
			))
		)
	));
	
	apiErrorLog($path,$e->getCode()?:1,$result,$e->getMessage());
}


function apiErrorLog($path,$error_code, $result, $error_message = '')
{
	$request_url = $_SERVER['REQUEST_URI'];
	$request_param = file_get_contents('php://input', 'r');
	$data = array(
		'enterprise_code' => $_GET['customerId'],
		'path'            => $path,
		'request_url'     => $request_url,
		'error_code'      => $error_code,
		'request_param'   => $request_param,
		'result'          => $result,
		'error_message'   => $error_message,
	);
	
	$error_log_model = new LogApiRequestError($data);
	$error_log_model->save();
}