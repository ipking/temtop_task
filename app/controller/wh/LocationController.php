<?php
/**
 * Created by PhpStorm.
 * Date: 2019-01-23
 * Time: 16:51
 */

namespace ttwms\controller\wh;

use Lite\Component\Paginate;
use Lite\Core\Router;
use Lite\Exception\BizException;
use ttwms\controller\BaseController;
use ttwms\model\WhArea;
use ttwms\model\WhLocation;
use function Lite\func\array_trim;
use function Temtop\t;

/**
 * @auth 仓库设置/货位管理
 * Date: 2019-01-22
 * Time: 14:28
 */
class LocationController extends BaseController{
	/**
	 * @auth 列表|查看
	 * @param $search
	 * @param array $post
	 * @return array
	 * @throws \Lite\Exception\Exception
	 */
	public function index($search, $post = []){
		$search = array_trim($search);
		$paginate = Paginate::instance();
		$select = WhLocation::find()
			->whereOnSet('is_mixed = ?',$search['is_mixed'])
			->whereOnSet('user_id = ?',$search['user_id'])
			->whereOnSet('update_user_id = ?',$search['update_user_id'])
			->whereOnSet('status = ?',$search['status'])
			->whereOnSet('area_id = ?',$search['area_id'])
			->whereOnSet('row_no = ?',$search['row_no'])
			->whereOnSet('col_no = ?',$search['col_no'])
			->whereOnSet('top_no = ?',$search['top_no'])
			->order('id desc');
		$list = $select->paginate($paginate);
		return [
			'paginate'     => $paginate,
			'list'         => $list,
			'search'       => $search
		];
	}
	
	/**
	 * @actionGroup 查看 @actionGroupEnd
	 * 简单的查看-用户显示仓库的列表等
	 */
	public function SimpleView($param)
	{
		$param = array_trim($param);
		$select = WhLocation::find()->order('id desc');
		$paginate = Paginate::instance();
		$area = WhArea::find('status=?', WhArea::STATUS_ENABLED)->column('id');
		//可用货位
		if (count($area)) {
			$select->where('area_id in ?', $area);
		} else {
			$select->where('1=2');
		}
		$select->where("status=?",WhLocation::STATUS_ENABLED);  //排除停用--要启用的
	
		//库区
		if (strlen($param['select_block'])) {
			$select->where("area_id=?", $param['select_block']);
		}
		//是否自动分配
		if (strlen($param['auto_allot'])) {
			$select->where("is_auto_allot=?", $param['auto_allot']);
		}
		//是否自动回收
		if (strlen($param['garbage_clean'])) {
			$select->where("is_garbage_clean=?", $param['garbage_clean']);
		}
		//--是否为空--
		if (strlen($param['empty'])) {
			$select->where("is_auto_allot=?", $param['empty']);
		}
		//创建人/修改人
		if (strlen($param['user_id'])) {
			$select->where("user_id=? or update_user_id=?", $param['user_id'], $param['user_id']);
		}
		//货位号-层
		if (strlen($param['code_top'])) {
			$select->where("top_no=?", $param['code_top']);
		}
		//货位号-行
		if (strlen($param['code_row'])) {
			$select->where("row_no=?", $param['code_row']);
		}
		//货位号-列
		if (strlen($param['code_col'])) {
			$select->where("col_no=?", $param['code_col']);
		}
		//code
		if (strlen($param['code'])) {
			$_code = $param['code'];
			$_codeInfo = explode("-", $_code);
			if (count($_codeInfo) != 4) {
				
				$select->where("1=2");
			} else {
				$_area_id = WhArea::find("code=?", $_codeInfo[0])->column('id');
				if (empty($_area_id[0])) {
					$select->where("1=2");
				} else {
					$select->where(" area_id=? and tbl_no=?  and row_no=? and col_no=?", $_area_id[0], intval($_codeInfo[1]), intval($_codeInfo[2]), intval($_codeInfo[3]));
				}
			}
		}
		if ($kw = $param['kw']) {
			$field = $param['for'];
			$select->where("{$field} like ?", "%{$kw}%");
		}
		$list = $select->paginate($paginate);
		return array(
			'list'       => $list,
			'pagination' => $paginate,
			'param'      => $param
		);
		
	}
	
	
	/**
	 * @auth 添加|编辑
	 * @param $get
	 * @param array $post
	 * @return array|\Lite\Core\Result
	 * @throws \Lite\Exception\BizException
	 * @throws \Lite\Exception\Exception
	 * @throws \Lite\Exception\RouterException
	 */
	public function add($get, $post = []){
		$info =$get['id']? WhLocation::find("id =?",$get['id'])->oneOrFail(): new WhLocation();
		if($post){
			$post['info'] = array_trim($post['info']);
			$info->setValues($post['info']);
			$info->save();
			return $this->getCommonResult(true);
		}
		return array(
			"get" => $get,
			"info" => $info
		);
	}
	
