<?php
/**
 * Created by PhpStorm.
 * Date: 2019-01-23
 * Time: 16:51
 */

namespace ttwms\controller\prd;

use Lite\Component\Paginate;
use Lite\Exception\BizException;
use ttwms\controller\BaseController;
use ttwms\CurrentUser;
use ttwms\model\PrdProduct;
use ttwms\model\PrdProductReviseLog;
use ttwms\ViewBase;
use function Lite\func\array_trim;
use function Temtop\t;

/**
 * @auth 产品管理/产品管理
 * Date: 2019-01-22
 * Time: 14:28
 */
class ProductController extends BaseController{
	/**
	 * @auth 列表|查看
	 * @param $search
	 * @param array $post
	 * @return array
	 */
	public function index($search, $post = []){
		$search = array_trim($search);
		$paginate = Paginate::instance();
		$select = PrdProduct::find()
			->whereOnSet('enterprise_id=?',$search['enterprise_id'])
			->whereOnSet('is_stop=?',$search['is_stop'])
			->whereOnSet('package_type=?',$search['package_type'])
			->whereOnSet('barcode_type=?',$search['barcode_type'])
			->whereOnSet('sku=?',$search['sku']);
		$select->order('create_time DESC');
		$list = $select->paginate($paginate);
		return [
			'paginate'         => $paginate,
			'list'             => $list,
			'search'           => $search
		];
	}
	
	/**
	 * @auth 添加
	 * @param $get
	 * @param array $post
	 * @return array
	 */
	public function add($get,$post){
		$info = new PrdProduct();
		if($post){
			$post['info'] = array_trim($post['info']);
			$data = $this->_filter_data_insert($post['info']);
			$info->setValues($data);
			$info->setValue('add_userid',CurrentUser::getUserId());
			$info->save();
			return $this->getCommonResult(true);
		}
		$url = 'prd/product/add';
		return array(
			'info' => $info,
			'url' => $url,
		);
	}
	
	
	/**
	 * @auth 添加
	 * @param $get
	 * @param array $post
	 * @return array
	 */
	public function edit($get,$post){
		$info = PrdProduct::find("id =?",$get['id']?:$post['id'])->oneOrFail();
		
		if($post){
			$post['info'] = array_trim($post['info']);
			$post['info']['id'] = $info->id;
			$data = $this->_filter_data_update($post['info']);
			
			if ($data['barcode_type'] != $info->barcode_type) {
				$info->barcode = PrdProduct::getBarcode($data['barcode_type'], $info);
			}
			
			//获取改变的值数组
			$changeData = PrdProduct::getSkuChangeData($info,$data,CurrentUser::getUserId());
			PrdProduct::transaction(function () use($info,$data,$changeData){
				$info->setValues($data);
				PrdProduct::createSkuLog($changeData);
				$info->save();
			});
			return $this->getCommonResult(true);
		}
		$url = 'prd/product/edit';
		
		$view = new ViewBase(array(
			"info"     => $info,
			'readonly'=> 'edit',
			'url' => $url,
		));
		$tpl = 'prd/product/add.php';
		return $view->render($tpl);
	}
	
	/**
	 * @auth 停用|启用
	 * @param $get
	 * @return array
	 */
	public function stop($get){
		$info = PrdProduct::find("id =?",$get['id'])->oneOrFail();
		$info->is_stop = $info->is_stop == PrdProduct::IS_STOP_NO ? PrdProduct::IS_STOP_YES : PrdProduct::IS_STOP_NO;
		$info->save();
		return $this->getCommonResult(true);
	}
	
	/**
	 * @auth 锁定|解锁
	 * @param $get
	 * @return array
	 */
	public function lock($get){
		$info = PrdProduct::find("id =?",$get['id'])->oneOrFail();
		$info->is_lock = $info->is_lock == PrdProduct::IS_LOCK_NO ? PrdProduct::IS_LOCK_YES : PrdProduct::IS_LOCK_NO;
		$info->save();
		return $this->getCommonResult(true);
	}
	
	
	/**
	 * @auth 查看
	 * @param $get
	 * @return array
	 */
	public function view($get){
		$info = PrdProduct::find("id =?",$get['id'])->oneOrFail();
		$url = 'prd/product/edit';
		
		$view = new ViewBase(array(
			"info"           => $info,
			'model'          => 'readonly',
			'url'          => $url,
		));
		$tpl = 'prd/product/add.php';
		return $view->render($tpl);
	}
	
