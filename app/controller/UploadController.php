<?php
namespace ttwms\controller;

use Lite\Component\Http;
use Lite\Component\MimeInfo;
use Lite\Core\Result;
use Lite\DB\Driver\DBAbstract;
use Lite\Exception\BizException;
use Temtop\component\OSSUpload;
use ttwms\CurrentUser;
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
class UploadController extends BaseController{
	const TYPE_IMAGE = 'image';
	const TYPE_FILE = 'file';
	const IMAGE_EXTENSIONS  = 'jpg,jpeg,png,gif';
	const MEDIA_EXTENSIONS  = 'avi,mp3,mp4,mov';
	const FILE_EXTENSIONS  = 'pdf,doc,docx,xls,xlsx,zip,rar,7z,txt,md,gif,png,jpg,jpeg';
	/**
	 * 发送头部CROS
	 * ttwmson_Controller_Upload constructor.
	 * @param $app
	 */
	public function __construct($app){
		if(preg_match('/\.ua\.com$/', $_SERVER['HTTP_ORIGIN'])){
			header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
			header('Access-Control-Allow-Credentials: true');
		}
		parent::__construct($app);
	}

	public function downloadImage($get, $post){
		$src = $get['src'];
		$ext = $get['ext'];
		$name = $get['name'];
		Http::headerDownloadFile("$name.$ext");
		echo file_get_contents($src);
		die;
	}

	/**
	 * 图片上传到OSS
	 * @param $get
	 * @param $post
	 * @return Result
	 */
	public function upload($get, $post){
		$type = $get['type'];

		switch($type){
			case 'file':
				return $this->file($get, $post);
			case 'media':
				return $this->media($get, $post);
			default:
				return $this->image($get, $post);
		}
	}

	/**
	 * 上传检查
	 * @param $config
	 * @param bool $break_on_error
	 * @return array
	 * @throws \Exception
	 * @throws \Exception
	 */
	private function preCheck($config = array(), $break_on_error = true){
		$config = array_merge(array(
			'path'           => '',                        //相对于public/upload/目录
			'filename'       => '',                    //文件名，如果为空，则随机名称
			'max_size'       => 1024*1024*10,            //最大文件大小(B)
			'extensions'     => '',                    //支持文件类型，为空
			'max_file_count' => 5                //单次最大上传文件个数
		), $config);

		$files = restructure_files($_FILES);
		$files = plain_items($files, null, 'original_key');

		$error_message_map = array(
			UPLOAD_ERR_INI_SIZE   => '文件大小超过系统设置',
			UPLOAD_ERR_FORM_SIZE  => '表单大小超过系统设置',
			UPLOAD_ERR_PARTIAL    => '文件发生部分损坏',
			UPLOAD_ERR_NO_FILE    => '文件丢失',
			UPLOAD_ERR_NO_TMP_DIR => '系统TMP目录缺失',
			UPLOAD_ERR_CANT_WRITE => '系统TMP文件写入失败',
			UPLOAD_ERR_EXTENSION  => '其他未知错误',
		);

		$errors = array();
		foreach($files as $k => $file){
			if($file['error']){
				$errors[$k] = $error_message_map[$file['error']] ?: $error_message_map[UPLOAD_ERR_EXTENSION];
			}
			if(!$file['tmp_name']){
				unset($files[$k]);
				continue;
			}
			if($file['size']>$config['max_size']){
				$errors[$k] = '文件大小超过上传设置的：' . format_size($config['max_size']);
			}
			if($config['extensions'] && !MimeInfo::checkByExtensions($config['extensions'], $file['type'])){
				$errors[$k] = '文件类型不符';
			}

			//handle error
			if($errors){
				if($break_on_error){
					throw new BizException(array_first($errors), null, array(
						'errors' => $errors,
						'config' => $config,
						'file'   => $file
					));
				} else{
					unset($files[$k]);
					continue;
				}
			}
		}
		if(empty($files)){
			throw new BizException('请选择要上传的文件', null, $errors);
		}
		$files = array_slice($files, 0, $config['max_file_count']);
		return $files;
	}
	
