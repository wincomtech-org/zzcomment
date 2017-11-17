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
       
        set_time_limit(C('TIMEOUT'));
        $time=time();
        $user0=M('Users')->where('id='.($this->userid))->find();
        
       
        if(!empty($_FILES['IDpic1']['name']) || !empty($_FILES['IDpic2']['name']) || !empty($_FILES['IDpic3']['name']) || !empty($_FILES['IDpic8']['name'])){
             
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
            //120*120
            $avatar0=$avatar.'.jpg';
            $image = new \Think\Image();
            $image->open(C("UPLOADPATH").$avatar); 
            $image->thumb(120, 120,\Think\Image::IMAGE_THUMB_FIXED)->save(C("UPLOADPATH").$avatar0);
            
            unlink(C("UPLOADPATH").$avatar);
            $data['avatar']=$avatar0;
        }
        //有图片要保存还要更新实名认证状态
        if(!empty($pic1)){
            $data['name_pic1']=$pic1;
            $data['name_status']=3;
        }
        if(!empty($pic2)){
            $data['name_pic2']=$pic2;
            $data['name_status']=3;
        }
        if(!empty($pic3)){
            $data['name_pic3']=$pic3;
            $data['name_status']=3;
        }
        
        $row=$m->data($data)->where('id='.($this->userid))->save();
        if($row===1){
            $user=M('Users')->where('id='.($this->userid))->find();
            session('user',$user);
            $this->success('信息保存成功');
        }else{
            $this->error('保存失败');
        }
        
    }
    
    public function psw(){
        $this->display();
    }
    public function psw_ajax(){
       $id=session('user.id');
       $oldpw=I('oldpw','');
       $newpw=I('newpw','');
       $psw=C('PSW');
       if(preg_match($psw, $oldpw)!=1 || preg_match($psw, $newpw)!=1){
           $data=array('errno'=>0,'error'=>'密码格式错误');
           $this->ajaxReturn($data);
           exit;
       }
       $oldpsw=sp_password($oldpw);
       $newpsw=sp_password($newpw);
       $m=M('Users');
       $where=array(
           'id'=>$id,
           'user_status'=>1,
           'user_type'=>2,
       );
       $tmp=$m->where($where)->find();
       if(empty($tmp) ){
           session('user',null);
           setcookie('zypjwLogin', null,time()-2,'/');
           $data=array('errno'=>2,'error'=>'用户信息错误,将退出登录');
           $this->ajaxReturn($data);
           exit;
       }elseif($tmp['user_pass']!=$oldpsw){
           $data=array('errno'=>2,'error'=>'原密码不匹配');
           $this->ajaxReturn($data);
           exit;
       }
            
       $row=$m->data(array('user_pass'=>$newpsw))->where($where)->save();
       if($row===1){
           $user=M('Users')->where('id='.$id)->find();
           session('user',$user);
          $data=array('errno'=>1,'error'=>'密码修改成功');
        }else{
            $data=array('errno'=>3,'error'=>'密码修改失败');
        }
        $this->ajaxReturn($data);
        exit;
       
    }
    //更改手机号
    public function mobile(){
        $this->display();
    }
    public function mobile_ajax(){
        $id=session('user.id');
        $psw=I('psw','');
        $code=I('code','');
        $mobile=I('mobile','');
        if(preg_match(C('PSW'), $psw)!=1 || preg_match(C('MOBILE'), $mobile)!=1){
            $data=array('errno'=>0,'error'=>'密码或手机号错误');
            $this->ajaxReturn($data);
            exit;
        }
        //检测短信码
        $res=checkMsg($code,$mobile,'mobileCode');
        if(empty($res)){
            $data=array('errno'=>0,'error'=>'短信码验证失败，请刷新页面重试');
            $this->ajaxReturn($data);
            exit;
        }elseif($res['errno']!=1){
            $data=array('errno'=>0,'error'=>$res['error']);
            $this->ajaxReturn($data);
            exit;
        }
        
        $oldpsw=sp_password($psw);
       
        $m=M('Users');
        $where=array(
            'id'=>$id,
            'user_status'=>1,
            'user_type'=>2,
        );
        $tmp=$m->where($where)->find();
        if(empty($tmp) ){
            session('user',null);
            setcookie('zypjwLogin', null,time()-2,'/');
            $data=array('errno'=>2,'error'=>'用户信息错误,将退出登录');
            $this->ajaxReturn($data);
            exit;
        }elseif($tmp['user_pass']!=$oldpsw){
            $data=array('errno'=>2,'error'=>'原密码不匹配');
            $this->ajaxReturn($data);
            exit;
        }
        
        $row=$m->data(array('mobile'=>$mobile))->where($where)->save();
        if($row===1){
            //成功后清除短信验证码
            session('msgCode',null);
            $data=array('errno'=>1,'error'=>'手机号修改成功');
        }else{
            $data=array('errno'=>3,'error'=>'手机号修改失败');
        }
        $this->ajaxReturn($data);
        exit;
        
    }
    //充值
    public function pay(){
        $this->display();
    }
    //充值alipay
    public function alipay(){
        $payment=I('post.payment','');
        $money=I('post.money','');
        if($payment!='alipayjs'){
            $this->error('错误地址');
        }
        if(!preg_match('/^\d{1,8}(\.\d{1,2})?$/',$money)){
            $this->error('充值金额错误,只能最多2位小数,1亿以下');
        }
        $time=time(); 
        //商户订单号，商户网站订单系统中唯一订单号，必填
        $out_trade_no = ($this->userid).'-'.date('Ymd-His'); 
        //订单名称，必填
        $title=session('company.title');
        $subject =$title['content'].'充值￥'.$money; 
        //付款金额，必填
        
        $total_fee =0.01; 
        //商品描述，可空
        $body = $subject;
        $alipay_config=C("ALIPAY_CONFIG");
        //构造要请求的参数数组，无需改动
        $parameter = array(
            "service"       => $alipay_config['service'],
            "partner"       => $alipay_config['partner'],
            "seller_id"  => $alipay_config['seller_id'],
            "payment_type"	=> $alipay_config['payment_type'],
            
            "notify_url"	=> $alipay_config['notify_url'],
            "return_url"	=> $alipay_config['return_url'],
            
            "anti_phishing_key"=>$alipay_config['anti_phishing_key'],
            "exter_invoke_ip"=>$alipay_config['exter_invoke_ip'],
            "out_trade_no"	=> $out_trade_no,
            "subject"	=> $subject,
            "total_fee"	=> $total_fee,
            "body"	=> $body,
            "_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
            //其他业务参数根据在线开发文档，添加参数.文档地址:https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.kiX33I&treeId=62&articleId=103740&docType=1
            //如"参数名"=>"参数值"
            
        );
        
        //建立请求
        vendor('Alipay.AlipaySubmit');
        $alipaySubmit = new \AlipaySubmit($alipay_config);
        
        $html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");
      
        echo $html_text;
    }
    
    public function alipay_return(){
        if(empty($_GET['out_trade_no'])){
            $this->error('页面信息错误');
        }
       /*  $out_trade_no = $_GET['out_trade_no'];//商户订单号
        $trade_no = $_GET['trade_no'];//支付宝交易号
        $trade_status = $_GET['trade_status'];//交易状态
        $total_fee=$_GET['total_fee'];//支付金额
        $buyer_id=$_GET['buyer_id']; //买家付款账号buyer_id */
        
        if($_GET['trade_status']== 'TRADE_FINISHED' || $_GET['trade_status']== 'TRADE_SUCCESS' ) {
            
            $paypay=M('Paypay')->where(array('oid'=>$_GET['out_trade_no']))->find();
            if(empty($paypay)){
                $status=0;
                $msg='支付成功，但网站数据异常，请记住支付信息联系客服';
            }else{
                $status=1;
                $msg='支付成功';
            }
        }else{
            $status=0;
            $msg='支付失败';
        }
      
       $this->assign('trade',$_GET)->assign('msg',$msg)->assign('status',$status);  
       $this->display();
            
    }
    
}