	/**
	 * @auth 查看日志
	 * @param $get
	 * @return array
	 */
	public function Log($get){
		$paginate = Paginate::instance();
		$logList = PrdProductReviseLog::find("product_id=?",$get['id'])->order('create_time desc')->paginate($paginate);
		return array(
			"logList"    => $logList,
			'pagination' => $paginate,
			'param'      => $get,
		);
	}
	
	/**
	 * @auth 打印
	 * @param $get
	 * @return array
	 */
	public function ToPrint($get){
		//查出选中的barcode
		$products = PrdProduct::find("id in ?", $get['ids'])->order('id desc')->all();
		
		return array(
			"products" => $products,
			'param' => $get
		);
	}
	
	/**
	 * @auth 打印
	 * @param $get
	 * @return array
	 */
	public function DoPrint($get){
		$productInfo = PrdProduct::find('id=?', $get['product_id'])->one();
		$data =  array(
			'productInfo' => $productInfo,
			'param' => $get,
			'fnsku' => $productInfo->sku,
			'enterpriseCode' => $productInfo->enterprise->code,
		);
		if ($productInfo->barcode_type != PrdProduct::BARCODE_TYPE_OWH) {
			
			$view = new ViewBase($data);
			$tpl = 'prd/product/printfnsku.php';
			return $view->render($tpl);
		}
		return $data;
	}
	
	
	/**
	 * @auth 自定义条码打印
	 * @param $get
	 * @return array
	 */
	public function PrintSelfBarcode($get)
	{
		$content = $get['content'];
		$data = array(
			'content' => $content,
			'param' => $get,
		);
		if ($content) {
			$view = new ViewBase($data);
			$tpl = 'prd/product/barcodeprint.php';
			return $view->render($tpl);
		}
		
		return $data;
	}
	
	/**
	 * @param $data
	 * @return mixed
	 */
	protected function _filter_data_insert($data){
		if(!strlen(trim($data['name']))){
			throw new BizException(t("名称不能为空"));
		}
		if(!strlen(trim($data['ename']))){
			$data['ename'] = '';
		}
		if(!strlen(trim($data['sku']))){
			throw new BizException(t("sku不能为空"));
		} else{
			if(strlen(trim($data['sku']))>10){
				throw new BizException(t("sku长度不能大于10"));
			}
			$data['sku'] = strtoupper(trim($data['sku']));
			$product = PrdProduct::find("sku=?", trim($data['sku']))->one();
			if($product->id){
				throw new BizException(t("该sku已存在"));
			}
			$data['sku'] = trim($data['sku']);
		}
		if(!strlen($data['enterprise_id'])){
			throw new BizException(t("客户代码不能为空"));
		}
		if(!intval($data['length']) || !intval($data['width']) || !intval($data['height'])){
			throw new BizException(t("长、宽、高 不能为空"));
		}
		if(!intval($data['weight_rough'])){
			throw new BizException(t("毛重 不能为空"));
		}
		$data['status'] = PrdProduct::STATUS_ENABLED;
		return $data;
	}
	
	/**
	 * @param $data
	 * @return mixed
	 */
	protected function _filter_data_update($data){
		if(!strlen(trim($data['name']))){
			throw new BizException(t("名称不能为空"));
		}
		if(!strlen(trim($data['ename']))){
			$data['ename'] = '';
		}
		if(!strlen(trim($data['sku']))){
			throw new BizException(t("sku不能为空"));
		} else{
			$product = PrdProduct::find("sku=? and id<>?", trim($data['sku']), $data['id'])->one();
			if($product->id){
				throw new BizException(t("该sku已存在"));
			}
			$data['sku'] = trim($data['sku']);
		}
		if(!intval($data['length']) || !intval($data['width']) || !intval($data['height'])){
			throw new BizException(t("长、宽、高 不能为空"));
		}
		if(!intval($data['weight_rough'])){
			throw new BizException(t("毛重 不能为空"));
		}
		return $data;
	}
}