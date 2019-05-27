<?php
namespace ttwms\api;

use Exception;
use ttwms\business\CurrentWMS;
use ttwms\business\Form;
use ttwms\helper\Helper_Array;
use ttwms\model\PrdProduct;
use ttwms\model\PurchaseReceipt;
use ttwms\model\PurchaseReceiptBox;
use ttwms\model\WhInventoryRecord;
use ttwms\model\WhInventoryRo;

class Inventory extends ApiAbstract{
	const ERROR_WAREHOUSE_CODE = 3001;//仓库code错误
	const ERROR_SKU = 3002;//sku不存在
	const ERROR_NO_SKU = 3003;//客户无sku
	
	/**
	 * 获取库存
	 * param $lstSku array() 如果为空 获取全部
	 * @return string json
	 * {
	 * errorCode: 错误代码
	 * errorMsg: 错误信息
	 * data:{
	 * sku：
	 * actualQuantity：实际库存
	 * availableQuantity：可用库存
	 * shippingQuantity：冻结库存
	 * pendingQuantity：在途数量
	 * }
	 * }
	 * @throws \Exception
	 */
	public function GetInventory(){
		$fields = array('lstSku', 'warehouseCode');
		$param = $this->getParam($fields);
		$customer = $this->user;
		$lstSku = $param['lstSku'];
		
		$data = array();
		if(count($lstSku)){
			//查出所有的sku
			$skus = PrdProduct::find("status =? and is_stop=? and enterprise_id =?",PrdProduct::STATUS_ENABLED,PrdProduct::IS_STOP_NO, $customer->id)->map("id", 'sku');
			$ids = array();//需要查询的产品id
			$sku_map = array();//需要查询的产品 array{prodct_id:sku}
			foreach($lstSku as $k => $sku){
				$index = array_search($sku, $skus, true);
				if($index){//判断SKU是否存在
					$ids[] = $index;
					$sku_map[$index] = $sku;
				} else{
					throw new Exception("[".$sku."]不存在", self::ERROR_SKU);
				}
			}
			//到仓未收货
			$sql = "select item.product_id as product_id,sum(item.qty) as qty  from
					purchase_receipt_box_item as item
					left join purchase_receipt_box as box
				on item.box_id= box.id where box.status='".PurchaseReceiptBox::STATUS_UNSETTLED."' and item.product_id in (".implode(",", $ids).") group by item.product_id";
			
			$db = PurchaseReceipt::setQuery($sql);
			$list = $db->all(true);
			
			$list_box = Helper_Array::toHashmap($list, 'product_id', 'qty');
			//未到仓的预到头程
			$sql = "select item.product_id as product_id,sum(item.qty) as qty  from
				purchase_receipt_item as item
				left join purchase_receipt as rec
				on item.receipt_id= rec.id where rec.status='".PurchaseReceipt::STATUS_PUBLISHED."' and item.product_id in (".implode(",", $ids).")
				group by item.product_id";
			
			$db = PurchaseReceipt::setQuery($sql);
			$list = $db->all(true);
			$list_receipt = Helper_Array::toHashmap($list, 'product_id', 'qty');
			
			//已上架  库存
			$sql = "select wh.product_id as product_id,wh.qty as qty,wh.frozen_qty as frozen_qty from wh_inventory as wh left join prd_product as prd
						on wh.product_id=prd.id where wh.goods_type = '".Form::GOODS_TYPE_GOOD."' and  wh.product_id in (".implode(",", $ids).")";
			
			
			$db = PurchaseReceipt::setQuery($sql);
			$list = $db->all(true);
			$dataAll = Helper_Array::toHashmap($list, 'product_id');
			foreach($sku_map as $key => $val){
				$qty_arr = $dataAll[$key];
				$data[] = array(
					"sku"               => $val,
					"actualQuantity"    => $qty_arr['qty']+$qty_arr['frozen_qty'],
					"availableQuantity" => intval($qty_arr['qty']),
					"shippingQuantity"  => intval($qty_arr['frozen_qty']),
					"pendingQuantity"   => $list_box[$key]+$list_receipt[$key]
				);
			}
		} else{
			$products = PrdProduct::find("status = ? and enterprise_id =? ",PrdProduct::STATUS_ENABLED, $customer->id)->all(true);
			if(!count($products)){
				throw new Exception("该客户无产品", self::ERROR_NO_SKU);
			}
			//到仓未收货
			$sql = "select item.product_id as product_id,sum(item.qty) as qty  from
							purchase_receipt_box_item as item
							left join purchase_receipt_box as box
							on item.box_id= box.id
							left join prd_product  as prd
							on item.product_id =prd.id
							where box.status='".PurchaseReceiptBox::STATUS_UNSETTLED."' and prd.enterprise_id = ".$customer->id." group by item.product_id";
			
			$db = PurchaseReceipt::setQuery($sql);
			$list = $db->all(true);
			$list_box = Helper_Array::toHashmap($list, 'product_id', 'qty');
			
			
			//未到仓的预到头程
			$sql = "select item.product_id as product_id,sum(item.qty) as qty  from
							purchase_receipt_item as item
							left join purchase_receipt as rec
							on item.receipt_id= rec.id
							left join prd_product  as prd
							on item.product_id =prd.id
						where rec.status='".PurchaseReceipt::STATUS_PUBLISHED."' and prd.enterprise_id = ".$customer->id."	group by item.product_id";
			$db = PurchaseReceipt::setQuery($sql);
			$list = $db->all(true);
			$list_receipt = Helper_Array::toHashmap($list, 'product_id', 'qty');
			
			//已上架  库存
			$sql = "select wh.product_id as product_id,wh.qty as qty,wh.frozen_qty as frozen_qty from wh_inventory as wh left join prd_product as prd
						on wh.product_id=prd.id where wh.goods_type = '".Form::GOODS_TYPE_GOOD."' and prd.enterprise_id = ".$customer->id;
			$db = PurchaseReceipt::setQuery($sql);
			$list = $db->all(true);
			$dataAll = Helper_Array::toHashmap($list, 'product_id');
			foreach($products as $key => $val){
				$qty_arr = $dataAll[$val['id']];
				$data[] = array(
					"sku"               => $val['sku'],
					"actualQuantity"    => $qty_arr['qty']+$qty_arr['frozen_qty'],
					"availableQuantity" => intval($qty_arr['qty']),
					"shippingQuantity"  => intval($qty_arr['frozen_qty']),
					"pendingQuantity"   => $list_box[$val['id']]+$list_receipt[$val['id']]
				);
			}
		}
		return $this->success($data, false);
	}
	
