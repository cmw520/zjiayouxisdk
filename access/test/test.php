<?php
function kuaisu($arr){

    $len = count($arr);
    if($len <= 1){
        return $arr;
    }
    $key = $arr[0];
    $left_arr = array();
    $right_arr = array();
    for($i=1; $i<$len;$i++){
        if($arr[$i] <= $key){
            $left_arr[] = $arr[$i];
           
           
        }else{
            $right_arr[] = $arr[$i];
            
        }
        
    }
    
    $left_arr = kuaisu($left_arr);

    $right_arr = kuaisu($right_arr);

    return array_merge($left_arr, array($key), $right_arr);

}
$arr = array(23,98,54,2,9,62,34);
//kuaisu($arr);
//print_r(kuaisu($arr));
//echo $n;
//$arr=array();
for($i=0;$i<count($arr)-1;$i++){
    for($j=0;$j<count($arr)-1-$i;$j++){
        if($arr[$j]>$arr[$j+1]){
            $tmp=$arr[$j];
            $arr[$j]=$arr[$j+1];
            $arr[$j+1]=$tmp;
        }
    }
}

$arr1=array('a','b',1,2,4);
$arr2=array('c','b',3,4,5,6);
$arr3=$arr1+$arr2;
//print_r($arr3);
$arr4=array_merge($arr1,$arr2);
//print_r($arr4);
//
$arr5=array(
       'a'=>'a',
       'b'=>'b'
    );
$arr6=array(
      'a'=>'x',
      'c'=>'C'
    );
$arr7=$arr5+$arr6;
print_r($arr7);
$arr8=array_merge($arr5,$arr6);
print_r($arr8);

 echo intval(0.58*100);
 echo floor((0.1+0.7)*10);