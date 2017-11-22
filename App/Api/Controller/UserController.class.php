<?php
// 本类由系统自动生成，仅供测试用途
namespace Api\Controller;

class UserController extends PublicController
{

    Public function verify()
    {
        $image = new \Org\Util\Image();
        $image->buildImageVerify();
    }

    // ***************************
    // 获取用户订单数量
    // ***************************
    public function getorder()
    {
        $uid = intval($_REQUEST['userId']);
        if (! $uid) {
            echo json_encode(array(
                'status' => 0,
                'err' => '非法操作.'
            ));
            exit();
        }
        
        $order = array();
        $order['pay_num'] = intval(M('order')->where('uid=' . intval($uid) . ' AND status=10 AND del=0')->getField('COUNT(id)'));
        $order['rec_num'] = intval(M('order')->where('uid=' . intval($uid) . ' AND status=30 AND del=0 AND back="0"')->getField('COUNT(id)'));
        $order['finish_num'] = intval(M('order')->where('uid=' . intval($uid) . ' AND status>30 AND del=0 AND back="0"')->getField('COUNT(id)'));
        $order['refund_num'] = intval(M('order')->where('uid=' . intval($uid) . ' AND back>"0"')->getField('COUNT(id)'));
        echo json_encode(array(
            'status' => 1,
            'orderInfo' => $order
        ));
        exit();
    }

    public function findfwd_edit()
    {
        $name = $_POST['name'];
        $tel = $_POST['tel'];
        $newpwd = $_POST['newpwd'];
        $newpwds = $_POST['newpwds'];
        if (empty($name)) {
            $this->error('请输入用户名', U('User/findfwd', array(
                'key' => $_REQUEST['key']
            )));
        }
        if (empty($tel)) {
            $this->error('请输入手机号', U('User/findfwd', array(
                'key' => $_REQUEST['key']
            )));
        }
        if ($newpwd != $newpwds) {
            $this->error('两次密码输入不同', U('User/findfwd', array(
                'key' => $_REQUEST['key']
            )));
        } else {
            $name = $_REQUEST['name']; // 帐号
            $tel = $_REQUEST['tel']; // 接受短信用户
            $yzm = $_REQUEST['yzm']; // 验证码
            $data['pwd'] = md5(md5($_REQUEST['newpwd'])); // 新密码
            $sms_o = file_get_contents('Public/Rand/' . $tel . '.txt');
            if ($sms_o != $yzm) {
                $this->error('验证码错误！', U('User/findfwd', array(
                    'key' => $_REQUEST['key']
                )));
            } else {
                $result = M("user")->where('name = "' . $name . '"')->save($data);
                if ($result !== false) {
                    $this->success('修改成功！', U('User/logo', array(
                        'key' => $_REQUEST['key']
                    )));
                } else {
                    $this->error('修改失败！', U('User/logo', array(
                        'key' => $_REQUEST['key']
                    )));
                }
            }
        }
    }

    // ***************************
    // 获取用户信息
    // ***************************
    public function userinfo()
    {
        /*
         * if (!$_SESSION['ID']) {
         * echo json_encode(array('status'=>4));
         * exit();
         * }
         */
        $uid = intval($_REQUEST['uid']);
        if (! $uid) {
            echo json_encode(array(
                'status' => 0,
                'err' => '非法操作.'
            ));
            exit();
        }
        
        $user = M("user")->where('id=' . intval($uid))->find();
        if (! empty($user['provinceid']))
            $user['province'] = M('china_city')->where("code = {$user['provinceid']}")->getField('name');
        if (! empty($user['cityid']))
            $user['city'] = M('china_city')->where("code = {$user['cityid']}")->getField('name');
        if (! empty($user['areaid']))
            $user['area'] = M('china_city')->where("code = {$user['areaid']}")->getField('name');
        if (! empty($user['agent_provinceid']))
            $user['agent_province'] = M('china_city')->where("code = {$user['agent_provinceid']}")->getField('name');
        if (! empty($user['agent_cityid']))
            $user['agent_city'] = M('china_city')->where("code = {$user['agent_cityid']}")->getField('name');
        if (! empty($user['agent_areaid']))
            $user['agent_area'] = M('china_city')->where("code = {$user['agent_areaid']}")->getField('name');
        
        if ($user['photo']) {
            if ($user['source'] == '') {
                $user['photo'] = __DATAURL__ . $user['photo'];
            }
        } else {
            $user['photo'] = __PUBLICURL__ . 'home/images/moren.png';
        }
        echo json_encode(array(
            'status' => 1,
            'userinfo' => $user
        ), JSON_UNESCAPED_UNICODE);
        exit();
    }

