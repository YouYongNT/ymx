<?php
namespace Admin\Controller;
use Think\Controller;
class OrderController extends PublicController{

	/*
	*
	* 构造函数，用于导入外部文件和公共方法
	*/
	public function _initialize(){
		$this->order = M('Order');
		$this->order_product = M('Order_product');

		$order_status = array('0'=>'取消','10'=>'未付款','50'=>'已付款');
		$this->assign('order_status',$order_status);
	}

	/*
	*
	* 获取、查询所有订单数据
	*/
	public function index(){
		//搜索
		//获取商家id
		if (intval($_SESSION['admininfo']['qx'])!=4) {
			$shop_id = intval(M('adminuser')->where('id='.intval($_SESSION['admininfo']['id']))->getField('shop_id'));
			if ($shop_id==0) {
				$this->error('非法操作.');
			}
		}else{
			$shop_id = intval($_REQUEST['shop_id']);
		}
		
		$pay_type = trim($_REQUEST['pay_type']);//支付类型
		$pay_status = intval($_REQUEST['pay_status']); //订单状态
		$start_time = intval(strtotime($_REQUEST['start_time'])); //订单状态
		$end_time = intval(strtotime($_REQUEST['end_time'])); //订单状态
		
		//构建搜索条件
		$condition = array();
		$condition['del'] = 0; 
		$where = '1=1 AND del=0';
		//根据支付类型搜索
		if ($pay_type) {
			$condition['type'] = $pay_type;
			$where .=' AND type='.$pay_type;
			//搜索内容输出
			$this->assign('pay_type',$pay_type);
		}
		//根据订单状态搜索
		if ($pay_status) {
			if ($pay_status<10) {
				//小于10的为退款
				$condition['back'] = $pay_status;
				$where .=' AND back='.intval($pay_status);
			}else{
				//大于10的为正常订单
				$condition['status'] = $pay_status;
				$where .=' AND status='.intval($pay_status);
			}
			
			//搜索内容输出
			$this->assign('pay_status',$pay_status);
		}
		//根据下单时间搜索
		if ($start_time) {
			$condition['addtime'] = array('gt',$start_time);
			$where .=' AND addtime > '.$start_time;
			//搜索内容输出
			$this->assign('start_time',$_REQUEST['start_time']);
		}
		
		//根据下单时间搜索
		if ($end_time) {
			$condition['addtime'] = array('lt',$end_time);
			$where .=' AND addtime < '.$end_time;
			//搜索内容输出
			$this->assign('end_time',$_REQUEST['end_time']);
		}
		/*if ($start_time && $end_time) {
			$condition['addtime'] = array('eq','addtime>'.$start_time.' AND addtime<='.$end_time);
		}*/

		//分页
		$count   = $this->order->where($where)->count();// 查询满足要求的总记录数
		$Page    = new \Think\Page($count,25);// 实例化分页类 传入总记录数和每页显示的记录数(25)

		//分页跳转的时候保证查询条件
		foreach($condition as $key=>$val) {
			$Page->parameter[$key]  =  urlencode($val);
		}
		if ($start_time && $end_time) {
			$addtime = 'addtime > '.$start_time.' AND addtime < '.$end_time;
			$Page->parameter['addtime']  =  urlencode($addtime);
		}

		//头部描述信息，默认值 “共 %TOTAL_ROW% 条记录”
		$Page->setConfig('header', '<li class="rows">共<b>%TOTAL_ROW%</b>条&nbsp;第<b>%NOW_PAGE%</b>页/共<b>%TOTAL_PAGE%</b>页</li>');
		//上一页描述信息
	    $Page->setConfig('prev', '上一页');
	    //下一页描述信息
	    $Page->setConfig('next', '下一页');
	    //首页描述信息
	    $Page->setConfig('first', '首页');
	    //末页描述信息
	    $Page->setConfig('last', '末页');
	    /*
	    * 分页主题描述信息 
	    * %FIRST%  表示第一页的链接显示  
	    * %UP_PAGE%  表示上一页的链接显示   
	    * %LINK_PAGE%  表示分页的链接显示
	    * %DOWN_PAGE% 	表示下一页的链接显示
	    * %END%   表示最后一页的链接显示
	    */
	    $Page->setConfig('theme', '%FIRST%%UP_PAGE%%LINK_PAGE%%DOWN_PAGE%%END%%HEADER%');

		$show    = $Page->show();// 分页显示输出
		// 进行分页数据查询 注意limit方法的参数要使用Page类的属性
		$order_list = $this->order->where($where)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		foreach ($order_list as $k => $v) {
			$order_list[$k]['u_name'] = M('user')->where('id='.intval($v['uid']))->getField('name');
		}
		//echo $where;
		$this->assign('order_list',$order_list);// 赋值数据集
		$this->assign('page',$show);// 赋值分页输出
		$this->assign('admin_qx',$_SESSION['admininfo']['qx']);//后台用户权限，目前设置为超级管理员权限
		$this->display(); // 输出模板

	}

