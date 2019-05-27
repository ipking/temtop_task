<?php

namespace ttwms\controller\order;

use Lite\Component\Paginate;
use Lite\Exception\BizException;
use ttwms\business\InventoryWrap;
use ttwms\controller\BaseController;
use ttwms\model\PrdProduct;
use ttwms\model\TransitDeliveryOrder;
use ttwms\model\TransitDeliveryOrderItem;
use ttwms\ViewBase;

/**
 * @auth 订单管理/出库单
 */
class TransitDeliveryOrderController extends BaseController
{
	/**
	 * @auth 列表
	 * @param $search
	 * @param $post
	 * @return array|\Lite\Core\Result
	 * @throws \Lite\Exception\Exception
	 */
	public function index($search, $post=null){
		$paginate = Paginate::instance();
		$paginate = $paginate->setConfig(['page_size' => 20]);
		ViewBase::setOrderConfig([
			'id',
			'confirm_date',
			'first_shipping_type',
			'create_time'
		], 'id', 'desc');
		$select = TransitDeliveryOrder::find();
		$select->whereOnSet('enterprise_id = ?', $search['enterprise_id']);
		$select->whereOnSet('first_shipping_type = ?', $search['first_shipping_type']);
		if($search['wms_no']){
			$search['wms_no'] = trim($search['wms_no']);
			$select->where('wms_no = ? or enterprise_order_no=?', $search['wms_no'], $search['wms_no']);
		}
		if (strlen($search['sku'])) {
			$pids = PrdProduct::find('sku like ?',"%{$search['sku']}%")->column('id');
			$rids = TransitDeliveryOrderItem::find('product_id in ?',$pids)->column('transit_delivery_order_id');
			$select->where('id in ?', $rids);
		}
		
		$select->whereOnSet('status = ?', $search['status']);
		$select->order(ViewBase::getCurrentOrderSet());
		$order_list = $select->paginate($paginate);
		
		return array(
			'get'        => $search,
			'paginate'   => $paginate,
			'order_list' => $order_list,
		);
	}
	
	/**
	 * @auth 确认发出
	 * @param $get
	 * @return array|\Lite\Core\Result
	 * @throws \Lite\Exception\Exception
	 */
	function Confirm($get)
	{
		$order = TransitDeliveryOrder::find('id = ?', $get['id'])->one();
		if (!in_array($order->status,[TransitDeliveryOrder::STATUS_NEW])) {
			return $this->getCommonResult(true);
		}
		try{
			TransitDeliveryOrder::transaction(function () use($order){
				$order->status = TransitDeliveryOrder::STATUS_SEND_OUT;
				$order->send_out_time = date('Y-m-d');
				$order->save();
				
				
				foreach($order->all_item_list as $item){
					if($item->is_delete == TransitDeliveryOrderItem::IS_DELETE_NO){
						$item->send_quantity = $item->quantity;
						$item->save();
					}
					//解冻扣除库存
					InventoryWrap::setPreFrozenInventoryOver($item,$item->send_quantity?:0);
				}
			});
		}catch(\Exception $e){
			throw new BizException($e->getMessage());
		}
		
		return $this->getCommonResult(true);
	}
	
	/**
	 * @auth 出库单取消
	 * @param $get
	 * @param $post
	 * @return array|\Lite\Core\Result
	 * @throws \Lite\Exception\Exception
	 */
	function Back($get,$post)
	{
		$order = TransitDeliveryOrder::find('id=?', $get['id'])->oneOrFail();
		
		if ($order->status != TransitDeliveryOrder::STATUS_NEW) {
			throw new BizException('状态错误');
		}
		if ($post) {
			$reason = $post['back_note'];
			if (!$reason) {
				throw new BizException('必须填写退回原因');
			}
			TransitDeliveryOrder::transaction(function()use($order,$reason){
				//解冻库存 并 回滚
				InventoryWrap::cancelTransitDeliveryOrder($order);
				$order->status = TransitDeliveryOrder::STATUS_CANCELED;
				$order->wms_cancel_note = $reason;
				$order->save();
			});
			
			return $this->getCommonResult(true);
		}
		return array(
			'info' => $order
		);
	}
	
	/**
	 * 详情信息
	 * @param $get
	 * @return array
	 */
	public function info($get){
		return array(
			'order' => TransitDeliveryOrder::findOneByPkOrFail($get['id'])
		);
	}
	
	/**
	 * @auth 装箱明细
	 * @param $get
	 * @return array
	 */
	public function boxItem($get){
		return array(
			'order' => TransitDeliveryOrder::findOneByPkOrFail($get['id'])
		);
	}
	
	
	public function delete($get){
		$ids = $get['ids'];
		$item_list = TransitDeliveryOrderItem::find('id in ?',$ids)->all();
		foreach ($item_list as $item){
			$item->is_delete = TransitDeliveryOrderItem::IS_DELETE_YES;
			$item->save();
		}
		return $this->getCommonResult(true);
	}
	
