<?php

namespace Admin\Controller;
use Common\Controller\AdminbaseController;
/**
 *
 * @author Innovation
 *
 */
class TestController extends AdminbaseController {

     public function _initialize() {
        parent::_initialize();
        
        //科学计算的位数
        bcscale(3);
    } 
   public function index(){
       echo 'test<br/>';
       $sql="select u.id,u.account
                            from cm_top_active as ta
                            left join cm_active as a on a.id=ta.pid
                            left join cm_seller as s on s.id=a.sid
                            left join cm_users as u on u.id=s.uid
                            where ta.id=1";
       $tmp=M()->query($sql);
       var_dump($tmp);
   }
   
   //读取excel内容添加数据库
   public function excel_reader1(){
       header("Content-Type:text/html;charset=utf-8");
       ini_set('max_execution_time', '0');
       vendor('PHPExcel.PHPExcel');
       //$filename=str_replace('.xls', '', $filename).'.xls';
       //$phpexcel = new \PHPExcel();
       
       $dir=getcwd().'/data/';
       $log=$dir.'excel.log';
       error_log('excel读取测试',3,$log);
       //require $dir."/PHPExcel/PHPExcel/IOFactory.php";//引入读取excel的类文件
       $filename=$dir."/ss.xls";
       $fileType=\PHPExcel_IOFactory::identify($filename);//自动获取文件的类型提供给phpexcel用
       $objReader=\PHPExcel_IOFactory::createReader($fileType);//获取文件读取操作对象
       //不指定就是加载所有
       //$sheetName=array('Sheet1');
      // $objReader->setLoadSheetsOnly($sheetName);//只加载指定的sheet
       $objPHPExcel=$objReader->load($filename);//加载文件
       
       //默认取第一个
       $sheet=$objPHPExcel->getSheet(0);
       $sql="insert into cm_city(id,name,fid,type) values";
       //保存省市信息
       $tmp=array();
       //foreach($objPHPExcel->getWorksheetIterator() as $sheet){//循环取sheet
           foreach($sheet->getRowIterator() as $row){//逐行处理
               //第一行不读
               /* if($row->getRowIndex()<2){
                   continue;
               } */
               
              
               $data=array();
               foreach($row->getCellIterator() as $cell){//逐列读取
                  
                   $data[]=$cell->getValue();//获取单元格数据
                  
               }
               $len=count($data);
               switch ($len){
                   case 2:
                       $tmp[0]=$data[0];
                       $sql.="({$data[0]},'{$data[1]}',0,1),";
                       break;
                   case 3:
                       $tmp[1]=$data[0];
                       $sql.="({$data[0]},'{$data[2]}',{$tmp[0]},2),";
                       break;
                   case 4:
                       $sql.="({$data[0]},'{$data[3]}',{$tmp[1]},3),";
                       break;
                   default:
                       error_log('excel读取错误，长度'.$len,3,$log);
                       continue;
               }
               
           }
           
           echo $sql;
           
       //}
       exit;
   }
   //读取excel内容添加数据库
   public function excel_reader0(){
       ini_set('max_execution_time', '0');
       vendor('PHPExcel.PHPExcel');
       //$filename=str_replace('.xls', '', $filename).'.xls';
       //$phpexcel = new \PHPExcel();
       
       $dir=getcwd().'/data/';
       //require $dir."/PHPExcel/PHPExcel/IOFactory.php";//引入读取excel的类文件
       $filename=$dir."/s0.xls";
       $fileType=\PHPExcel_IOFactory::identify($filename);//自动获取文件的类型提供给phpexcel用
       $objReader=\PHPExcel_IOFactory::createReader($fileType);//获取文件读取操作对象
       $sheetName=array('Sheet1');
       $objReader->setLoadSheetsOnly($sheetName);//只加载指定的sheet
       $objPHPExcel=$objReader->load($filename);//加载文件
       /**$sheetCount=$objPHPExcel->getSheetCount();//获取excel文件里有多少个sheet
        for($i=0;$i<$sheetCount;$i++){
        $data=$objPHPExcel->getSheet($i)->toArray();//读取每个sheet里的数据 全部放入到数组中
        print_r($data);
        }**/
       foreach($objPHPExcel->getWorksheetIterator() as $sheet){//循环取sheet
           foreach($sheet->getRowIterator() as $row){//逐行处理
               if($row->getRowIndex()<2){
                   continue;
               }
               foreach($row->getCellIterator() as $cell){//逐列读取
                   $data=$cell->getValue();//获取单元格数据
                   echo $data." ";
               }
               echo '<br/>';
           }
           echo '<br/>';
       }
       exit;
   }
}

?>