	/**
	 * @auth 添加|编辑
	 * @param $get
	 * @param array $post
	 * @return array
	 */
	public function addBatch($get, $post)
	{
		if ($post) {
			WhLocation::transaction(function()use($post){
				$post['info'] = array_trim($post['info']);
				$post['batch'] = array_trim($post['batch']);
				//主信息
				$data = $this->_filter_insert_data($post['info']);
				//批量信息
				$batch = $this->_filter_insert_data_batch($post['batch']);
				
				//批量创建货位
				//第 row_index 行 开始，共有row_numbers 行
				for($y = $batch['row_index']; $y<$batch['row_numbers']+$batch['row_index']; $y++){
					//第col_index 列开始      共有col_numbers列
					for($x = $batch['col_index']; $x<$batch['col_numbers']+$batch['col_index']; $x++){
						//第top_index层开始，共有top_numbers排
						for($i = $batch['top_index']; $i<$batch['top_numbers']+$batch['top_index']; $i++){
							
							$_data = $data;
							$_data['top_no'] = $i;
							$_data['col_no'] = $x;
							$_data['row_no'] = $y;
							
							$this->_check_code_exist($_data);
							$_location = new WhLocation();
							$_location->setValues($_data);
							$_location->save();
						}
					}
				}
			});
			return $this->getCommonResult(true);
		}
	}
	
	/**
	 * @auth 批量修改
	 * @param $get
	 * @param $post
	 * @return array
	 */
	public function batch($get,$post)
	{
		if ($post) {
			$ids = $get['ids'];
			$post['info'] = array_trim($post['info']);
			//主信息
			$data = $this->_filter_insert_data($post['info']);
		
			//批量更改信息
			foreach ($ids as $id) {
				$location = WhLocation::find("id=?", $id)->one();
				$location->setValues($data);
				$location->save();
			}
			return $this->getCommonResult(true);
			
		}
		return array(
			"get" => $get
		);
		
	}
	
	/**
	 * @auth 打印
	 * @param $get
	 * @return array
	 */
	public function toPrint($get)
	{
		$ids = $get['ids'];
		if (empty($ids)) {
			throw new BizException(t("没有选择提交项!"));
		}
		//查出选中的barcode
		$locations = WhLocation::find("id in ?", $ids)->all();
		return array("locations" => $locations);
	}
	
	/**
	 * @auth 打印
	 * @param $get
	 * @return array
	 */
	public function doPrint($get)
	{
		$counts = (array)$get['bars'];
		if (!count($counts)) {
			throw new BizException(t("参数错误"));
		}
		$list = WhLocation::find("id in ?", array_keys($counts))->all();
		return array(
			'list'   => $list,
			'counts' => $counts
		);
	}
	
