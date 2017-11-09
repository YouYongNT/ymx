<?php
namespace Admin\Controller;
use Think\Controller;
class VipController extends PublicController{

	//*************************
	// 普通会员的管理
	//*************************
	public function index(){
		$aaa_pts_qx=1;
		$type=$_GET['type'];
		$id=(int)$_GET['id'];
		$mobile = trim($_REQUEST['mobile']);
		$uname = $this->htmlentities_u8(trim($_REQUEST['uname']));
		$start_time = strtotime($_REQUEST['start_time']);
		$end_time = strtotime($_REQUEST['end_time']);
		$number = trim($_REQUEST['number']);

		//搜索
		$where="1=1";
		$uname!='' ? $where.=" and u.name like '%$uname%'" : null;
		$mobile!='' ? $where.=" and u.mobile like '%$mobile%'" : null;
		$start_time!='' ? $where.=" and v.dateline >= $start_time" : null;
		$end_time!='' ? $where.=" and v.dateline <= $end_time" : null;
		$number!='' ? $where.=" and v.number like '%$number%'" : null;

		define('rows',20);
		$count=M('vip_card')->alias("v")->join('left join lr_user as u on v.uid=u.id')->join('left join lr_product as p on v.pro_id=p.id')->where($where)->count();
		$rows=ceil($count/rows);
 
		$page=(int)$_GET['page'];
		$page<0?$page=0:'';
		$limit=$page*rows;
		$viplist=M('vip_card')->alias("v")->join('left join lr_user as u on v.uid=u.id')->join('left join lr_product as p on v.pro_id=p.id')->field('u.name,u.mobile,v.number,p.name as pro_name,v.dateline')->where($where)->order('v.id desc')->limit($limit,rows)->select();
		$page_index=$this->page_index($count,$rows,$page);
		foreach ($viplist as $k => $v) {
			$viplist[$k]['dateline']=date("Y-m-d H:i",$v['dateline']);
		}
		//====================
		// 将GET到的参数输出
		//=====================
		$this->assign('uname',$uname);
		$this->assign('mobile',$mobile);
		$this->assign('start_time',$start_time);
		$this->assign('end_time',$end_time);
		$this->assign('number',$number);

		//=============
		//将变量输出
		//=============
		$this->assign('page_index',$page_index);
		$this->assign('page',$page);
		$this->assign('viplist',$viplist);
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
					$sql = M('vip_card')->where('id='.$id)->save($array);
				}else{
					$array['addtime']=time();
					$sql = M('vip_card')->add($array);
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
			$info = M('vip_card')->where('id='.intval($id))->find();
			$province_list = M('china_city')->where('tid=0')->select();
			if ($info['provinceid']){
				$city_list = M('china_city')->where('tid='.$info['provinceid'])->select();
			}
			if ($info['cityid']){
				$area_list = M('china_city')->where('tid='.$info['cityid'])->select();
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
		$info = M('vip_card')->where('id='.intval($id))->find();
		if (!$info) {
			$this->error('会员信息错误.'.__LINE__);
			exit();
		}

		$data=array();
		$data['del'] = $info['del'] == '1' ?  0 : 1;
		$up = M('vip_card')->where('id='.intval($id))->save($data);
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
		$info = M('vip_card')->where('id='.intval($id))->find();
		if (!$info) {
			$this->error('会员信息错误.'.__LINE__);
			exit();
		}

		//删除
		if (M('vip_card')->where('id='.intval($id))->delete()) {
			$this->redirect('User/index',array('page'=>intval($_REQUEST['page'])));
			exit();
		}else{
			$this->error('操作失败.');
			exit();
		}
	}
}