    // ***************************
    // 修改用户信息
    // ***************************
    public function user_edit()
    {
        $time = mktime();
        $arr = $_POST['photo'];
        if ($_POST['photo'] != '') {
            $data['photo'] = $arr;
        }
        
        $user_id = intval($_REQUEST['user_id']);
        $old_pwd = $_REQUEST['old_pwd'];
        $pwd = $_REQUEST['new_pwd'];
        $old_tel = $_REQUEST['old_tel'];
        $uname = $_REQUEST['uname'];
        $tel = $_REQUEST['new_tel'];
        
        $user_info = M('user')->where('id=' . intval($user_id) . ' AND del=0')->find();
        if (! $user_info) {
            echo json_encode(array(
                'status' => 0,
                'err' => '会员信息错误.'
            ));
            exit();
        }
        
        // 用户密码检测
        $data = array();
        if ($pwd) {
            $data['pwd'] = md5(md5($pwd));
            if ($user_info['pwd'] && md5(md5($old_pwd)) !== $user_info['pwd']) {
                echo json_encode(array(
                    'status' => 0,
                    'err' => '旧密码不正确.'
                ));
                exit();
            }
        }
        
        // 用户手机号检测
        if ($tel) {
            if ($user_info['tel'] && $old_tel !== $user_info['tel']) {
                echo json_encode(array(
                    'status' => 0,
                    'err' => '原手机号不正确.'
                ));
                exit();
            }
            $check_tel = M('user')->where('tel=' . trim($tel) . ' AND del=0')->count();
            if ($check_tel) {
                echo json_encode(array(
                    'status' => 0,
                    'err' => '新手机号已存在.'
                ));
                exit();
            }
            $data['tel'] = trim($tel);
        }
        
        if ($uname && $uname !== $user_info['uname']) {
            $data['uname'] = trim($uname);
        }
        
        if (! $data) {
            echo json_encode(array(
                'status' => 0,
                'err' => '您没有输入要修改的信息.' . __LINE__
            ));
            exit();
        }
        // dump($data);exit;
        $result = M("user")->where('id=' . intval($user_id))->save($data);
        // echo M("aaa_pts_user")->_sql();exit;
        if ($result) {
            echo json_encode(array(
                'status' => 1
            ));
            exit();
        } else {
            echo json_encode(array(
                'status' => 0,
                'err' => '操作失败.'
            ));
            exit();
        }
    }

    // ***************************
    // 用户反馈接口
    // ***************************
    public function feedback()
    {
        $uid = intval($_REQUEST['uid']);
        if (! $uid) {
            echo json_encode(array(
                'status' => 0,
                'err' => '登录状态异常.'
            ));
            exit();
        }
        
        $con = $_POST['con'];
        if (! $con) {
            echo json_encode(array(
                'status' => 0,
                'err' => '请输入反馈内容.'
            ));
            exit();
        }
        $data = array();
        $data['uid'] = $uid;
        $data['message'] = $con;
        $data['addtime'] = time();
        $res = M('fankui')->add($data);
        if ($res) {
            echo json_encode(array(
                'status' => 1
            ));
            exit();
        } else {
            echo json_encode(array(
                'status' => 0,
                '保存失败！'
            ));
            exit();
        }
    }

    // ***************************
    // 用户商品收藏信息
    // ***************************
    public function collection()
    {
        $user_id = intval($_REQUEST['id']);
        if (! $user_id) {
            echo json_encode(array(
                'status' => 0,
                'err' => '系统错误，请稍后再试.'
            ));
            exit();
        }
        
        $pro_sc = M('product_sc');
        $count = $pro_sc->where('uid=' . intval($user_id))->count(); // 查询满足要求的总记录数
        $Page = new \Org\Util\Page($count, 10); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        
        $sc_list = $pro_sc->where('uid=' . intval($user_id))
            ->order('id desc')
            ->select();
        foreach ($sc_list as $k => $v) {
            $pro_info = M('product')->where('id=' . intval($v['pid']) . ' AND del=0 AND is_down=0')->find();
            if ($pro_info) {
                $sc_list[$k]['pro_name'] = $pro_info['name'];
                $sc_list[$k]['photo'] = __DATAURL__ . $pro_info['photo_x'];
                $sc_list[$k]['price_yh'] = number_format($pro_info['price_yh'], 2);
            } else {
                $pro_sc->where('id=' . intval($v['id']))->delete();
            }
        }
        
        echo json_encode(array(
            'status' => 1,
            'sc_list' => $sc_list
        ));
        exit();
    }

