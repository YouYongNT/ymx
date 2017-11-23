<?php
namespace Admin\Controller;

use Think\Controller;

class UserController extends PublicController
{
    static $statusarr = ['待审核','已通过','已驳回']; 

    // *************************
    // 普通会员的管理
    // *************************
    public function index()
    {
        $aaa_pts_qx = 1;
        $type = $_GET['type'];
        $id = (int) $_GET['id'];
        $mobile = trim($_REQUEST['mobile']);
        $name = trim($_REQUEST['name']);
        $names = $this->htmlentities_u8($_GET['name']);
        
        // 搜索
        $where = "1=1";
        $name != '' ? $where .= " and uname like '%$names%'" : null;
        $mobile != '' ? $where .= " and mobile like '%$mobile%'" : null;
        
        define('rows', 20);
        $count = M('user')->where($where)->count();
        $rows = ceil($count / rows);
        
        $page = (int) $_GET['page'];
        $page < 0 ? $page = 0 : '';
        $limit = $page * rows;
        $userlist = M('user')->where($where)
            ->order('id desc')
            ->limit($limit, rows)
            ->select();
        $page_index = $this->page_index($count, $rows, $page);
        foreach ($userlist as $k => $v) {
            $userlist[$k]['addtime'] = date("Y-m-d H:i", $v['addtime']);
            if ($userlist[$k]['vip_starttime']) {
                $userlist[$k]['vip_starttime'] = date("Y-m-d H:i", $v['vip_starttime']);
            }
            if ($userlist[$k]['vip_endtime']) {
                $userlist[$k]['vip_endtime'] = date("Y-m-d H:i", $v['vip_endtime']);
            }
        }
        // ====================
        // 将GET到的参数输出
        // =====================
        $this->assign('name', $name);
        $this->assign('mobile', $mobile);
        
        // =============
        // 将变量输出
        // =============
        $this->assign('page_index', $page_index);
        $this->assign('page', $page);
        $this->assign('userlist', $userlist);
        $this->display();
    }

    /**
     * 增加/编辑会员
     */
    public function add()
    {
        $id = intval($_REQUEST['id']);
        if (isset($_POST['submit'])) {
            try {
                $array = array(
                    'name' => $_POST['name'],
                    'uname' => $_POST['name'],
                    'mobile' => $_POST['mobile'],
                    'group_id' => $_POST['group_id'],
                    'provinceid' => intval($_POST['provinceid']),
                    'cityid' => intval($_POST['cityid']),
                    'areaid' => intval($_POST['areaid']),
                    'vip_starttime' => strtotime($_POST['vip_starttime']),
                    'vip_endtime' => strtotime($_POST['vip_endtime']),
                    'del' => intval($_POST['del']),
                    'is_agent' => intval($_POST['is_agent']),
                    'agent_provinceid' => intval($_POST['agent_provinceid']),
                    'agent_cityid' => intval($_POST['agent_cityid']),
                    'agent_areaid' => intval($_POST['agent_areaid'])
                );
                if ($_POST['password'] != '') {
                    $array['pwd'] = md5(md5($_POST['password']));
                }
                // 执行添加
                if ($id) {
                    // 将空数据排除掉，防止将原有数据空置
                    foreach ($array as $k => $v) {
                        if (empty($v)) {
                            unset($v);
                        }
                    }
                    $sql = M('user')->where('id=' . $id)->save($array);
                } else {
                    $array['addtime'] = time();
                    $sql = M('user')->add($array);
                    $id = $sql;
                }
                
                // 规格操作
                if ($sql) {
                    $this->success('操作成功.');
                    exit();
                } else {
                    throw new \Exception('操作失败.');
                }
            } catch (\Exception $e) {
                echo "<script>alert('" . $e->getMessage() . "');location='{:U('user')}';</script>";
            }
        }
        
        if ($id) {
            $info = M('user')->where('id=' . intval($id))->find();
            $province_list = M('china_city')->where('tid=0')->select();
            if ($info['areaid']) {
                $city = M('china_city')->where("code = {$info['areaid']}")->find();
                $area_list = M('china_city')->where('tid=' . $city['tid'])->select();
            }
            if ($info['provinceid']) {
                $province = M('china_city')->where("code = {$info['provinceid']}")->find();
                $city_list = M('china_city')->where('tid=' . $province['id'])->select();
            }
            
            $this->assign('id', $id);
            $this->assign('province_list', $province_list);
            $this->assign('city_list', $city_list);
            $this->assign('area_list', $area_list);
            $this->assign('userinfo', $info);
        }
        
        // 会员组
        $group_list = M('user_group')->select();
        $this->assign('group_list', $group_list);
        $this->display();
    }

    /**
     * ajax 通过ID获取下级地区
     */
    public function getArea()
    {
        $id = intval($_REQUEST['id']);
        $arealist = M('china_city')->where('tid=' . $id)->select();
        echo json_encode(array(
            'arealist' => $arealist
        ));
        exit();
    }

