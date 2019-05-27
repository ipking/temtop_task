<?php
/**
 * Created by PhpStorm.
 * Date: 2019-01-23
 * Time: 16:51
 */

namespace ttwms\controller\wh;

use Lite\Component\Paginate;
use ttwms\controller\BaseController;
use ttwms\CurrentUser;
use ttwms\model\WhArea;
use function Lite\func\array_trim;

/**
 * @auth 仓库设置/库区管理
 * Date: 2019-01-22
 * Time: 14:28
 */
class AreaController extends BaseController{
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
		$select = WhArea::find()
		->whereOnSet('user_id = ?',$search['user_id'])
		->whereOnSet('update_user_id = ?',$search['update_user_id'])
		->whereLikeOnSetBatch(['code','name'],"%{$search['kw']}%")
		->order('seq desc');
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
		$area =$get['id']? WhArea::find("id =?",$get['id'])->oneOrFail(): new WhArea();
		if($post){
			$post = array_trim($post);
			$area->setValues($post);
			$area->user_id = $area->user_id?:CurrentUser::getUserId();
			$area->update_user_id = CurrentUser::getUserId();
			$area->save();
			return $this->getCommonResult(true);
		}
		return array(
			"get" => $get,
			"area" => $area
		);
	}
}