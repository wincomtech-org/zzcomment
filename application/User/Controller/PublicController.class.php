<?php
namespace User\Controller;

use Common\Controller\HomebaseController;

class PublicController extends HomebaseController {
    
   
     //验证码校验
     public function ajaxVerify(){
         
         if (! sp_check_verify_code()) {
             $data=array('errno'=>0,'error'=>'验证码错误或过期');
         } else{
             $data=array('errno'=>1,'error'=>'验证码正确');
         }
         $this->ajaxReturn($data);
         exit;
     }
     //检测手机号是否占用
     public function ajaxMobile(){
         $mobile=I('mobile',0);
         $tmp=M('Users')->where(array('mobile'=>$mobile))->find();
         if(empty($tmp)){
             $data=array('errno'=>2,'error'=>'该手机号不存在');
         } else{
             $data=array('errno'=>1,'error'=>'该手机号已存在');
         }
         $this->ajaxReturn($data);
         exit;
     }
     //检测用户名是否占用
     public function ajaxUsername(){
         $mobile=I('username',0);
         $tmp=M('Users')->where(array('user_login'=>$mobile))->find();
         if(empty($tmp)){
             $data=array('errno'=>2,'error'=>'该用户名不存在');
         } else{
             $data=array('errno'=>1,'error'=>'该用户名已存在');
         }
         $this->ajaxReturn($data);
         exit;
     }
     
      
     //短信码发送
     public function ajaxSend(){
         header("Content-Type:text/html;charset=utf-8");
         $apikey = "697655fbf93ebaedbaa7e411ad7cb619"; //修改为您的apikey(https://www.yunpian.com)登录官网后获取
         $mobile = I('mobile',''); //请用自己的手机号代替
         $type=I('type','');
         $time=time();
         $num='';
        for($i=0;$i<4;$i++){
            $num.=rand(0,9);
        }
        
        if(empty(session($type))){
             session($type,array($num,$time));
         }else{
             $yun= session($type);
             if(($time-$yun[1])<60){
                 $data=array('errno'=>0,'error'=>'短信发送还没满60秒');
                 $this->ajaxReturn($data);
                 exit;
             }else{
                 session($type,array($num,$time));
             }
         }
         $text="您的验证码是".$num;
         $ch = curl_init();
         
         /* 设置验证方式 */
         curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept:text/plain;charset=utf-8',
             'Content-Type:application/x-www-form-urlencoded', 'charset=utf-8'));
         /* 设置返回结果为流 */
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
         
         /* 设置超时时间*/
         curl_setopt($ch, CURLOPT_TIMEOUT, 10);
         
         /* 设置通信方式 */
         curl_setopt($ch, CURLOPT_POST, 1);
         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
          
         // 发送短信
         $data=array('text'=>$text,'apikey'=>$apikey,'mobile'=>$mobile);
         curl_setopt ($ch, CURLOPT_URL, 'https://sms.yunpian.com/v2/sms/single_send.json');
         curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
         $result = curl_exec($ch);
        $error = curl_error($ch);
        if($result === false){
           $arr= $error;
        }else{
            $arr= $result;
        }
         
        $array = json_decode($arr,true);
        if((isset($array['code'])) && $array['code']==0) {
             $data=array('errno'=>1,'error'=>'发送成功');
         } else{
             $data=array('errno'=>2,'error'=>'发送失败，请检查手机号或一小时内发送超过3次短信');
         }
         $this->ajaxReturn($data);
         exit;
     }
     

    
}
