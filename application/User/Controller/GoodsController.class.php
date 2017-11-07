<?php
namespace User\Controller;

use Common\Controller\MemberbaseController;
/*
 * 商品管理  */
class GoodsController extends MemberbaseController {
	private $m;
	private $sid;
	function _initialize(){
		parent::_initialize();
		$this->m=M('Goods');
		$this->sid=I('sid',0);
		$this->assign('sid',$this->sid);
	}
	 
    //  
    public function index() {
        $m=$this->m;
        $where=array('sid'=>$this->sid); 
        
        $total=$m->where($where)->count();
        $page = $this->page($total, C('PAGE'));
        $list=$m->where($where)->order('start_time desc')->limit($page->firstRow,$page->listRows)->select();
         
       $this->assign('page',$page->show('Admin'));
       $this->assign('list',$list);
       $this->display();
       
    }
    public function add(){ 
        $this->display();
       exit;
    }
    //删除
    public function del(){
        $m=$this->m;
        $id=I('id',0);
        $where='id='.$id; 
        $row=$m->where($where)->delete();
        if($row===1){
            $data=array('errno'=>1,'error'=>'删除成功'); 
            M('TopGoods')->where('pid='.$id)->delete();
        }else{
            $data=array('errno'=>2,'error'=>'删除失败');
        }
        $this->ajaxReturn($data);
        exit;
    }
    //top0
    public function top0(){
       
        $m=$this->m;
        $id=I('id',0);
        $time=time();
        $data=array('errno'=>0,'error'=>'未执行');
        $price=session('company.top_goods_fee0');
        $price=$price['content'];
        
        //扣款
        if($price>0){
            $m_user=M('Users');
            $user=$m_user->where('id='.($this->userid))->find();
            if(empty($user) || $user['account']<$price){
                $data['error']='你的余额不足，请充值';
                $this->ajaxReturn($data);
                exit;
            }
            $account=bcsub($user['account'],$price);
            $m_user->startTrans();
            $row_user=$m_user->data(array('account'=>$account))->where('id='.($this->uid))->save();
            if($row_user!==1){
                $m_user->rollback();
                $data['error']='扣款失败';
                $this->ajaxReturn($data);
                exit;
            }
        }
       //推荐
        $where='id='.$id;
        $row=$m->data(array('start_time'=>$time))->where($where)->save();
        if($row===1){
            $data=array('errno'=>1,'error'=>'推荐成功');
            if(!empty($row_user)){
                $data_pay=array(
                    'uid'=>$this->uid,
                    'money'=>'-'.$price,
                    'time'=>$time,
                    'content'=>'推荐商品'.$id, 
                );
                M('Pay')->add($data_pay);
                $data_top0=array(
                    'pid'=>$id,
                    'status'=>2,
                    'create_time'=>$time,
                    'price'=>$price,
                );
                M('TopGoods0')->add($data_top0);
                $m_user->commit();
            }
        }else{
            if(!empty($row_user)){
                $m_user->rollback();
            }
            $data=array('errno'=>2,'error'=>'推荐失败');
        }
        $this->ajaxReturn($data);
        exit;
    }
    
    //购买置顶
    public function add_top(){
        $time=time();
        $id=I('id',0);
        $m=$this->m;
        $info=$m->where('id='.$id)->find();
        if($info['status']!=2){
            $this->error('该商品无法购买置顶');
        }
        //计算得到可置顶天数，最多10天
        $i=11;
        $top=array();
        $m_top=M('TopGoods');
        //得到总置顶位，再计算剩余
        $num=session('company.top_goods_num');
        $num=$num['content'];
        $where_tops=array(
            'pid'=>array('eq',$id),
            'status'=>array('in','0,2'),
        );
        $tops=$m_top->where($where_tops)->select();
        
        $flag=0;
        for($j=2;$j<=$i;$j++){
            $day=date('Y-m-d',$time+3600*24*$j);
            $time1=strtotime($day);
            foreach ($tops as $v){
                if($time1==$v['start_time']){
                    $flag=1;
                    continue;
                }
            }
            if($flag==1){
                $flag=0;
                continue;
            }
            $where=array('pid'=>$id,'status'=>2,'start_time'=>$time1);
            $count=$m_top->where($where)->count();
            if($count<$num){
                $top[]=array('day'=>$day,'count'=>($num-$count));
            }
            
        }
         
        $this->assign('type','商品名')->assign('info',$info)->assign('top',$top);
        $this->display();
    }
    
