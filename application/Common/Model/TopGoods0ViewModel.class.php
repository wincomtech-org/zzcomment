<?php
namespace Common\Model;

use Think\Model\ViewModel;

/**
 *  
 * 店铺商品推荐
 *
 */
class TopGoods0ViewModel extends ViewModel {
    public $viewFields = array(
        'TopGoods'=>array(
            'id',
            'pid',
            'create_time', 
            'status', 
            'price',
            '_type'=>'LEFT'
        ), 
        'Goods'=>array(
            'id'=>'aid',
            'sid', 
            'pic', 
            'name'=>'aname',
            'content',
            'create_time'=>'acreate_time',
            'start_time'=>'astart_time',
            'end_time'=> 'aend_time',
            'status'=>'astatus', 
            '_type'=>'LEFT','_on'=>'Goods.id=TopGoods.pid'
        ), 
      
        'Seller'=>array('name'=>'sname',  '_on'=>'Goods.sid=Seller.id'),
        
    );
}
