<?php

namespace Admin\Controller;
use Common\Controller\AdminbaseController;
/**
 *
 * 店铺动态
 */
class ActiveController extends AdminbaseController {

    private $m;
    private $order;
    public function _initialize() {
        parent::_initialize();
        $this->m = M('Active');
        $this->order='id desc';
        $this->assign('flag','店铺动态'); 
    }
    
    //编辑
    function index(){
        $m=D('Active0View');
        $id=trim(I('id',''));
        $name=trim(I('name',''));
        $sid=trim(I('sid',''));
        $sname=trim(I('sname',''));
        $status=I('status',-1);
        $where=array();
        if($id!=''){
            $where['id']=array('like','%'.$id.'%');
        }
        if($sid!=''){
            $where['sid']=array('like','%'.$sid.'%');
        }
        if($name!=''){
            $where['name']=array('like','%'.$name.'%');
        }
        if($sname!=''){
            $where['sname']=array('like','%'.$sname.'%');
        }
        if($status!=-1){
            $where['status']=array('eq',$status);
        }
        $total=$m->where($where)->count();
        $page = $this->page($total, 10);
        $list=$m->where($where)->order($this->order)->limit($page->firstRow,$page->listRows)->select();
        $this->assign('page',$page->show('Admin'));
        $this->assign('list',$list);
        $this->assign('id',$id)
        ->assign('sid',$sid)
        ->assign('name',$name)
        ->assign('sname',$sname)
        ->assign('status',$status);
        $this->display();
    }
    
    //详情
    function info(){
        $id=I('id',0);
        $info=D('Active0View')->where('Active.id='.$id)->find();
        $this->assign('info',$info);
        $this->display();
    }
    //动态审核
    function review(){
        $url=I('url','');
        $m=$this->m;
        $review=I('review',0);
        $id=I('id',0);
        $status=I('status',-1);
        if($id==0 || $review==0 || $status==-1){
           
            $this->error('数据错误，请刷新重试');
        }
        $info=$m->where('id='.$id)->find();
        if(empty($info) || $info['status']!=$status){
            $this->error('数据更新，请刷新重试');
        }
         
        $time=time();
        $m_action=M('AdminAction');
        $data_action=array(
            'uid'=>session('ADMIN_ID'),
            'time'=>$time,
            'sid'=>$id,
            'sname'=>'active',
        );
        $desc='店铺'.$info['sid'].'的动态'.$id;
        
        switch($review){
            case 1:
                if($status!=0){
                    $this->error('错误，已审核过');
                    exit;
                }
                $data_action['descr']=$desc.'审核不通过';
                $row=$m->where('id='.$id)->data(array('status'=>1))->save();
                break;
            case 2:
                if($status!=0){
                    $this->error('错误，已审核过');
                    exit;
                }
                $data_action['descr']=$desc.'审核通过'; 
                $row=$m->where('id='.$id)->data(array('status'=>2))->save();
                break;
            case 3:
                //删除前未生效的置顶费用应退还,其关联的置顶也需删除
                $m->startTrans();
                $data_action['descr']=$desc.'删除'; 
                $row=$m->where('id='.$id)->delete();
                //之前审核通过且未过期的动态才计算退置顶费
                if($row===1 && $info['status']=2 && $info['end_time']>$time){ 
                    //计算置顶费用
                    $m_top_active=M('TopActive');
                    $where_top=array();
                    $where_top['pid']=$id;
                    $where_top['status']=array('in',array(0,2));
                    $where_top['start_time']=array('gt',$time);
                    $tmp=$m_top_active->where($where_top)->select();
                    $price=0;
                    foreach ($tmp as $v){
                        $price=bcadd($price, $v['price']);
                    }
                    $m_top_active->startTrans();
                    $row_top=$m_top_active->where('pid='.$id)->delete();
                    if($row_top===false){
                        $m->rollback();
                        $this->error('操作失败，请刷新重试');
                    }
                    $sql="select u.id,u.account
                    from cm_seller as s 
                    left join cm_users as u on u.id=s.uid
                    where s.id={$info['sid']} limit 1";
                    $tmp=M()->query($sql);
                    
                    //价格没有或店铺不存在就不用还钱了
                    if($price>0 && !empty($tmp) && !empty($tmp[0]) && !empty($tmp[0]['id'])){ 
                         
                        $data_action['descr'].='，且退还未生效的置顶费用￥'.$price;
                        $account=bcadd($price, $tmp[0]['account']);
                        $row_account=M('Users')->data(array('account'=>$account))->where('id='.$tmp[0]['id'])->save();
                        if($row_account!==1){
                            $m->rollback();
                            $m_top_active->rollback();
                            $this->error('操作失败，请刷新重试');
                        }
                         
                    }
                    $m_top_active->commit();
                    $m->commit();
                }elseif($row===1){
                    $m->commit();
                }
                break;
        }
        
        if($row===1){
            $m_action->add($data_action);
            if($url=='index'){
                $this->success('删除成功');
            }elseif($review==3){
                $this->success('删除成功',U('index'),3);
            }else{
                $this->success('操作成功');
            }
           
        }else{
            $this->error('操作失败，请刷新重试');
        }
        exit; 
    }
    //top
    function top(){
        $this->assign('flag','动态置顶');
        $m=D('TopActive0View');
        $aid=trim(I('aid',''));
        $aname=trim(I('aname',''));
        $sid=trim(I('sid',''));
        $sname=trim(I('sname',''));
        $status=I('status',-1);
        $where=array();
         
        if($aid!=''){
            $where['pid']=array('like','%'.$aid.'%');
        }
        if($sid!=''){
            $where['sid']=array('like','%'.$sid.'%');
        }
        if($aname!=''){
            $where['aname']=array('like','%'.$aname.'%');
        }
        if($sname!=''){
            $where['sname']=array('like','%'.$sname.'%');
        }
        if($status!=-1){
            $where['status']=array('eq',$status);
        }
        $total=$m->where($where)->count();
        $page = $this->page($total, 10);
        $list=$m->where($where)->order($this->order)->limit($page->firstRow,$page->listRows)->select();
        $this->assign('page',$page->show('Admin'));
        $this->assign('list',$list);
        $this->assign('aid',$aid)
        ->assign('sid',$sid)
        ->assign('aname',$aname)
        ->assign('sname',$sname)
        ->assign('status',$status);
        $this->display();
    }
    