    // ***************************
    // 用户单个商品取消收藏
    // ***************************
    public function collection_qu()
    {
        $sc_id = intval($_REQUEST['id']);
        if (! $sc_id) {
            echo json_encode(array(
                'status' => 0,
                'err' => '非法操作.'
            ));
            exit();
        }
        
        $product = M("product_sc");
        $ress = $product->where('id =' . intval($sc_id))->delete();
        // echo $shangchang->_sql();
        if ($ress) {
            echo json_encode(array(
                'status' => 1
            ));
            exit();
        } else {
            echo json_encode(array(
                'status' => 0,
                'err' => '网络异常！' . __LINE__
            ));
            exit();
        }
    }

    // ***************************
    // 用户单个店铺取消收藏
    // ***************************
    public function unfollow()
    {
        $sc_id = intval($_REQUEST['id']);
        if (! $sc_id) {
            echo json_encode(array(
                'status' => 0,
                'err' => '非法操作.'
            ));
            exit();
        }
        // 取消关注店铺
        $shangchang = M("shangchang_sc");
        $ress = $shangchang->where('id =' . intval($sc_id))->delete();
        // echo $shangchang->_sql();
        if ($ress) {
            echo json_encode(array(
                'status' => 1
            ));
            exit();
        } else {
            echo json_encode(array(
                'status' => 0,
                'err' => '网络异常！' . __LINE__
            ));
            exit();
        }
    }

    // ***************************
    // 获取用户店铺收藏数据
    // ***************************
    public function shangchang()
    {
        // 关注店铺
        $user_id = intval($_REQUEST['user_id']);
        if (! $user_id) {
            echo json_encode(array(
                'status' => 0,
                'err' => '系统错误，请稍后再试.'
            ));
            exit();
        }
        
        $shangchang = M("shangchang_sc");
        $count = $shangchang->where('uid=' . intval($user_id))->count(); // 查询满足要求的总记录数
        $Page = new \Org\Util\Page($count, 4); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        
        $sc_list = $shangchang->where('uid=' . intval($user_id))
            ->order('id desc')
            ->select();
        foreach ($sc_list as $k => $v) {
            $sc_info = M('shangchang')->where('id=' . intval($v['shop_id']))->find();
            if ($sc_info) {
                $sc_list[$k]['shop_name'] = $sc_info['name'];
                $sc_list[$k]['uname'] = $sc_info['uname'];
                $sc_list[$k]['logo'] = 'http://' . $_SERVER['SERVER_NAME'] . __DATA__ . '/' . $sc_info['logo'];
                $sc_list[$k]['tel'] = $sc_info['tel'];
                $sc_list[$k]['sheng'] = M('china_city')->where('id=' . intval($sc_info['sheng']))->getField('name');
                $sc_list[$k]['city'] = M('china_city')->where('id=' . intval($sc_info['city']))->getField('name');
                $sc_list[$k]['quyu'] = M('china_city')->where('id=' . intval($sc_info['quyu']))->getField('name');
            } else {
                $shangchang->where('id=' . intval($v['id']))->delete();
            }
        }
        
        echo json_encode(array(
            'status' => 1,
            'sc_list' => $sc_list
        ));
    }

