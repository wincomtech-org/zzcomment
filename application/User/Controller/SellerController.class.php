<?php
namespace User\Controller;

use Common\Controller\MemberbaseController;

class SellerController extends MemberbaseController {
	private $m;
	function _initialize(){
		parent::_initialize();
		$this->m=M('Seller');
		 
	}
	public function create(){
	    $city=I('town',0);
	    $cid=I('letter',0);
	    
	    $name=I('shop_name','');
	    $address=I('shop_address','');
	    if($city==0 || $cid==0 || $name=='' || $address==''){
	        $this->error('信息填写不完整');
	    }
	    $verify=I('verify','');
	    if(!sp_check_verify_code()){
	        $this->error('验证码错误');
	    }
	    
	    $m=$this->m;
	    $data=array(
	        'name'=>$name,
	        'address'=>$address,
	        'city'=>$city,
	        'cid'=>$cid,
	        'author'=>session('user.id'),
	        'create_time'=>time(),
	    );
	    $insert=$m->add($data);
	    
	    if($insert>=1){
	        $this->success('创建成功，等待管理员审核');
	    }else{
	        $this->error('创建失败，请重试');
	    }
	}
    //领用店铺
    public function apply(){
        $sid=I('sid',0);
        $m=$this->m;
        $info=$m->where('id='.$sid)->find();
        $this->assign('info',$info);
        $this->display();
    }
    //领用店铺
    public function apply_do(){
        $fname=trim(I('fname',''));
        if($fname==''){
            $this->error('法人为必填项');
        }
        if(empty($_FILES['IDpic5']['name'])){
            $this->error('营业执照必须上传');
        } 
        $sid=I('sid',0);
        
        $m=M('SellerApply');
        $time=time();
        $subname=date('Y-m-d',$time);
        $data=array(
            'uid'=>$this->userid,
            'sid'=>$sid,
            'create_time'=>$time,
            'corporation'=>$fname,
            'scope'=>I('jyfw',''),
            'tel'=>I('tell',''),
            'mobile'=>I('phone',''),
            'bussiness_time'=>I('jysj',''),
            'link'=>I('webaddr',''),
            
        );
        $upload = new \Think\Upload();// 实例化上传类
        //20M
        $upload->maxSize   =  C('SIZE') ;// 设置附件上传大小
        $upload->rootPath=getcwd().'/';
        $upload->subName = $subname;
        $upload->savePath  =C("UPLOADPATH").'/seller/';
        $info   =   $upload->upload();
        if(!$info) {// 上传错误提示错误信息
            $this->error($upload->getError());
        }
        
        foreach ($info as $v){ 
            switch ($v['key']){
                case 'IDpic3':$data['pic']='seller/'.$subname.'/'.$v['savename'];break;
                case 'IDpic4':$data['qrcode']='seller/'.$subname.'/'.$v['savename'];break;
                case 'IDpic5':$data['cards']='seller/'.$subname.'/'.$v['savename'];break; 
            } 
        }
        $row=$m->add($data);
        if($row>=1){
            $this->success('已提交申请，等待管理员审核',U('Portal/Seller/home',array('sid'=>$sid)));
        }else{
            $this->error('操作失败');
        }
         exit;   
    }
   
}
