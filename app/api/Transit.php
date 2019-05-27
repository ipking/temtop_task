<?php
namespace ttwms\api;

use Exception;
use ttwms\business\CurrentWMS;
use ttwms\business\InventoryWrap;
use ttwms\model\PrdProduct;
use ttwms\model\TransitDeliveryOrder;
use ttwms\model\TransitDeliveryOrderItem;

class Transit extends ApiAbstract
{
	
	
	const ERROR_NO_ORDERCODE = 2001;
	const ERROR_NO_COUNTRY = 2002;
	const ERROR_USER = 2003;
	const ERROR_EMAIL = 2004;
	const ERROR_STATE = 2005;
	const ERROR_CITY = 2006;
	
	const ERROR_STREET = 2007;
	const ERROR_DOORPLATE = 2008;
	const ERROR_PHONE = 2009;
	const ERROR_COMPANY = 2010;
	const ERROR_POSTALCODE = 2011;
	const ERROR_ORDER_EXIST = 2012;
	const ERROR_ORDER_NULL = 2013;
	const ERROR_INVENTORY = 2015;
	const ERROR_SOURCE = 2016;
	const ERROR_SHIPPING_METHOD = 2017;
	const ERROR_QUANTITY = 2018;
	const ERROR_ORDER_CANCEL = 2019;
	const ERROR_SYSTEM = 2020;
	const ERROR_SERVICE = 2021;
	const ERROR_PARAM = 2022;
	const ERROR_DISTRICT_COUNTRY = 2022; //超出配送范围
	const ERROR_OVER_WEIGHT = 2023; //超重
	const ERROR_OVER_HEIGHT = 2024; //超高
	const ERROR_OVER_WIDTH = 2025; //超宽
	const ERROR_OVER_LENGTH = 2026; //超长
	const ERROR_METHOD_COUNTRY = 2017; // 物流方式对应的国家出错;
	const ERROR_METHOD_REPLY = 2018; // 物流方式对应的国家出错;
	const ERROR_NO_EXIST_FINANCE = 2019; // 未配置基础运费;
	const ERROR_FINANCE = 2020; // 未配置基础运费;
	const ERROR_CREDIT_LINE = 2021;//额度受限
	const ERROR_ORDER_NO_OVER_LENGTH = 2022;//订单号超过限定长度
	const ERROR_COUNTRY = 2023;
	