    // *****************************
    //
    // h5头像上传
    // ******************************
    public function uploadify()
    {
        $imgtype = array(
            'gif' => 'gif',
            'png' => 'png',
            'jpg' => 'jpg',
            'jpeg' => 'jpeg'
        ); // 图片类型在传输过程中对应的头信息
        $message = $_POST['message']; // 接收以base64编码的图片数据
        $filename = $_POST['filename']; // 自定义文件名称
        $ftype = $_POST['filetype']; // 接收文件类型
                                     // 首先将头信息去掉，然后解码剩余的base64编码的数据
        $message = base64_decode(substr($message, strlen('data:image/' . $imgtype[strtolower($ftype)] . ';base64,')));
        $filename2 = $filename . "." . $ftype;
        $furl = "./Data/UploadFiles/user_img/" . date("Ymd");
        if (! is_dir($furl)) {
            @mkdir($furl, 0777);
        }
        $furl = $furl . '/';
        
        // 开始写文件
        $file = fopen($furl . $filename2, "w");
        if (fwrite($file, $message) === false) {
            echo json_encode(array(
                'status' => 0,
                'err' => 'failed'
            ));
            exit();
        }
        
        // //图片URL地址
        $pic_url = $furl . $filename2;
        // $pic_url = "./Data/UploadFiles/user_img/20170115/0.jpeg";
        $image = new \Think\Image();
        $image->open($pic_url);
        // 生成一个居中裁剪为150*150的缩略图并保存为thumb.jpg
        $image->thumb(100, 100, \Think\Image::IMAGE_THUMB_SCALE)->save($pic_url);
        /*
         * echo $pic_url;
         * exit();
         */
        
        $uid = intval($_REQUEST['uid']);
        if (! $uid) {
            echo json_encode(array(
                'status' => 0,
                'err' => '登录状态异常！error'
            ));
            exit();
        }
        // 获取原来的头像链接
        $oldpic = M('user')->where('id=' . intval($uid))->getField('photo');
        $oldpic2 = './Data/' . $oldpic;
        
        $data = array();
        $data['photo'] = "UploadFiles/user_img/" . date("Ymd") . '/' . $filename2;
        $up = M('user')->where('id=' . intval($uid))->save($data);
        if ($up) {
            // 如果原头像存在就删除
            if ($oldpic && file_exists($oldpic2)) {
                @unlink($oldpic2);
            }
            echo json_encode(array(
                'status' => 1,
                'urls' => 'Data/' . $data['photo']
            ));
            exit();
        } else {
            echo json_encode(array(
                'status' => 0,
                'err' => '头像保存失败.'
            ));
            exit();
        }
    }

    // ***************************
    // 用户修改密码接口
    // ***************************
    public function forget_pwd()
    {
        $user_name = trim($_REQUEST['username']);
        $tel = trim($_REQUEST['tel']);
        if (! $user_name || ! $tel) {
            echo json_encode(array(
                'status' => 0,
                'err' => '请输入账号或手机号.'
            ));
            exit();
        }
        
        $where = array();
        $where['name'] = $user_name;
        $where['tel'] = $tel;
        $check = M('user')->where($where)->count();
        if ($check) {
            echo json_encode(array(
                'status' => 1
            ));
            exit();
        } else {
            echo json_encode(array(
                'status' => 0,
                'err' => '账号不存在.'
            ));
            exit();
        }
    }

    // ***************************
    // 用户修改密码接口
    // ***************************
    public function up_pwd()
    {
        $id = trim($_POST['uid']);
        $passtwo = trim($_POST['passtwo']);
        $oldpassword = trim($_POST['old']);
        if (! $id || ! $passtwo || ! $oldpassword) {
            echo json_encode(array(
                'status' => 0,
                'err' => '信息不完全.'
            ));
            exit();
        }
        
        $where = array();
        $where['id'] = $id;
        $where['pwd'] = md5(md5($oldpassword));
        $pwd = md5(md5($passtwo));
        $up = M('user')->where($where)->save(array(
            'pwd' => $pwd
        ));
        if ($up) {
            echo json_encode(array(
                'status' => 1
            ));
            exit();
        } else {
            echo json_encode(array(
                'status' => 0,
                'err' => '账号不存在.'
            ));
            exit();
        }
    }

