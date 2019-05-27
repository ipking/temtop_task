<?php
namespace ttwms\controller\receipt;

use Lite\Component\Paginate;
use Lite\Core\Result;
use Lite\Exception\BizException;
use ttwms\business\Form;
use ttwms\business\InventoryWrap;
use ttwms\business\T;
use ttwms\controller\BaseController;
use ttwms\CurrentUser;
use ttwms\model\Enterprise;
use ttwms\model\PrdProduct;
use ttwms\model\PrdProductExternalCode;
use ttwms\model\PurchaseReceipt;
use ttwms\model\PurchaseReceiptBox;
use ttwms\model\PurchaseReceiptBoxItem;
use ttwms\model\PurchaseReceiptItem;
use ttwms\model\PurchaseReceiptPutBad;
use ttwms\model\PurchaseReceiptPutGood;
use ttwms\model\PurchaseReceiptQt;
use ttwms\model\WhArea;
use ttwms\model\WhInventoryRo;
use ttwms\model\WhLocation;
use ttwms\model\WhProductLocation;
use ttwms\model\WhProductLocationMapping;
use function Lite\func\array_group;
use function Lite\func\array_trim;
use function Temtop\t as t_text;


/**
 * @auth 入库单/入库单
 * Date: 2019-01-22
 * Time: 14:28
 */
class PurchaseReceiptController extends BaseController
{
	
	const UNIQUE_HASH_KEY_RECEIPT = 'ro_receipt_on';
	const UNIQUE_HASH_KEY_QT = 'ro_qt_on';
	const UNIQUE_HASH_KEY_PUT = 'ro_put_on';
	
	/**
	 * @auth 列表|查看
	 * @param $search
	 * @param $post
	 * @return array
	 * @throws \Lite\Exception\Exception
	 */
	function index($search, $post=null)
	{
		$search = array_trim($search);
		$paginate = Paginate::instance();
		$search['status'] = $search['status']?: PurchaseReceipt::STATUS_PUBLISHED;
		$select = PurchaseReceipt::find();
		
		$select->where('status = ?', $search['status']);
		
		
		$end = " 23:59:59";
		//入库单号
		if (strlen($search['receipt_no'])) {
			$select->where('receipt_no like ?', '%' . $search['receipt_no'] . '%');
		}
		if (strlen($search['external_no'])) {
			$select->where('external_no like ?', '%' . $search['external_no'] . '%');
		}
		$select->whereOnSet('code = ?', $search['enterprise_code']);

		//流入时间
		if (strlen($search['create_time_start'])) {
			$select->where('create_time>=?', $search['create_time_start']);
		}
		if (strlen($search['create_time_end'])) {
			$select->where('create_time<=?', $search['create_time_end'] . $end);
		}
		//到货时间
		if (strlen($search['confirm_date_start'])) {
			$select->where('confirm_date>=?', $search['confirm_date_start']);
		}
		if (strlen($search['confirm_date_end'])) {
			$select->where('confirm_date<=?', $search['confirm_date_end'] . $end);
		}
		//预计到货时间
		if (strlen($search['arrival_date_start'])) {
			$select->where('arrival_date>=?', $search['arrival_date_start']);
		}
		if (strlen($search['arrival_date_end'])) {
			$select->where('arrival_date<=?', $search['arrival_date_end'] . $end);
		}
		if (strlen($search['sku'])) {
			$pids = PrdProduct::find('sku like ?',"%{$search['sku']}%")->column('id');
			$rids = PurchaseReceiptItem::find('product_id in ?',$pids)->column('receipt_id');
			$select->where('id in ?', $rids);
		}

		$list = $select->order('confirm_date DESC, create_time DESC')->paginate($paginate);

		return array(
			'list'       => $list,
			'pagination' => $paginate,
			'search'      => $search
		);
	}
	
	/**
	 * @auth 备注
	 * @param $get
	 * @param $post
	 * @return array|\Lite\Core\Result
	 * @throws \Lite\Exception\BizException
	 * @throws \Lite\Exception\Exception
	 */
	function Note($get,$post)
	{
		$box = PurchaseReceiptBox::find('id=?', $get['id'])->one();
		if ($post) {
			$box->note = $post['note'];
			$box->save();
			return $this->getCommonResult(true);
		}
		return array(
			'info' => $box
		);
	}
	
