<?php
/**
 * Created by PhpStorm.
 * Date: 2019-01-23
 * Time: 16:51
 */

namespace ttwms\controller\enterprise;

use Lite\Component\Paginate;
use Lite\Exception\BizException;
use ttwms\controller\BaseController;
use ttwms\model\Enterprise;
use function Lite\func\array_trim;

/**
 * @auth 用户管理/用户管理
 * Date: 2019-01-22
 * Time: 14:28
 */
class EnterpriseController extends BaseController{
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
		$select = Enterprise::find()
		->whereOnSet("status=?",$search['status'])
		->whereLikeOnSetBatch(['code','name'],"%{$search['kw']}%");
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
		$user =$get['id']? Enterprise::find("id =?",$get['id'])->oneOrFail(): new Enterprise();
		if($post){
			$post = array_trim($post);
			$data['token'] = md5($post['code'].time());
			$data['login_name'] = $post['code'];
			$data['password'] = Enterprise::encryptPassword($post['password']);
			
			$existUser = Enterprise::find("login_name =?",$post['login_name'])->whereOnSet("id<>?",$user->id)->one();
			if($existUser->id){
				throw new BizException("该登录账号已存在");
			}
			
			$user->code = $user->code?:$post['code'];
			$user->name = $post['name'];
			$user->login_name = $user->login_name?:$data['login_name'];
			$user->password = $user->password?:$data['password'];
			$user->token = $user->token?:$data['token'];
			$user->level_id = $post['level_id'];
			$user->credit_line = $post['credit_line'];
			$user->save();
			return $this->getCommonResult(true);
		}
		return array(
			"get" => $get,
			"user" => $user
		);
	}
	
	/**
	 * @auth 更新token
	 * @param $get
	 * @return \Lite\Core\Result
	 * @throws \Lite\Exception\Exception
	 */
	public function token($get)
	{
		$user =  Enterprise::find("id =?",$get['id'])->oneOrFail();
		$user->token =  md5($user->code.time());
		$user->save();
		return $this->getCommonResult(true);
	}
	
	/**
	 * @auth 重置密码
	 * @param $get
	 * @param array $post
	 * @return array
	 * @throws \Lite\Exception\Exception
	 */
	public function password($get,$post)
	{
		$user =  Enterprise::find("id =?",$get['id'])->oneOrFail();
		if($post){
			$post = array_trim($post);
			$user->password = Enterprise::encryptPassword($post['password']);
			$user->save();
			return $this->getCommonResult(true);
		}
		return array(
			"get" => $get,
			"user" => $user
		);
	}
}