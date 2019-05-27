<?php
namespace ttwms\api;

use Exception;
use ttwms\business\CurrentWMS;
use ttwms\model\PrdProduct;
use ttwms\model\PurchaseReceipt;
use ttwms\model\PurchaseReceiptBox;
use ttwms\model\PurchaseReceiptItem;

class Receive extends ApiAbstract
{
	const ERROR_ORDER = 2001;
	const ERROR_ORDER_EXISTS = 2002;
	const ERROR_ORDER_DETAIL = 2003;
	const ERROR_SKU = 2004;
	const ERROR_INSURE = 2005;
	const ERROR_DUTY_PAY = 2006;
	const ERROR_CUSTOMS_TYPE = 2007;
	const ERROR_PACKAGE_TYPE = 2008;
	const ERROR_GOODS_TYPE = 2009;
	const ERROR_ORDER_NOT_EXISTS = 2010;
	const ERROR_CUSTOMS = 2011;

	const VERIFY_YES = 1;
	const VERIFY_NO = 0;

	/**
	 * 创建头程单
	 * @return string
	 */
	public function CreateOrder()
	{
		$fields = array(
			'warehouseCode',
			'carrierCode',
			'insureType',
			'dutypayType',
			'firstShipping',
			'trackingNo',
			'containerType',
			'containerNo',
			'sealNo',
			'is_transit',
			'referenceCode',
			'description',
			'customsType',
			'lstItem',
			'arrival_date',
			'verify'
		);
		$param = $this->getParam($fields);
		
		$customer = $this->user;
	
		if (empty($param['referenceCode'])) {
			throw new Exception("不存在订单号", self::ERROR_ORDER);
		}
		if (empty($param['lstItem'])) {
			throw new Exception("不存在订单明细", self::ERROR_ORDER_DETAIL);
		}
		
		if (!PurchaseReceipt::$tax_type_map[$param['customsType']]) {
			throw new Exception("报关类型错误", self::ERROR_CUSTOMS);
		}
		if (!PurchaseReceipt::$insure_type_map[$param['insureType']]) {
			throw new Exception("保险类型错误", self::ERROR_INSURE);
		}
		if (!PurchaseReceipt::$duty_pay_type_map[$param['dutypayType']]) {
			throw new Exception("关税类型错误", self::ERROR_DUTY_PAY);
		}
		
		$orderExist = PurchaseReceipt::find('external_no = ? and status<>? and code = ?', $param['referenceCode'], PurchaseReceipt::STATUS_CANCELED, $customer->code)->count();
		
		if ($orderExist) {
			throw new Exception("订单已存在", self::ERROR_ORDER_EXISTS);
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
		$receiptItem = array();
		$productList = \Temtop\array_group($productList, 'sku', true);
		foreach ($param['lstItem'] as $key => $item) {
			if (!isset(PurchaseReceiptItem::$package_type_map[$item['packageType']])) {
				throw new Exception("包裹类型错误", self::ERROR_PACKAGE_TYPE);
			}
			if (!isset(PurchaseReceiptItem::$goodstype_map[$item['goodsType']])) {
				throw new Exception("货物类型错误", self::ERROR_CUSTOMS_TYPE);
			}
			
			$skuArr[$item['sku']] += $item['quantity'];
			//仓库收货单明细
			$receiptItem[] = array(
				'receipt_id'    => '',
				'box_no'        => $item['boxNumber'],
				'package_type'  => $item['packageType'],
				'goodsType'     => $item['goodsType'],
				'validity_date' => $item['validDate'] ?: 0,
				'sku'           => $item['sku'],
				'product_id'    => $productList[$item['sku']]['id'],
				'qty'           => $item['quantity'],
				'weight'        => $item['weight'] ?: 0,
				'length'        => $item['length'] ?: 0,
				'width'         => $item['width'] ?: 0,
				'height'        => $item['height'] ?: 0,
			);
		}
		
		$receipt = new PurchaseReceipt();
		
		//入库单主体信息
		$rec['external_no'] = $param['referenceCode'];
		$rec['tax_type'] = $param['customsType'];
		$rec['code'] = $customer->code;
		$rec['user_id'] = $customer->id;
		$rec['status'] = $param['verify'] = PurchaseReceipt::STATUS_PUBLISHED;
		$rec['description'] = $param['description'];
		$rec['arrival_date'] = $param['arrival_date'];
		$rec['first_shipping'] = $param['firstShipping'];
		$rec['tracking_no'] = $param['trackingNo'];
		$rec['container_type'] = $param['containerType'];
		$rec['container_no'] = $param['containerNo'];
		$rec['seal_no'] = $param['sealNo'];
		$rec['insure_type'] = $param['insureType'];
		$rec['duty_pay_type'] = $param['dutypayType'];
		$rec['is_transit'] = $param['is_transit'];
		
		PurchaseReceipt::transaction(function()use($receiptItem,$rec,&$receipt){
			$receipt->setValues($rec);
			$receipt->save();
			$receipt->receipt_no = "RCV" . CurrentWMS::getWmsLogogramCode() . '-' . date("ymd") . '-' . str_pad($receipt->id, 5, "0", STR_PAD_LEFT);
			$receipt->save();
			$receiptId = $receipt->id;
			
			//创建入库单明细
			foreach ($receiptItem as $k => $item) {
				$receipt_item_model = new PurchaseReceiptItem($item);
				$receipt_item_model->receipt_id = $receiptId;
				$receipt_item_model->save();
			}
		});
		
		return $this->success($receipt->receipt_no);
	}

	/**
	 * 更新头程单
	 * @return string
	 */
	public function UpdateOrder()
	{
		$fields = array(
			'receiptNo',
			'warehouseCode',
			'carrierCode',
			'insureType',
			'dutypayType',
			'firstShipping',
			'trackingNo',
			'containerType',
			'containerNo',
			'sealNo',
			'is_transit',
			'referenceCode',
			'description',
			'customsType',
			'lstItem',
			'arrival_date',
			'verify'
		);
		$param = $this->getParam($fields);
		
		$customer = $this->user;
		
		if(empty($param['referenceCode'])){
			throw new Exception("不存在订单号", self::ERROR_ORDER);
		}
		
		if(empty($param['lstItem'])){
			throw new Exception("不存在订单明细", self::ERROR_ORDER_DETAIL);
		}
		
		if (!PurchaseReceipt::$tax_type_map[$param['customsType']]) {
			throw new Exception("报关类型错误", self::ERROR_CUSTOMS);
		}
		if (!PurchaseReceipt::$insure_type_map[$param['insureType']]) {
			throw new Exception("保险类型错误", self::ERROR_INSURE);
		}
		if (!PurchaseReceipt::$duty_pay_type_map[$param['dutypayType']]) {
			throw new Exception("关税类型错误", self::ERROR_DUTY_PAY);
		}
		
		$purchaseReceipt = PurchaseReceipt::find('receipt_no = ? and code=? and status in ?', $param['receiptNo'], $customer->code, [
			PurchaseReceipt::STATUS_PUBLISHED
		])->one();
		
		if(!$purchaseReceipt->id){
			throw new Exception("入库单不存在".$param['receiptNo'], self::ERROR_ORDER_NOT_EXISTS);
		}
		
		//判断是否有不存在或者已停用的sku
		$skuNameList = array_column($param['lstItem'], 'sku');
		$productList = PrdProduct::find('is_stop=? and sku in ?', PrdProduct::IS_STOP_NO, $skuNameList)->all(true);
		$existSkuNameList = array_column($productList, 'sku');
		
		$skuDiff = array_diff($skuNameList, $existSkuNameList);
		if(!empty($skuDiff)){
			throw new Exception("SKU(".implode(',', $skuDiff).")不存在或已停用", self::ERROR_SKU);
		}
		
		$skuArr = array();
		$receiptItem = array();
		$productList = \Temtop\array_group($productList, 'sku', true);
		foreach($param['lstItem'] as $key => $item){
			if (!isset(PurchaseReceiptItem::$package_type_map[$item['packageType']])) {
				throw new Exception("包裹类型错误", self::ERROR_PACKAGE_TYPE);
			}
			if (!isset(PurchaseReceiptItem::$goodstype_map[$item['goodsType']])) {
				throw new Exception("货物类型错误", self::ERROR_CUSTOMS_TYPE);
			}
			$skuArr[$item['sku']] += $item['quantity'];
			//仓库收货单明细
			$receiptItem[] = array(
				'receipt_id'    => '',
				'box_no'        => $item['boxNumber'],
				'package_type'  => $item['packageType'],
				'goodsType'     => $item['goodsType'],
				'validity_date' => $item['validDate'] ?: 0,
				'sku'           => $item['sku'],
				'product_id'    => $productList[$item['sku']]['id'],
				'qty'           => $item['quantity'],
				'weight'        => $item['weight'] ?: 0,
				'length'        => $item['length'] ?: 0,
				'width'         => $item['width'] ?: 0,
				'height'        => $item['height'] ?: 0,
			);
		}
		
		//入库单主体信息
		$rec['external_no'] = $param['referenceCode'];
		$rec['tax_type'] = $param['customsType'];
		$rec['code'] = $customer->code;
		$rec['user_id'] = $customer->id;
		$rec['description'] = $param['description'];
		$rec['arrival_date'] = $param['arrival_date'];
		$rec['first_shipping'] = $param['firstShipping'];
		$rec['tracking_no'] = $param['trackingNo'];
		$rec['container_type'] = $param['containerType'];
		$rec['container_no'] = $param['containerNo'];
		$rec['seal_no'] = $param['sealNo'];
		$rec['insure_type'] = $param['insureType'];
		$rec['duty_pay_type'] = $param['dutypayType'];
		$rec['is_transit'] = $param['is_transit'];
		
		PurchaseReceipt::transaction(function() use ($receiptItem, $rec, $purchaseReceipt){
			$purchaseReceipt->setValues($rec);
			$purchaseReceipt->save();
			$receiptId = $purchaseReceipt->id;
			
			PurchaseReceiptItem::meta()->deleteWhere("receipt_id=?", $receiptId);
			//创建入库单明细
			foreach($receiptItem as $k => $item){
				$receipt_item_model = new PurchaseReceiptItem($item);
				$receipt_item_model->receipt_id = $receiptId;
				$receipt_item_model->save();
			}
		});
	
		return $this->success($purchaseReceipt->receipt_no);
	}

	/**
	 *
	 * 入库状态:
	 * O:新建(客户或者操作员新建的入库单)。R(已收货--中转仓已经收货完成)。T(转运中--中转仓已经出货，交给服务商转运)。E(待入库--货物已经到达目的仓,等待入库)。I(已入库--目的仓已入库)。P(已上架--货品在目的仓已上架)。X(已取消--异常或取消)
	 * @return string
	 * @throws Exception
	 */
	public function GetReceivingOrder()
	{
		$fields = array(
			'receivingCode'
		);
		$param = $this->getParam($fields);
		$receipt = PurchaseReceipt::find('(external_no=? or receipt_no=?) and code = ?', $param['receivingCode'], $param['receivingCode'],$this->user->code)->one();
		if (!$receipt->id) {
			throw new Exception('批次不存在', self::ERROR_ORDER_EXISTS);
		}
		$status = array(
			PurchaseReceipt::STATUS_PUBLISHED => 'T',
			PurchaseReceipt::STATUS_ARRIVAL   => 'E',
			PurchaseReceipt::STATUS_RECEIVED  => 'E',
			PurchaseReceipt::STATUS_CHECKED   => 'E',
			PurchaseReceipt::STATUS_FINISHED  => 'P',
			PurchaseReceipt::STATUS_CANCELED  => 'X'
		);
		
		$sku_predict_qty_map = $receipt->sku_predict_qty_map;
		
		$BoxPutQty = $receipt->getBoxPutQty();
		$prd_list = PrdProduct::find('id in ?' ,array_keys($sku_predict_qty_map))->map('id','sku');
		
		//这里用其中一箱的收货时间作为收货时间
		$receive_date = PurchaseReceiptBox::find('receipt_id = ? and status = ?',$receipt->id,PurchaseReceiptBox::STATUS_RECEIVED)->one()->status_date;
		
		$box_item_list = $receipt->box_item;
		$detail = array();
		foreach ($box_item_list as $item) {
			
			$detail[] = array(
				"sku"                  => $prd_list[$item->product_id],
				"quantity"             => $item->qty,
				"acceptedQuantity"     => $item->receive_qty?:0,
				"destAcceptedQuantity" => $BoxPutQty[$item->box_id.'-'.$item->product_id]?:0,//这里用分配的数量
				"arrivalDate"          => $receipt->arrival_date,
				"receivedDate"         => $receive_date,
				"boxNumber"            => $item->box->no
			);
			
		}
		$return = array(
			"warehouseCode" => CurrentWMS::getWmsCode(),
			"createDate"    => $receipt->create_time,
			"status"        => $status[$receipt->status],
			'backReason'    => $receipt->back_reason,
			'confirmDate'   => $receipt->confirm_date,
			"shippingCode"  => '',
			"lstItem"       => $detail
		);
		
		return $this->success($return, false);
	}
	
}