	/**
	 * @auth 查看
	 * @param $get
	 * @return array
	 * @throws \Lite\Exception\Exception
	 */
	function View($get)
	{
		$info = PurchaseReceipt::find('id=?', $get['id'])->one();
		$pids = PurchaseReceiptItem::find('receipt_id=?', $get['id'])->column('product_id');
		
		$product_list = PrdProduct::find('id in ?',$pids)->all();
		return array(
			'list' => $product_list,
			'info' => $info,
		);
	}
	
	/**
	 * @auth 查看
	 * @param $get
	 * @return array
	 * @throws \Lite\Exception\Exception
	 */
	function BoxView($get)
	{
		$info = PurchaseReceiptBox::find('id=?', $get['id'])->one();
		$list = PurchaseReceiptBoxItem::find('box_id=?', $get['id'])->all();
		return array(
			'info' => $info,
			'list' => $list,
			'mode' => false
		);
	}
	
	/**
	 * @auth 确认到货
	 * @param $get
	 * @return array|\Lite\Core\Result
	 * @throws \Lite\Exception\Exception
	 */
	function Confirm($get)
	{
		$receipt = PurchaseReceipt::find('id = ?', $get['id'])->one();
		$receiptItem = PurchaseReceiptItem::find('receipt_id = ?', $get['id'])->all();
		if (!in_array($receipt->status,[PurchaseReceipt::STATUS_PUBLISHED])) {
			return $this->getCommonResult(true);
		}
		PurchaseReceipt::transaction(function () use($receipt,$receiptItem){
            $receipt->status = PurchaseReceipt::STATUS_ARRIVAL;
            $receipt->confirm_date = date('Y-m-d');
            $receipt->save();
            $boxes = array();
            foreach ($receiptItem as $detail) {
                //以箱为单位
                $boxes[$detail->box_no]['receipt_id'] = $receipt->id;
                $boxes[$detail->box_no]['no'] = $detail['box_no'];
                $boxes[$detail->box_no]['weight'] = $detail->weight;
                $boxes[$detail->box_no]['package_type'] = $detail['package_type'];
                $boxes[$detail->box_no]['user_id'] = CurrentUser::getUserId();
                $boxes[$detail->box_no]['product_id'][] = $detail->product_id;
                $boxes[$detail->box_no]['qty'][] = $detail->qty;
            }
            foreach ($boxes as $b_key => $box_data) {
                //生成箱数据和箱明细
                $box = new PurchaseReceiptBox();
                $box->setValues($box_data);
                $box->save();
                foreach ($box_data['product_id'] as $k => $prd) {
                    $box_item = new PurchaseReceiptBoxItem();
                    $box_item_data['box_id'] = $box->id;
                    $box_item_data['product_id'] = $prd;
                    $box_item_data['qty'] = $boxes[$b_key]['qty'][$k];
                    $box_item->setValues($box_item_data);
                    $box_item->save();
                }
            }
            
        });
		return $this->getCommonResult(true);
	}
	
	/**
	 * @auth 收货
	 * @param $param
	 * @return array
	 * @throws \Lite\Exception\Exception
	 */
	function BoxList($param)
	{
		set_time_limit(0);
		$param = array_trim($param);
		$select = PurchaseReceiptBox::find('receipt_id=?', $param['id'])->order('abs(no) asc');
		$sku = $param['sku'];

		if (strlen($sku) > 0) {
			$product = PrdProduct::find('barcode like ?', '%'.$sku.'%')->column('id');

			if (empty($product)) {
				$pe = PrdProductExternalCode::find('external_code=?', $sku)->one();

				$sku = $pe->sku;
				$product = PrdProduct::find('barcode like ?', '%'.$sku.'%')->column('id');
			}
			$param['sku'] = $sku;
			$box = PurchaseReceiptBoxItem::find('product_id in ?', $product)->column('box_id');
			$select->where('id in ?', $box);
		}
		$mode = $param['mode'];

		$list = $select->all(true);

		if (!empty($list)) {
			$boxItemIds = array_column($list, 'id');
			$boxItemList = PurchaseReceiptBoxItem::find('box_id in ?', $boxItemIds)->all(true);

			$itemIds = array_column($boxItemList, 'id');
			$productIds = array_column($boxItemList, 'product_id');
			$boxItemList = array_group($boxItemList, 'box_id');

			$productLocationMappingList = WhProductLocationMapping::find('ref_id in ? and product_id in ? and ref_type=?', $itemIds, $productIds, WhProductLocationMapping::REF_TYPE_RO_IN)->all(true);
			$pm = array();
			foreach ($productLocationMappingList as $i => $j) {
				$pm[$j['ref_id']][$j['product_id']] += $j['qty'];
			}

			foreach ($list as $k => $item) {
				$allCount = $allReceive = $putCountTmp = 0;
				foreach ($boxItemList[$item['id']] ?: array() as $row) {
					$allCount += $row['qty'];
					$allReceive += $row['receive_qty'];
					$putCountTmp += $pm[$row['id']][$row['product_id']];
					$list[$k]['allCount'] = $allCount;
					$list[$k]['allReceive'] = $allReceive;
					$list[$k]['putCountTmp'] = $putCountTmp;
				}
			}
		}

		return array(
			'list'  => $list,
			'param' => $param,
			'info'  => PurchaseReceipt::find('id=?',  $param['id'])->one(),
			'mode'  => $mode
		);
	}
	
