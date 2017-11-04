<?php
namespace Admin\Controller;

use Common\Controller\AdminbaseController;
use Think\Model;
/* 
 * 店铺后台控制
 *  */
class SellerController extends AdminbaseController {
	private $m;
	private $m1;
	private $order;
	private $order1;
	public function _initialize() {
	    parent::_initialize();
	    $this->m = M('Seller');
	    $this->m1 = M('SellerApply');
	    $this->order='id desc';
	    $this->order1='sid desc';
	}
    //店铺管理首页
    public function index(){
        
        
        //这是选择框的分类
        $list0=M('Cate')->order('fid asc,sort desc,name asc')->select();
       //1一级分类，2二级分类
        $cates2=array();
        $cates1=array();
        $tmp=array();
       
        //得到分类
       foreach ($list0 as $v){
           if($v['fid']==0){
               $cates1[]=$v;
           }else{
               $cates2[]=$v;
               $tmp[$v['fid']][]=$v['id'];
           }
       }
       //排序
       $sort=I('sort',0);
       $order=' order by ';
       switch ($sort){ 
           case 2:$order.=' s.score desc,s.id desc ';break;
           case 3:$order.=' s.browse desc,s.id desc ';break;
           default:$order.=' s.id desc ';break;
       }
       $status=I('status',-1);
       //$where=array();
       $where=' where s.status>0 ';
       switch ($status){
           case -1:$where=' where s.status>0 ';break;
           case 1:$where=' where s.status=1 ';break;
           case 2:$where=' where s.status=2 ';break;
           case 3:$where=' where s.status=3 ';break; 
       }
       $id=trim(I('id',''));
       if($id!=''){
           $where.=" and s.id like '%{$id}%' ";
       }
       //分类查询条件
       $fid1=I('fid1',0);
       $fid2=I('fid2',0);
    	
    	if($fid2==0 ){
    	    if($fid1==0){
    	        
    	    }elseif(empty($tmp[$fid1])){
    	        //$where['cid']=array('eq',0);
    	        $where.=' and s.cid=0 ';
    	    }else{
    	        //$where['cid']=array('in',$tmp[$fid1]);
    	        $str=implode(',', $tmp[$fid1]);
    	        $where.=' and s.cid in ('.$str.')';
    	    }
    	}else{
    	    //$where['cid']=array('eq',$fid2);
    	    $where.=" and s.cid=".$fid2;
    	}
    	//店铺名搜索
    	$name=trim(I('name',''));
    	if($name!=''){
    	    $where.=" and s.name like '%{$name}%' ";
    	}
    	
    	$m=M();
    	 
    	$sql="select count(s.id) as total from cm_seller as s {$where}";
    	//$total=$m->where($where)->count();
    	$tmp=$m->query($sql);
    	 
    	$total=$tmp[0]['total'];
    	$page = $this->page($total, 10);
    	
    	//$list=$m->where($where)->order($this->order)->limit($page->firstRow,$page->listRows)->select();
    	$sql="select s.*,c3.name as name3,c3.id as city1,c3.fid as city2,c2.name as name2,c2.fid as city1,c1.name as name1
    	from cm_seller as s
        left join cm_city as c3 on c3.id=s.city
    	left join cm_city as c2 on c2.id=c3.fid
    	left join cm_city as c1 on c1.id=c2.fid
    	{$where} {$order}
        limit {$page->firstRow},{$page->listRows}";
    	$list=$m->query($sql);
    	
    	$this->assign('page',$page->show('Admin'));
        $this->assign('list',$list);
        $this->assign('cates1',$cates1);
        $this->assign('cates2',$cates2);
        $this->assign('sort',$sort)
        ->assign('fid1',$fid1)
        ->assign('fid2',$fid2)
        ->assign('name',$name)
        ->assign('status',$status)
        ->assign('id',$id);
        
    	$this->display();
    }
    
