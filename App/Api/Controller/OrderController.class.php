<?php
// 本类由系统自动生成，仅供测试用途
namespace Api\Controller;

use Think\Controller;

class OrderController extends PublicController
{

    // ***************************
    // 用户获取订单信息接口
    // ***************************
    public function index()
    {
        $uid = intval($_REQUEST['uid']);
        if (! $uid) {
            echo json_encode(array(
                'status' => 0,
                'err' => '登录状态异常'
            ));
            exit();
        }
        
        // 分页
        $pages = intval($_REQUEST['page']);
        if (! $pages) {
            $pages = 0;
        }
        
        $orders = M("order");
        $orderp = M("order_product");
        $shangchang = M('shangchang');
        
        // 按条件查询
        $condition = array();
        $condition['del'] = 0;
        $condition['back'] = '0';
        $condition['uid'] = intval($uid);
        $condition['status'] = 10;
        $order_type = trim($_REQUEST['order_type']);
        if ($order_type) {
            switch ($order_type) {
                case 'pay':
                    $condition['status'] = 10;
                    break;
                case 'deliver':
                    $condition['status'] = 20;
                    break;
                case 'receive':
                    $condition['status'] = 30;
                    break;
                case 'evaluate':
                    $condition['status'] = 40;
                    break;
                case 'finish':
                    $condition['status'] = array(
                        'IN',
                        array(
                            40,
                            50
                        )
                    );
                    break;
                default:
                    $condition['status'] = 10;
                    break;
            }
        }
        
        // 获取总页数
        $count = $orders->where($condition)->count();
        $eachpage = 7;
        
        $order_status = array(
            '0' => '已取消',
            '10' => '待付款',
            '20' => '待发货',
            '30' => '待收货',
            '40' => '待评价',
            '50' => '交易完成',
            '51' => '交易关闭'
        );
        
        $order = $orders->where($condition)
            ->order('id desc')
            ->field('id,order_sn,pay_sn,status,price,type,product_num')
            ->limit($pages . ',7')
            ->select();
        foreach ($order as $n => $v) {
            $order[$n]['desc'] = $order_status[$v['status']];
            $prolist = $orderp->where('order_id=' . intval($v['id']))->find();
            $order[$n]['photo_x'] = __DATAURL__ . $prolist['photo_x'];
            $order[$n]['pid'] = $prolist['pid'];
            $order[$n]['name'] = $prolist['name'];
            $order[$n]['price_yh'] = $prolist['price'];
            $order[$n]['pro_count'] = $orderp->where('order_id=' . intval($v['id']))->getField('COUNT(id)');
        }
        
        echo json_encode(array(
            'status' => 1,
            'ord' => $order,
            'eachpage' => $eachpage
        ));
        exit();
    }

    // ***************************
    // 用户获取订单信息接口
    // ***************************
    public function get_more()
    {
        $uid = intval($_REQUEST['uid']);
        if (! $uid) {
            echo json_encode(array(
                'status' => 0,
                'err' => '登录状态异常'
            ));
            exit();
        }
        
        // 分页
        $pages = intval($_REQUEST['page']);
        if (! $pages) {
            $pages = 2;
        }
        $limit = $pages * 7 - 7;
        
        $orders = M("order");
        $orderp = M("order_product");
        $shangchang = M('shangchang');
        
        // 按条件查询
        $condition = array();
        $condition['del'] = 0;
        $condition['back'] = '0';
        $condition['uid'] = intval($uid);
        $condition['status'] = 10;
        $order_type = trim($_REQUEST['order_type']);
        if ($order_type) {
            switch ($order_type) {
                case 'pay':
                    $condition['status'] = 10;
                    break;
                case 'deliver':
                    $condition['status'] = 20;
                    break;
                case 'receive':
                    $condition['status'] = 30;
                    break;
                case 'evaluate':
                    $condition['status'] = 40;
                    break;
                case 'finish':
                    $condition['status'] = array(
                        'IN',
                        array(
                            40,
                            50
                        )
                    );
                    break;
                default:
                    $condition['status'] = 10;
                    break;
            }
        }
        
        // 获取总页数
        $count = $orders->where($condition)->count();
        $eachpage = 7;
        
        $order_status = array(
            '0' => '已取消',
            '10' => '待付款',
            '20' => '待发货',
            '30' => '待收货',
            '40' => '待评价',
            '50' => '交易完成',
            '51' => '交易关闭'
        );
        
        $order = $orders->where($condition)
            ->order('id desc')
            ->field('id,order_sn,pay_sn,status,price,type,product_num')
            ->limit($limit . ',7')
            ->select();
        foreach ($order as $n => $v) {
            $order[$n]['desc'] = $order_status[$v['status']];
            $prolist = $orderp->where('order_id=' . intval($v['id']))->find();
            $order[$n]['photo_x'] = __DATAURL__ . $prolist['photo_x'];
            $order[$n]['pid'] = $prolist['pid'];
            $order[$n]['name'] = $prolist['name'];
            $order[$n]['price_yh'] = $prolist['price'];
            $order[$n]['pro_count'] = $orderp->where('order_id=' . intval($v['id']))->getField('COUNT(id)');
        }
        
        echo json_encode(array(
            'status' => 1,
            'ord' => $order
        ));
        exit();
    }