	/**
	 * @auth 入库单退回
	 * @param $get
	 * @param $post
	 * @return array|\Lite\Core\Result
	 * @throws \Lite\Exception\Exception
	 */
	function Back($get,$post)
	{
		$order = PurchaseReceipt::find('id=?', $get['id'])->oneOrFail();
		
		if ($order->status != PurchaseReceipt::STATUS_PUBLISHED) {
			return new Result(t_text('状态错误'), false);
		}
		if ($post) {
			$reason = $post['back_note'];
			if (!$reason) {
				return new Result(t_text('必须填写退回原因'), false);
			}

			$order->status = PurchaseReceipt::STATUS_CANCELED;
			$order->back_reason = $reason;
			$order->back_time = date('Y-m-d H:i:s');
			$order->save();
			return new Result(t_text('操作成功'), true);
		}
		return array(
			'info' => $order
		);
	}
	
	/**
	 * @auth 打印
	 * @param $get
	 * @param $post
	 * @return array
	 * @throws \Lite\Exception\Exception
	 */
	function PrintBySku($get,$post)
	{
		$ids = $post['ids'];
		$id = $get['id'];
		$info = PurchaseReceipt::find('id=?', $id)->one();
		$select = PurchaseReceiptBoxItem::find('box_id in ?', $ids)
            ->leftJoin('purchase_receipt_box', 'purchase_receipt_box_item.box_id=purchase_receipt_box.id')
            ->order('purchase_receipt_box_item.product_id asc,(purchase_receipt_box.no + 0) asc');

		$list = $select->all();

		return array(
			'list'  => $list,
			'ids'   => $ids,
			'info'  => $info,
			'count' => $select->count(),
		);
	}

	
	/**
	 * @auth 打印
	 * @param $get
	 * @return array
	 * @throws \Lite\Exception\BizException
	 * @throws \Lite\Exception\Exception
	 */
	function PressSkuPrint($get)
	{

		$productId = $get['product_id'];
		$pids = explode(',',trim($productId,','));
		$id = $get['id'];
		if (!$pids || !$id) {
			throw new BizException(t_text('参数错误'));
		}
		$info = PurchaseReceipt::find('id=?', $id)->one();
		$boxIds = PurchaseReceiptBox::find('receipt_id=?', $info->id)->column('id');


		$select = PurchaseReceiptBoxItem::find('box_id in ? and product_id in ?', $boxIds,$pids)
			->leftJoin('purchase_receipt_box', 'purchase_receipt_box_item.box_id=purchase_receipt_box.id')
			->order('purchase_receipt_box_item.product_id asc,(purchase_receipt_box.no + 0) asc');
		$list = $select->all();
		//组合数据
		$data = [];
		foreach($list as $row){
			$data[$row->product_id]['product_id'] = $row->product_id;
			$data[$row->product_id]['sku'] = $row->product->sku;
			$data[$row->product_id]['clearance_name'] = $row->product->clearance_name;
			$data[$row->product_id]['qty'] += $row->qty;
		}

		return array(
			'list'  => $data,
			'info'  => $info,
			'count' => $select->count(),
		);
	}
	
	/**
	 * @auth 打印
	 * @param $get
	 * @param $post
	 * @return array
	 * @throws \Lite\Exception\Exception
	 */
	public function PrintByCase($get,$post)
	{
		$params = $post;
		$ids = $params['ids'];
		$id = $get['id'];
		$info = PurchaseReceipt::find('id=?', $id)->one();
		$select = PurchaseReceiptBoxItem::find('box_id in ?', $ids)
			->leftJoin('purchase_receipt_box','purchase_receipt_box_item.box_id=purchase_receipt_box.id')
			->order('(purchase_receipt_box.no +0) asc');
		$list = $select->all();

		return array(
			'list'  => $list,
			'ids'   => $ids,
			'info'  => $info,
			'count' => $select->count(),
		);
	}
	
	
	