	/**
	 * 获取库龄
	 * @return string
	 */
	public function GetInventoryDetail(){
		$fields = array('sku', 'warehouseCode');
		$param = $this->getParam($fields);
		
		$productInfo = PrdProduct::find('sku=? and enterprise_id=?', $param['sku'], $this->user->id)->one();
		if(!$productInfo->id){
			throw new \Exception("该客户无产品", self::ERROR_NO_SKU);
		}
		$inventoryRoList = WhInventoryRo::find('product_id=? and remain_qty>0  and goods_type = ?', $productInfo->id,Form::GOODS_TYPE_GOOD)->all();
		
		$data = array();
		foreach($inventoryRoList as $k => $ro){
			$data[] = array(
				'sku'              => $ro->product->sku,
				'skuId'            => null,
				"warehouseCode"    => CurrentWMS::getWmsCode(),
				"inventoryQtyGood" => $ro->remain_qty,
				"inventoryQtyBad"  => 0,
				"addedDate"        => $ro->create_time,
				"inventoryAge"     => $ro->age,
				"receivingCode"    => $ro->ref_code,
				"qualityCode"      => null
			);
		}
		return $this->success($data);
	}

	/**
	 * 获取单个SKU的库存流水
	 * @return string
	 * @throws \Exception
	 */
	public function GetSellerInventoryLog(){
		$fields = array('sku', 'warehouseCode', 'createDateBegin', 'createDateEnd', 'pageSize', 'pageNo');
		$param = $this->getParam($fields);
		
		$productInfo = PrdProduct::find('sku=? and enterprise_id=?', $param['sku'], $this->user->id)->one();
		if(!$productInfo->id){
			throw new \Exception("该客户无产品", self::ERROR_NO_SKU);
		}
		$sql = WhInventoryRecord::find('product_id=? and goods_type = ?', $productInfo->id,Form::GOODS_TYPE_GOOD);
		$sql->whereOnSet('create_time>=?', $param['createDateBegin']);
		$sql->whereOnSet('create_time<=?', $param['createDateEnd']);
		$cloneSql = clone ($sql);
		$count = $cloneSql->count();
		
		$inventoryLogList = $sql->limit(($param['pageNo']-1)*$param['pageSize'], $param['pageSize'])->order('create_time desc')->all();
		$sellerInventory = array();
		foreach($inventoryLogList as $k => $log){
			$sellerInventory[] = array(
				'sku'               => $productInfo->sku,
				'swlId'             => $log->id,
				"warehouseCode"     => CurrentWMS::getWmsCode(),
				"refCode"           => $log->no,
				"codeType"          => $log->ref_type,
				"availableQuantity" => $log->qty_balance,
				"warehouseQuantity" => $log->qty,
				"note"              => $log->note,
				"createDate"        => $log->create_time,
			);
		}
		$data['pageTotal'] = $count ?: 0;
		$data['pageSize'] = $param['pageSize'];
		$data['pageNo'] = $param['pageNo'];
		$data['sellerInventory'] = $sellerInventory;
		
		return $this->success($data);
	}
}