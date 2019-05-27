<?php

namespace ttwms\controller\user;

use Lite\Component\Paginate;
use Lite\Core\Result;
use Lite\Core\Router;
use Lite\Exception\BizException;
use ttwms\controller\BaseController;
use ttwms\model\SysAccess;
use ttwms\model\SysRole;
use ttwms\model\SysRoleAuth;
use function Lite\func\array_trim;


/**
 * @auth 角色配置/角色管理
 * Class RoleController
 * @package temtopsys\wsm\controller
 */
class RoleController extends BaseController{
	
	/**
	 * @auth 列表
	 * @param $search
	 * @param array $post
	 * @return array
	 * @throws \Lite\Exception\Exception
	 */
	public function index($search, $post = []){
		$search = array_trim($search);
		$paginate = Paginate::instance();
		$select = SysRole::find()
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
	 * @param $post
	 * @return array|\Lite\Core\Result
	 * @throws \Lite\Exception\BizException
	 * @throws \Lite\Exception\Exception
	 * @throws \Lite\Exception\RouterException
	 */
	public function add($get,$post){
		$role =$get['id']? SysRole::find("id =?",$get['id'])->oneOrFail(): new SysRole();
		if($post){
			$post = array_trim($post);
			$exist = SysRole::find("name =?",$post['name'])->whereOnSet("id<>?",$role->id)->one();
			if($exist->id){
				throw new BizException("该角色已存在");
			}
			$role->setValues([
				"name"        => $post['name'],
				"type"        => $post['type'],
				"description" => $post['description'],
			]);
			$role->save();
			return $this->getCommonResult(true);
		}
		return array(
			"get" => $get,
			"role" => $role
		);
	}
	
	/**
	 * @auth 设置权限
	 * @param $get
	 * @param $post
	 * @return array|\Lite\Core\Result
	 * @throws \Lite\Exception\Exception
	 * @throws null
	 */
	public function updateAccess($get, $post){
		$act_list = SysAccess::getAccessList();
		$menu_list = SysAccess::getAccessMenuList();
		if($post){
			if(empty($post['role_id'])){
				throw new BizException('参数错误');
			}
			SysRoleAuth::transaction(function() use ($post, $act_list, $menu_list){
				SysRoleAuth::deleteWhere(0, 'role_id=?',$post['role_id']);
				$data = array();
				$tmp = array();
				if($post['ids']){
					foreach($post['ids'] as $name){
						if(!$menu_list[$name]){
							continue;
						}
						foreach($menu_list[$name] as $uri){
							$tmp[] = $uri;
							$data[] = array(
								'role_id' => $post['role_id'],
								'uri'       => $uri,
							);
						}
					}
				}
				
				if($data){
					SysRoleAuth::insertMany($data);
				}
			});
			return new Result('操作成功', true, null, Router::getUrl('user/role/index'));
		}
		$w_user_group_values = array();
		$user_group_values = SysRoleAuth::getAuthDataByRole($get['role_id']);
		foreach($user_group_values as $val){
			$name = $act_list[$val['uri']]['name'];
			if(!$w_user_group_values[$name]){
				$w_user_group_values[$name] = in_array($val['uri'],array_keys($act_list));
			}
		}
		
		$auth_list = array();
		foreach($menu_list as $name => $uri){
			$a = $this->convertPathToArray('全部/'.$name, array($name, $w_user_group_values[$name]));
			$auth_list = array_merge_recursive($auth_list, $a);
		}
		
		return array(
			"get"       => $get,
			'auth_list' => $auth_list,
		);
	}
	
	/**
	 * @param $path
	 * @param array $bind_data
	 * @param string $delimiter
	 * @return array
	 */
	private function convertPathToArray($path, $bind_data = array(), $delimiter = '/'){
		$ps = explode($delimiter, $path);
		
		$ret = array();
		$k = array_shift($ps);
		if(count($ps) > 1){
			$ret[$k] = $this->convertPathToArray(join($delimiter, $ps), $bind_data, $delimiter);
		} else {
			$ret[$k][$ps[0]] = $bind_data;
		}
		return $ret;
	}
}