    // *************************
    // 会员地址管理
    // *************************
    public function address()
    {
        // $aaa_pts_qx=1;
        $id = (int) $_GET['id'];
        if ($id < 1) {
            return;
        }
        if ($_GET['type'] == 'del' && $id > 0 && $_SESSION['admininfo']['qx'] == 4) {
            $this->delete('address', $id);
        }
        // 搜索
        $address = M('address')->where("uid=$id")->select();
        
        // =============
        // 将变量输出
        // =============
        $this->assign('address', $address);
        $this->display();
    }

    /**
     * 禁用
     */
    public function del()
    {
        $id = intval($_REQUEST['did']);
        $info = M('user')->where('id=' . intval($id))->find();
        if (! $info) {
            $this->error('会员信息错误.' . __LINE__);
            exit();
        }
        
        $data = array();
        $data['del'] = $info['del'] == '1' ? 0 : 1;
        $up = M('user')->where('id=' . intval($id))->save($data);
        if ($up) {
            $this->redirect('User/index', array(
                'page' => intval($_REQUEST['page'])
            ));
            exit();
        } else {
            $this->error('操作失败.');
            exit();
        }
    }

    /**
     * 删除用户
     */
    public function dodel()
    {
        $id = intval($_REQUEST['did']);
        $info = M('user')->where('id=' . intval($id))->find();
        if (! $info) {
            $this->error('会员信息错误.' . __LINE__);
            exit();
        }
        
        // 删除
        if (M('user')->where('id=' . intval($id))->delete()) {
            M('user_course')->where('uid=' . intval($id))->delete();
            M('user_voucher')->where('uid=' . intval($id))->delete();
            
            $this->redirect('User/index', array(
                'page' => intval($_REQUEST['page'])
            ));
            exit();
        } else {
            $this->error('操作失败.');
            exit();
        }
    }

    /**
     * 收益列表
     */
    public function income()
    {
        $mobile = trim($_REQUEST['mobile']);
        $name = trim($_REQUEST['name']);
        $names = $this->htmlentities_u8($_GET['name']);
        
        // 搜索
        $where = "1=1";
        $name != '' ? $where .= " and u.uname like '%$names%'" : null;
        $mobile != '' ? $where .= " and u.mobile like '%$mobile%'" : null;
        
        define('rows', 20);
        $count = M('income')->alias("i")
            ->join('left join lr_user as u on i.uid=u.id')
            ->where($where)
            ->count();
        $rows = ceil($count / rows);
        
        $page = (int) $_GET['page'];
        $page < 0 ? $page = 0 : '';
        $limit = $page * rows;
        $incomelist = M('income')->alias("i")
            ->join('left join lr_user as u on i.uid=u.id')
            ->where($where)
            ->field('u.uname,u.mobile,i.*')
            ->order('id desc')
            ->limit($limit, rows)
            ->select();
        $page_index = $this->page_index($count, $rows, $page);
        // ====================
        // 将GET到的参数输出
        // =====================
        $this->assign('name', $name);
        $this->assign('mobile', $mobile);
        
        // =============
        // 将变量输出
        // =============
        $this->assign('page_index', $page_index);
        $this->assign('page', $page);
        $this->assign('incomelist', $incomelist);
        $this->display();
    }

    public function income_info()
    {
        $uid = trim($_REQUEST['id']);
        $income = M('income')->alias("i")
            ->join('left join lr_user as u on i.uid=u.id')
            ->where("u.id = $uid")
            ->field('u.uname,u.mobile,i.*')
            ->find();
        $incomeinfo = M('income_info')->alias("i")
            ->join('left join lr_user as u on i.uid=u.id')
            ->where("u.id = $uid")
            ->field('u.uname,u.mobile,i.*')
            ->order('id desc')
            ->select();
        foreach ($incomeinfo as $k => $v) {
            $incomeinfo[$k]['optime'] = date("Y-m-d H:i:s", $v['dateline']);
        }
        $this->assign('income', $income);
        $this->assign('incomeinfo', $incomeinfo);
        $this->display();
    }
    public function newincome()
    {
        $mobile = trim($_REQUEST['mobile']);
        $name = trim($_REQUEST['name']);
        $names = $this->htmlentities_u8($_GET['name']);
        
        // 搜索
        $where = "1=1";
        $name != '' ? $where .= " and u.uname like '%$names%'" : null;
        $mobile != '' ? $where .= " and u.mobile like '%$mobile%'" : null;
        
        define('rows', 20);
        $count = M('income_log')->alias("i")
            ->join('left join lr_user as u on i.uid=u.id')
            ->where($where)
            ->count();
        $rows = ceil($count / rows);
        
        $page = (int) $_GET['page'];
        $page < 0 ? $page = 0 : '';
        $limit = $page * rows;
        $incomelist = M('income_log')->alias("i")
            ->join('left join lr_user as u on i.uid=u.id')
            ->where($where)
            ->field('u.uname,u.mobile,u.cardholder,u.cardnumber,u.cardbank,i.*')
            ->order('id desc')
            ->limit($limit, rows)
            ->select();
        $page_index = $this->page_index($count, $rows, $page);
        // ====================
        // 将GET到的参数输出
        // =====================
        $this->assign('name', $name);
        $this->assign('mobile', $mobile);
        
        // =============
        // 将变量输出
        // =============
        foreach ($incomelist as $kk => $vv)
            $incomelist[$kk]['ss'] = self::$statusarr[$vv['status']];
        $this->assign('page_index', $page_index);
        $this->assign('page', $page);
        $this->assign('incomelist', $incomelist);
        $this->display();
    }

