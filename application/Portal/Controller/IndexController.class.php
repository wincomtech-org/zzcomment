<?php
 
namespace Portal\Controller;
use Common\Controller\HomebaseController; 
/**
 * 首页
 */
class IndexController extends HomebaseController {
	
    //首页
	public function index() {
	     $m=M();
	    $time=time();
	    //banner图
	   
	    $banners=M('Banner')->order('sort desc')->select();
	    
	    //商家//商家排名10
	    $where_seller=array();
	    //0未审核，1未认领，2已认领,3已冻结
	    $where_seller['status']=array('between','1,2'); 
	     
	    $m_seller=M('Seller');
	   
	    $list_score_seller=$m_seller->where($where_seller)->order('score desc')->limit('0,10')->select();
	    
	    //推荐商家
	    $where_top=array();
	    //0申请。，1不同意，2同意
	    $where_top['status']=array('eq',2);
	    //0预定，1正在推荐，2过期
	    $where_top['state']=array('lt',2);
	    //此处直接比较时间，没有服务器检查过期
	    $where_top['start_time']=array('lt',$time);
	    $where_top['end_time']=array('gt',$time);
	    
	    $tmp=M('TopSeller')->where($where_top)->limit('0,9')->select();
	    $sids=array();
	    foreach ($tmp as $v){
	        $sids[]=$v['sid'];
	    }
	    $len=count($sids);
	    if($len>0){
	        $where=array('id'=>array('in',$sids));
	        //推荐商家按等级高低排名
	        $list_top_seller=$m_seller->order('score desc')->where($where)->select();
	    }
	   
	    //少于10个要有默认图片
	    $list_top_seller_empty=array();
	    $empty=session('company.top_seller_empty');
	    
	    for($i=$len;$i<10;$i++){
	        $list_top_seller_empty[]=array('pic'=>$empty['content'],'name'=>$empty['title']);
	    }
	    
	   //商品上新
	    $list_goods=M('Goods')->where($where_top)->order('start_time desc')->limit(0,8)->select();
	    //新增店铺,按创建时间排序
	    //店铺信息
	    $sql="select s.*,c2.name as citys,
	    au.user_login as author_name
	    from cm_seller as s
	    left join cm_city as c3 on c3.id=s.city
	    left join cm_city as c2 on c2.id=c3.fid
	    left join cm_users as au on au.id=s.author
        where s.status>0
        order by create_time desc limit 0,9";
	    $list_new_seller=$m->query($sql);
	    //$list_new_seller=$m_seller->where($where_seller)->order('create_time desc')->limit('0,9')->select();
	    
	    
	    //最新点评
	    $list_comment=D('Comment0View')->where(array('status'=>2))->order('create_time desc')->limit('0,2')->select();
	    $m_reply=D('Reply0View');
	    foreach ($list_comment as $k=>$v){
	        $list_comment[$k]['reply']=$m_reply->where('cid='.$v['id'])->select();
	    }
	    //最新动态
	   
	    $m_active=M('Active');
	    //先找置顶动态
	    $active_len=session('company.top_active_num');
	    $active_len=$active_len['content'];
	     
	    $tmp=M('TopActive')->where($where_top)->order('start_time desc')->limit(0,$active_len)->select();
	    $sids=array();
	    foreach ($tmp as $v){
	        $sids[]=$v['sid'];
	    }
	    $len=0;
	    if((count($sids))>0){
	        $where=array('id'=>array('in',$sids));
	        //推荐动态发布时间排名
	        $list_top_active=$m_active->order('start_time desc')->where($where)->select();
	        $len=count($list_top_active);
	    }
	    //少于$active_len个要有其他动态
	    if($len<$active_len){
	        $len=$active_len-$len;
	        $list_top_active_empty=array();
	        $list_top_active_empty=$m_active->where($where_top)->order('id desc')->limit('0,'.$len)->select();
	    }
	     
	    $this->assign('banners',$banners)
	    ->assign('list_score_seller',$list_score_seller)
	    ->assign('list_top_seller',$list_top_seller)
	    ->assign('list_top_seller_empty',$list_top_seller_empty)
	    ->assign('list_new_seller',$list_new_seller)
	    ->assign('list_goods',$list_goods)
	    ->assign('list_comment',$list_comment)
	    ->assign('list_top_active',$list_top_active)
	    ->assign('list_top_active_empty',$list_top_active_empty);
	     
	    $this->display();
    }

}