	public function GetSku($get){
		$enterpriseCode = $get['enterpriseCode'];
		$enterpriseInfo = Enterprise::find("code=?",$enterpriseCode)->one();
		$codes = explode('-', $get['code']);
		if (strlen($codes[0]) == 6) {//todo:临时解决
		unset($codes[0]);
		}
		$code = implode('-', $codes) ? implode('-', $codes) : $get['code'];
		$product = PrdProduct::find("sku=? and enterprise_id=?", $code,$enterpriseInfo->id)->one();
		$sku = $product->sku;
		if ( empty($sku) ) {
			$sku = PrdProductExternalCode::getSkuByExtCodes($enterpriseInfo->id, $code);
		}
		echo $sku;
	}

	
	/**
	 * @auth 修改预计日期
	 * @param $get
	 * @param array $post
	 * @return array|\Lite\Core\Result
	 */
	public function ArrivalDateEditAndDescription($get,$post)
	{
		$info = PurchaseReceipt::find('id = ?', $get['id'])->oneOrFail();
		if ($post) {
			$info->arrival_date = $post['arrival_date'];
			$info->description = $post['description'];
			$info->save();
			return $this->getCommonResult(true);
		}
		
		return array( 'info' => $info );
	}

	
	/**
	 * @auth 按SKU收货
	 * @param $get
	 * @param $post
	 * @return array|\Lite\Core\Result
	 * @throws \Lite\Exception\BizException
	 * @throws \Lite\Exception\Exception
	 */
	public function PressSKUReceipt($get,$post){
		$id = $get['id'];
		$flag = PurchaseReceipt::checkBoxSinglePack($id);
		if($flag){
			throw new BizException(t_text('该入库单，装箱明细包含混装，不能按SKU进行收货'));
		}
		
		if($post){
			$product_id = $get['product_id'];
			$itemData = [];
		
			if($product_id){
				$product_id = explode(',',trim($product_id,','));
				$itemData['id'] = $post['id'];
				foreach($product_id as $id){
					$itemData['items'][$id] = $post['items'][$id];
				}
			}else{
				$itemData = $post;
			}
			foreach($itemData['items'] as $rowData){
				if ($rowData['receive_qty'] < 0 || $rowData['receive_qty'] > $rowData['expectNum']) {
					throw new BizException(t_text('收货数量错误'));
				}
			}

			//重组数据，调用之前的上架接口
			$receiptInfo = PurchaseReceipt::find('id=?', $itemData['id'])->one();
			$boxIds = PurchaseReceiptBox::find('receipt_id=?', $receiptInfo->id)->column('id');
			$newItemData = [];
			foreach($itemData['items'] as $row){
				$boxItemList = PurchaseReceiptBoxItem::find('box_id in ? and product_id=?', $boxIds,$row['product_id'])->all(true,'id');
				foreach($boxItemList as $itemId=>$boxItem){
					$receive_qty = 0;
					if($row['receive_qty']>=0) {
						$row['receive_qty'] -= $boxItem['qty'];
						if ($row['receive_qty'] <= 0) {
							$receive_qty = $row['receive_qty']+$boxItem['qty'];
						} else {
							$receive_qty = $boxItem['qty'];
						}
					}
					$newItemData[$itemId] = [
						'item_id'=>$boxItem['id'],
						'product_id'=>$row['product_id'],
						'receive_qty'=> $receive_qty,
						'expectNum' => $boxItem['qty'],
					];
				}
			}
			PurchaseReceiptBoxItem::transaction(function()use($newItemData){
				$msg = $this->handleReceipt($newItemData);
				if ($msg) {
					throw new BizException($msg);
				}
			});
			return $this->getCommonResult(true);
		}
		
		
		$boxList = PurchaseReceiptBox::find('receipt_id=?', $id)->order('abs(no) asc')->all(true,'id');
		$boxItemIds = array_column($boxList, 'id');
		$boxItemList = PurchaseReceiptBoxItem::find('box_id in ?', $boxItemIds)->all(true,'id');
		$productIds = array_column($boxItemList, 'product_id');
		$productList = PrdProduct::find("id in ?",$productIds)->all(true,'id');
		//按SKU维度合并
		$data = [];
		foreach($boxItemList as $item){
			$data[$item['product_id']]['product_id'] 	= $item['product_id'];
			$data[$item['product_id']]['box_id'][] 		= $item['box_id'];
			$data[$item['product_id']]['sku'] 			= $productList[$item['product_id']]['sku'];
			$data[$item['product_id']]['name'] 			= $productList[$item['product_id']]['name'];
			$data[$item['product_id']]['ename'] 		= $productList[$item['product_id']]['ename'];
			$data[$item['product_id']]['qty'] 			+= $item['qty'];
			$data[$item['product_id']]['receive_qty'] 	+= $item['receive_qty'];
			$data[$item['product_id']]['boxDetail'][]   = $item['qty'];
			$data[$item['product_id']]['item_id'][]     = $item['id'];
			$data[$item['product_id']]['status']        = $boxList[$item['box_id']]['status'];
		}
		return array(
			'list'  => $data,
			'info'  => PurchaseReceipt::find('id=?', $id)->one(),
		);
	}
	
