<?php
namespace ttwms\controller\wh;


use Lite\Component\Paginate;
use Temtop_Helper_Excel;
use ttwms\business\Form;
use ttwms\controller\BaseController;
use ttwms\model\PrdProduct;
use ttwms\model\WhInventoryRecord;
use function Lite\func\array_trim;
use function Temtop\t;


/**
 * @auth 仓库管理/库存流水
 */
class InventoryRecordController extends BaseController{
	
	
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
		$select = WhInventoryRecord::find()->order(" id desc")->whereOnSet('goods_type = ?',$search['goods_type']);
		if(strlen(trim($search['sku'])) || strlen($search['enterprise_id'])){
			$select_pro = PrdProduct::find();
			//sku
			if(strlen(trim($search['sku']))){
				$select_pro->where("sku like ? ", "%".trim($search['sku'])."%");
			}
			//仓租用户
			if(strlen($search['enterprise_id'])){
				$select_pro->where("enterprise_id = ?", $search['enterprise_id']);
			}
			
			$products = $select_pro->column('id');
			if(empty($products)){
				$select->where("1=2");
			} else{
				$select->where("product_id in ?", $products);
			}
		}
		if(strlen($search['ref_type'])){
			$select->where("ref_type =?", $search['ref_type']);
		}
		if(strlen(trim($search['tag_no']))){
			$select->where("tag_no =?", trim($search['tag_no']));
		}
		if(strlen($search['add_time_start'])){
			$select->where("create_time >=?", $search['add_time_start']);
		}
		if(strlen($search['add_time_end'])){
			$select->where("create_time <=?", $search['add_time_end']." 23:59:59");
		}
		//导出
		if(strlen($search['export'])){
			set_time_limit(0);
			$page =0;
			$pageSize = 50;
			$title = t("库存流水表").".csv";
			$header = array(
				'code'        => t('客户代码'),
				'sku'         => t('SKU'),
				'name'        => t('中文名称'),
				'ename'       => t('英文名称'),
				'no'          => t('单据号'),
				'ref_type'    => t('类型'),
				'qty'         => t('操作数量'),
				'qty_balance' => t('库内库存'),
				'add_time'    => t('操作时间'),
			);
			while(true){
				$list = $select->limit($page*$pageSize, $pageSize)->all();
				if(!count($list)){
					break;
				}
				$data = array();
				foreach($list as $k => $v){
					$data[] = array(
						'code'                  =>$v->product->enterprise_code,
						'sku'                   =>$v->product->sku,
						'name'                  =>$v->product->name,
						'ename'                 =>$v->product->ename,
						'no'                    =>$v->no,
						'ref_type'              =>Form::$typeList[$v->ref_type],
						'qty'                   =>$v->qty,
						'qty_balance'           =>$v->qty_balance,
						'add_time'              =>$v->create_time,
					);
				}
				$page++;
				Temtop_Helper_Excel::exportCSVChunk($data, $header, $title);
			}
			exit;
		}
		$list = $select->paginate($paginate);
		return array(
			'list'       => $list,
			'param'      => $search,
			'pagination' => $paginate,
		);
	}
	
}