    //后台操作店铺状态
    public function index_del(){
        $old_status=I('status',0);
        $review=I('review',0);
        $id=I('id',0);
        $m=$this->m;
        if($old_status==0 || $id==0 || $review==0){
            $this->error('数据错误');
        }
        $info=$m->where('id='.$id)->find();
        
        //查看是否被他人操作
        if(empty($info) || $info['status'] != $old_status){
            $this->error('错误，店铺已被修改,请刷新');
        }
        $m_action=M('AdminAction');
        $data_action=array(
            'uid'=>session('ADMIN_ID'),
            'time'=>time(),
            'sid'=>$id,
            'sname'=>'seller',
        );
        $desc='店铺'.$id;
        $m->startTrans();
        switch ($review){
            case 1:
                $desc='冻结了'.$desc;
                if($info['status']=='3'){
                    $m->commit();
                    $this->error('已冻结');
                    exit;
                }
                $row=$m->data(array('status'=>3))->where('id='.$id)->save();
                if($row===1){
                    $m->commit();
                    
                    $data_action['descr']=$desc;
                    $m_action->add($data_action);
                    $this->success($desc);
                    exit;
                }
                break;
            case 2:
                $desc='解冻了'.$desc;
                if($info['status']!='3'){
                    $m->commit();
                    $this->error('数据错误');
                    exit;
                }
                $new_status=empty($info['uid'])?1:2;
                $row=$m->data(array('status'=>$new_status))->where('id='.$id)->save();
                if($row===1){
                    $m->commit();
                    
                    $data_action['descr']=$desc;
                    $m_action->add($data_action);
                    $this->success($desc);
                    exit;
                }
                break;
            case 3:
                $desc='删除了'.$desc;
                $row=$m->where('id='.$id)->delete();
                if($row===1){
                    //删除店铺后还要删除店铺动态，，商品，点评回复，各种推荐
                    //动态
                    $where='sid='.$id;
                    M('Dynamic')->delete($where);
                    //商品
                    M('Goods')->delete($where);
                    //店铺推荐
                    M('Recommend')->delete($where);
                    //点评,还要删除回复
                    $m_comment=M('Comment');
                    $comments=$m_comment->field('id')->select();
                    $m_comment->delete($where);
                    foreach ($comments as $v){
                        $ids[]=$v['id'];
                    }
                    if(!empty($ids)){
                        M('Reply')->where(array('cid'=>array('in',$ids)))->delete();
                    }
                    $m->commit();
                    $data_action['descr']=$desc;
                    $m_action->add($data_action);
                    $this->success($desc,U('index'));
                    exit;
                }
                break;
            default:break;
        }
        $m->rollback();
        $this->error('操作失败，请刷新重试');
        exit;
    }
    //新创建店铺 待审核
    public function create(){
        
       
        //$where=array();
        $where=' where s.status=0 ';
        $order=' order by id desc ';
        
        //店铺名搜索
        $name=trim(I('name',''));
        if($name!=''){
            $where.=" and s.name like '%{$name}%' ";
        }
        $m=M();
        
        $sql="select count(s.id) as total from cm_seller as s {$where}";
       
        $tmp=$m->query($sql);
        
        $total=$tmp[0]['total'];
        $page = $this->page($total, 10);
        
        $sql="select s.*,concat(c1.name,'-',c2.name,'-',c3.name) as citys,
            concat(cate1.name,'-',cate2.name) as cate,u.user_login as authorname
        from cm_seller as s
        left join cm_city as c3 on c3.id=s.city
        left join cm_city as c2 on c2.id=c3.fid
        left join cm_city as c1 on c1.id=c2.fid
        left join cm_cate as cate2 on cate2.id=s.cid
        left join cm_cate as cate1 on cate1.id=cate2.fid
        left join cm_users as u on u.id=s.author
        {$where} {$order}
        limit {$page->firstRow},{$page->listRows}";
        $list=$m->query($sql);
        
        $this->assign('page',$page->show('Admin'));
        $this->assign('list',$list);
        
        $this->assign('name',$name);
        
        $this->display();
    }
    //创建店铺的审核
    public function create_do(){
        $action=I('action',0);
        $id=I('id',0);
        if($action==0 ||$id==0){
            $this->error('数据错误,请刷新');
        }
        $m=$this->m;
        $info=$m->where('id='.$id)->find();
        if(empty($info) || $info['status']!=0){
            $this->error('数据错误,请刷新');
        }
        
        $data_action=array(
            'uid'=>session('ADMIN_ID'),
            'time'=>time(),
            'sid'=>$id,
            'sname'=>'seller',
        );
        $desc='新建店铺'.$id;
        if($action==1){
            $row=$m->data(array('status'=>1))->where('id='.$id)->save();
            $desc.='审核成功';
        }else{
            $row=$m->where('id='.$id)->delete();
            $desc.='删除成功';
        }
        if($row===1){
            $data_action['descr']=$desc;
            M('AdminAction')->add($data_action);
            $this->success($desc);
        }else{
            $this->error('操作错误');
        }
        exit;
    }
    