    // ***************************
    // 获取用户优惠券
    // ***************************
    public function voucher()
    {
        $uid = intval($_REQUEST['uid']);
        if (! $uid) {
            echo json_encode(array(
                'status' => 0,
                'err' => '登录状态异常！' . __LINE__
            ));
            exit();
        }
        
        // 获取未使用或者已失效的优惠券
        $nouse = array();
        $nouses = array();
        $offdate = array();
        $offdates = array();
        $vou_list = M('user_voucher')->where('uid=' . intval($uid) . ' AND status!=2')->select();
        foreach ($vou_list as $k => $v) {
            $vou_info = M('voucher')->where('id=' . intval($v['vid']))->find();
            if (intval($vou_info['del']) == 1 || $vou_info['end_time'] < time()) {
                $offdate['vid'] = intval($vou_info['id']);
                $offdate['full_money'] = floatval($vou_info['full_money']);
                $offdate['amount'] = floatval($vou_info['amount']);
                $offdate['start_time'] = date('Y.m.d', intval($vou_info['start_time']));
                $offdate['end_time'] = date('Y.m.d', intval($vou_info['end_time']));
                $offdates[] = $offdate;
            } elseif ($vou_info['end_time'] > time()) {
                $nouse['vid'] = intval($vou_info['id']);
                $nouse['shop_id'] = intval($vou_info['shop_id']);
                $nouse['title'] = $vou_info['title'];
                $nouse['full_money'] = floatval($vou_info['full_money']);
                $nouse['amount'] = floatval($vou_info['amount']);
                if ($vou_info['proid'] == 'all' || empty($vou_info['proid'])) {
                    $nouse['desc'] = '店内通用';
                } else {
                    $nouse['desc'] = '限定商品';
                }
                $nouse['start_time'] = date('Y.m.d', intval($vou_info['start_time']));
                $nouse['end_time'] = date('Y.m.d', intval($vou_info['end_time']));
                if ($vou_info['proid']) {
                    $proid = explode(',', $vou_info['proid']);
                    $nouse['proid'] = intval($proid[0]);
                }
                $nouses[] = $nouse;
            }
        }
        
        // //获取已使用的优惠券
        $used = array();
        $useds = array();
        $vouusedlist = M('user_voucher')->where('uid=' . intval($uid) . ' AND status=2')->select();
        foreach ($vouusedlist as $k => $v) {
            $vou_info = M('voucher')->where('id=' . intval($v['vid']))->find();
            $used['vid'] = intval($vou_info['id']);
            $used['full_money'] = floatval($vou_info['full_money']);
            $used['amount'] = floatval($vou_info['amount']);
            $used['start_time'] = date('Y.m.d', intval($vou_info['start_time']));
            $used['end_time'] = date('Y.m.d', intval($vou_info['end_time']));
            $useds[] = $used;
        }
        
        echo json_encode(array(
            'status' => 1,
            'offdates' => $offdates,
            'nouses' => $nouses,
            'useds' => $useds
        ));
    }

    /**
     * 银行卡信息修改
     */
    public function bedit()
    {
        $userid = intval($_POST['userid']);
        $username = $_POST['username'];
        $banknum = $_POST['banknum'];
        $bankname = $_POST['bankname'];
        
        $user_info = M('user')->where('id=' . intval($userid) . ' AND del=0')->find();
        if (! $user_info) {
            echo json_encode(array(
                'status' => 0,
                'err' => '会员信息错误.'
            ));
            exit();
        }
        
        $result = M('user')->where('id=' . intval($userid))->setField(array(
            'cardnumber' => $banknum,
            'cardholder' => $username,
            'cardbank' => $bankname
        ));
        if ($result) {
            echo json_encode(array(
                'status' => 1
            ));
            exit();
        } else {
            echo json_encode(array(
                'status' => 0,
                'err' => '操作失败'
            ));
            exit();
        }
    }

    public function smssend()
    {
        $numbers = range(0, 9);
        shuffle($numbers);
        $code = '';
        $tel = trim($_REQUEST['tel']);
        
        if (empty($tel)) {
            echo json_encode(array(
                'status' => 0,
                'err' => '参数错误'
            ));
            exit();
        }
        
        $count = M('sms_reg')->where([
            'tel' => $tel,
            "addtime like '" . date('Y-m-d') . "%'"
        ])->count();
        
        if ($count > 4) {
            echo json_encode(array(
                'status' => 0,
                'err' => '已达当日上限'
            ), JSON_UNESCAPED_UNICODE);
            exit();
        }
        for ($i = 0; $i < 6; $i ++)
            $code .= $numbers[$i];
        
        Vendor('SMS.Send');
        $send = new \Send();
        $res = $send->sendSms($tel, "感谢您注册亚马逊商学院，您的验证码是" . $code . "，请勿泄漏。");
        if ($res) {
            $op = M('sms_reg')->add([
                'tel' => $tel,
                'regcode' => $code,
                'addtime' => date('Y-m-d H:i:s')
            ]);
            if ($op) {
                echo json_encode(array(
                    'status' => 1
                ));
                exit();
            } else {
                echo json_encode(array(
                    'status' => 0,
                    'err' => '操作异常'
                ), JSON_UNESCAPED_UNICODE);
                exit();
            }
        } else {
            echo json_encode(array(
                'status' => 0,
                'err' => '操作失败'
            ), JSON_UNESCAPED_UNICODE);
        }
    }