	/**
	 * @auth 收货
	 * @param $get
	 * @param $post
	 * @return array|\Lite\Core\Result
	 * @throws \Lite\Exception\BizException
	 * @throws \Lite\Exception\Exception
	 */
	public function receipt($get,$post)
	{
		$info = PurchaseReceiptBox::find('id=?', $get['id'])->one();
		if ($info->status == PurchaseReceiptBox::STATUS_RECEIVED) {
			throw new BizException("已收货");
		}
		$list = PurchaseReceiptBoxItem::find('box_id=?',  $get['id'])->all();
		
		if ($post) {
			$putData = $post['items'];
			PurchaseReceiptBoxItem::transaction(function()use($putData){
				$msg = $this->handleReceipt($putData);
				if ($msg) {
					throw new BizException($msg);
				}
			});
			return $this->getCommonResult(true);
		}
		$mode = true;
		if ($info->status == PurchaseReceiptBox::STATUS_RECEIVED) {
			$mode = false;
		}
		return array(
			'info' => $info,
			'list' => $list,
			'mode' => $mode
		);
	}
	
	/**
	 * 收货
	 * @param $putData
	 * @return string
	 * @throws \Exception
	 */
	private function handleReceipt($putData)
	{
		foreach ($putData as $item) {
			if ($item['receive_qty'] < 0 || $item['receive_qty'] > $item['expectNum']) {
				return '收货数量错误';
			}
			$boxItem = PurchaseReceiptBoxItem::find('id=?', $item['item_id'])->one();
			$boxItem->receive_qty = $item['receive_qty'];
			$boxItem->save();
		}
		return '';
	}
	
	/**
	 * @auth 批量收货完毕|确认收货
	 * @param $get
	 * @return \Lite\Core\Result
	 * @throws \Lite\Exception\BizException
	 * @throws \Lite\Exception\Exception
	 */
	public function batchReceiptDone($get)
	{
		set_time_limit(0);
		$idArr = $get['ids'];
		if(!is_array($idArr)){
			$idArr = explode(',', trim($get['ids'],','));
		}
		if (!T::getUniqTicket(self::UNIQUE_HASH_KEY_RECEIPT)) {//写入唯一hash值，处理多并发
			throw new BizException("Receipt done is processing");
		}
		try {
			PurchaseReceiptBox::transaction(function()use($idArr){
				//批量判断并改变箱或单状态
				$boxColl = PurchaseReceiptBox::find('id in ?', $idArr)->all();
				$boxIds = array();
				$receipt_id = null;
				foreach ($boxColl as $box) {
					if ($box->status == PurchaseReceiptBox::STATUS_RECEIVED) {
						throw new BizException(t_text('箱号'.$box->no . "不能重复操作"));
					}
					//默认按预报数量收货
					/**
					 * @var PurchaseReceiptBoxItem $i
					 */
					foreach($box->items as $i){
						if($i->receive_qty == 0){
							$i->receive_qty = $i->qty;
							$i->save();
						}
					}
					$boxIds[] = $box->id;
					$receipt_id = $box->receipt_id;
				}
				$boxStr = implode(',', $boxIds);
				$status_put = PurchaseReceiptBox::STATUS_RECEIVED;
				$date = date('Y-m-d');
				$sql_box = "update purchase_receipt_box set status = '{$status_put}',status_date='{$date}' WHERE id in ({$boxStr})";
				$db = PurchaseReceiptBox::setQuery($sql_box);
				$db->execute();
				$boxCount = PurchaseReceiptBox::find('receipt_id=? and status = ?', $receipt_id, PurchaseReceiptBox::STATUS_UNSETTLED)->count();
				if (!$boxCount) {
					$receipt = PurchaseReceipt::find('id=?', $receipt_id)->one();
					$receipt->status = PurchaseReceipt::STATUS_RECEIVED;
					$receipt->save();
					
					// 进入待质检
					$boxIds = PurchaseReceiptBox::find('receipt_id=?', $receipt_id)->column('id');
					$boxItems = PurchaseReceiptBoxItem::find('box_id in ?', $boxIds)->all();
					$product_list = [];
					foreach ($boxItems as $item) {
						$product_list[$item->product_id] += $item->receive_qty;
					}
					foreach ($product_list as $pid => $receive_qty) {
						$qt = new PurchaseReceiptQt();
						$qt->receipt_id = $receipt->id;
						$qt->product_id = $pid;
						$qt->save();
					}
					
				}
			});
			T::delUniqTicket(self::UNIQUE_HASH_KEY_RECEIPT);
			return $this->getCommonResult(true);
		} catch (\Exception $e) {
			//删除hash值
			T::delUniqTicket(self::UNIQUE_HASH_KEY_RECEIPT);
			throw new BizException($e->getMessage());
		}
	}
	
