<?php
/**
 *根据汉字输出拼音首字母大写
 */
function getFirstChar($str){
    //$char为获取字符串首个字符
    $char=mb_substr($str, 0, 1,'utf8');
    if(preg_match('/[0-9]/', $char)){
        return $char;
    }
    if(preg_match('/[a-zA-Z]/', $char)){
        return strtoupper($char);
    }
    
    //ord获取ASSIC码值
    
    $fchar = ord($char);
     //为了兼容gb2312和utf8
    $s1 = iconv("UTF-8","gb2312", $char);
    $s2 = iconv("gb2312","UTF-8", $s1);
    
    //如果是utf8编码，则$s2=char,是gb2312则s1=char
    if($s2 == $char){$s = $s1;}else{$s = $char;}
    
    $asc = ord($s[0]) * 256 + ord($s[1]) - 65536;
    //('A', 45217, 45252),gb2312编码以拼音A开头的汉字编码为45217---45252
    
    if($asc >= -20319 and $asc <= -20284) return "A";
    if($asc >= -20283 and $asc <= -19776) return "B";
    if($asc >= -19775 and $asc <= -19219) return "C";
    if($asc >= -19218 and $asc <= -18711) return "D";
    if($asc >= -18710 and $asc <= -18527) return "E";
    if($asc >= -18526 and $asc <= -18240) return "F";
    if($asc >= -18239 and $asc <= -17923) return "G";
    if($asc >= -17922 and $asc <= -17418) return "H";
    if($asc >= -17417 and $asc <= -16475) return "J";
    if($asc >= -16474 and $asc <= -16213) return "K";
    if($asc >= -16212 and $asc <= -15641) return "L";
    if($asc >= -15640 and $asc <= -15166) return "M";
    if($asc >= -15165 and $asc <= -14923) return "N";
    if($asc >= -14922 and $asc <= -14915) return "O";
    if($asc >= -14914 and $asc <= -14631) return "P";
    if($asc >= -14630 and $asc <= -14150) return "Q";
    if($asc >= -14149 and $asc <= -14091) return "R";
    if($asc >= -14090 and $asc <= -13319) return "S";
    if($asc >= -13318 and $asc <= -12839) return "T";
    if($asc >= -12838 and $asc <= -12557) return "W";
    if($asc >= -12556 and $asc <= -11848) return "X";
    if($asc >= -11847 and $asc <= -11056) return "Y";
    if($asc >= -11055 and $asc <= -10247) return "Z";
    return false;
    
}

/*
 * 根据区id显示省-市-区
 *   */
function getCityNames($city){
    $m=M();
    $sql="select c3.*,c2.name as name2,c2.fid as city2,c1.name as name1
        from cm_city as c3
        left join cm_city as c2 on c2.id=c3.fid
        left join cm_city as c1 on c1.id=c2.fid
        where c3.id={$city} limit 1";
    $res=$m->query($sql);
    $names=$res[0];
    return $names['name1'].'-'.$names['name2'].'-'.$names['name'];
}

/* 
 * 更新动态过期时间 */
function updateTime($list){
   
}
