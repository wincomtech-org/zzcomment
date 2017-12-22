<?php
ini_set('date.timezone','Asia/Shanghai');
//error_reporting(E_ERROR);

require_once "lib/WxPay.Api.php";
require_once "WxPay.NativePay.php";
require_once 'example/log.php';

//模式一
/**
 * 流程：
 * 1、组装包含支付信息的url，生成二维码
 * 2、用户扫描二维码，进行支付
 * 3、确定支付之后，微信服务器会回调预先配置的回调地址，在【微信开放平台-微信支付-支付配置】中进行配置
 * 4、在接到回调通知之后，用户进行统一下单支付，并返回支付信息以完成支付（见：native_notify.php）
 * 5、支付完成之后，微信服务器会通知支付成功
 * 6、在支付成功通知中需要查单确认是否真正支付成功（见：notify.php）
 */
// $notify = new NativePay();
// $url1 = $notify->GetPrePayUrl("123456789");

//模式二
/**
 * 流程：
 * 1、调用统一下单，取得code_url，生成二维码
 * 2、用户扫描二维码，进行支付
 * 3、支付完成之后，微信服务器会通知支付成功
 * 4、在支付成功通知中需要查单确认是否真正支付成功（见：notify.php）
 */
$data=$_GET['data'];

$num=$_GET['order'];
// $input = new WxPayUnifiedOrder();
// $input->SetBody("test");
// $input->SetAttach("test");
// $input->SetOut_trade_no(WxPayConfig::MCHID.date("YmdHis"));
// $input->SetTotal_fee("1");
// $input->SetTime_start(date("YmdHis"));
// $input->SetTime_expire(date("YmdHis", time() + 600));
// $input->SetGoods_tag("test");
// $input->SetNotify_url("http://paysdk.weixin.qq.com/example/notify.php");
// $input->SetTrade_type("NATIVE");
// $input->SetProduct_id("123456789");
// $result = $notify->GetPayUrl($input);
// $url2 = $result["code_url"];
?>

<html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1" /> 
    <title>微信支付</title>
     <style type="text/css" media="screen">
       .lf{float:left;}
       .weixin{width:100%;box-sizing:border-box;box-sizing:-moz-border-box;box-sizing:-webkit-border-box;box-sizing:-o-border-box;padding:50px 150px;text-align:center;}
       .weixin_con{display:inline-block;}
       .weixin .weixin_bottom {margin-top:25px;}
       .weixin .weixin_bottom .lf{margin-right:48px;}
       .pic_erweima{width:183px;/*border:1px solid #ddd;*/}
       .pic_erweima img{width:150px;height:150px;border:1px solid #ddd;vertical-align:middle;margin-top:10px;}
       .pic_erweima span{display:block;font-size:14px;font-family:"微软雅黑";color:#333;margin:10px 0;}
  </style>
</head>
<body>
	
   <div class="pay_tab_con_list_item" style="display:block;">
          <div class="weixin">
              <div class="weixin_con">
                   <img src="image/weixin3.png" alt="">
                  <div class="weixin_bottom">
                     <div class="lf pic_erweima">
                        <img alt="扫码支付" src="http://paysdk.weixin.qq.com/example/qrcode.php?data=<?php echo $data;?>" style="width:150px;height:150px;"/>
                        <span>打开手机端微信<br/>扫一扫继续付款</span>
                    </div>
                     <div class="lf"><img src="image/weixin2.png" alt=""></div>
                      <div class="clear"></div>
                  </div>
              </div>
          </div>
          <div id="myDiv" style="text-align: center"></div><div id="timer" style="display: none">0</div>
      </div>
	
	<script>  
     //设置每隔1000毫秒执行一次load() 方法  
     var myIntval=setInterval(function(){load()},5000);  
     function load(){  
        document.getElementById("timer").innerHTML=parseInt(document.getElementById("timer").innerHTML)+1; 
         var xmlhttp;    
         if (window.XMLHttpRequest){    
             // code for IE7+, Firefox, Chrome, Opera, Safari    
             xmlhttp=new XMLHttpRequest();    
         }else{    
             // code for IE6, IE5    
             xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");    
         }    
         xmlhttp.onreadystatechange=function(){    
             if (xmlhttp.readyState==4 && xmlhttp.status==200){    
                 trade_state=xmlhttp.responseText;  
                 document.getElementById("myDiv").innerHTML=trade_state;
                 if(trade_state=='SUCCESS'){  
                     document.getElementById("myDiv").innerHTML='支付成功';  
                     //alert(transaction_id);  
                     //延迟3000毫秒执行tz() 方法
                     clearInterval(myIntval);  
                     setTimeout("location.href='.../../wxpayback.php?dat=<?php echo $num?>'",3000);  
 
                 }else if(trade_state=='REFUND'){  
                     document.getElementById("myDiv").innerHTML='转入退款'; 
                     clearInterval(myIntval); 
                 }else if(trade_state=='NOTPAY'){  
                     document.getElementById("myDiv").innerHTML='请扫码支付';  
                       
                 }else if(trade_state=='CLOSED'){  
                     document.getElementById("myDiv").innerHTML='已关闭';  
                     clearInterval(myIntval);
                 }else if(trade_state=='REVOKED'){  
                     document.getElementById("myDiv").innerHTML='已撤销';  
                     clearInterval(myIntval);
                 }else if(trade_state=='USERPAYING'){  
                     document.getElementById("myDiv").innerHTML='用户支付中';  
                 }else if(trade_state=='PAYERROR'){  
                     document.getElementById("myDiv").innerHTML='支付失败'; 
                     clearInterval(myIntval); 
                 }  
                  
             }    
         }    
         //orderquery.php 文件返回订单状态，通过订单状态确定支付状态  
         xmlhttp.open("POST","example/orderquery.php",false);    
         //下面这句话必须有    
         //把标签/值对添加到要发送的头文件。    
         xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");    
         xmlhttp.send("out_trade_no=<?php echo $num;?>");  
          
         }  
     </script>
	
</body>
</html>