	/*
	* 选择商家里面的省市联动
	*/
	public function get_city(){
		$id=(int)$_GET['id'];

		$data=M('china_city')->where('tid='.intval($id))->field('id,name')->select();
		$i=0;
		$array=array();
		foreach ($data as $v) {
		   $array[$i]['id']=$v['id'];
		   $array[$i]['name']=$v['name'];
		   $i+=1;
		}
		echo json_encode($array);
	}


	/*
	*
	* 查看订单详情
	*/
	public function show(){
		//获取传递过来的id
		$order_id = intval($_GET['oid']);
		if(!$order_id) {
			$this->error('系统错误.');
		}

		//根据订单id获取订单数据还有商品信息
		$order_info = $this->order->where('id='.intval($order_id))->find();
		$order_pro = $this->order_product->where('order_id='.intval($order_id))->select();
		if (!$order_info || !$order_pro) {
			$this->error('订单信息错误.');
		}
		foreach ($order_pro as $k => $v) {
			$data=array();
			$data = unserialize($v['pro_guide']);
			if ($data) {
				$order_pro[$k]['g_name'] = $data['gname'];
			}else{
				$order_pro[$k]['g_name'] = '无';
			}
		}

		$this->assign('post_info',$post_info);
		$this->assign('order_info',$order_info);
		$this->assign('order_pro',$order_pro);
		$this->display();
	}


