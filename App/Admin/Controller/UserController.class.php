<?php
namespace Admin\Controller;
use Think\Controller;
class UserController extends PublicController{

	//*************************
	// 普通会员的管理
	//*************************
	public function index(){
		$aaa_pts_qx=1;
		$type=$_GET['type'];
		$id=(int)$_GET['id'];
		$mobile = trim($_REQUEST['mobile']);
		$name = trim($_REQUEST['name']);

		$names=$this->htmlentities_u8($_GET['name']);
		//搜索
		$where="1=1";
		$name!='' ? $where.=" and name like '%$name%'" : null;
		$mobile!='' ? $where.=" and mobile like '%$mobile%'" : null;

		define('rows',20);
		$count=M('user')->where($where)->count();
		$rows=ceil($count/rows);

		$page=(int)$_GET['page'];
		$page<0?$page=0:'';
		$limit=$page*rows;
		$userlist=M('user')->where($where)->order('id desc')->limit($limit,rows)->select();
		$page_index=$this->page_index($count,$rows,$page);
		foreach ($userlist as $k => $v) {
			$userlist[$k]['addtime']=date("Y-m-d H:i",$v['addtime']);
			if ($userlist[$k]['vip_starttime']){
				$userlist[$k]['vip_starttime']=date("Y-m-d H:i",$v['vip_starttime']);
			}
			if ($userlist[$k]['vip_endtime']){
				$userlist[$k]['vip_endtime']=date("Y-m-d H:i",$v['vip_endtime']);
			}
		}
		//====================
		// 将GET到的参数输出
		//=====================
		$this->assign('name',$name);
		$this->assign('mobile',$mobile);

		//=============
		//将变量输出
		//=============
		$this->assign('page_index',$page_index);
		$this->assign('page',$page);
		$this->assign('userlist',$userlist);
		$this->display();	
	}

	/**
	 * 增加/编辑会员
	 */
	public function add(){
		$id = intval($_REQUEST['id']);
		if(isset($_POST['submit'])){
			try{
				$array=array(
						'name'=>$_POST['name'] ,
						'uname'=>$_POST['name'] ,
						'mobile'=> $_POST['mobile'] ,
						'provinceid'=> intval($_POST['provinceid']) ,
						'cityid'=> intval($_POST['cityid']) ,
						'areaid'=> intval($_POST['areaid']) ,
						'vip_starttime'=> strtotime($_POST['vip_starttime']) ,
						'vip_endtime'=> strtotime($_POST['vip_endtime']) ,
						'del'=> intval($_POST['del']) ,
						'is_agent'=> intval($_POST['is_agent']) ,
						'agent_provinceid'=> intval($_POST['agent_provinceid']) ,
						'agent_cityid'=> intval($_POST['agent_cityid']) ,
						'agent_areaid'=> intval($_POST['agent_areaid'])
				);
				if ($_POST['password'] != ''){
					$array['pwd'] = md5(md5($_POST['password']));
				}
				//执行添加
				if($id){
					//将空数据排除掉，防止将原有数据空置
					foreach ($array as $k => $v) {
						if(empty($v)){
							unset($v);
						}
					}
					$sql = M('user')->where('id='.$id)->save($array);
				}else{
					$array['addtime']=time();
					$sql = M('user')->add($array);
					$id=$sql;
				}
		
				//规格操作
				if($sql){
					$this->success('操作成功.');
					exit();
				}else{
					throw new \Exception('操作失败.');
				}
					
			}catch(\Exception $e){
				echo "<script>alert('".$e->getMessage()."');location='{:U('user')}';</script>";
			}
		}
		
		if ($id){
			$info = M('user')->where('id='.intval($id))->find();
			$province_list = M('china_city')->where('tid=0')->select();
			if ($info['areaid']){
			    $city = M('china_city')->where("code = {$info['areaid']}")->find();
				$area_list = M('china_city')->where('tid='.$city['tid'])->select();
			}
			if ($info['provinceid']){
			    $province = M('china_city')->where("code = {$info['provinceid']}")->find();
				$city_list = M('china_city')->where('tid='.$province['id'])->select();
			}
			
			$this->assign('id',$id);
			$this->assign('province_list',$province_list);
			$this->assign('city_list',$city_list);
			$this->assign('area_list',$area_list);
			$this->assign('userinfo',$info);
		}
		$this->display();
	}
	
	/**
	 * ajax 通过ID获取下级地区
	 */
	public function getArea(){
		$id = intval($_REQUEST['id']);
		$arealist = M('china_city')->where('tid='.$id)->select();
		echo json_encode(array('arealist'=>$arealist));
		exit();
	}
	
	//*************************
	//会员地址管理
	//*************************
	public function address(){
		// $aaa_pts_qx=1;
		$id=(int)$_GET['id'];
		if($id<1){return;}
		if($_GET['type']=='del' && $id>0 && $_SESSION['admininfo']['qx']==4){
		  $this->delete('address',$id);
		}
		//搜索
		$address=M('address')->where("uid=$id")->select();
		
	    //=============
		//将变量输出
		//=============
		$this->assign('address',$address);
		$this->display();
	}

	/**
	 * 禁用
	 */
	public function del()
	{
		$id = intval($_REQUEST['did']);
		$info = M('user')->where('id='.intval($id))->find();
		if (!$info) {
			$this->error('会员信息错误.'.__LINE__);
			exit();
		}

		$data=array();
		$data['del'] = $info['del'] == '1' ?  0 : 1;
		$up = M('user')->where('id='.intval($id))->save($data);
		if ($up) {
			$this->redirect('User/index',array('page'=>intval($_REQUEST['page'])));
			exit();
		}else{
			$this->error('操作失败.');
			exit();
		}
	}
	
	/**
	 * 删除用户
	 */
	public function dodel(){
		$id = intval($_REQUEST['did']);
		$info = M('user')->where('id='.intval($id))->find();
		if (!$info) {
			$this->error('会员信息错误.'.__LINE__);
			exit();
		}

		//删除
		if (M('user')->where('id='.intval($id))->delete()) {
			M('user_course')->where('uid='.intval($id))->delete();
			M('user_voucher')->where('uid='.intval($id))->delete();
			
			$this->redirect('User/index',array('page'=>intval($_REQUEST['page'])));
			exit();
		}else{
			$this->error('操作失败.');
			exit();
		}
	}
}