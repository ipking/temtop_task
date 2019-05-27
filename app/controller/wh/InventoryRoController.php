<?php
namespace ttwms\controller\wh;


use Lite\Component\Paginate;
use Temtop_Helper_Excel;
use ttwms\business\Form;
use ttwms\controller\BaseController;
use ttwms\model\PrdProduct;
use ttwms\model\WhInventoryRo;
use ttwms\model\WhInventoryRoLog;
use ttwms\ViewBase;
use function Lite\func\array_trim;
use function Temtop\t;


/**
 * @auth 仓库管理/批次库存
 */
class InventoryRoController extends BaseController{
	
	
	/**
	 * @auth 列表|查看
	 * @param $search
	 */
	public function Index($search, $post = []){
		ViewBase::setOrderConfig([
			'id',
			'put_on_date',
		], 'id', 'desc');
		
		$search = array_trim($search);
		$paginate = Paginate::instance();
		$select = WhInventoryRo::find('remain_qty>0')->order(ViewBase::getCurrentOrderSet())->whereOnSet('goods_type = ?',$search['goods_type']);
		
		if($search['sku'] or  $search['enterprise_id']){
			$select_pro = PrdProduct::find();
			//sku
			$select_pro->whereOnSet("sku like ? ", "%" . $search['sku'] . "%");
			//仓租用户
			$select_pro->whereOnSet("enterprise_id = ?", $search['enterprise_id']);
			$products = $select_pro->column('id');
			if (empty($products)) {
				$select->where("1=2");
			} else {
				$select->where("product_id in ?", $products);
			}
		}
		
		if ($search['put_on_day'] > 0) {
			$select->where('put_on_date<=?', date('Y-m-d', strtotime("-{$search['put_on_day']} day")));
		}
		$select->whereOnSet('ref_type=?', $search['ref_type']);
		//导出
		if (strlen($search['export'])) {
			set_time_limit(0);
			$page = 0;
			$pageSize = 50;
			$title = t("在库库龄").".csv";
			$header = array(
				'sku'           => 'SKU',
				'remain_qty'    => t('剩余数量'),
				'put_on_time'   => t('上架日期'),
				'store_days'    => t('库龄'),
				'no'            => t('单据号'),
				'ref_type'      => t('操作类型'),
			);
			while (true) {
				$list = $select->limit($page * $pageSize, $pageSize)->all();
				if (!count($list)) {
					break;
				}
				$data = array();
				foreach ($list as $k => $row) {
					$data[] = array(
						'sku'         => $row->product->sku,
						'remain_qty'  => $row->remain_qty,
						'put_on_time' => $row->put_on_date,
						'store_days'  => $row->age,
						'no'          => $row->ref_code,
						'ref_type'    => Form::$typeList[$row->ref_type],
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
	
	/**
	 * @auth 查看日志
	 * @param $get
	 */
	public function Log($get)
	{
		WhInventoryRo::find("id=? ", $get['id'])->oneOrFail();
		$paginate = Paginate::instance();
		$list = WhInventoryRoLog::find("inventory_ro_id=?", $get['id'])->order('id desc')->paginate($paginate);
		return array(
			'list'       => $list,
			'pagination' => $paginate,
			'param'      => $get,
		);
	}
}