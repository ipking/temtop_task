<?php
namespace ttwms\controller\wh;


use Lite\Component\Paginate;
use ttwms\controller\BaseController;
use ttwms\model\PrdProduct;
use ttwms\model\WhArea;
use ttwms\model\WhLocation;
use function Lite\func\array_trim;


/**
 * @auth 仓库管理/货位查询
 */
class ProductLocationController extends BaseController{
	
	
	/**
	 * @auth 列表|查看
	 * @param $search
	 * @param array $post
	 * @return array
	 * @throws \Lite\Exception\Exception
	 */
	public function Index($search, $post = []){
		$search = array_trim($search);
		$paginate = Paginate::instance();
		//货位
		$select = WhLocation::find()->order('wh_location.id desc');
		$select->select('wh_location.id,wh_location.row_no,wh_location.top_no,wh_location.row_no,wh_location.area_id,wh_product_location.id as wpl_id,wh_product_location.product_id,wh_product_location.qty,wh_product_location.frozen_qty,wh_product_location.is_check,wh_product_location.goods_type');
		//有值的货位
		$select->leftJoin("wh_product_location","wh_product_location.location_id=wh_location.id");
		//仓库
		if(strlen($search['select_wh_id'])){
			$select->where("wh_location.wh_id=?", $search['select_wh_id']);
		}
		
		//库区
		if(strlen($search['select_block'])){
			$select->where("wh_location.area_id=?", $search['select_block']);
		}
		//货位号-层
		if(strlen($search['code_top'])){
			$select->where("wh_location.top_no=?", $search['code_top']);
		}
		//货位号-行
		if(strlen($search['code_row'])){
			$select->where("wh_location.row_no=?", $search['code_row']);
		}
		//货位号-列
		if(strlen($search['code_col'])){
			$select->where("wh_location.col_no=?", $search['code_col']);
		}
		$select->whereOnSet('wh_product_location.goods_type = ?',$search['goods_type']);
		
		//存放数量是否为零
		if(strlen($search['qty'])){
			$select->where('wh_product_location.qty <= ? and wh_product_location.qty >0 and wh_location.status = 1',(int)$search['qty']);
		}
		//货位号-all
		if(strlen(trim($search['code']))){
			$search['code'] = trim($search['code']);
			$_code = $search['code'];
			$_codeInfo = explode("-", $_code);
			if(count($_codeInfo) != 4){
				$select->where("1=2");
			} else{
				$AreaId = WhArea::find("code=?", $_codeInfo[0])->column('id');
				if(empty($AreaId[0])){
					$select->where("1=2");
				} else{
					$select->where(" wh_location.area_id=?  wh_location.and wh_location.row_no=? and wh_location.col_no=?  and wh_location.top_no=?", $AreaId[0], $_codeInfo[1], $_codeInfo[2], $_codeInfo[3]);
				}
			}
		}
		
		if($search['sku'] or $search['enterprise_id']){
			$productSelect =  PrdProduct::find();
			$productSelect->whereOnSet("sku=?",$search['sku']);
			$_ids = $productSelect->whereOnSet('enterprise_id=?',$search['enterprise_id'])->column('id');
			if(empty($_ids)){
				$select->where("1=2");
			} else{
				$select->where("wh_product_location.product_id in ?", $_ids);
			}
		}
		
		$list = $select->paginate($paginate,true);
		$listNew = [];
		//找到对象封装起来
		foreach($list as $data){
			$listNew[] = array(
				'location' => WhLocation::find("id=?", $data['id'])->one(),
				'product'  => PrdProduct::find("id=?", $data['product_id'])->one(),
				'info'     => $data,
			);
		}
		return array(
			'list'       => $listNew,
			'param'      => $search,
			'pagination' => $paginate,
		);
	}
	
}