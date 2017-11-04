<?php

namespace Admin\Controller;
use Common\Controller\AdminbaseController;
/**
 *
 * @author Innovation
 *
 */
class BannerController extends AdminbaseController {

    private $m;
    private $order;
    public function _initialize() {
        parent::_initialize();
        $this->m = M('Banner');
        $this->order='sort desc,start_time asc,id asc';
        
    }
    
    //编辑
    function index(){
        $m=$this->m;
        $total=$m->count();
        $page = $this->page($total, 10);
        $list=$m->order($this->order)->limit($page->firstRow,$page->listRows)->select();
        $this->assign('page',$page->show('Admin'));
        $this->assign('list',$list);
        $this->display();
    }
    //编辑
    function edit(){
        $m=$this->m;
        $info=$m->where('id='.I('id',0))->find();
        
        $this->assign('info',$info);
        //不同类别到不同的页面
        $this->display('edit');
    }
    //编辑
    function doEdit(){
        $m=$this->m;
        $data=array(
            'title'=>I('title',''), 
            'pic'=>I('pic',''),
            'link'=>I('link',''),
            'sort'=>I('sort',0)
        );
        $id=I('id',0);
        $row=$m->data($data)->where('id='.$id)->save();
        if($row===1){
            $this->success('修改成功');
        }elseif($row===0){
            $this->success('未修改');
        }else{
            $this->error('修改失败');
        }
        
    }
    //删除
    function del(){
        $m=$this->m;
        
        $id=I('id',0);
        $row=$m->where('id='.$id)->delete();
        if($row===1){
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
        exit;
    }
   
    //添加 
    public function add(){
        
        $this->display();
    }
    
    //添加 执行
    public function add_do(){
        
        $m=$this->m;
        
        $data=array(
            'pic'=>I('pic',''),
            'title'=>I('title',''),
            'sort'=>I('sort',0),
            'link'=>I('link','')
            
        );
        $row=$m->data($data)->add();
        if($row>=1){
            $this->success('已成功添加');
        }else{
            $this->error('添加失败');
        }
        exit;
    }
}

?>