    public function getBankInfo()
    {
        Vendor('Bank.Bank');
        $bank = new \BankList();
        $num = $_REQUEST['bnum'];
        $bank = $bank->bankInfo($num);
        echo json_encode(array(
            'status' => 1,
            'bank' => $bank
        ));
    }

    /**
     * 用户注册
     */
    public function register()
    {
        $userid = intval($_POST['userid']);
        $provinceid = intval($_POST['provinceid']);
        $cityid = intval($_POST['cityid']);
        $areaid = intval($_POST['areaid']);
        $mobile = trim($_POST['mobile']);
        $realname = trim($_POST['realname']);
        $pwd = md5(md5($_POST['pwd']));
        
        $user_info = M('user')->where('id=' . intval($userid) . ' AND del=0')->find();
        if (! $user_info) {
            echo json_encode(array(
                'status' => 0,
                'err' => '会员信息错误.'
            ));
            exit();
        }
        
        $user = M('user');
        $where = array();
        $where['mobile'] = $mobile;
        $count = $user->where($where)->count();
        if ($count) {
            echo json_encode(array(
                'status' => 0,
                'err' => '手机号已存在！'
            ));
            exit();
        }
        
        $coun = M('sms_reg')->where([
            'tel' => trim($_POST['mobile']),
            'regcode' => trim($_POST['code']),
            "addtime like '" . date('Y-m-d') . "%'"
        ])->count();
        
        if (! $coun) {
            echo json_encode(array(
                'status' => 0,
                'err' => '验证码错误'
            ), JSON_UNESCAPED_UNICODE);
            exit();
        }
        
        $data = array();
        $data['mobile'] = $mobile;
        $data['pwd'] = $pwd;
        $data['uname'] = $realname;
        $data['provinceid'] = $provinceid;
        $data['cityid'] = $cityid;
        $data['areaid'] = $areaid;
        $data['addtime'] = time();
        $res = $user->where(array(
            'id' => $userid
        ))->setField($data);
        if ($res) {
            $_SESSION['LoginName'] = $name;
            $_SESSION['ID'] = $res;
            $arr = array();
            $arr['status'] = 1;
            $arr['uid'] = $res;
            $arr['LoginName'] = $name;
            echo json_encode($arr);
            exit();
        } else {
            echo json_encode(array(
                'status' => 0,
                'err' => '注册失败！'
            ));
            exit();
        }
    }

    public function checkuser()
    {
        $openid = $_POST['openid'];
        $where = array(
            'openid' => $openid
        );
        $user_info = M('user')->where($where)
            ->field('mobile')
            ->find();
        if (! $user_info['mobile']) {
            echo json_encode(array(
                'status' => 0,
                'err' => '会员信息错误.'
            ));
            exit();
        } else {
            echo json_encode(array(
                'status' => 1,
                'userinfo' => $user_info
            ));
            exit();
        }
    }

    public function vipcard()
    {
        $uid = trim($_REQUEST['uid']);
        if (intval($uid) <= 0) {
            echo json_encode(array(
                
                'status' => 0,
                'err' => '参数错误'
            
            ));
            exit();
        }
        // 用户购买的卡
        $vipcard = M('vip_card')->where([
            'uid' => $uid
        ])->select();
        if ($vipcard) {
            $vipcards = array();
            foreach ($vipcard as $k => $v) {
                if (intval($v['pid']) > 0) {
                    $pro = M('product')->where([
                        'id' => $v['pid']
                    ])->find();
                    $pro['photo_x'] = __DATAURL__ . $pro['photo_x'];
                    $codes = M('invite_code')->where([
                        'vip_id' => $v['id']
                    ])->select();
                    $pro['codes'] = $codes;
                    $arr = unserialize($pro['relate']);
                    if (count($arr) > 0) {
                        $list = array();
                        $TIC = 27;
                        $COU = 28;
                        foreach ($arr as $kk => $vv) {
                            $d = M('product')->where('id=' . intval($kk) . ' AND del=0 AND is_down=0')->find();
                            $d['num'] = $vv; // 数量
                            $d['photo_x'] = __DATAURL__ . $d['photo_x'];
                            if ($d['cid'] == $TIC)
                                $d['name'] .= '门票';
                            if ($d['cid'] == $COU)
                                $d['name'] .= '课程';
                            $list[] = $d;
                        }
                        $pro['relate'] = $list;
                        $pro['vip_id'] = $v['id'];
                        $pro['uid'] = $v['uid'];
                        $pro['cou'] = M('course')->where([
                            'vip_id' => $v['id']
                        ])->select();
                        $tickets = M('ticket')->where([
                            'uid' => $uid,
                            'vip_id' => $v['id']
                        ])->select();
                        $pro['tic'] = $tickets;
                    }
                    $vipcards[] = $pro;
                }
            }
            echo json_encode(array(
                'status' => 1,
                'vipcards' => $vipcards
            ), JSON_UNESCAPED_UNICODE);
        } else
            echo json_encode(array(
                'status' => 0,
                'err' => '暂无信息'
            ));
    }

