<?php
namespace Common\Model;

use Think\Model\ViewModel;

/**
 *  
 * 店铺商品
 *
 */
class Goods0ViewModel extends ViewModel {
    public $viewFields = array(
        'Goods'=>array(
            'id',
            'sid', 
            'pic', 
            'name',
            'price',
            'link',
            'create_time',
            'start_time',
            'end_time',
             'status',
            'content',
            '_type'=>'LEFT' 
        ), 
      
        'Seller'=>array('name'=>'sname',  '_on'=>'Goods.sid=Seller.id'),
        
    );
}