	/**
	 * 数据插入前处理过滤
	 * @param array $data
	 * @return array
	 * @throws BizException
	 */
	protected function _filter_insert_data($data)
	{
		if (empty($data['area_id'])) {
			throw new BizException(t("没有设置库区"));
		}
		$area = WhArea::find("id=?", $data['area_id'])->one();
		if (!$area->id) {
			throw new BizException(t("库区不存在"));
		}
		
		if (isset($data['row_no']) && empty($data['row_no'])) {
			throw new BizException(t("没有设置货架行号"));
		}
		if (isset($data['col_no']) && empty($data['col_no'])) {
			throw new BizException(t("没有设置货架列号"));
		}
		if (isset($data['top_no']) && empty($data['top_no'])) {
			throw new BizException(t("没有设置货架层号"));
		}
		if (isset($data['length']) && empty($data['length'])) {
			throw new BizException(t("没有设置长"));
		}
		if (isset($data['width']) && empty($data['width'])) {
			throw new BizException(t("没有设置宽"));
		}
		if (isset($data['height']) && empty($data['height'])) {
			throw new BizException(t("没有设置高"));
		}
		if (isset($data['max_pcs']) && empty($data['max_pcs'])) {
			throw new BizException(t("没有设置最大单位"));
		}
		if (isset($data['max_weight']) && empty($data['max_weight'])) {
			throw new BizException(t("没有设置最大承重"));
		}
		
		$this->_check_code_exist($data);
		return $data;
	}
	
	/**
	 * 在更新前对数据过滤
	 * @param array $data
	 * @return array
	 */
	protected function _filter_update_data($data)
	{
		return $this->_filter_insert_data($data);
	}
	
	/**
	 * 检查货位code是否存在，保持唯一
	 * @param array $data
	 * @throws BizException
	 */
	protected function _check_code_exist($data)
	{
		$id = Router::post('id');
		$_codeParam = $data['area_id'] . $data['top_no'] . $data['col_no'] . $data['row_no'];
		if (empty($data['top_no']) || empty($data['col_no']) || empty($data['row_no'])) {
			return;
		}
		if ($id) {
			$location = WhLocation::find("id=?", $id)->one();
			if ($location->id) {
				$_codeData =  $location->area_id . $location->top_no . $location->col_no . $location->row_no;
				if ($_codeData == $_codeParam) { //没有变化
					return;
				}
			}
		}
		$locationCheck = WhLocation::find(" area_id=? and top_no=? and col_no=? and row_no=?", $data['area_id'], $data['top_no'], $data['col_no'], $data['row_no'])->count();
		if ($locationCheck) {
			throw new BizException("当前区域下已经存在[货架行号:" . $data['col_no'] . "]-[列号:" . $data['row_no'] . "]-[层号:" . $data['top_no'] . "] 的货位");
		}
		
		if ($data['row_no'] > 999) {
			throw new BizException(t("货架行号最大999"));
		}
		if ($data['col_no'] > 99) {
			throw new BizException(t("货架列号最大99"));
		}
		if ($data['top_no'] > 99) {
			throw new BizException(t("货架层号最大99"));
		}
	}
	
	/**
	 * 批量数据插入过滤
	 * @param array $data
	 * @return array
	 * @throws BizException
	 */
	protected function _filter_insert_data_batch($data)
	{
		if (isset($data['row_index']) && empty($data['row_index'])) {
			throw new BizException(t("没有设置排号"));
		}
		if (isset($data['row_numbers']) && empty($data['row_numbers'])) {
			throw new BizException(t("没有设置排数"));
		}
		if (isset($data['col_index']) && empty($data['col_index'])) {
			throw new BizException(t("没有设置起始列号"));
		}
		if (isset($data['col_numbers']) && empty($data['col_numbers'])) {
			throw new BizException(t("没有设置列数"));
		}
		if (isset($data['top_index']) && empty($data['top_index'])) {
			throw new BizException(t("没有设置起始层号"));
		}
		if (isset($data['top_numbers']) && empty($data['top_numbers'])) {
			throw new BizException(t("没有设置层数"));
		}
		return $data;
	}
}