    public function course()
    {
        $uid = trim($_REQUEST['uid']);
        if (intval($uid) <= 0) {
            echo json_encode(array(
                
                'status' => 0,
                'err' => '参数错误'
            
            ));
            exit();
        }
        $course = M('course')->where([
            'uid' => $uid
        ])->select();
        if ($course) {
            $courses = array();
            foreach ($course as $k => $v) {
                if (intval($v['pid']) > 0 && $v['vip_id'] == 0) {
                    $co = M('product')->where([
                        'id' => $v['pid']
                    ])->find();
                    $cou = array();
                    $cou['k'] = $v['id'];
                    $cou['id'] = $co['id'];
                    $cou['name'] = $co['name'];
                    $cou['vip_id'] = $v['vip_id'];
                    $cou['photo_x'] = __DATAURL__ . $co['photo_x'];
                    $cou['status'] = $v['status'];
                    $cou['number'] = $v['number'];
                    $cou['uname'] = $v['uname'];
                    $cou['umobile'] = $v['umobile'];
                    $courses[] = $cou;
                }
            }
            
            echo json_encode(array(
                
                'status' => 1,
                'courses' => $this->discourse($courses)
            
            ), JSON_UNESCAPED_UNICODE);
        } else
            echo json_encode(array(
                
                'status' => 0,
                'err' => '暂无信息'
            
            ));
    }

    private function discourse($courses)
    {
        $ff = array();
        foreach ($courses as $key => $value) {
            $ff[$value['id']] = $value['name'];
        }
        // 先根据课程产品主键处理出不同的课程数组
        $arrout = $out = array();
        foreach ($ff as $k => $v) {
            foreach ($courses as $kk => $vv) {
                if ($k == $vv['id'])
                    $arrout[$k][] = $courses[$kk];
            }
        }
        foreach ($arrout as $kk => $vv) {
            $out[] = $vv;
        }
        return $out;
    }

    // 查询总收益
    public function income()
    {
        $uid = trim($_REQUEST['uid']);
        if (intval($uid) <= 0) {
            echo json_encode(array(
                
                'status' => 0,
                'err' => '参数错误'
            
            ));
            exit();
        }
        $income = M('income')->where([
            'uid' => $uid
        ])->find();
        if ($income)
            echo json_encode(array(
                
                'status' => 1,
                'income' => $income
            
            ), JSON_UNESCAPED_UNICODE);
        else
            echo json_encode(array(
                
                'status' => 0,
                'err' => '暂无信息'
            
            ));
    }

    // 查询收益流水
    public function incomeinfo()
    {
        $uid = trim($_REQUEST['uid']);
        if (intval($uid) <= 0) {
            echo json_encode(array(
                
                'status' => 0,
                'err' => '参数错误'
            
            ));
            exit();
        }
        $incomeinfo = M('income_info')->where([
            'uid' => $uid
        ])
            ->order('id desc')
            ->select();
        foreach ($incomeinfo as $k => $v)
            $incomeinfo[$k]['dateline'] = date('Y年m月d日', $v['dateline']);
        
        if ($incomeinfo)
            echo json_encode(array(
                
                'status' => 1,
                'incomeinfo' => $incomeinfo
            
            ), JSON_UNESCAPED_UNICODE);
        else
            echo json_encode(array(
                
                'status' => 0,
                'err' => '暂无信息'
            
            ));
    }

