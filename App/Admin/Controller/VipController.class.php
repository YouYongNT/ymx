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
		$count=M('vip_card')->alias("v")->join('left join lr_user as u on v.uid=u.id')->join('left join lr_product as p on v.pid=p.id')->where($where)->count();
		$rows=ceil($count/rows);
 
		$page=(int)$_GET['page'];
		$page<0?$page=0:'';
		$limit=$page*rows;
		$viplist=M('vip_card')->alias("v")->join('left join lr_user as u on v.uid=u.id')->join('left join lr_product as p on v.pid=p.id')->field('u.uname,u.mobile,v.id,v.number,p.name as pro_name,v.dateline')->where($where)->order('v.id desc')->limit($limit,rows)->select();
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
	 * VIP卡详情
	 */
	public function show(){
		//获取传递过来的id
		$id = intval($_GET['id']);
		if(!$id) {
			$this->error('系统错误.');
		}

		//根据vip卡id获取vip详情
		$vip_info = M('vip_card')->where('id='.intval($id))->find();
		if (!$vip_info) {
			$this->error('VIP卡信息错误.');
		}
		
		

		$this->assign('post_info',$post_info);
		$this->assign('order_info',$order_info);
		$this->assign('order_pro',$order_pro);
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
}