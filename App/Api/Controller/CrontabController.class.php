<?php
//定时执行任务
namespace Api\Controller;
use Think\Controller;
class CrontabController extends PublicController {
	//定时结算周期金额
    public function index(){
    	$income_list = M('income')->select();
    	foreach ($income_list as $income){
    		M('income')->where('uid='.$income['uid'])->save(array('allow_amount'=>floatval($income['cycle_amount'] + $income['allow_amount']),'cycle_amount'=>0));
    	}
    }
}