	/**
	 * @auth 质检(按SKU)
	 * @param $get
	 * @param $post
	 * @return array|\Lite\Core\Result
	 * @throws \Lite\Exception\Exception
	 */
	public function PressSKUQt($get,$post){
		$info = PurchaseReceipt::find('id=?', $get['id'])->oneOrFail();
		
		if($post){
			$item_id = $get['item_id'];
			$itemData = [];
		
			if($item_id){
				$itemData['items'][$item_id] = $post['items'][$item_id];
			}else{
				$itemData = $post;
			}

			$newItemData = $itemData['items'];
			PurchaseReceiptBoxItem::transaction(function()use($post,$newItemData,$info){
				$info->qt_note = $post['qt_note'];
				$info->save();
				$msg = $this->handleQt($newItemData);
				if ($msg) {
					throw new BizException($msg);
				}
			});
			return $this->getCommonResult(true);
		}
		
		$qtList = PurchaseReceiptQt::find('receipt_id = ?', $get['id'])->all();
		
		return array(
			'list'  => $qtList,
			'info'  => $info,
		);
	}
	
	/**
	 * 质检
	 * @param $putData
	 * @return string
	 * @throws \Exception
	 */
	private function handleQt($putData)
	{
		foreach ($putData as $item) {
			$boxItem = PurchaseReceiptQt::find('id=?', $item['item_id'])->one();
			$boxItem->good_qty = $item['good_qty'];
			$boxItem->bad_qty = $item['bad_qty'];
			$boxItem->note = $item['note'];
			$boxItem->save();
		}
		return '';
	}
	
	/**
	 * @auth 批量质检完毕|确认质检
	 * @param $get
	 * @return \Lite\Core\Result
	 * @throws \Lite\Exception\BizException
	 * @throws \Lite\Exception\Exception
	 */
	public function batchQtDone($get)
	{
		set_time_limit(0);
		$idArr = $get['ids'];
		if(!is_array($idArr)){
			$idArr = explode(',', trim($get['ids'],','));
		}
		if (!T::getUniqTicket(self::UNIQUE_HASH_KEY_QT)) {//写入唯一hash值，处理多并发
			throw new BizException("Qt done is processing");
		}
		try {
			PurchaseReceiptQt::transaction(function()use($idArr){
				//批量判断并改变箱或单状态
				$boxColl = PurchaseReceiptQt::find('id in ?', $idArr)->all();
				$boxIds = array();
				$receipt_id = null;
				foreach ($boxColl as $box) {
					if ($box->status == PurchaseReceiptQt::STATUS_QT) {
						throw new BizException(t_text("不能重复操作:".$box->product->sku));
					}
					$boxIds[] = $box->id;
					$receipt_id = $box->receipt_id;
				}
				$boxStr = implode(',', $boxIds);
				$status_qt = PurchaseReceiptQt::STATUS_QT;
				$sql_box = "update purchase_receipt_qt set status = '{$status_qt}' WHERE id in ({$boxStr})";
				$db = PurchaseReceiptQt::setQuery($sql_box);
				$db->execute();
				$boxCount = PurchaseReceiptQt::find('receipt_id=? and status = ?', $receipt_id, PurchaseReceiptQt::STATUS_UNSETTLED)->count();
				if (!$boxCount) {
					$receipt = PurchaseReceipt::find('id=?', $receipt_id)->one();
					$receipt->status = PurchaseReceipt::STATUS_CHECKED;
					$receipt->save();
					
					// 进入待上架
					$qt_list = PurchaseReceiptQt::find('receipt_id=?', $receipt_id)->all();
					foreach ($qt_list as $item) {
						if($item->good_qty){
							$put = new PurchaseReceiptPutGood();
							$put->receipt_id = $receipt->id;
							$put->product_id = $item->product_id;
							$put->save();
						}
						
						if($item->bad_qty){
							$put = new PurchaseReceiptPutBad();
							$put->receipt_id = $receipt->id;
							$put->product_id = $item->product_id;
							$put->save();
						}
					}
					
				}
			});
			T::delUniqTicket(self::UNIQUE_HASH_KEY_QT);
			return $this->getCommonResult(true);
		} catch (\Exception $e) {
			//删除hash值
			T::delUniqTicket(self::UNIQUE_HASH_KEY_QT);
			throw new BizException($e->getMessage());
		}
	}
	
