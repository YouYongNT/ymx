<?php
namespace Admin\Controller;
use Think\Controller;
class AreaController extends PublicController{

	/*
	*
	* 构造函数，用于导入外部文件和公共方法
	*/
	public function _initialize(){
		$this->Area = M('China_city');
		// 获取所有分类，进行关系划分
		$list = $this->Area->where('tid=0')->order('id asc')->field('id,tid,name,code')->select();
		foreach ($list as $k1 => $v1) {
			$list[$k1]['list2'] = $this->Area->where('tid='.intval($v1['id']))->field('id,tid,name,code')->select();
			foreach ($list[$k1]['list2'] as $k2 => $v2) {
				$list[$k1]['list2'][$k2]['list3'] = $this->Area->where('tid='.intval($v2['id']))->field('id,tid,name,code')->select();
			}
		}
		$this->assign('list',$list);// 赋值数据集
	}

	/*
	*
	* 获取、查询栏目表数据
	*/
	public function index(){
		
		$this->display(); // 输出模板
	}


	/*
	*
	* 跳转添加或修改栏目页面
	*/
	public function add(){
		//如果是修改，则查询对应分类信息
		if (intval($_GET['aid'])) {
			$area_id = intval($_GET['aid']);
		
			$area_info = $this->Area->where('id='.intval($area_id))->find();
			if (!$area_info) {
				$this->error('没有找到相关信息.');
			}
			$this->assign('area_info',$area_info);
		}
		$this->assign('tid',intval($_GET['tid']));
		$this->display();
	}


	/*
	*
	* 添加或修改栏目信息
	*/
	public function save(){
		//限制三级以上分类添加的判断
		$tid = intval($_POST['tid']);
		//获取用户选择分类的父级分类
		$tid1 = $this->Area->where('id='.intval($tid))->getField('tid');
		if (intval($tid1)) {
			$tid2 = $this->Area->where('id='.intval($tid1))->getField('tid');
			if (intval($tid2)) {
				$c_info = $this->Area->where('id='.intval($tid2))->find();
				if ($c_info) {
					$this->error('该栏目已经是第三级分类，不可添加下级分类.');
				}
			}
		}

		//判断是否已经存在该栏目
		if (!intval($_POST['aid'])) {
			$check_id = $this->Area->where('tid='.intval($tid).' AND name="'.trim($_POST['name']).'"')->getField('id');
			if (is_int($check_id)) {
				$this->error('该栏目已存在.');
			}
		}
		
		if ($tid>0 && $tid==intval($_POST['aid'])) {
			$this->error('所属栏目不能成为自己的上级.');
		}

		//构建数组
		$this->Area->create();
		//保存数据
		if (intval($_POST['aid'])) {
			$result = $this->Area->where('id='.intval($_POST['aid']))->save();
		}else{
			$result = $this->Area->add();
		}
		//判断数据是否更新成功
		if ($result) {
			$this->success('操作成功.','index');
		}else{
			$this->error('操作失败.');
		}
	}


	/*
	*
	*  设置栏目推荐
	*/
	public function set_tj(){
		$tj_id = intval($_GET['tj_id']);
		$area_info = $this->Area->where('id='.intval($tj_id))->find();
		if (!$area_info) {
			$this->error('栏目信息错误.');
		}
		$data=array();
		$data['bz_2'] = $area_info['bz_2'] == '1' ?  0 : 1;
		$up = $this->Area->where('id='.intval($tj_id))->save($data);
		if ($up) {
			$this->success('操作成功.');
		}else{
			$this->error('操作失败.');
		}
	}

	/*
	*
	* 栏目删除
	*/
	public function del(){
		//以后删除还要加权限登录判断
		$id = intval($_GET['did']);
		$check_info = $this->Area->where('id='.intval($id))->find();
		if (!$check_info) {
			$this->error('非法操作.');
			exit();
		}

		//判断该分类下是否还有子分类
		$check_id = $this->Area->where('tid='.intval($id))->getField('id');
		if ($check_id) {
			$this->error('该栏目下存在子栏目，请先删除子栏目！');
		}

		//删除
		$res = $this->Area->where('id='.intval($id))->delete();
		if ($res) {
			if ($check_info['bz_1']) {
				$img_url = "Data/".$check_info['bz_1'];
				if(file_exists($img_url)) {
					@unlink($img_url);
				}
			}
			$this->redirect('index');
		}else{
			$this->error('操作失败.');
		}
	}


	/*
	*
	* 图片上传的公共方法
	*  $file 文件数据流 $exts 文件类型 $path 子目录名称
	*/
	private function upload_pic($file,$exts,$path){
		$upload = new \Think\Upload();// 实例化上传类
		$upload->maxSize   =  2097152 ;// 设置附件上传大小2M
		$upload->exts      =  $exts;// 设置附件上传类型
		$upload->rootPath  =  './Data/UploadFiles/'; // 设置附件上传根目录
		$upload->savePath  =  ''; // 设置附件上传（子）目录
		$upload->saveName = time().mt_rand(100000,999999); //文件名称创建时间戳+随机数
		$upload->autoSub  = true; //自动使用子目录保存上传文件 默认为true
		$upload->subName  = $path; //子目录创建方式，采用数组或者字符串方式定义
		// 上传文件 
		$info = $upload->uploadOne($file);
		if(!$info) {// 上传错误提示错误信息
		    return $upload->getError();
		}else{// 上传成功 获取上传文件信息
			//return 'UploadFiles/'.$file['savepath'].$file['savename'];
			return $info;
		}
	}
}