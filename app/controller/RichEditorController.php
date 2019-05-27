<?php
namespace ttwms\controller;

use Exception;
use Lite\Core\Config;
use Lite\Exception\BizException;
use Temtop\component\OSSUpload;
use function Lite\func\array_first;
use function Lite\func\format_size;
use function Lite\func\plain_items;
use function Lite\func\restructure_files;

/**
 * Created by PhpStorm.
 * User: sasumi
 * Date: 2014/11/5
 * Time: 12:25
 */
class RichEditorController extends BaseController{
	public function index($get, $post){
		switch(strtolower($get['action'])){
			case 'config':
				$this->config($get, $post);
				break;

			case 'uploadimage':
				$this->uploadImage($get, $post);
				break;

			default:
				throw new Exception('ACTION NO SUPPORT');
		}
	}

	/**
	 * 发送头部CROS
	 * ttwmson_Controller_Upload constructor.
	 * @internal param $app
	 */
	public function __construct(){
		if(preg_match('/\.temtop\.com$/', $_SERVER['HTTP_ORIGIN'])){
			header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
			header('Access-Control-Allow-Credentials: true');
		}
		parent::__construct();
	}
	
	/**
	 * 返回编辑器需要的配置
	 * 具体配置请修改app.yaml里的editor段
	 * @param $get
	 * @param $post
	 */
	public function config($get, $post){
		$data = Config::get('editor');
		$callback = $get['callback'];
		$data['imageAllowFiles'] = explode(',', $data['imageAllowFiles']);
		$data = json_encode($data);
		if($callback){
			if(preg_match('/^[\w_]+$/', $callback)){
				echo htmlspecialchars($callback) . '(' . $data . ')';
			} else{
				echo json_encode(array(
					'state' => 'callback参数不合法'
				));
			}
		} else{
			echo $data;
		}
		exit;
	}

	/**
	 * 编辑器上传文件功能
	 * @param $get
	 * @param $post
	 * @throws BizException
	 * @throws Exception
	 */
	public function uploadImage($get, $post){
		$files = $this->preCheck(array(
			'meta_list' => array('image/jpg', 'image/jpeg', 'image/pjpeg', 'image/png', 'image/gif'),
		));

		//站点
		$site = $post['site'] ?: $_SERVER['HTTP_ORIGIN'];
		if(!preg_match('/temtop\.com$/i', $site)){
			$site = 'knowledge.temtop.com';
		}
		$path = $post['path'] ?: 'default' . '/' . date('Ym');
		if(empty($site)){
			throw new Exception('site empty');
		}
		$ret = array();
		foreach($files as $f){
			$path = OSSUpload::instance()->uploadImage($f, $path, $site);
			$ret[] = array(
				'thumb' => OSSUpload::getImageThumbUrl($path),
				'src'   => OSSUpload::getImageUrl($path),
				'value' => $path,
			);
		}

		$one = $ret[0];

		$this->outputHtml(array(
			'state'    => 'SUCCESS',
			'url'      => $one['value'],
			'title'    => 'xxx',
			'original' => 'asdfasdf',
			'type'     => '.' . array_pop(explode('.', $one['value'])),
			'size'     => 20,
		), $get['callback']);
	}

	/**
	 * 上传检查
	 * @param array $config
	 * @param bool $break_on_error
	 * @return array
	 * @throws BizException
	 */
	private function preCheck($config = array(), $break_on_error = true){
		$config = array_merge(array(
			'path'           => '',                        //相对于public/upload/目录
			'filename'       => '',                    //文件名，如果为空，则随机名称
			'max_size'       => 1024*1024*10,            //最大文件大小 10MB
			'meta_list'      => '',                    //支持文件类型，为空
			'max_file_count' => 5                //单次最大上传文件个数
		), $config);

		$files = restructure_files($_FILES);
		$files = plain_items($files);

		$error_message_map = array(
			UPLOAD_ERR_INI_SIZE   => '文件大小超过系统设置',
			UPLOAD_ERR_FORM_SIZE  => '表单大小超过系统设置',
			UPLOAD_ERR_PARTIAL    => '文件发生部分损坏',
			UPLOAD_ERR_NO_FILE    => '文件丢失',
			UPLOAD_ERR_NO_TMP_DIR => '系统TMP目录缺失',
			UPLOAD_ERR_CANT_WRITE => '系统TMP文件写入失败',
			UPLOAD_ERR_EXTENSION  => '其他未知错误',
		);

		foreach($files as $k => $file){
			$err = array();
			if($file['error']){
				$err[$k] = $error_message_map[$file['error']] ?: $error_message_map[UPLOAD_ERR_EXTENSION];
			}
			if(!$file['tmp_name']){
				unset($files[$k]);
				continue;
			}
			if($file['size']>$config['max_size']){
				$err[$k] = '文件大小超过上传设置的：' . format_size($config['max_size']);
			}
			if($config['meta_list'] && !in_array($file['type'], $config['meta_list'])){
				$err[$k] = '文件类型不符';
			}

			//handle error
			if($err){
				if($break_on_error){
					throw new BizException(array_first($err), null, $err);
				} else{
					unset($files[$k]);
					continue;
				}
			}
		}
		if(empty($files)){
			throw new BizException('请选择要上传的文件');
		}
		$files = array_slice($files, 0, $config['max_file_count']);
		return $files;
	}

	/**
	 * 输出结果
	 * @param $data
	 * @param null $callback
	 */
	private function outputHtml($data, $callback = null){
		$string = json_encode($data);
		if($callback){
			if(preg_match('/^[\w_]+$/', $callback)){
				$string = htmlspecialchars($callback) . '(' . $string . ')';
			} else{
				$string = json_encode(array(
					'state' => 'callback参数不合法'
				));
			}
		}

		if(!$callback || stripos($_SERVER['HTTP_X_REQUESTED_WITH'], 'flash')){
			die($string);
		}

		$html = <<<EOT
<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Document</title>
	<script>document.domain = "temtop.com"</script>
</head>
<body>
	$string
</body>
</html>
EOT;
		echo $html;
		exit;
	}
}