	/**
	 * @auth 上架(按SKU)
	 * @param $get
	 * @param $post
	 * @return array|\Lite\Core\Result
	 * @throws \Lite\Exception\Exception
	 */
	public function PressSkuPut($get,$post){
		$info = PurchaseReceipt::find('id=?', $get['id'])->oneOrFail();
		$get['type'] = $get['type']?:Form::GOODS_TYPE_GOOD;
		$type = $get['type'];
		if($post){
			$item_id = $get['item_id'];
			$itemData = [];
			
			if($item_id){
				$itemData['items'][$item_id] = $post['items'][$item_id];
			}else{
				$itemData = $post;
			}
			
			$newItemData = $itemData['items'];
			PurchaseReceiptBoxItem::transaction(function()use($newItemData,$type){
				$msg = $this->handlePut($newItemData,$type);
				if ($msg) {
					throw new BizException($msg);
				}
			});
			return $this->getCommonResult(true);
		}
		switch($type){
			case Form::GOODS_TYPE_GOOD:
				$qtList = PurchaseReceiptPutGood::find('receipt_id = ?', $get['id'])->all();
				break;
			default:
				$qtList = PurchaseReceiptPutBad::find('receipt_id = ?', $get['id'])->all();
				break;
		}
		return array(
			'list'  => $qtList,
			'info'  => $info,
			'type'  => $type,
		);
	}
	
	
	/**
	 * 上架处理
	 */
	private function handlePut($putData,$goods_type)
	{
		foreach ($putData as $item) {
			if(!isset($item['put_qty'])){
				return "上架数量不能为空";
			}
			switch($goods_type){
				case Form::GOODS_TYPE_GOOD:
					$boxItem = PurchaseReceiptPutGood::find('id=?', $item['item_id'])->one();
					$boxItem->put_qty = array_sum($item['put_qty']);
					break;
				default:
					$boxItem = PurchaseReceiptPutBad::find('id=?', $item['item_id'])->one();
					$boxItem->put_qty = array_sum($item['put_qty']);
					break;
			}
			$boxItem->save();
			//现将之前上架的明细都删除
			WhProductLocationMapping::deleteWhere(0,"ref_id=? and ref_type=? and goods_type = ?", $item['item_id'], Form::TYPE_RO_IN, $goods_type);
			foreach ($item['location_code'] as $k => $val) {
				//判断货位是否存在
				$Wharr = explode("-", $val);
				if (count($Wharr) != 4) {
					return "目标货位【{$val}】错误";
				}
				$area = WhArea::find("code=?", $Wharr[0])->one();
				if (!$area->id) {
					return "目标货位【{$val}】错误";
				}
				$location = WhLocation::find(" area_id=?  and row_no =? and col_no =? and top_no =? and status=?", $area->id, intval($Wharr[1]), intval($Wharr[2]), intval($Wharr[3]),WhLocation::STATUS_ENABLED)->one();
				if (empty($location->id)) {
					return "目标货位【{$val}】不存在";
				}
				if ($location->is_mixed == WhLocation::IS_MIXED_NO) {
					$otherExits = WhProductLocation::find('product_id<>? and location_id=? and qty>0 and goods_type = ?', $item['product_id'], $location->id, $goods_type)->count();
					if ($otherExits) {
						$prd = PrdProduct::find('id=?', $item['product_id'])->one();
						return "单放货架不能放不同产品[{$prd['sku']}]";
					}
				}
				
				$mapping = new WhProductLocationMapping(array(
					'ref_id'      => $item['item_id'],
					'ref_mid'     => $item['receipt_id'],
					'qty'         => $item['put_qty'][$k],
					'location_id' => $location->id,
					'product_id'  => $item['product_id'],
					'ref_type'    => Form::TYPE_RO_IN,
					'goods_type'  => $goods_type,
				));
				$mapping->save();
			}
		}
		return '';
	}
	
	
	