    //ajax
    public function add_top_ajax(){
        $id=I('id',0);
        $m=M('TopGoods');
        $days=I('days',array());
        $data=array('errno'=>0,'error'=>'未执行操作');
        if(empty($days)){
            $data['error']='未选中日期';
            $this->ajaxReturn($data);
            exit;
        }
        $uid=$this->userid;
        $price0=session('company.top_goods_fee');
        $price=bcmul($price0['content'],count($days));
        //扣款
        if($price>0){
            $m_user=M('Users');
            $user=$m_user->where('id='.$uid)->find();
            if(empty($user) || $user['account']<$price){
                $data['error']='你的余额不足，请充值';
                $this->ajaxReturn($data);
                exit;
            }
            $account=bcsub($user['account'],$price);
            $m_user->startTrans();
            $row_user=$m_user->data(array('account'=>$account))->where('id='.$uid)->save();
            if($row_user!==1){
                $m_user->rollback();
                $data['error']='扣款失败';
                $this->ajaxReturn($data);
                exit;
            }
        }
        //推荐
       
        $time=time();
        $data_top=array();
        foreach ($days as $v){
            $time1=strtotime($v);
            $data_top[]=array(
                'pid'=>$id,
                'create_time'=>$time,
                'start_time'=>$time1,
                'end_time'=>$time1+3600*24-1,
                'price'=>$price0['content'],
            );
        }
        $row=$m->addAll($data_top);
        if($row>=1){
            $data=array('errno'=>1,'error'=>'推荐成功');
            if(!empty($row_user)){
                $data_pay=array(
                    'uid'=>$uid,
                    'money'=>'-'.$price,
                    'time'=>$time,
                    'content'=>'置顶商品'.$id,
                );
                M('Pay')->add($data_pay);
                $m_user->commit();
            }
        }else{
            if(!empty($row_user)){
                $m_user->rollback();
            }
            $data=array('errno'=>2,'error'=>'置顶失败');
        }
        $this->ajaxReturn($data);
        exit;
        
    }
    
    //add_do
    public function add_do(){
        $pic='';
        $time=time();
        $subname=date('Y-m-d',$time);
        if(!empty($_FILES['IDpic6']['name'])){
             
            $upload = new \Think\Upload();// 实例化上传类
            //20M
            $upload->maxSize   =  C('SIZE') ;// 设置附件上传大小
            $upload->rootPath=getcwd().'/';
            $upload->subName = $subname;
            $upload->savePath  =C("UPLOADPATH").'/goods/';
            $info   =   $upload->upload();
            if(!$info) {// 上传错误提示错误信息
                $this->error($upload->getError());
            }
            foreach ($info as $v){ 
                $pic='goods/'.$subname.'/'.$v['savename'];
            }
        }
        
        $data=array(
            'sid'=>$this->sid,
            'pic'=>$pic,
            'create_time'=>$time,
            'start_time'=>$time, 
            'name'=>I('shopname',''),
            'price'=>trim(I('shopprice','')),
            'pic0'=>$pic,
        );
        //实名认证无需审核
        if(session('user.name_status')==1){
            $data['status']==2;
        }
        $m=$this->m;
        $insert=$m->add($data);
        if($insert>=1){
            $this->success('商品发布成功，等待审核');
        }else{
            $this->error('发布失败');
        }
        exit;
    }
}