	const ERROR_ORDER = 2024;
	const ERROR_ORDER_EXISTS = 2025;
	const ERROR_ORDER_DETAIL = 2026;
	const ERROR_SKU = 2027;
	const ERROR_ORDER_SEND_OUT = 2028;
	/**
	 * 创建中转出库单
	 * @return string
	 */
	public function CreateOrder()
	{
		$fields = array(
			'warehouseCode',
			'firstShipping',
			'trackingNo',
			'containerType',
			'containerNo',
			'sealNo',
			'referenceCode',
			'description',
			'lstItem',
			'consignee',
			'whType',
			'whCode',
			'shipmentCode',
		);
		$param = $this->getParam($fields);
		if($param['whType'] == TransitDeliveryOrder::TARGET_WH_TYPE_FBA){
			$this->handleAddress($param);
		}
		$address = $param['consignee'];
		$customer = $this->user;
	
		if (empty($param['referenceCode'])) {
			throw new Exception("不存在订单号", self::ERROR_ORDER);
		}
		if (empty($param['lstItem'])) {
			throw new Exception("不存在订单明细", self::ERROR_ORDER_DETAIL);
		}
		if (empty($param['shipmentCode'])) {
			throw new Exception("不存在箱唛编码", self::ERROR_ORDER);
		}
		$orderExist = TransitDeliveryOrder::find('enterprise_order_no = ? and status<>? and enterprise_id = ?', $param['referenceCode'], TransitDeliveryOrder::STATUS_CANCELED, $customer->id)->count();
		
		if ($orderExist) {
			throw new Exception("出库单已存在", self::ERROR_ORDER_EXISTS);
		}

		//判断是否有不存在或者已停用的sku
		$skuNameList = array_column($param['lstItem'], 'sku');
		$productList = PrdProduct::find('is_stop=? and sku in ? and enterprise_id = ?',PrdProduct::IS_STOP_NO, $skuNameList,$customer->id)->all(true);
		$existSkuNameList = array_column($productList, 'sku');

		$skuDiff = array_diff($skuNameList, $existSkuNameList);
		if (!empty($skuDiff)) {
			$skuDiff = array_unique($skuDiff);
			throw new Exception("SKU(" . implode(',', $skuDiff) . ")不存在或已停用", self::ERROR_SKU);
		}

		$skuArr = array();
		$TDItem = array();
		$productList = \Temtop\array_group($productList, 'sku', true);
		foreach ($param['lstItem'] as $key => $item) {
			if($param['whType'] == TransitDeliveryOrder::TARGET_WH_TYPE_4PX and !$item['boxBarcode']){
				throw new Exception("箱号为".$item['boxNumber']."箱条码不能为空", self::ERROR_SKU);
			}
			$skuArr[$item['sku']] += $item['quantity'];
			//仓库出库单明细
			$TDItem[] = array(
				'box_no'      => $item['boxNumber'],
				'box_barcode' => $item['boxBarcode'],
				'product_id'  => $productList[$item['sku']]['id'],
				'quantity'    => $item['quantity'],
			);
		}
		
		$order = new TransitDeliveryOrder();
		
		//入库单主体信息
		$order->enterprise_order_no = $param['referenceCode'];
		$order->enterprise_id = $customer->id;
		$order->note = $param['description'];
		$order->first_shipping_type = $param['firstShipping'];
		$order->tracking_no = $param['trackingNo'];
		$order->container_type = $param['containerType'];
		$order->container_no = $param['containerNo'];
		$order->seal_no = $param['sealNo'];
		$order->target_wh_type = $param['whType'];
		$order->target_wh_code = $param['whCode'];
		$order->shipment_code = $param['shipmentCode'];
		
		$order->user_name = $address['fullName'];
		$order->user_country = $address['countryCode'];
		$order->user_city = $address['city'];
		$order->user_street = $address['street'];
		$order->user_house_no = $address['doorplate'];
		$order->user_postcode = $address['postalCode'];
		$order->user_email = $address['email'];
		$order->user_tel = $address['phone'];
		$order->user_company = $address['company'];
		$order->user_state = $address['state'];
		
		TransitDeliveryOrder::transaction(function()use($TDItem,&$order){
			
			$order->save();
			$order->wms_no = "TO" . CurrentWMS::getWmsLogogramCode() . '-' . date("ymd") . '-' . str_pad($order->id, 5, "0", STR_PAD_LEFT);
			$order->save();
			$orderId = $order->id;
			
			//创建出库单明细
			foreach ($TDItem as $k => $item) {
				$receipt_item_model = new TransitDeliveryOrderItem($item);
				$receipt_item_model->transit_delivery_order_id = $orderId;
				$receipt_item_model->save();
				//冻结库存
				InventoryWrap::preFreezeInventory($receipt_item_model->product_id, $receipt_item_model->quantity, $receipt_item_model);
			}
			
		});
		
		return $this->success($order->wms_no);
	}
	
	
	/**
	 * 更新中转出库单
	 * @return string
	 */
	public function UpdateOrder()
	{
		$fields = array(
			'warehouseCode',
			'firstShipping',
			'trackingNo',
			'containerType',
			'containerNo',
			'sealNo',
			'referenceCode',
			'description',
			'lstItem',
			'consignee',
			'whType',
			'whCode',
			'shipmentCode',
		);
		$param = $this->getParam($fields);
		if($param['whType'] == TransitDeliveryOrder::TARGET_WH_TYPE_FBA){
			$this->handleAddress($param);
		}
		$address = $param['consignee'];
		$customer = $this->user;
		
		if (empty($param['referenceCode'])) {
			throw new Exception("不存在订单号", self::ERROR_ORDER);
		}
		if (empty($param['lstItem'])) {
			throw new Exception("不存在订单明细", self::ERROR_ORDER_DETAIL);
		}
		if (empty($param['shipmentCode'])) {
			throw new Exception("不存在箱唛编码", self::ERROR_ORDER);
		}
		
		$order = TransitDeliveryOrder::find('enterprise_order_no = ? and status<>? and enterprise_id = ?', $param['referenceCode'], TransitDeliveryOrder::STATUS_CANCELED, $customer->id)->one();
		
		if (!$order->id) {
			throw new Exception("出库单不存在", self::ERROR_ORDER_EXISTS);
		}
		if($order->status !=TransitDeliveryOrder::STATUS_NEW){
			throw new Exception("该出库单不允许修改", self::ERROR_ORDER);
		}
		
		//判断是否有不存在或者已停用的sku
		$skuNameList = array_column($param['lstItem'], 'sku');
		$productList = PrdProduct::find('is_stop=? and sku in ? and enterprise_id = ?',PrdProduct::IS_STOP_NO, $skuNameList,$customer->id)->all(true);
		$existSkuNameList = array_column($productList, 'sku');
		
		$skuDiff = array_diff($skuNameList, $existSkuNameList);
		if (!empty($skuDiff)) {
			$skuDiff = array_unique($skuDiff);
			throw new Exception("SKU(" . implode(',', $skuDiff) . ")不存在或已停用", self::ERROR_SKU);
		}
		
		$skuArr = array();
		$TDItem = array();
		$productList = \Temtop\array_group($productList, 'sku', true);
		foreach ($param['lstItem'] as $key => $item) {
			if($param['whType'] == TransitDeliveryOrder::TARGET_WH_TYPE_4PX and !$item['boxBarcode']){
				throw new Exception("箱号为".$item['boxNumber']."箱条码不能为空", self::ERROR_SKU);
			}
			$skuArr[$item['sku']] += $item['quantity'];
			//仓库出库单明细
			$TDItem[] = array(
				'box_no'      => $item['boxNumber'],
				'box_barcode' => $item['boxBarcode'],
				'product_id'  => $productList[$item['sku']]['id'],
				'quantity'    => $item['quantity'],
			);
		}
		
		$order->note = $param['description'];
		$order->first_shipping_type = $param['firstShipping'];
		$order->tracking_no = $param['trackingNo'];
		$order->container_type = $param['containerType'];
		$order->container_no = $param['containerNo'];
		$order->seal_no = $param['sealNo'];
		$order->target_wh_type = $param['whType'];
		$order->target_wh_code = $param['whCode'];
		$order->shipment_code = $param['shipmentCode'];
		
		$order->user_name = $address['fullName'];
		$order->user_country = $address['countryCode'];
		$order->user_city = $address['city'];
		$order->user_street = $address['street'];
		$order->user_house_no = $address['doorplate'];
		$order->user_postcode = $address['postalCode'];
		$order->user_email = $address['email'];
		$order->user_tel = $address['phone'];
		$order->user_company = $address['company'];
		$order->user_state = $address['state'];
		
		TransitDeliveryOrder::transaction(function()use($TDItem,&$order){
			
			$order->save();
			$orderId = $order->id;
			//释放库存
			InventoryWrap::cancelTransitDeliveryOrder($order);
			//删除原出库单明细
			TransitDeliveryOrderItem::deleteWhere(0,'transit_delivery_order_id = ?',$orderId);
			
			//创建出库单明细
			foreach ($TDItem as $k => $item) {
				$receipt_item_model = new TransitDeliveryOrderItem($item);
				$receipt_item_model->transit_delivery_order_id = $orderId;
				$receipt_item_model->save();
				//冻结库存
				InventoryWrap::preFreezeInventory($receipt_item_model->product_id, $receipt_item_model->quantity, $receipt_item_model);
			}
			
		});
		
		return $this->success($order->wms_no);
	}
	

