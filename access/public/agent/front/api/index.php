<?php
$db_con=array();
if(isset($_GET['action'])&&($_GET['action'])){
    
    $api_name=$_GET['action'];
    if(function_exists($api_name)){
        init_db();
        $api_name();
        close_db();
    }else{
        echo '404';
    }
}

function get_products_data(){
    global $db_con;
    $start=0;
    $num=10;
    $query="SELECT * FROM `products` ORDER BY `id` ASC LIMIT $start , $num  ";

    $result=mysql_query($query,$db_con);

    if(!$result){
        ajaxReturn(array("error"=>"1","msg"=>"没有记录".mysql_error()));
    }


    $data=get_all_record_arr($result);

    foreach($data as $k => $v){
    }
    ajaxReturn($data);
}

function get_recruit_data(){

    global $db_con;
    $start=0;
    $num=10;
    $query="SELECT * FROM `recruit` ORDER BY `create_time` DESC LIMIT $start , $num  ";

    $result=mysql_query($query,$db_con);

    if(!$result){
        ajaxReturn(array("error"=>"1","msg"=>"没有记录".mysql_error()));
    }


    $data=get_all_record_arr($result);

    foreach($data as $k => $v){
        $data[$k]['create_time']=date("Y-m-d H:i:s",$v['create_time']);
        $data[$k]['start_time']=date("Y-m-d",$v['start_time']);
        $data[$k]['end_time']=date("Y-m-d",$v['end_time']);
        
        $data[$k]['content']=  nl2br($v['content']);
    }
    ajaxReturn($data);
}

function get_news_data(){
    global $db_con;
    $start=0;
    $num=5;
    if(isset($_GET['page'])&&  is_numeric($_GET['page'])&&($_GET['page']>=1)){
        $page=$_GET['page'];
        $start=($page-1)*$num;
    }
    
    $total_data=mysql_query("SELECT * FROM news",$db_con);
    $total_rows=  mysql_num_rows($total_data);
    $total_pages=ceil($total_rows/$num);
    
    $query="SELECT * FROM news ORDER BY `create_time` DESC LIMIT $start , $num  ";
    $result=mysql_query($query,$db_con);
    if(!$result){
        ajaxReturn(array("error"=>"1","msg"=>"没有记录".mysql_error()));
    }

    $data=get_all_record_arr($result);
    foreach($data as $k => $v){
        $data[$k]['time']=date("Y-m-d H:i:s",$v['create_time']);
    }

    ajaxReturn(array("total_pages"=>$total_pages,"list"=>$data));
}

function get_all_record_arr($result){
    $data=array();
    while($row= mysql_fetch_assoc($result)){
        $data[]=$row;
    }
    return $data;
}

function get_news_item_data(){
    global $db_con;
    $id=$_GET['news_id'];
    $query="SELECT * FROM news WHERE id= $id ";
    $result=mysql_query($query,$db_con);
     $data=  get_all_record_arr($result);
    ajaxReturn(array("news"=>$data[0]));
}
function ajaxReturn($data) {
    header('Content-Type:application/json; charset=utf-8');
    exit(json_encode($data));
}

function add_news_post(){
    global $db_con;
    $title=$_POST['title'];
    $content=$_POST['content'];
    $time=time();
    $query="INSERT INTO news "
            . "(`title`,`content`,`create_time`) "
            . "VALUES "
            . "('$title','$content','$time')";
    $result=mysql_query($query,$db_con);
    if(!$result){
        $data=array("error"=>"1","msg"=>"添加失败");
        ajaxReturn($data);
    }
    $data=array("error"=>"0","msg"=>"添加成功");
    ajaxReturn($data);
}

function del_news_post(){
    $data=array("error"=>"0","msg"=>"删除成功");
    ajaxReturn($data);
}

function update_news_post(){
    $data=array("error"=>"0","msg"=>"编辑成功");
    ajaxReturn($data);
}

function init_db(){
    global $db_con;
    $db_con = mysql_connect("localhost","root","");
    if (!$db_con){
        $data=array("error"=>"1","msg"=>'Could not connect: ' . mysql_error());
        ajaxReturn($data);
    }
    mysql_set_charset('utf8', $db_con);
    mysql_select_db("kechuang_www", $db_con);

}

function close_db(){
    global $db_con;
    mysql_close($db_con);
}