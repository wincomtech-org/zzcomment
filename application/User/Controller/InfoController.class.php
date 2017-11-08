<?php
namespace User\Controller;

use Common\Controller\MemberbaseController;

class InfoController extends MemberbaseController {
	
	function _initialize(){
		parent::_initialize();
		$this->assign('user_flag','我的账户');
	}
	
    // 会员中心资料
	public function index() {
	    $this->assign('user_flag','会员资料');
		$this->assign('user',$this->user);
    	$this->display();
    }
    
    // 会员中心资料
    public function info() {
        $this->assign('user',$this->user);
        $this->display();
    }
    
    // 编辑用户资料提交
    public function edit() {
       
        set_time_limit(600);
        $time=time();
         
        if(!empty($_FILES['IDpic1']['name']) || !empty($_FILES['IDpic2']['name']) || !empty($_FILES['IDpic3']['name'])){
         
            $subname=date('Y-m-d',$time);
            $upload = new \Think\Upload();// 实例化上传类
            //20M
            $upload->maxSize   =  C('SIZE') ;// 设置附件上传大小
            $upload->rootPath=getcwd().'/';
            $upload->subName = $subname;
            $upload->savePath  =C("UPLOADPATH").'/user/';
            $info   =   $upload->upload();
            if(!$info) {// 上传错误提示错误信息
                $this->error($upload->getError());
            }
            $data=array();
            foreach ($info as $v){
                switch ($v['key']){
                    case 'IDpic1':$avatar='user/'.$subname.'/'.$v['savename'];break;
                    case 'IDpic2':$pic1='user/'.$subname.'/'.$v['savename'];break;
                    case 'IDpic3':$pic2='user/'.$subname.'/'.$v['savename'];break;
                    case 'IDpic8':$pic3='user/'.$subname.'/'.$v['savename'];break;
                    
                }
            }
        }
        $data=array(
            'update_time'=>$time,
            'sex'=>I('sex',0),
            'qq'=>I('qq',''),
            
        );
        $user_login=I('user_login','');
        $m=M('Users');
        if($user_login!=session('user.user_login')){
            $tmp=$m->where(array('user_login'=>$user_login))->find();
            if(!empty($tmp)){
                $this->error('用户名已被占用');
            }else{
                $data['user_login']=$user_login;
            }
        }
        if(!empty($avatar)){
            $data['avatar']=$avatar;
        }
        if(!empty($pic1)){
            $data['name_pic1']=$pic1;
        }
        if(!empty($pic2)){
            $data['name_pic2']=$pic2;
        }
        if(!empty($pic3)){
            $data['name_pic3']=$pic3;
        }
        
        $row=$m->data($data)->where('id='.($this->userid))->save();
        if($row===1){
            $this->success('信息保存成功');
        }else{
            $this->error('保存失败');
        }
        
    }
    
    
}