    public function newincome_view()
    {
        $id = trim($_REQUEST['id']);
        $incomelog = M('income_log')->alias("i")
            ->join('left join lr_user as u on i.uid=u.id')
            ->where("i.id = $id")
            ->field('u.uname,u.mobile,u.cardholder,u.cardnumber,u.cardbank,i.*')
            ->find();
        $this->assign('incomelog', $incomelog);
        $this->display();
    }

    public function newincomeopt()
    {
        $id = trim($_POST['id']);
        $status = trim($_POST['status']);
        $uid = trim($_POST['uid']);
        $number = trim($_POST['number']);
        if (intval($status) <= 0)
            $this->error('操作失败');
        $ilog = M('income_log')->where([
            'id' => $id
        ])->find();
        if (! $ilog)
            $this->error('非法操作');
        if ($ilog['status'] > 0)
            $this->error('订单已操作');
        $update = M('income_log')->where([
            'id' => $id
        ])->setField([
            'status' => $status
        ]);
        if (! $update)
            $this->error('订单操作失败');
        // 提现通过，需更新相应的income表记录
        // 若通过，扣除提现中金额，增加已提现金额
        if ($update && $status == 1 && intval($uid) > 0) {
            if (M('income')->where([
                'uid' => $uid
            ])->setInc('already_amount', $number) && M('income')->where([
                'uid' => $uid
            ])->setDec('incash_amount', $number))
                $this->success('操作成功');
            else
                $this->error('操作失败');
        }
        // 若驳回，扣除提现中金额，增加可提现金额
        if ($update && $status == 2 && intval($uid) > 0) {
            if (M('income')->where([
                'uid' => $uid
            ])->setInc('allow_amount', $number) && M('income')->where([
                'uid' => $uid
            ])->setDec('incash_amount', $number))
                $this->success('操作成功');
            else
                $this->error('操作失败');
        }
    }

    /**
     * 会员组管理
     */
    public function group()
    {
        $grouplist = M('user_group')->order('id desc')->select();
        
        $this->assign('grouplist', $grouplist);
        $this->display();
    }

    /**
     * 会员组添加
     */
    public function add_group()
    {
        $id = intval($_REQUEST['id']);
        if (isset($_POST['submit'])) {
            try {
                $array = array(
                    'group_name' => $_POST['group_name'],
                    'discount' => $_POST['discount']
                );
                // 执行添加
                if ($id) {
                    // 将空数据排除掉，防止将原有数据空置
                    foreach ($array as $k => $v) {
                        if (empty($v)) {
                            unset($v);
                        }
                    }
                    $sql = M('user_group')->where('id=' . $id)->save($array);
                } else {
                    $sql = M('user_group')->add($array);
                    $id = $sql;
                }
                
                // 规格操作
                if ($sql) {
                    $this->success('操作成功.');
                    exit();
                } else {
                    throw new \Exception('操作失败.');
                }
            } catch (\Exception $e) {
                echo "<script>alert('" . $e->getMessage() . "');location='{:U('User/group')}';</script>";
            }
        }
        
        if ($id) {
            $info = M('user_group')->where('id=' . intval($id))->find();
            
            $this->assign('id', $id);
            $this->assign('groupinfo', $info);
        }
        $this->display();
    }

    /**
     * 删除用户组
     */
    public function dodel_group()
    {
        $id = intval($_REQUEST['did']);
        $info = M('user_group')->where('id=' . intval($id))->find();
        if (! $info) {
            $this->error('会员组信息错误.' . __LINE__);
            exit();
        }
        
        $ulist = M('user')->where('group_id=' . intval($id))->find();
        if (! empty($ulist)) {
            $this->error('该会员组被会员绑定，请修改会员信息后删除会员组.' . __LINE__);
            exit();
        }
        
        // 删除
        if (M('user_group')->where('id=' . intval($id))->delete()) {
            $this->redirect('User/group');
            exit();
        } else {
            $this->error('操作失败.');
            exit();
        }
    }
}