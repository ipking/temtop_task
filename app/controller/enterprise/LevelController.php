<?php
/**
 * Created by PhpStorm.
 * Date: 2019-01-23
 * Time: 16:51
 */

namespace ttwms\controller\enterprise;

use Lite\Component\Paginate;
use ttwms\controller\BaseController;
use ttwms\model\EnterpriseLevel;
use function Lite\func\array_trim;

/**
 * @auth 用户管理/用户级别管理
 * Date: 2019-01-22
 * Time: 14:28
 */
class LevelController extends BaseController{
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
		$select = EnterpriseLevel::find('status = ?',EnterpriseLevel::STATUS_ENABLED)
			->whereLikeOnSetBatch(['name'],"%{$search['kw']}%");
		$list = $select->paginate($paginate);
		return [
			'paginate'     => $paginate,
			'list'         => $list,
			'search'       => $search
		];
	}
	
	/**
	 * @auth 添加|编辑
	 * @param $get
	 * @param array $post
	 * @return array
	 * @throws \Lite\Exception\Exception
	 */
	public function add($get, $post = []){
		$level =$get['id']? EnterpriseLevel::find("id =?",$get['id'])->oneOrFail(): new EnterpriseLevel();
		if($post){
			$post = array_trim($post);
			$level->name = $post['name'];
			$level->save();
			return $this->getCommonResult(true);
		}
		return array(
			"get" => $get,
			"level" => $level
		);
	}
	
	/**
	 * @auth 删除
	 * @param $get
	 * @return array|\Lite\Core\Result
	 */
	public function delete($get){
		$level = EnterpriseLevel::find("id =?",$get['id'])->one();
		$level->status = EnterpriseLevel::STATUS_DISABLED;
		$level->save();
		return $this->getCommonResult(true);
	}
}