	private function afterSave($file_list,$type){
		DBAbstract::distinctQueryOff();
		$sale_amoeba_code = CurrentUser::getCustomerCode();
		//保存到"默认上传"目录
		$name = '默认上传';
		$default_folder = DiskFolder::find('is_default = ?',DiskFolder::IS_DEFAULT_YES)->one();
		if(!$default_folder->id){
			//可能遇到并发问题 创建多个默认目录
			$default_folder = new DiskFolder();
			$default_folder->name = $name;
			$default_folder->parent_id = 0;//根目录
			$default_folder->sale_amoeba_code = $sale_amoeba_code;
			$default_folder->is_default = DiskFolder::IS_DEFAULT_YES;
			$default_folder->save();
		}
		$list = [];
		foreach($file_list?:[] as $item){
			try{
				$file = new DiskFile();
				$file->folder_id = $default_folder->id;
				$file->sale_amoeba_code = $sale_amoeba_code;
				$file->url = $item['value'];
				$file->size = $item['size'];
				$tmp_arr = explode('.',$item['original_name']);
				$file->ext = array_pop($tmp_arr);
				$file->name = $item['original_name'];
				$file->save();
			}catch(\Exception $e){
				foreach($file_list?:[] as $k){
					switch($type){
						case OSSUpload::TYPE_IMAGE:
							OSSUpload::instance()->deleteImage($k['value']);
							break;
						case OSSUpload::TYPE_FILE:
							OSSUpload::instance()->deleteFile($k['value']);
							break;
						case OSSUpload::TYPE_MEDIA:
							OSSUpload::instance()->deleteMedia($k['value']);
							break;
					}
				}
				throw new BizException($e->getMessage());
			}
			
			$item['file_id'] = $file->id;
			$list[] = $item;
		}
		
		return $list;
	}

	public function progress(){
		session_start();
		$key = ini_get("session.upload_progress.prefix") . 'file';
		if(!empty($_SESSION[$key])){
			$current = $_SESSION[$key]["bytes_processed"];
			$total = $_SESSION[$key]["content_length"];
			echo $current<$total ? ceil($current/$total*100) : 100;
		} else{
			echo 100;
		}
	}
	
	/**
	 * 图片上传到OSS
	 * @param $get
	 * @param $post
	 * @return Result
	 */
	public function image($get, $post){
		$files = $this->preCheck(array(
			'extensions' => self::IMAGE_EXTENSIONS
		));
		
		//站点
		$site = $post['site'] ?: $_SERVER['HTTP_ORIGIN'];
		$path = $post['path'] ?: 'default' . '/' . date('Ym');
		
		$ret = array();
		foreach($files as $f){
			$path = OSSUpload::instance()->uploadImage($f, $path, $site);
			$ret[] = array(
				'thumb'         => OSSUpload::getImageThumbUrl($path),
				'src'           => OSSUpload::getImageUrl($path),
				'size'          => $f['size'],
				'value'         => $path,
				'original_key'  => $f['original_key'],
				'original_name' => $f['name']
			);
		}
		$ret = $this->afterSave($ret,OSSUpload::TYPE_IMAGE);
		$r = $ret[0];
		$r['more'] = array_slice($ret, 1);
		return new Result('上传成功', true, $r);
	}

	private function media($get, $post){
		$files = $this->preCheck(array(
			'extensions' => self::MEDIA_EXTENSIONS,
		));

		//站点
		$site = $post['site'] ?: $_SERVER['HTTP_ORIGIN'];
		$path = $post['path'] ?: 'default' . '/' . date('Ym');

		$ret = array();
		foreach($files as $f){
			$path = OSSUpload::instance()->uploadMedia($f, $path, $site);
			$ret[] = array(
				'src'           => OSSUpload::getMediaUrl($path),
				'size'          => $f['size'],
				'value'         => $path,
				'original_key'  => $f['original_key'],
				'original_name' => $f['name']
			);
		}
		$ret = $this->afterSave($ret,OSSUpload::TYPE_MEDIA);
		$r = $ret[0];
		$r['more'] = array_slice($ret, 1);
		return new Result('上传成功', true, $r);
	}

	private function file($get, $post){
		$files = $this->preCheck(array(
			'extensions' => self::FILE_EXTENSIONS,
		));

		//站点
		$site = $post['site'] ?: $_SERVER['HTTP_ORIGIN'];
		$path = $post['path'] ?: 'default' . '/' . date('Ym');

		$ret = array();
		foreach($files as $f){
			$path = OSSUpload::instance()->uploadFile($f, $path, $site);
			$ret[] = array(
				'src'           => OSSUpload::getFileUrl($path),
				'size'          => $f['size'],
				'value'         => $path,
				'original_key'  => $f['original_key'],
				'original_name' => $f['name']
			);
		}
		$ret = $this->afterSave($ret,OSSUpload::TYPE_FILE);
		$r = $ret[0];
		$r['more'] = array_slice($ret, 1);
		return new Result('上传成功', true, $r);
	}
	
}