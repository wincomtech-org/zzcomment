<?php
class WxPay{
    /* 得到二维码img.src */
    public function qrcode($data){
        require_once 'lib/phpqrcode/phpqrcode.php';
        $url = urldecode($data);
       QRcode::png($url); 
    }
    
    /* 统一下单 */
    public function order($data){
        require_once 'lib/WxPay.Api.php';
        require_once 'lib/WxPay.NativePay.php';
        $notify = new \NativePay();
        $input = new \WxPayUnifiedOrder();
        $input->SetBody($data['body']);
        $input->SetAttach($data['attach']);
        $input->SetOut_trade_no($data['out_trade_no']);
        $input->SetTotal_fee($data['total_fee']);  //此处以人民币分为最小单位，1为0.01元
        $input->SetTime_start(date("YmdHis"),$data['time_start']);
        $input->SetTime_expire(date("YmdHis", $data['time_expire']));
        $input->SetGoods_tag($data['goods_tag']);
        $input->SetNotify_url($data['goods_tag']);
        // $input->SetNotify_url("http://103.210.236.106:88/notify2.php");
        $input->SetTrade_type("NATIVE");
        $input->SetProduct_id("123456789");
        $result = $notify->GetPayUrl($input);
    }
    /* 查询订单 */
    public function order_query($oid){
        require_once 'lib/WxPay.Api.php';
        require_once 'lib/WxPay.NativePay.php'; 
        $input = new WxPayOrderQuery();
        $input->SetOut_trade_no($oid);
        $result = WxPayApi::orderQuery($input);
        
        if(array_key_exists("return_code", $result)
            && array_key_exists("result_code", $result)
            && $result["return_code"] == "SUCCESS"
            && $result["result_code"] == "SUCCESS"
            && $result['trade_state'] == 'SUCCESS'
            )
        {
            return $result;
        } 
        return 0; 
    }
    
    /*notify  */
    public function notify(){
        $data = file_get_contents("php://input");
        $postObj = simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA);
        $oid= $postObj->out_trade_no;
        return $this->order_query($oid);
    }
}