    // 查询单个VIP卡下的所有邀请码
    public function invitecode()
    {
        $cid = trim($_REQUEST['cid']);
        if (intval($cid) <= 0) {
            echo json_encode(array(
                'status' => 0,
                'err' => '参数错误'
            ));
            exit();
        }
        $codes = M('invite_code')->where([
            'vip_id' => $cid
        ])->select();
        
        if ($codes)
            echo json_encode(array(
                'status' => 1,
                'codes' => $codes
            ), JSON_UNESCAPED_UNICODE);
        else
            echo json_encode(array(
                'status' => 0,
                'err' => '暂无信息'
            ));
    }

    // 计算用户邀请人数，待修改
    public function abc()
    {
        $uid = trim($_REQUEST['uid']);
        if (intval($uid) <= 0) {
            echo json_encode([
                'status' => 0,
                'err' => '参数错误'
            ]);
            exit();
        }
        $vipcards = M('vip_card')->where([
            'uid' => $uid
        ])->select();
        if (! vipcards) {
            echo json_encode([
                'status' => 0,
                'err' => '暂无数据'
            ]);
            exit();
        }
        $count = array();
        foreach ($vipcards as $k => $v) {
            $cc = M('invite_code')->where([
                'vip_id' => $v['id'],
                'number' => $v['invite_code'],
                'status > 0'
            ])->find();
            if ($cc)
                $count[] = $cc;
        }
        $c = 0;
        if (count($count) > 0) {}
        $already_amount = M('income')->where([
            'uid' => $uid
        ])->sum('already_amount');
        if (empty($already_amount))
            $already_amount = 0;
        echo json_encode([
            'status' => 1,
            'b' => count($count),
            'c' => $c,
            'aa' => $already_amount
        ]);
    }

    // 购买门票、课程
    public function useticket()
    {
        $id = $_POST['id'];
        $uid = $_POST['uid'];
        $bn = trim($_POST['bn']);
        $bm = trim($_POST['bm']);
        $type = trim($_POST['type']);
        
        if (empty($type) || empty($id) || empty($uid) || empty($bn) || empty($bm)) {
            echo json_encode([
                'status' => 0,
                'err' => '参数错误'
            ]);
            exit();
        }
        $update = false;
        if ($type == 'tic')
            $update = M('ticket')->where([
                'id' => $id,
                'uid' => $uid
            ])->setField([
                'status' => 1,
                'buyer_name' => $bn,
                'buyer_mobile' => $bm
            ]);
        if ($type == 'cou')
            $update = M('course')->where([
                'id' => $id,
                'uid' => $uid
            ])->setField([
                'status' => 1,
                'uname' => $bn,
                'umobile' => $bm
            ]);
        if ($update) {
            echo json_encode([
                'status' => 1
            ]);
        } else {
            echo json_encode([
                'status' => 0,
                'err' => '更新出错'
            ]);
        }
    }

    public function newincome()
    {
        $uid = $_POST['uid'];
        $number = 0;
        if (intval($uid) <= 0) {
            echo json_encode([
                'status' => 0,
                'err' => '参数错误'
            ]);
            exit();
        }
        
        $user = M('user')->where([
            'id' => $uid
        ])->find();
        if (empty($user['cardholder']) || empty($user['cardnumber']) || empty($user['cardbank'])) {
            echo json_encode([
                'status' => 0,
                'err' => '请先完善银行账户信息'
            ]);
            exit();
        }
        $number = M('income')->where([
            'uid' => $uid
        ])->getField('allow_amount');
        if ($number <= 0) {
            echo json_encode([
                'status' => 0,
                'err' => '可提现金额不足'
            ]);
            exit();
        }
        $in = M('income_log')->save([
            'uid' => $uid,
            'number' => $number,
            'optime' => date('Y-m-d H:i:s'),
            'type' => 1
        ]);
        if ($in) {
            if (M('income')->where([
                'uid' => $uid
            ])->setField([
                'incash_amount' => $number,
                'allow_amount' => 0
            ]))
                echo json_encode([
                    'status' => 1
                ]);
            else
                echo json_encode([
                    'status' => 0,
                    'err' => '操作失败'
                ]);
        } else
            echo json_encode([
                'status' => 0,
                'err' => '提交失败'
            ]);
    }
}