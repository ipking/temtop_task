<?php
namespace ttwms\api;

use ttwms\model\PrdProduct;

class Item extends ApiAbstract
{
	const ERROR_SKU = 2001;
	const ERROR_SKU_EXISTS = 2002;
	const ERROR_SKU_NOT_EXISTS = 2003;
	const ERROR_UNITS = 2004;
	const ERROR_SKU_LWH = 2005;
	const ERROR_SKU_STOP = 2006;
	const ERROR_PARAM = 2007;

	public function create()
	{
		$customer = $this->user;
		
		$fields = array('sku', 'itemName', 'ename', 'unitPrice', 'units', 'weight', 'length', 'width', 'height', 'declare', 'referenceCode', 'description', 'clearance_code', 'is_battery','barcodeType');
		$param = $this->getParam($fields);
		$this->checkParam($param);
		//创建新产品
		$product = PrdProduct::find('sku=? and enterprise_id = ?', $param['sku'],$customer->id)->one();
		if ($product->is_stop == PrdProduct::IS_STOP_YES) {
			throw new \Exception("SKU已停用", self::ERROR_SKU_STOP);
		}
		
		//产品条码，如果自己仓库需要加上仓租用户编码
		if ($param['barcodeType'] == PrdProduct::BARCODE_TYPE_OWH) {
			$barcode =  $customer->code.'-'.$param['sku'];
		}else{
			$barcode =  $param['sku'];
		}


		if (!$product->id) {
			$newSku = array(
				'sku'               => $param['sku'],
				'name'              => $param['itemName'],
				'ename'             => $param['ename'],
				'clearance_price'   => $param['unitPrice'],
				'clearance_name'    => $param['declare'],
				'clearance_code'    => $param['clearance_code'],
				'width'             => $param['width'],
				'height'            => $param['height'],
				'length'            => $param['length'],
				'weight_rough'      => $param['weight'],
				'pcs_unit'          => $param['units'],
				'status'            => PrdProduct::STATUS_ENABLED,
				'status_on'         => PrdProduct::STATUS_ON_SHELVE,
				'status_time'       => date('Y-m-d H:i:s'),
				'add_time'          => date('Y-m-d H:i:s'),
				'note'              => '入库单创建',
				'point_description' => $param['description'],
				'enterprise_id'     => $customer->id,
				'is_battery'        => $param['is_battery']?PrdProduct::IS_BATTERY_YES:PrdProduct::IS_BATTERY_NO,
				'barcode_type'		=> $param['barcodeType'],
				'barcode'			=> $barcode
			);

			$skuObj = new PrdProduct($newSku);
			$skuObj->save();
			//TableLog::log_add('prd_product', $skuObj->id, 'add', 'API创建产品', '');
		}
		return $this->success($param['sku']);
	}

	private function checkParam($param)
	{
		if (!preg_match("/^[A-Za-z0-9-]+$/", $param['sku'])) {
			throw new \Exception("SKU格式错误", self::ERROR_SKU);
		}
		//$units = Q::ini("defined/interface/units/{$param['units']}");
//		if (!$units) {
//			throw new \Exception("计量单位错误", self::ERROR_UNITS);
//		}
		if (!$param['declare']) {
			throw new \Exception("报关名称不能为空", self::ERROR_PARAM);
		}
		if (!$param['unitPrice']) {
			throw new \Exception("报关价格不能为空", self::ERROR_PARAM);
		}

		$barcodeTypeList = PrdProduct::$barcode_type_map;
		if (!isset($barcodeTypeList[$param['barcodeType']])) {
			throw new \Exception("产品条码类型错误", self::ERROR_PARAM);
		}

		$preg = "/^[0-9]{1,4}(.[0-9]{0,2})?$/";
		if ($param['length'] >= 0.01 && $param['width'] >= 0.01 && $param['height'] >= 0.01) {
			if (!preg_match($preg, $param['length']) || !preg_match($preg, $param['width']) || !preg_match($preg, $param['height'])) {
				throw new \Exception("SKU长、宽或高错误", self::ERROR_SKU_LWH);
			}
		} else {
			throw new \Exception("SKU长、宽或高错误", self::ERROR_SKU_LWH);
		}
	}

	public function GetItemList()
	{
		$param = $this->getParam(array('lstSku'));
		$skuList = PrdProduct::find('sku in ? and is_stop=?', $param['lstSku'],PrdProduct::IS_STOP_NO)->all();
		$data = array();
		foreach ($skuList as $item) {
			$data[] = array(
				'categoryCode'     => '',
				'sku'              => $item->sku,
				'itemName'         => $item->ename,
				'shortDescription' => '',
				'unitPrice'        => $item->clearance_price,
				'units'            => $item->unit,
				'weight'           => $item->weight_rough,
				'length'           => $item->length,
				'width'            => $item->width,
				'height'           => $item->height,
				'declare'          => $item->clearance_name,
				'referenceCode'    => '',
				'imBarNo'          => '',
				'imBarCode'        => '',
				'description'      => $item->point_description
			);
		}
		return $this->success($data);
	}
}