	/**
	 * @auth 批量上架
	 * @param $get
	 * @return \Lite\Core\Result
	 * @throws \Lite\Exception\BizException
	 * @throws \Lite\Exception\Exception
	 */
	public function BatchPutDone($get)
	{
		set_time_limit(0);
		$idArr = $get['ids'];
		if(!is_array($idArr)){
			$idArr = explode(',', trim($get['ids'],','));
		}
		$type = $get['type']?:Form::GOODS_TYPE_GOOD;
		switch($type){
			case Form::GOODS_TYPE_GOOD:
				$boxItems = PurchaseReceiptPutGood::find('id in ?', $idArr)->all();
				break;
			default:
				$boxItems = PurchaseReceiptPutBad::find('id in ?', $idArr)->all();
				break;
		}
		
		if (!T::getUniqTicket(self::UNIQUE_HASH_KEY_PUT)) {//写入唯一hash值，处理多并发
			throw new BizException("Put done is processing");
		}
		try {
			PurchaseReceiptBox::transaction(function()use($boxItems,$idArr,$type){
				//处理箱明细
				/**
				 * @var PurchaseReceiptPutGood $item
				 */
				foreach ($boxItems as $item) {
					if ($item->status == PurchaseReceiptPutGood::STATUS_PUT) {
						throw new BizException(t_text('产品'.$item->product->sku . "不能重复操作"));
					}
					$puts = WhProductLocationMapping::find('ref_id=? and ref_type=? and goods_type = ?', $item->id, WhProductLocationMapping::REF_TYPE_RO_IN,$type)->all();
					$real_put = $putQty = 0;
					foreach ($puts as $pt) {
						if (!$pt->location_id) {
							throw new BizException(t_text("未选择货架的的不能完毕"));
						}
						if ($pt->location->is_mixed == WhLocation::IS_MIXED_NO) {
							//todo 不良品 可以跟良品放在一起?
							$otherExits = WhProductLocation::find('product_id<>? and location_id=? and goods_type = ?', $pt->product_id, $pt->location_id,$type)->count();
							if ($otherExits) {
								//提示时把具体货架呈现
								$pro = PrdProduct::find('id=?', $pt->product_id)->one();
								throw new BizException(t_text("单放货架不能放不同产品[".$pro['sku']."]"));
							}
						}
						$real_put += $pt->qty;
						if ($pt->qty > 0) {
							InventoryWrap::setGoodsType($type);
							InventoryWrap::putOn($pt->product_id, $pt->location_id, $pt->qty, $item);
						}
						$putQty += $pt->qty;
					}
					if($putQty>0){
						InventoryWrap::createInventoryRo($item->receipt->id,$item->product_id,$putQty,date('Y-m-d'),WhInventoryRo::REF_TYPE_RO_IN,$item->receipt->receipt_no);
					}
				}
				
				//批量判断并改变箱或单状态
				$boxStr = implode(',', $idArr);
				$status_put = PurchaseReceiptPutGood::STATUS_PUT;
				$table = $type== Form::GOODS_TYPE_GOOD?'purchase_receipt_put_good':'purchase_receipt_put_bad';
				$sql_box = "update {$table} set status = '{$status_put}' WHERE id in ({$boxStr})";
				$db = PurchaseReceiptPutGood::setQuery($sql_box);
				$db->execute();
				$goodCount = PurchaseReceiptPutGood::find('receipt_id=? and status = ?', $item->receipt->id, PurchaseReceiptPutGood::STATUS_UNSETTLED)->count();
				$badCount = PurchaseReceiptPutBad::find('receipt_id=? and status = ?', $item->receipt->id, PurchaseReceiptPutBad::STATUS_UNSETTLED)->count();
				if (!$goodCount and !$badCount) {
					$receipt = PurchaseReceipt::find('id=?', $item->receipt->id)->one();
					$receipt->status = PurchaseReceipt::STATUS_FINISHED;
					$receipt->save();
				}
			});
			T::delUniqTicket(self::UNIQUE_HASH_KEY_PUT);
			return $this->getCommonResult(true);
		} catch (\Exception $e) {
			//删除hash值
			T::delUniqTicket(self::UNIQUE_HASH_KEY_PUT);
			throw new BizException($e->getMessage());
		}
	}
}