    //查看店铺详情
    public function info(){
        $id=I('id',0);
        $m=M();
       // $sql="select s.*,c3.name as name3,c3.id as city1,c3.fid as city2,c2.name as name2,c2.fid as city1,c1.name as name1,
        $sql="select s.*,concat(c1.name,'-',c2.name,'-',c3.name) as citys,
            u.user_login as uname,au.user_login as author_name,concat(cate1.name,'-',cate2.name) as cname
        from cm_seller as s
        left join cm_city as c3 on c3.id=s.city
        left join cm_city as c2 on c2.id=c3.fid
        left join cm_city as c1 on c1.id=c2.fid
        left join cm_users as u on s.uid=u.id
        left join cm_users as au on au.id=s.author
        left join cm_cate as cate2 on cate2.id=s.cid
        left join cm_cate as cate1 on cate1.id=cate2.fid
        where s.id={$id} limit 1";
         
        $info=$m->query($sql);
        $info=$info[0];
        
        $m_user=M('Users'); 
        //判断状态
        switch ($info['status']){
            case 1:
                $info['status_name']='未领用';
                break;
            case 2:
                $info['status_name']='已领用';
                
                break;
            case 3:
                $info['status_name']='已冻结';
               
                break;
            default:
                $info['status_name']='错误状态';
                break;
        }
        $this->assign('info',$info);
        
        $this->display();
        
    }
    //待审核
    public function applying(){
        $m=$this->m1;
        $order=$this->order1;
        //未处理
        $where=array('status'=>0);
        $total=$m->where($where)->count();
        $page = $this->page($total, 10);
        $list=D('SellerApply0View')->where($where)->order($this->order)->limit($page->firstRow,$page->listRows)->select();
        $this->assign('page',$page->show('Admin'));
        $this->assign('list',$list);
        
        $this->display();
    }
    //已审核
    public function applied(){
        $m=$this->m1;
        $order=$this->order1;
        //未处理
        $where['status']=array('gt',0);
        $total=$m->where($where)->count();
        $page = $this->page($total, 10);
        $list=D('SellerApply1View')->where($where)->order($this->order)->limit($page->firstRow,$page->listRows)->select();
        $this->assign('page',$page->show('Admin'));
        $this->assign('list',$list);
        
        $this->display();
    }
    //查看店铺领用申请详情
    public function applyinfo(){
        $id=I('id',0);
        $m=M();
       
        $sql="select sa.*,s.create_time as stime,s.name as sname,s.author,s.address,s.city,s.status as sstatus,
                concat(c1.name,'-',c2.name,'-',c3.name) as citys,concat(cate1.name,'-',cate2.name) as cname,
                u.user_login as uname,au.user_login as authorname
            from cm_seller_apply as sa
            left join cm_seller as s on sa.sid=s.id 
            left join cm_city as c3 on c3.id=s.city
            left join cm_city as c2 on c2.id=c3.fid
            left join cm_city as c1 on c1.id=c2.fid
            left join cm_cate as cate2 on cate2.id=s.cid
            left join cm_cate as cate1 on cate1.id=cate2.fid
            left join cm_users as u on sa.uid=u.id
            left join cm_users as au on au.id=s.author
            where sa.id={$id} limit 1";
        $info=$m->query($sql);
        $info=$info[0];
        
        //已审核,查找审核人
        $m_user=M('Users');
        if($info['status']>0){
            $info['statusName']=($info['status']==1)?'通过':'不通过';
            $review=$m_user->where('id='.$info['rid'])->find();
            $info['rname']=$review['user_login'];
        }
        switch ($info['sstatus']){
            case 1: $info['sstatusName']='未领用';break;
            case 2: $info['sstatusName']='已领用';break;
            case 3: $info['sstatusName']='已冻结';break;
            default: $info['sstatusName']='状态异常';break;
        }
        
        $this->assign('info',$info);
        
        $this->display();
        
    }
    