    // ***************************
    // 用户退款退货接口
    // ***************************
    public function order_refund()
    {
        $uid = intval($_REQUEST['uid']);
        if (! $uid) {
            echo json_encode(array(
                'status' => 0,
                'err' => '登录状态异常'
            ));
            exit();
        }
        
        // 分页
        $pages = intval($_REQUEST['page']);
        if (! $pages) {
            $pages = 0;
        }
        
        $orders = M("order");
        $orderp = M("order_product");
        $shangchang = M('shangchang');
        
        $condition = array();
        $condition['back'] = array(
            'gt',
            '0'
        );
        // 获取总页数
        $count = $orders->where($condition)->count();
        $the_page = ceil($count / 6);
        
        $refund_status = array(
            '1' => '退款申请中',
            '2' => '已退款',
            '3' => '处理中',
            '4' => '已拒绝'
        );
        
        $order = $orders->where($condition)
            ->order('back_addtime desc')
            ->field('id,price,order_sn,product_num,back,back_addtime')
            ->limit($pages . ',6')
            ->select();
        foreach ($order as $n => $v) {
            $order[$n]['desc'] = $refund_status[$v['back']];
            $prolist = $orderp->where('order_id=' . intval($v['id']))->find();
            $order[$n]['photo_x'] = __DATAURL__ . $prolist['photo_x'];
            $order[$n]['pid'] = $prolist['pid'];
            $order[$n]['name'] = $prolist['name'];
            $order[$n]['price_yh'] = $prolist['price'];
            $order[$n]['back_addtime'] = date("Y-m-d H:i", $v['back_addtime']);
            $order[$n]['pro_count'] = $orderp->where('order_id=' . intval($v['id']))->getField('COUNT(id)');
        }
        
        echo json_encode(array(
            'status' => 1,
            'ord' => $order
        ));
        exit();
    }

    // ***************************
    // 用户退款退货接口
    // ***************************
    public function get_refund_more()
    {
        $uid = intval($_REQUEST['uid']);
        if (! $uid) {
            echo json_encode(array(
                'status' => 0,
                'err' => '登录状态异常'
            ));
            exit();
        }
        
        // 分页
        $pages = intval($_REQUEST['page']);
        if (! $pages) {
            $pages = 2;
        }
        $limit = $pages * 6 - 6;
        
        $orders = M("order");
        $orderp = M("order_product");
        $shangchang = M('shangchang');
        
        $condition = array();
        $condition['back'] = array(
            'gt',
            '0'
        );
        // 获取总页数
        $count = $orders->where($condition)->count();
        $the_page = ceil($count / 6);
        
        $refund_status = array(
            '1' => '退款申请中',
            '2' => '已退款',
            '3' => '处理中',
            '4' => '已拒绝'
        );
        
        $order = $orders->where($condition)
            ->order('back_addtime desc')
            ->field('id,price,order_sn,product_num,back,back_addtime')
            ->limit($limit . ',6')
            ->select();
        foreach ($order as $n => $v) {
            $order[$n]['desc'] = $refund_status[$v['back']];
            $prolist = $orderp->where('order_id=' . intval($v['id']))->find();
            $order[$n]['photo_x'] = __DATAURL__ . $prolist['photo_x'];
            $order[$n]['pid'] = $prolist['pid'];
            $order[$n]['name'] = $prolist['name'];
            $order[$n]['price_yh'] = $prolist['price'];
            $order[$n]['back_addtime'] = date("Y-m-d H:i", $v['back_addtime']);
            $order[$n]['pro_count'] = $orderp->where('order_id=' . intval($v['id']))->getField('COUNT(id)');
        }
        
        echo json_encode(array(
            'status' => 1,
            'ord' => $order
        ));
        exit();
    }

