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
        $this->error("暂不开放！");
        
        if(IS_POST){
            $_POST['id']=$this->userid;
            if ($this->users_model->field('id,user_nicename,mobile')->create()!==false) {
                if ($this->users_model->save()!==false) {
                    $this->user=$this->users_model->find($this->userid);
                    sp_update_current_user($this->user);
                    $this->redirect(U('index'));
                } else {
                    $this->error("保存失败！");
                }
            } else {
                $this->error($this->users_model->getError());
            }
        }
        
    }
    
    
}