    //审核
    public function review(){
        $old_status=I('status',0);
        $status=I('review',0);
        $id=I('id',0);
        $m=$this->m1;
        if($status==0 || $id==0){
            $this->error('数据错误');
        }
        $info=$m->where('id='.$id)->find();
        //查看是否被他人审核或已审核通过
        if(empty($info) || $info['status'] != $old_status || $info['status']==1){
            $this->error('错误，申请已被审核,请刷新');
        }
        //删除
        $uid=session('ADMIN_ID');
        $time=time();
        $data_action=array(
            'uid'=>$uid,
            'time'=>$time,
            'sid'=>$id,
            'sname'=>'seller_apply',
        );
        $desc='用户'.$uid.'领用店铺'.$info['sid'].'的申请';
        if($status==3){
            $data_action['descr']='删除了'.$desc;
            $row=$m->where('id='.$id)->delete(); 
            if($row===1){
                M('AdminAction')->add($data_action);
                $this->success('删除成功',U('applied'));
                
            }else{
                $this->error('操作失败');
            }
            exit;
        }
        
        //查看店铺是否已被领用
        $m_seller=$this->m;
        $seller=$m_seller->where('id='.$info['sid'])->find();
        if(empty($seller)){
            $this->error('错误，店铺不存在');
        }
        if($seller['status']!=1){
            $this->error('错误，店铺已被领用或冻结');
        }
        
        //审核
        $data1=array(
            'status'=>$status,
             
        );
        $m->startTrans();
        $row1=$m->data($data1)->where('id='.$id)->save();
        if($row1===1){
            if($status==2){ 
                $data_action['descr']='通过了'.$desc;
                $data2=array(
                    'status'=>2,
                    'uid'=>$info['uid'],
                    'tel'=>$info['tel'], 
                    'mobile'=>$info['mobile'],
                    'pic'=>$info['pic'],
                    'corporation'=>$info['corporation'], 
                    'scope'=>$info['scope'],
                    'bussiness_time'=>$info['bussiness_time'], 
                    'cards'=>$info['cards'],
                    'link'=>$info['link'],
                    'qrcode'=>$info['qrcode'],
                );
                $row2=$m_seller->data($data2)->where('id='.$info['sid'])->save();
                if($row2!==1){
                    $m->rollback();
                    $this->error('审核失败，请刷新重试');
                    exit;
                } 
            }else{
                $data_action['descr']='不同意'.$desc;
            }
            $m->commit();
            M('AdminAction')->add($data_action);
            $this->success('审核成功');
            exit;
            
        }
        $m->rollback();
        $this->error('审核失败，请刷新重试');
         
        exit;
    }
    
    
     
}