    // ***************************
    // 用户订单编辑接口
    // ***************************
    public function orders_edit()
    {
        $orders = M("order");
        $order_id = intval($_REQUEST['id']);
        $type = $_REQUEST['type'];
        
        $check_id = $orders->where('id=' . intval($order_id) . ' AND del=0')->getField('id');
        if (! $check_id || ! $type) {
            echo json_encode(array(
                'status' => 0,
                'err' => '订单信息错误.' . __LINE__
            ));
            exit();
        }
        
        $data = array();
        if ($type === 'cancel') {
            $data['status'] = 0;
        } elseif ($type === 'receive') {
            $data['status'] = 40;
        } elseif ($type === 'refund') {
            $data['back'] = 1;
            $data['back_remark'] = $_REQUEST['back_remark'];
        }
        
        if ($data) {
            $result = $orders->where('id=' . intval($order_id))->save($data);
            if ($result !== false) {
                echo json_encode(array(
                    'status' => 1
                ));
                exit();
            } else {
                echo json_encode(array(
                    'status' => 0,
                    'err' => '操作失败.' . __LINE__
                ));
                exit();
            }
        } else {
            echo json_encode(array(
                'status' => 0,
                'err' => '订单信息错误.' . __LINE__
            ));
            exit();
        }
    }

    // ***************************
    // 用户订单详情接口
    // ***************************
    public function order_details()
    {
        $order_id = intval($_REQUEST['order_id']);
        // 订单详情
        $orders = M("order");
        $product_dp = M("product_dp");
        $orderp = M("order_product");
        $id = intval($_REQUEST['id']);
        $qz = C('DB_PREFIX'); // 前缀
        
        $order_info = $orders->where('id=' . intval($order_id . ' AND del=0'))
            ->field('id,order_sn,shop_id,status,addtime,price,type,post,tel,receiver,address_xq,remark')
            ->find();
        if (! $order_info) {
            echo json_encode(array(
                'status' => 0,
                'err' => '订单信息错误.'
            ));
            exit();
        }
        
        // 订单状态
        $order_status = array(
            '0' => '已取消',
            '10' => '待付款',
            '20' => '待发货',
            '30' => '待收货',
            '40' => '已收货',
            '50' => '交易完成'
        );
        // 支付类型
        $pay_type = array(
            'cash' => '现金支付',
            'alipay' => '支付宝',
            'weixin' => '微信支付'
        );
        
        $order_info['shop_name'] = M('shangchang')->where('id=' . intval($order_info['shop_id']))->getField('name');
        $order_info['order_status'] = $order_status[$order_info['status']];
        $order_info['pay_type'] = $pay_type[$order_info['type']];
        $order_info['addtime'] = date('Y-m-d H:i:s', $order_info['addtime']);
        $order_info['yunfei'] = 0;
        if ($order_info['post']) {
            $order_info['yunfei'] = M('post')->where('id=' . intval($order_info['post']))->getField('price');
        }
        
        // 获取产品
        $pro = $orderp->where('order_id=' . intval($order_info['id']))->select();
        foreach ($pro as $k => $v) {
            $pro[$k]['photo_x'] = __DATAURL__ . $v['photo_x'];
        }
        
        echo json_encode(array(
            'status' => 1,
            'pro' => $pro,
            'ord' => $order_info
        ));
        exit();
    }

