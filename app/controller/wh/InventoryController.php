<?php
namespace ttwms\controller\wh;


use Lite\Component\Paginate;
use Temtop_Helper_Excel;
use ttwms\controller\BaseController;
use ttwms\model\PrdProduct;
use ttwms\model\WhInventory;
use function Lite\func\array_trim;
use function Temtop\t;


/**
 * @auth 仓库管理/库存查询
 */
class InventoryController extends BaseController{
	
	
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
		$search['goods_type'] = $search['goods_type']?:WhInventory::GOODS_TYPE_GOOD;
		$select = WhInventory::find()->whereOnSet('goods_type = ?',$search['goods_type']);
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
        //导出
        if(strlen($search['export'])){
            set_time_limit(0);
            $page =0;
            $pageSize = 50;
            $title = t('库存表').".csv";
            $header = array(
	            'sku'        => t('SKU'),
	            'name'       => t('中文名称'),
	            'ename'      => t('英文名称'),
	            'code'       => t('客户代码'),
	            'total_qty'  => t('总库存'),
	            'qty'        => t('可用数量'),
	            'frozen_qty' => t('待出库数量'),
            );
            while(true){
                $list = $select->limit($page*$pageSize, $pageSize)->all();
                if(!count($list)){
                    break;
                }
                $data = array();
                foreach($list as $k => $v){
                    $data[] = array(
	                    'sku'        => $v->product->sku,
	                    'name'       => $v->product->name,
	                    'ename'      => $v->product->ename,
	                    'code'       => $v->product->enterprise->code,
	                    'total_qty'  => $v->qty+$v->frozen_qty,
	                    'qty'        => $v->qty,
	                    'frozen_qty' => $v->frozen_qty,
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
			'pagination' => $paginate
		);
	}
	
}