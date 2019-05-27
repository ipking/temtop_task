<?php
/**
 * Created by PhpStorm.
 * Date: 2019-01-23
 * Time: 16:51
 */

namespace ttwms\controller\user;

use Lite\Component\Paginate;
use Lite\Core\Result;
use Lite\Exception\BizException;
use ttwms\controller\BaseController;
use ttwms\CurrentUser;
use ttwms\model\SysUser;
use function Lite\func\array_trim;

/**
 * @auth 角色配置/员工管理
 * Date: 2019-01-22
 * Time: 14:28
 */
class UserController extends BaseController{
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
		$select = SysUser::find('account != ?','root')
		->whereOnSet("status=?",$search['status'])
		->whereOnSet("role_id =? ",$search['role_id'])
		->whereLikeOnSetBatch(['account','name'],"%{$search['kw']}%");
		$list = $select->paginate($paginate);
		return [
			'paginate'     => $paginate,
			'list'         => $list,
			'search'       => $search
		];
	}
	
	/**
	 * @param $get
	 * @param $post
	 * @return array|\Lite\Core\Result
	 * @throws \Lite\Exception\BizException
	 * @throws \Lite\Exception\Exception
	 * @throws \Lite\Exception\RouterException
	 */
	public function add($get,$post){
		$user =$get['id']? SysUser::find("id =?",$get['id'])->oneOrFail(): new SysUser();
		//不允许编辑 root
		if($user->account == 'root'){
			throw new BizException("不允许编辑该账户!");
		}
		if($post){
			$post = array_trim($post);
			$existUser = SysUser::find("account =?",$post['account'])->whereOnSet("id<>?",$user->id)->one();
			if($existUser->id){
				throw new BizException("该登录账号已存在");
			}
			$user->setValues([
				"account" => $post['account'],
				"name"    => $post['name'],
				"email"   => $post['email'],
				"status"  => $post['status'],
				"role_id" => $post['role_id'],
			]);
			//新增用户时需要用户密码
			if(!$user->id){
				$user->setValue("password",SysUser::getPassWord($post['password']));
			}
			$user->save();
			return $this->getCommonResult(true);
		}
		return array(
			"get" => $get,
			"user" => $user
		);
	}
	
	/**
	 * 重置密码
	 * @param $get
	 * @param $post
	 * @return array|\Lite\Core\Result
	 * @throws \Lite\Exception\BizException
	 * @throws \Lite\Exception\Exception
	 * @throws \Lite\Exception\RouterException
	 */
	public function resetPwd($get,$post){
		$user =$get['id']? SysUser::find("id =?",$get['id'])->oneOrFail(): new SysUser();
		if($post){
			$post = array_trim($post);
			$user->setValue("password",SysUser::getPassWord($post['password']));
			$user->save();
			return $this->getCommonResult(true);
		}
		return array(
			"get" => $get,
			"user" => $user
		);
	}
	
	public function myInfo($get){
		$user = SysUser::find("id =?",$get['id'])->oneOrFail();
		return array(
			"get" => $get,
			"user" => $user
		);
	}
	
	public function updatePwd($get,$post){
		$user =SysUser::find("id =?",CurrentUser::getUserId())->oneOrFail();
		if($post){
			//检查原密码
			if($user->password != SysUser::getPassWord($post['old_password'])){
				throw new BizException("原密码错误");
			}
			$user->setValues([
				"password" => SysUser::getPassWord($post['password']),
				"password_update_time" => date("Y-m-d H:i:s")
			]);
			$user->save();
			CurrentUser::instance()->logout();
			return new Result("修改成功，请重新登录",0,[],"index/login");
		}
		return array(
			"get" => $get,
			"user" => $user
		);
	}
	
}