<?php
namespace ttwms\api;

use ttwms\model\Enterprise;


/**
 * @property Enterprise user
 */
abstract class ApiAbstract {
	const ERROR_AUTH = 1001;
	const ERROR_TOKEN = 1002;
	const ERROR_IDENTIFY = 1003;
	const ERROR_BUSINESS = 1004;//业务出错
	const ERROR_SYSTEM = 9999; //系统出错
	const ERROR_ILLEGAL_REQUEST = 9998;//非法请求
	const ERROR_DEFAULT = 2020;
	
	const DEFAULT_PAGE_SIZE = 20;
	const MAX_PAGE_SIZE = 100;
	
	protected $user;
	protected $data;
	
	public function __construct($client_id,$data = ''){
		$this->user = Enterprise::find('code=? and status=?', $client_id, Enterprise::STATUS_ENABLED)->one();
		$this->data = $data;
	}
	
	/**
	 * 过滤仅需要的字段
	 * @param array $fields
	 * @return array
	 */
	protected function getParam($fields)
	{
		$ret = array();
		if (empty($fields)) {
			return $ret;
		}
		$data = $this->getData();
		foreach ($fields as $field) {
			$ret[$field] = $data[$field];
		}
		return $ret;
	}

	final protected function getData(){
		return $this->data?json_decode($this->data,true):json_decode(file_get_contents('php://input', 'r'), true);
	}
	
	/**
	 * @param array | string | int $data
	 * @param bool $isDocCode
	 * @param string $message
	 * @return string
	 */
	protected function success($data, $isDocCode = true, $message = 'success')
	{
		if ($isDocCode) {
			$data = array(
				'ack'          => 'Y',
				'documentCode' => $data,
			);
		}
		
		$ret = array(
			'errorCode' => 0,
			'errorMsg'  => $message ?: 'success',
			'data'      => $data
		);
		
		return json_encode($ret);
	}
	
	/**
	 * 由于要和4px返回格式一致，这里做多一层封装
	 * @param string $message
	 * @param int $code
	 * @return string
	 */
	protected function error($message, $code = self::ERROR_DEFAULT)
	{
		
		$ret = array(
			'errorCode' => $code,
			'errorMsg'  => $message,
			'data'      => array(
				'ack'          => 'N',
				'documentCode' => '',
				'errors'       => array(array(
					'code'     => $code,
					'codeNote' => $message
				))
			)
		);
		return json_encode($ret);
	}
	
	/**
	 * 获取分页大小
	 * @param $page_size
	 * @param null $page_number
	 * @return array
	 */
	protected function getPageSize($page_size, $page_number=null){
		$page_size = intval($page_size);
		if ($page_size) {
			$p =  $page_size > self::MAX_PAGE_SIZE ? self::MAX_PAGE_SIZE : $page_size;
		}else{
			$p = self::DEFAULT_PAGE_SIZE;
		}
		
		$n = intval($page_number);
		$n = $n <= 0 ? 1 : $n;
		
		return array(($n - 1)*$p, $p);
	}
}