	/**
	 *
	 * 出库单状态:
	 * @return string
	 * @throws Exception
	 */
	public function GetTransitOrder()
	{
		$fields = array(
			'transitCode'
		);
		$param = $this->getParam($fields);
		$order = TransitDeliveryOrder::find('(wms_no=? or enterprise_order_no=?) and enterprise_id = ?', $param['transitCode'], $param['transitCode'],$this->user->id)->one();
		if (!$order->id) {
			throw new Exception('出库单不存在', self::ERROR_ORDER_EXISTS);
		}
	
		$status = array(
			TransitDeliveryOrder::STATUS_NEW       => 'O',
			TransitDeliveryOrder::STATUS_SEND_OUT  => 'S',
			TransitDeliveryOrder::STATUS_CANCELED  => 'X'
		);
		
		$item_list = TransitDeliveryOrderItem::find('transit_delivery_order_id = ?',$order->id)->all();
		$detail = array();
		foreach ($item_list as $item) {
			$prd = PrdProduct::find('id = ?', $item->product_id)->one();
			$detail[] = array(
				"sku"          => $prd->sku,
				"quantity"     => $item->quantity,
				"sendQuantity" => $item->send_quantity ?: 0,
				"boxNumber"    => $item->box_no,
				"boxBarcode"   => $item->box_barcode
			);
		}
		$return = array(
			"warehouseCode" => CurrentWMS::getWmsCode(),
			"createDate"    => $order->create_time,
			"status"        => $status[$order->status],
			'backReason'    => $order->wms_cancel_note,
			'confirmDate'   => $order->send_out_time?:'',
			"shippingCode"  => '',
			"lstItem"       => $detail
		);
		
		return $this->success($return, false);
	}
	/**
	 *
	 * 取消出库单:
	 * @return string
	 * @throws Exception
	 */
	public function cancel()
	{
		$fields = array(
			'transitCode',
			'cancelNote',
		);
		$param = $this->getParam($fields);
		$order = TransitDeliveryOrder::find('(wms_no=? or enterprise_order_no=?) and enterprise_id = ?', $param['transitCode'], $param['transitCode'],$this->user->id)->one();
		if (!$order->id) {
			throw new Exception('出库单不存在', self::ERROR_ORDER_EXISTS);
		}
		
		if($order->status == TransitDeliveryOrder::STATUS_SEND_OUT){
			throw new Exception('出库单已发出', self::ERROR_ORDER_SEND_OUT);
		}
		
		if($order->status == TransitDeliveryOrder::STATUS_CANCELED){
			return $this->success( false);
		}
		
		if($order->status == TransitDeliveryOrder::STATUS_NEW){
			TransitDeliveryOrder::transaction(function()use($order,$param){
				$order->status = TransitDeliveryOrder::STATUS_CANCELED;
				$order->enterprise_cancel_note = $param['cancelNote'];
				$order->save();
				//解冻库存 并 回滚
				InventoryWrap::cancelTransitDeliveryOrder($order);
			});
			return $this->success( false);
		}
		return $this->success( false);
	}
	
	
	/**
	 * 创建订单时校验地址信息
	 * 返回校验后的地址
	 * @param $param
	 * @return array
	 * @throws Exception
	 */
	private function handleAddress($param)
	{
		$address = $param['consignee'];
		
		$address = array_map(function ($val) {
			return trim($val);
		}, $address);
		//国家CODE
		if (strlen($address['countryCode']) > 2) {
			throw new Exception('收件人国家错误', self::ERROR_COUNTRY);
		}
		//城市可以为空，如果不为空只能包含英文字母，标点符号以及空格，城市的长度不能大于60位
		if (strlen($address['city']) > 60) {
			throw new Exception('收件人城市错误', self::ERROR_CITY);
		}
		//公司的长度不能大于64位
		if (strlen($address['company']) && strlen($address['company']) > 64) {
			throw new Exception("收件人公司错误", self::ERROR_COMPANY);
		}
		return $address;
	}
}