    //详情
    function top_info(){
        $this->assign('flag','动态置顶');
        $id=I('id',0);
        $info=D('TopActive0View')->where('TopActive.id='.$id)->find();
        $this->assign('info',$info);
        
        $this->display();
    }
    //动态审核
    function top_review(){
        $this->assign('flag','动态置顶');
        $m=M('TopActive');
        $url=I('url','');
        $review=I('review',0);
        $id=I('id',0);
        $status=I('status',-1);
        if($id==0 || $review==0 || $status==-1){
            
            $this->error('数据错误，请刷新重试');
        }
        $info=$m->where('id='.$id)->find();
        if(empty($info) || $info['status']!=$status){
            $this->error('数据更新，请刷新重试');
        }
        
        $time=time();
        $m_action=M('AdminAction');
        $data_action=array(
            'uid'=>session('ADMIN_ID'),
            'time'=>$time,
            'sid'=>$id,
            'sname'=>'top_active',
        );
        //删除前未生效的置顶费用应退还
        $m->startTrans();
        $desc='动态'.$info['pid'].'的置顶申请'.$id;
        switch($review){
            case 1:
                if($status!=0){
                    $this->error('错误，已审核过'); 
                }
                //不通过退还余额
                $data_action['descr']=$desc.'审核不通过';
                $row=$m->where('id='.$id)->data(array('status'=>1))->save();
                break;
            case 2:
                if($status!=0){
                    $this->error('错误，已审核过');
                }
                $data_action['descr']=$desc.'审核通过';
                
                $row=$m->where('id='.$id)->data(array('status'=>2))->save();
                break;
            case 3: 
                $data_action['descr']=$desc.'删除';  
                $row=$m->where('id='.$id)->delete(); 
                break;
        }
        //删除或审核不通过 应退还 前未生效的置顶费用
        if($row===1){
           
            if($review!=2){  
               
                //计算置顶费用
                $price=0;
                if($info['start_time']>$time && $info['status']!=1){
                    $price=$info['price'];
                }
                
                $sql="select u.id,u.account
                from cm_active as a
                left join cm_seller as s on s.id=a.sid
                left join cm_users as u on u.id=s.uid
                where a.id={$info['pid']} limit 1";
                $tmp=M()->query($sql);
                $user=$tmp[0];
               
                //价格没有或店铺用户不存在就不用还钱了
                if($price>0 && !empty($user) && !empty($user['id'])){
                    $data_action['descr'].='，且退还未生效的置顶费用￥'.$price;
                    $account=bcadd($price, $user['account']);
                    $row_account=M('Users')->data(array('account'=>$account))->where('id='.$user['id'])->save();
                    if($row_account!==1){
                        $m->rollback();
                        $this->error('操作失败，请刷新重试');
                    }
                } 
            }
            $m->commit();
            $m_action->add($data_action);
            if($review==3){
                if($url=='top'){
                    $this->success('删除成功');
                }else{
                    $this->success('删除成功',U('top'),3);
                }
               
            }else{
                $this->success('审核成功');
            }
            
        }else{
            $m->rollback();
            $this->error('操作失败，请刷新重试');
        }
        exit;
    }
   
}

?>