	/*
	*
	* 修改订单状态，添加物流名称、物流单号
	*/
	public function sms_up(){
		$oid = intval($_POST['oid']);
		$o_info = $this->order->where('id='.intval($oid))->find();
		if (!$o_info) {
			$arr = array();
			$arr = array('returns'=>0 , 'message'=>'没有找到相关订单.');
			echo json_encode($arr);
			exit();
		}

		//接收ajax传过来的值
		$order_status = intval($_POST['order_status']);
		if (intval($o_info['status'])==$order_status) {
			$arr = array();
			$arr = array('returns'=>0 , 'message'=>'修改信息未发生变化.');
			echo json_encode($arr);
			exit();
		}

		try{
			//付款成功
			if ($order_status == 50){
				$opList = M('order_product')->where('order_id='.$o_info['id'])->select();
				Vendor('SMS.Send');
				foreach ($opList as $o){
					$send = new \Send();
					$res = $send->sendSms($o_info['tel'], '亲爱的亚马逊商学院会员'.$o_info['receiver'].'，您购买的产品'.$o['name'].'已确定付款成功！进入平台个人中心查看详情。');
				}
				
				//产品关联处理
				foreach ($opList as $op){
					for ($i=0; $i<$op['num']; $i++){//购买的单个产品数量
						$pro = M('product')->where('id='.$op['pid'])->find();
						if ($pro['cid'] == 20){//vip会员卡
							//默认亚马逊的邀请码
							$a_code = 'A00001';
							
							//生成会员卡和关联门票及课程
							$invite_code = array();
							if ($o_info['uninum'] != ''){
								$a_code = $o_info['uninum'];
								$invite_code = M('invite_code')->where("number='{$a_code}'")->find();
							}
							if (!empty($invite_code)){
								$invite_id = $invite_code['vip_id'];
							}else {
								$invite_id = 0;
							}
							$vip_array = array(
									'uid'			=> $o_info['uid'],
									'pid'			=> $pro['id'],
									'amount'		=> floatval($op['price']),
									'invite_id'		=> $invite_id,
									'invite_code'	=> $a_code,
									'dateline'		=> time()
									);
							$vid = M('vip_card')->add($vip_array);
							if ($vid){//添加会员卡成功
								//生成会员卡编号
								$tmp_num = $pro['pro_number'].sprintf('%05s', $vid);
								//上级信息
								$pinfo = array();
								$ppinfo = array();
								$pinfo = M('vip_card')->where('id='.$invite_id)->find();
								
								if (!empty($pinfo)){
									$n1 = explode(' ', $pinfo['number']);
									$tmp_num .= ' '.$n1[0];
									//上上级
									$ppinfo = M('vip_card')->where('id='.$pinfo['invite_id'])->find();
									if (!empty($ppinfo)){
										$n2 = explode(' ', $ppinfo['number']);
										$tmp_num .= ' '.$n2[0];
									}else {
										$tmp_num .= ' A00001';
									}
								}else {
									$tmp_num .= ' A00001 A00001';
								}
								M('vip_card')->where('id='.$vid)->save(array('number'=>$tmp_num));
								
								//上级提成
								if (!empty($pinfo)){
									if ($pro['pro_number'] == 'L1'){//事业伙伴
										$percent_1 	= 0.1;
										$amount_1 	= floatval($op['price'] * 0.1);
									}else {//L2-L6的提成
										$percent_1 	= 0.3;
										$amount_1 	= floatval($op['price'] * 0.3);
									}
									$parray = array(
											'uid'				=> $pinfo['uid'],
											'invite_card_id'	=> $pinfo['id'],
											'invite_card_code'	=> $pinfo['number'],
											'card_id'			=> $vid,
											'card_number'		=> $tmp_num,
											'card_type'			=> $pro['pro_number'],
											'percent'			=> $percent_1,
											'amount'			=> $amount_1,
											'dateline'			=> time()
									);
									M('income_info')->add($parray);
									$pincome = M('income')->where('uid='.$pinfo['uid'])->find();
									
									//更新或添加会员收益
									if (!empty($pincome)){
										M('income')->where('uid='.$pinfo['uid'])->save(array('amount'=>floatval($pincome['amount']+$amount_1),'cycle_amount'=>floatval($pincome['cycle_amount']+$amount_1)));
									}else {
										M('income')->add(array('uid'=>$pinfo['uid'],'amount'=>$amount_1,'cycle_amount'=>$amount_1));
									}
								}
								
								//上上级分成
								if (!empty($ppinfo)){
									$percent_2 	= 0.15;
									$amount_2 	= floatval($op['price'] * 0.15);
									$pparray = array(
											'uid'				=> $ppinfo['uid'],
											'invite_card_id'	=> $ppinfo['id'],
											'invite_card_code'	=> $ppinfo['number'],
											'card_id'			=> $vid,
											'card_number'		=> $tmp_num,
											'card_type'			=> $pro['pro_number'],
											'percent'			=> $percent_2,
											'amount'			=> $amount_2,
											'dateline'			=> time()
									);
									M('income_info')->add($pparray);
									$ppincome = M('income')->where('uid='.$pparray['uid'])->find();
									
									//更新或添加会员收益
									if (!empty($ppincome)){
										M('income')->where('uid='.$ppinfo['uid'])->save(array('amount'=>floatval($ppincome['amount']+$amount_1),'cycle_amount'=>floatval($ppincome['cycle_amount']+$amount_1)));
									}else {
										M('income')->add(array('uid'=>$ppinfo['uid'],'amount'=>$amount_1,'cycle_amount'=>$amount_1));
									}
								}
								
								//修改邀请码状态
								if ($o_info['uninum'] != ''){
									M('invite_code')->where("number='{$o_info['uninum']}'")->save(array('status'=>2));//status 0：未使用；1：锁定；2：已使用
								}
							}
							
							//生成邀请码
							for ($j=0;$j<$pro['code_count'];$j++){
								$nj = $this->make_coupon_card();
								M('invite_code')->add(array('vip_id'=>$vid,'number'=>$nj,'status'=>0,'dateline'=>time()));
							}
							
							//关联门票或课程
							if ($pro['relate'] != ''){
								$relate = unserialize($pro['relate']);
								foreach ($relate as $id=>$num){
									$rp = M('product')->where('id='.$id)->find();
									if ($rp['cid'] == 27){//门票
										for ($k=0;$k<intval($num);$k++){
											$tnum = $this->make_coupon_card();
											M('ticket')->add(array('uid'=>$o_info['uid'],'number'=>$tnum,'pid'=>$id,'vip_id'=>$vid,'status'=>0,'dateline'=>time()));
										}
									}elseif ($rp['cid'] == 28){//课程
										for ($l=0;$l<intval($num);$l++){
											$cnum = $this->make_coupon_card();
											M('course')->add(array('uid'=>$o_info['uid'],'number'=>$cnum,'pid'=>$id,'vip_id'=>$vid,'status'=>0,'dateline'=>time()));
										}
									}
								}
							}
							
						}elseif ($pro['cid'] == 27){//门票
							$tnum = $this->make_coupon_card();
							M('ticket')->add(array('uid'=>$o_info['uid'],'number'=>$tnum,'pid'=>$pro['id'],'vip_id'=>0,'status'=>0,'dateline'=>time()));
						}elseif ($pro['cid'] == 28){//课程
							$cnum = $this->make_coupon_card();
							M('course')->add(array('uid'=>$o_info['uid'],'number'=>$cnum,'pid'=>$pro['id'],'vip_id'=>0,'status'=>0,'dateline'=>time()));
						}
					}
				}
			}
			
			$data = array();
			$data['status'] = $order_status;
			if ($order_status) {
				$data['payer'] = $_POST['payer'];
				$data['voucher'] = $_POST['voucher'];
			}
			$up = $this->order->where('id='.intval($oid))->save($data);
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

	//生成邀请码
	function make_coupon_card() {
		$code = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$rand = $code[rand(0,25)]
		.strtoupper(dechex(date('m')))
		.date('d').substr(time(),-5)
		.substr(microtime(),2,5)
		.sprintf('%02d',rand(0,99));
		for(
				$a = md5( $rand, true ),
				$s = '0123456789ABCDEFGHIJKLMNOPQRSTUV',
				$d = '',
				$f = 0;
				$f < 8;
				$g = ord( $a[ $f ] ),
				$d .= $s[ ( $g ^ ord( $a[ $f + 8 ] ) ) - $g & 0x1F ],
				$f++
				);
		return $d;
	}

	/*
	*
	*  确认退款  修改退款状态
	*/
	public function back(){
	   $id =(int)$_GET['oid'];
	   $back_info = $this->order->where('id='.intval($id))->find();
	   if(!$back_info || intval($back_info['back'])!=1) {
	   		$this->error('订单信息错误.');
	   }

	   $data = array();
	   $data['back']=2;

	   $up_back = $this->order->where('id='.intval($id))->save($data);
	   if ($up_back) {
	   		$this->success('操作成功.');
	   }else{
	   		$this->error('操作失败.');
	   }
	}

	/*
	*
	*  订单删除方法
	*/
	public function del(){
		//以后删除还要加权限登录判断
		$id = intval($_GET['did']);
		$check_info = $this->order->where('id='.intval($id))->find();
		if (!$check_info) {
			$this->error('系统错误，请稍后再试.');
		}

		$up = array();
		$up['del'] = 1;
		$res = $this->order->where('id='.intval($id))->save($up);
		if ($res) {
			$this->success('操作成功.');
		}else{
			$this->error('操作失败.');
		}
	}


	/*
	*
	*  订单统计功能
	*/
	public function order_count(){
		//查询类型 d日视图  m月视图
		$type = $_GET['type'];
		//查询商家id
		$where = '1=1';

		//获取商家id
		if (intval($_SESSION['admininfo']['qx'])!=4) {
			$shop_id = intval(M('adminuser')->where('id='.intval($_SESSION['admininfo']['id']))->getField('shop_id'));
			if ($shop_id==0) {
				$this->error('非法操作.');
			}
		}else{
			$shop_id = intval($_REQUEST['shop_id']);
		}

		if ($shop_id) {
			$where .= ' AND shop_id='.intval($shop_id);
			$shop_name = M('shangchang')->where('id='.intval($shop_id))->getField('name');
			$this->assign('shop_name',$shop_name);
			$this->assign('shop_id',$shop_id);
		}

		for($i=0;$i<12;$i++){
		  //日期
		  if($type=='m'){
			 $day = strtotime(date('Y-m')) - 86400*30*(11-$i);
			 $dayend = $day+86400*30;
			 $day_String .= ',"'.date('Y/m',$day).'"';
		  }else{
			 $day = strtotime(date('Y-m-d')) - 86400*(11-$i);
			 $dayend = $day+86400; 
			 $day_String .= ',"'.date('m/d',$day).'"';
		  }

		  //$hyxl=select('id','aaa_pts_order',"1 $where and addtime>$day and addtime<$dayend",'num');
		  $hyxl = $this->order->where($where.' AND addtime>'.$day." AND addtime<".$dayend)->count('id');
		  $data1.=',['.$i.','.$hyxl.']';
		}
		$this->assign('data1',$data1);
		$this->assign('day_String',$day_String);
		//当天日期的时间戳
		$today = strtotime(date('Y-m-d'));
		$this->assign('today',$today);

		//获取最近订单数据
		$order_list = $this->order->where($where)->order('id desc')->limit('0,20')->select();
		foreach ($order_list as $k => $v) {
			$order_list[$k]['shop_name'] = M('shangchang')->where('id='.$v['shop_id'])->getField('name');
		}
		$this->assign('order_list',$order_list);
		$this->assign('type',$type);
		//print_r($where);die();
		$this->display();
	}

}