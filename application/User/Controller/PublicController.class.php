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
     
     //短信码发送
     public function ajaxSend(){
         
         if (1) {
             $data=array('errno'=>0,'error'=>'短信码错误或过期');
         } else{
             $data=array('errno'=>1,'error'=>'验证码正确');
         }
         $this->ajaxReturn($data);
         exit;
     }
     //短信码校验
     public function ajaxCode(){
         
         if (1) {
             $data=array('errno'=>0,'error'=>'短信码错误或过期');
         } else{
             $data=array('errno'=>1,'error'=>'验证码正确');
         }
         $this->ajaxReturn($data);
         exit;
     }
    

    
}