	public function printShipment($get){
		$transit = TransitDeliveryOrder::find("id=?",$get['id'])->one();
		$printerConfig =[];
		switch($get['page']){
			case "shipmentFba":
				$printerConfig['index'] = "print_transit";
				$printerConfig['paper_type'] = "label";
				$printerConfig['paper_width'] = 150;
				$printerConfig['paper_height'] =100;
				break;
			case "shipmentPage":
				$printerConfig['index'] = "print_transit";
				$printerConfig['paper_type'] = "label";
				$printerConfig['paper_width'] = 150;
				$printerConfig['paper_height'] =100;
				break;
			case "shipmentPageSpx":
				$printerConfig['index'] = "print_transit";
				$printerConfig['paper_type'] = "label";
				$printerConfig['paper_width'] = 150;
				$printerConfig['paper_height'] = 100;
				break;
			case "rcvList":
				$printerConfig['index'] = "print_out";
				$printerConfig['paper_type'] = "A4";
				$printerConfig['paper_width'] = 297;
				$printerConfig['paper_height'] =210;
				break;
			default:
				break;
		}
		return array(
			"get" => $get,
			"transit" => $transit,
			"printerConfig" => $printerConfig
		);
	}
	
	public function shipmentPage($get){
		$transit = TransitDeliveryOrder::find("id=?",$get['id'])->one();
		$boxList = $transit->send_item_list;
		$boxNoList =[];
		if($get["box_no"]){
			$boxNos = explode('、',$get["box_no"]);
			foreach($boxNos as $boxNoStr){
				if(strstr($boxNoStr,"-")){
					$firstIndex = (int)substr($boxNoStr,0,strpos($boxNoStr,"-"));
					$endIndex = (int)substr($boxNoStr,strripos($boxNoStr,"-")+1,strlen($boxNoStr)-strripos($boxNoStr,"-"));
					for($i=$firstIndex;$i<=$endIndex;$i++){
						$boxNoList[]=$i;
					}
					continue;
				}
				$boxNoList[]=(int)$boxNoStr;
			}
		}
		return array(
			"get" => $get,
			"transit" => $transit,
			"boxList" => $boxList,
			"boxNoList" => $boxNoList,
		);
	}
	
	public function shipmentPageSpx($get){
		$transit = TransitDeliveryOrder::find("id=?",$get['id'])->one();
		$boxList = $transit->send_item_list;
		
		$sku_type_map = [];
		$list = [];
		foreach($boxList as $item){
			$list[$item->box_no]['box_no'] = $item->box_no;
			$list[$item->box_no]['box_barcode'] = $item->box_barcode;
			$list[$item->box_no]['item'][] = $item;
			$sku_type_map[$item->product->sku] = 1;
		}
		$i = 0;
		foreach($sku_type_map as $sku =>$v){
			$i++;
			$sku_type_map[$sku] = $this->getTypeMap($i);
		}
		$boxNoList =[];
		if($get["box_no"]){
			$boxNos = explode('、',$get["box_no"]);
			foreach($boxNos as $boxNoStr){
				if(strstr($boxNoStr,"-")){
					$firstIndex = (int)substr($boxNoStr,0,strpos($boxNoStr,"-"));
					$endIndex = (int)substr($boxNoStr,strripos($boxNoStr,"-")+1,strlen($boxNoStr)-strripos($boxNoStr,"-"));
					for($i=$firstIndex;$i<=$endIndex;$i++){
						$boxNoList[]=$i;
					}
					continue;
				}
				$boxNoList[]=(int)$boxNoStr;
			}
		}
		
		return array(
			"get"          => $get,
			"transit"      => $transit,
			"boxList"      => $list,
			"boxNoList"    => $boxNoList,
			"sku_type_map" => $sku_type_map,
		);
	}
	
	private function getTypeMap($num){
		if($num == 0){
			return false;
		}
		$map = [
			'1' => 'A',
			'2' => 'B',
			'3' => 'C',
			'4' => 'D',
			'5' => 'E',
			'6' => 'F',
			'7' => 'G',
			'8' => 'H',
			'9' => 'I',
			'10' => 'J',
			'11' => 'K',
			'12' => 'L',
			'13' => 'M',
			'14' => 'N',
			'15' => 'O',
			'16' => 'P',
			'17' => 'Q',
			'18' => 'R',
			'19' => 'S',
			'20' => 'T',
			'21' => 'U',
			'22' => 'V',
			'23' => 'W',
			'24' => 'X',
			'25' => 'Y',
			'26' => 'Z',
		];
		//简单做
		
		//取倍数
		$k = bcdiv($num,count($map),0);
		//取余数
		$m = $num%count($map);
		if($k and !$m){
			return $map[$k-1].$map[count($map)];
		}
		return $map[$k].$map[$m];
	}


	public function rcvList($get){
		$transit = TransitDeliveryOrder::find("id=?", $get['id'])->one();
		$boxList = $transit->send_item_list;
		
		return array(
			"get"          => $get,
			"transit"      => $transit,
			"boxList"      => $boxList,
		);
	}
	
	
	public function shipmentFba($get){
		$transit = TransitDeliveryOrder::find("id=?", $get['id'])->one();
		$boxList = $transit->send_item_list;
		$boxNoList =[];
		if($get["box_no"]){
			$boxNos = explode('、',$get["box_no"]);
			foreach($boxNos as $boxNoStr){
				if(strstr($boxNoStr,"-")){
					$firstIndex = (int)substr($boxNoStr,0,strpos($boxNoStr,"-"));
					$endIndex = (int)substr($boxNoStr,strripos($boxNoStr,"-")+1,strlen($boxNoStr)-strripos($boxNoStr,"-"));
					for($i=$firstIndex;$i<=$endIndex;$i++){
						$boxNoList[]=$i;
					}
					continue;
				}
				$boxNoList[]=(int)$boxNoStr;
			}
		}
		
		return array(
			"get"           => $get,
			"transit"       => $transit,
			"boxList"       => $boxList,
			"boxNoList"    => $boxNoList,
		);
	}
}