    // ***************************
    // 用户订单评论接口
    // ***************************
    public function order_comment()
    {
        $id = explode(',', $_GET['id']);
        
        $orderid = (int) $_GET['orderid'];
        $order_product = M('order_product');
        $order = M('order');
        $orderr = $order->where('id=' . $orderid)->select();
        foreach ($id as $key => $value) {
            $result[$key] = $order_product->where('`order`=' . $orderid . ' and pid=' . $value)->select();
        }
        $this->assign('result', $result);
        $this->assign('orderr', $orderr);
        $this->display();
    }

    // ***************************
    // 用户订单评论接口
    // ***************************
    public function addMessage()
    {
        $product_dp = M('product_dp');
        $order_product = M("order_product");
        $order = M("order");
        // 获取商品的ID
        $id = $_POST['pid'];
        $id = explode(",", $id);
        $data['orderid'] = $_POST['orderid'];
        $data['type'] = 1;
        $status['mstatus'] = 1;
        $data['uid'] = $_SESSION['ID'];
        $data['addtime'] = time();
        foreach ($id as $key => $value) {
            $data['pid'] = $value;
            $data['concent'] = $_POST['content' . $data['pid']];
            $data['num'] = $_POST['pingfen' . $data['pid']];
            $result = $product_dp->add($data);
        }
        /*
         * $meresult = $product_dp->where('uid='.$_SESSION['ID'].' and orderid='.$_POST['orderid'].' and pid='.$_POST['id'])->select();
         * if($meresult){
         * echo 2;
         * exit();
         * }
         */
        
        if ($result) {
            $order->where('id=' . $_POST['orderid'])->save($status);
            $this->success('评价成功', U('User/orders', array(
                'key' => $_POST['key']
            )));
        } else {
            $this->error('评价失败');
        }
    }
    /**
     * CREATE TABLE `lr_order` (
  `id` int(11) NOT NULL COMMENT '订单id',
  `order_sn` varchar(20) NOT NULL COMMENT '订单编号',
  `pay_sn` varchar(20) DEFAULT NULL COMMENT '支付单号',
  `shop_id` int(11) NOT NULL DEFAULT '0' COMMENT '商家ID',
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `price` decimal(9,2) NOT NULL DEFAULT '0.00' COMMENT '价格',
  `amount` decimal(9,2) DEFAULT '0.00' COMMENT '优惠后价格',
  `addtime` int(10) NOT NULL DEFAULT '0' COMMENT '购买时间',
  `del` tinyint(2) NOT NULL DEFAULT '0' COMMENT '删除状态',
  `type` enum('weixin','alipay','cash') DEFAULT 'weixin' COMMENT '支付方式',
  `price_h` decimal(9,2) NOT NULL DEFAULT '0.00' COMMENT '真实支付金额',
  `status` tinyint(2) NOT NULL DEFAULT '10' COMMENT '订单状态{0,已取消10未付款20代发货30待收货40待评价50交易完成51交易关闭',
  `vid` int(11) DEFAULT '0' COMMENT '优惠券ID',
  `receiver` varchar(15) NOT NULL COMMENT '收货人',
  `tel` char(15) NOT NULL COMMENT '联系方式',
  `address_xq` varchar(50) NOT NULL COMMENT '地址详情',
  `code` int(11) NOT NULL COMMENT '邮编',
  `post` int(11) DEFAULT NULL COMMENT '快递ID',
  `remark` varchar(255) DEFAULT NULL COMMENT '买家留言',
  `post_remark` varchar(255) NOT NULL COMMENT '邮费信息',
  `product_num` int(11) NOT NULL DEFAULT '1' COMMENT '商品数量',
  `trade_no` varchar(50) DEFAULT NULL COMMENT '微信交易单号',
  `kuaidi_name` varchar(10) DEFAULT NULL COMMENT '快递名称',
  `kuaidi_num` varchar(20) DEFAULT NULL COMMENT '运单号',
  `back` enum('1','2','0') DEFAULT '0' COMMENT '标识客户是否有发起退款1申请退款 2已退款',
  `back_remark` varchar(255) DEFAULT NULL COMMENT '退款原因',
  `back_addtime` int(11) DEFAULT '0' COMMENT '申请退款时间',
  `order_type` tinyint(2) DEFAULT '1' COMMENT '订单类型 1普通订单 2抢购订单'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
     */
    
}