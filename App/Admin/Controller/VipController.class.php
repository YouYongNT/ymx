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
		$start_time = intval(strtotime($_REQUEST['start_time']));
		$end_time = intval(strtotime($_REQUEST['end_time']));
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
		$this->assign('start_time',$_REQUEST['start_time']);
		$this->assign('end_time',$_REQUEST['end_time']);
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
		$vip_info = M('vip_card')->alias("v")->join('left join lr_user as u on v.uid=u.id')->join('left join lr_product as p on v.pid=p.id')->field('u.uname,u.mobile,v.id,v.number,p.name as pro_name,v.dateline')->where('v.id='.intval($id))->find();
		if (!$vip_info) {
			$this->error('VIP卡信息错误.');
		}
		$vip_info['dateline'] = date('Y-m-d',$vip_info['dateline']);
		
		//关联门票
		$tList = M('ticket')->where('vip_id='.$id)->select();
		$tArray = array();
		foreach ($tList as $kt=>$t){
			if ($kt == 0){
				$pinfo = M('product')->where('id='.$t['pid'])->find();
				$tArray[$t['pid']]['name'] = $pinfo['name'];
			}
			
			$tArray[$t['pid']]['count'] ++;
			if ($t['status'] == 3){
				$tArray[$t['pid']]['use'] ++;
			}
		}
		
		//关联课程
		$cList = M('course')->where('vip_id='.$id)->select();
		$cArray = array();
		foreach ($cList as $kc=>$c){
			if ($kc == 0){
				$pinfo = M('product')->where('id='.$c['pid'])->find();
				$cArray[$c['pid']]['name'] = $pinfo['name'];
			}
			$cArray[$c['pid']]['count'] ++;
			if ($c['status'] == 2){
				$cArray[$c['pid']]['use'] ++;
			}
		}
		
		//关联邀请码
		$iList = M('invite_code')->where('vip_id='.$id)->select();
		$iArray = array();
		foreach ($iList as $key=>$i){
			$iArray['count'] ++;
			if ($i['status'] == 2){
				$iArray['use'] ++;
			}
		}
		
		$this->assign('tArray',$tArray);
		$this->assign('cArray',$cArray);
		$this->assign('iArray',$iArray);
		$this->assign('vip_info',$vip_info);
		$this->display();
	}
	
	/**
	 * 招募邀请码
	 */
	public function code(){
		//获取传递过来的id
		$id = intval($_GET['id']);
		if(!$id) {
			$this->error('系统错误.');
		}
		
		//根据vip卡id获取vip详情
		$code_list = M('invite_code')->where('vip_id='.$id)->select();
		if (!$code_list) {
			$this->error('VIP卡信息错误.');
		}
		
		foreach ($code_list as $key=>$code){
			if ($code['status'] == 2){
				$vc_info = M('vip_card')->alias("v")->join('left join lr_user as u on v.uid=u.id')->field('v.number,v.dateline,u.name as uname,u.mobile')->where("v.invite_code='{$code['number']}'")->find();
				$code_list[$key]['info'] = $vc_info;
			}elseif ($code['status'] == 1){
				$o_info = M('order')->where("uninum='{$code['number']}'")->find();
				$code_list[$key]['info']['dateline'] = $o_info['addtime'];
			}
		}
		
		$count = M('invite_code')->field('count(*) as count')->where('vip_id='.$id)->find();
		$nousenum = M('invite_code')->field('count(*) as nousenum')->where('status=0 and vip_id='.$id)->find();
		$locknum = M('invite_code')->field('count(*) as locknum')->where('status=1 and vip_id='.$id)->find();
		$usenum = M('invite_code')->field('count(*) as usenum')->where('status=2 and vip_id='.$id)->find();
		$co = array();
		$co['count'] = $count['count'];
		$co['nousenum'] = $nousenum['nousenum'];
		$co['locknum'] = $locknum['locknum'];
		$co['usenum'] = $usenum['usenum'];
		
		//vip卡信息
		$vip_info = M('vip_card')->alias("v")->join('left join lr_product as p on v.pid=p.id')->field('p.name as pro_name')->where('v.id='.intval($id))->find();
		
		$this->assign('co',$co);
		$this->assign('vip_info',$vip_info);
		$this->assign('code_list',$code_list);
		$this->display();
	}
	
	/**
	 * 门票激活管理 ticket
	 */
	public function ticket(){
		$type = $_GET['type'];
		$status = $_GET['status'];

		//搜索
		$where="1=1";
		$type!='' ? $where.=" and p.id={$type}" : null;
		if ($status!=''){
			$s = $status-1;
			$where.=" and t.status={$s}";
		}

		define('rows',20);
		$count=M('ticket')->alias("t")->join('left join lr_product as p on t.pid=p.id')->where($where)->count();
		$rows=ceil($count/rows);
 
		$page=(int)$_GET['page'];
		$page<0?$page=0:'';
		$limit=$page*rows;
		$ticketlist=M('ticket')->alias("t")->join('left join lr_product as p on t.pid=p.id')->field('t.*,p.name as pro_name')->where($where)->order('t.id desc')->limit($limit,rows)->select();
		$page_index=$this->page_index($count,$rows,$page);
		
		//查询关联vip卡
		foreach ($ticketlist as $key=>$t){
			if ($t['vip_id']){
				$vip = M('vip_card')->where('id='.$t['vip_id'])->find();
				$ticketlist[$key]['vip_number'] = $vip['number'];
			}
		}
		
		//门票类型
		$tlist = M('product')->where('cid=27')->select();
		
		//====================
		// 将GET到的参数输出
		//=====================
		$this->assign('status',$status);
		$this->assign('type',$type);

		//=============
		//将变量输出
		//=============
		$this->assign('tlist',$tlist);
		$this->assign('page_index',$page_index);
		$this->assign('page',$page);
		$this->assign('ticketlist',$ticketlist);
		$this->display();
	}
	
	/**
	 * 激活
	 */
	public function doactive(){
		$id = intval($_POST['id']);
		$status = intval($_POST['status']);
		$uname = $_POST['uname']!='' ? $_POST['uname'] : '';
		$umobile = $_POST['umobile']!='' ? $_POST['umobile'] : '';
		$info = M('ticket')->where('id='.intval($id))->find();
		
		if (!$info) {
			$arr = array();
			$arr = array('returns'=>0 , 'message'=>'没有找到相关门票.');
			echo json_encode($arr);
			exit();
		}
		try{
			$up = M('ticket')->where('id='.intval($id))->save(array('status'=>$status,'uname'=>$uname,'umobile'=>$umobile));
			$json = array();
			if ($up) {
				$json['message']="操作成功.";
				$json['returns']=1;
			}else{
				$json['message']="操作失败.";
				$json['returns']=0;
			}
		}catch(\Exception $e){
			$json = array('returns'=>0 , 'message'=>$e->getMessage());
		}
		echo json_encode($json);
		exit();
	}
	
	/**
	 * 课程激活管理 course
	 */
	public function course(){
		$type = $_GET['type'];
		$status = $_GET['status'];
	
		//搜索
		$where="1=1";
		$type!='' ? $where.=" and p.id={$type}" : null;
		if ($status!=''){
			$s = $status-1;
			$where.=" and c.status={$s}";
		}
	
		define('rows',20);
		$count=M('course')->alias("c")->join('left join lr_product as p on c.pid=p.id')->where($where)->count();
		$rows=ceil($count/rows);
		
		$page=(int)$_GET['page'];
		$page<0?$page=0:'';
		$limit=$page*rows;
		$courselist=M('course')->alias("c")->join('left join lr_product as p on c.pid=p.id')->field('c.*,p.name as pro_name')->where($where)->order('c.id desc')->limit($limit,rows)->select();
		$page_index=$this->page_index($count,$rows,$page);
	
		//查询关联vip卡
		foreach ($courselist as $key=>$t){
			if ($t['vip_id']){
				$vip = M('vip_card')->where('id='.$t['vip_id'])->find();
				$courselist[$key]['vip_number'] = $vip['number'];
			}
		}
		//门票类型
		$tlist = M('product')->where('cid=28')->select();
	
		//====================
		// 将GET到的参数输出
		//=====================
		$this->assign('status',$status);
		$this->assign('type',$type);
	
		//=============
		//将变量输出
		//=============
		$this->assign('tlist',$tlist);
		$this->assign('page_index',$page_index);
		$this->assign('page',$page);
		$this->assign('courselist',$courselist);
		$this->display();
	}
	
	/**
	 * 确认课程
	 */
	public function do_course_active(){
		$id = intval($_POST['id']);
		$status = intval($_POST['status']);
		$info = M('course')->where('id='.intval($id))->find();
	
		if (!$info) {
			$arr = array();
			$arr = array('returns'=>0 , 'message'=>'没有找到相关门票.');
			echo json_encode($arr);
			exit();
		}
		try{
			$up = M('course')->where('id='.intval($id))->save(array('status'=>$status));
			$json = array();
			if ($up) {
				$json['message']="操作成功.";
				$json['returns']=1;
			}else{
				$json['message']="操作失败.";
				$json['returns']=0;
			}
		}catch(\Exception $e){
			$json = array('returns'=>0 , 'message'=>$e->getMessage());
		}
		echo json_encode($json);
		exit();
	}
}