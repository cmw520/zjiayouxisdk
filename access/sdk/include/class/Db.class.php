<?php
/**
 * DB - A simple database class
 * 
 * @author Author: Vivek Wicky Aswal. (https://twitter.com/#!/VivekWickyAswal) @git https://github.com/indieteq/PHP-MySQL-PDO-Database-Class
 * @version 0.2ab
 * modify by lingguihua 说明：在760行多出一张表操作，需要注释掉，目前已经注释。@time 2016/10/08
 */
require ("Response.class.php");

class DB {
    // @object, The PDO object
    private $pdo;
    
    // @object, PDO statement object
    private $sQuery;
    
    // @array, The database settings
    private $settings;
    
    // @bool , Connected to the database
    private $bConnected = false;
    
    // @object, Object for logging exceptions
    private $log;
    
    // @array, The parameters of the SQL query
    private $parameters;
    private $_dbConfig = array (
        'db_type' => DB_TYPE, // dbms
        'db_host' => DB_HOST, // 主机地址
        'db_prefix' => DB_PREFIX, // 数据库前缀
        'db_user' => DB_USER, // 数据库用户
        'db_pwd' => DB_PWD, // 密码
        'db_name' => DB_DATABASE 
    ); // 数据库名
    
    /**
     * Default Constructor 1.
     * Instantiate Log class. 2. Connect to database. 3. Creates the parameter array.
     */
    public function __construct() {
        $this->log = new Switchlog(true);
        $this->Connect();
        $this->parameters = array ();
    }
    
    /**
     * This method makes connection to the database.
     * 1. Reads the database settings from a ini file. 2. Puts the ini content into the settings array. 3. Tries to connect to the database. 4. If connection failed, exception is displayed and a log file gets created.
     */
    private function Connect() {
        $dsn = $this->_dbConfig['db_type'] .
             ':dbname=' . $this->_dbConfig['db_name'] . ';host=' . $this->_dbConfig['db_host'] . '';
        try {
            // Read settings from INI file, set UTF8
            $this->pdo = new PDO(
                    $dsn, $this->_dbConfig['db_user'], $this->_dbConfig['db_pwd'], 
                    array (
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8" 
                    ));
            
            // We can now log any exceptions on Fatal error.
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Disable emulation of prepared statements, use REAL prepared statements instead.
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            
            // Connection succeeded, set the boolean to true.
            $this->bConnected = true;
        } catch (PDOException $e) {
            // Write into log
            $this->ExceptionLog($e->getMessage());
            die();
        }
    }
    
    /*
     * You can use this little method if you want to close the PDO connection
     */
    public function CloseConnection() {
        // Set the PDO object to null to close the connection
        // http://www.php.net/manual/en/pdo.connections.php
        $this->pdo = null;
    }
    
    /**
     * Every method which needs to execute a SQL query uses this method.
     * 1. If not connected, connect to the database. 2. Prepare Query. 3. Parameterize Query. 4. Execute Query. 5. On exception : Write Exception into the log + SQL query. 6. Reset the Parameters.
     */
    private function Init($query, $parameters = "") {
        // Connect to database
        if (!$this->bConnected) {
            $this->Connect();
        }
        
        try {
            // Prepare query
            $this->sQuery = $this->pdo->prepare($query);

            // Add parameters to the parameter array
            $this->bindMore($parameters);
            
            // Bind parameters
            if (!empty($this->parameters)) {
                foreach ($this->parameters as $param) {
                    $parameters = explode("\x7F", $param);
                    $this->sQuery->bindParam($parameters[0], $parameters[1]);
                }
            }
            // Execute SQL
            $this->succes = $this->sQuery->execute();
            
        } catch (PDOException $e) {
            // Write into log and display Exception
            $this->ExceptionLog($e->getMessage(), $query);
            die();
        }
        
        // Reset the parameters
        $this->parameters = array ();
    }
    
    /**
     * 检查字符串是否是UTF8编码
     * 
     * @param string $string 字符串
     * @return Boolean
     */
    public function is_utf8($string) {
        return preg_match(
                '%^(?:
                             [\x09\x0A\x0D\x20-\x7E]            # ASCII
                           | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
                           |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
                           | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
                           |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
                           |  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
                           | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
                           |  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
                        )*$%xs', 
                $string);
    }
    
    /**
     * @void Add the parameter to the parameter array
     * 
     * @param string $para
     * @param string $value
     */
    public function bind($para, $value) {
        if ($this->is_utf8($value)) {
            $str = $value;
        } else {
            $str = utf8_encode($value);
        }
        $this->parameters[sizeof($this->parameters)] = ":" . $para . "\x7F" . $str;
    }
    /**
     * @void Add more parameters to the parameter array
     * 
     * @param array $parray
     */
    public function bindMore($parray) {
        if (empty($this->parameters) && is_array($parray)) {
            $columns = array_keys($parray);
            foreach ($columns as $i => &$column) {
                $this->bind($column, $parray[$column]);
            }
        }
    }
    
    /**
     * If the SQL query contains a SELECT or SHOW statement it returns an array containing all of the result set row If the SQL statement is a DELETE, INSERT, or UPDATE statement it returns the number of affected rows
     * 
     * @param string $query
     * @param array $params
     * @param int $fetchmode
     * @return mixed
     */
    public function query($query, $params = null, $fetchmode = PDO::FETCH_ASSOC) {
        $query = trim($query);

        $this->Init($query, $params);

        $rawStatement = explode(" ", $query);
        
        // Which SQL statement is used
        $statement = strtolower($rawStatement[0]);
        
        if ($statement === 'select' || $statement === 'show') {
            return $this->sQuery->fetchAll($fetchmode);
        } elseif ($statement === 'insert' || $statement === 'update' || $statement === 'delete') {
            return $this->sQuery->rowCount();
        } else {
            return NULL;
        }
    }
    
    /**
     * Returns the last inserted id.
     * 
     * @return string
     */
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Returns an array which represents a column from the result set
     * 
     * @param string $query
     * @param array $params
     * @return array
     */
    public function column($query, $params = null) {
        $this->Init($query, $params);
        $Columns = $this->sQuery->fetchAll(PDO::FETCH_NUM);
        
        $column = null;
        
        foreach ($Columns as $cells) {
            $column[] = $cells[0];
        }
        
        return $column;
    }
    /**
     * Returns an array which represents a row from the result set
     * 
     * @param string $query
     * @param array $params
     * @param int $fetchmode
     * @return array
     */
    public function row($query, $params = null, $fetchmode = PDO::FETCH_ASSOC) {
        $this->Init($query, $params);
        return $this->sQuery->fetch($fetchmode);
    }
    /**
     * Returns the value of one single field/column
     * 
     * @param string $query
     * @param array $params
     * @return string
     */
    public function single($query, $params = null) {
        $this->Init($query, $params);
        return $this->sQuery->fetchColumn();
    }
    /**
     * Writes the log and returns the exception
     * 
     * @param string $message
     * @param string $sql
     * @return string
     */
    private function ExceptionLog($message, $sql = "") {
        // $exception = 'Unhandled Exception. <br />';
        // $exception .= $message;
        // $exception .= "<br /> You can find the error back in the log.";
        if (!empty($sql)) {
            // Add the Raw SQL to the Log
            $message .= "\r\nRaw SQL : " . $sql;
        }
        // Write into log
        $this->log->write($message);
        
        // return $exception;
    }
    
    public function checkClient($clientid, $appid){
        $sql = "select is_switch from ".$this->_dbConfig['db_prefix']."game_client where id=:clintid AND app_id=:appid AND status=1 ";
        $this->bind("clintid", $clientid);
        $this->bind("appid", $appid);
        $is_switch = $this->single($sql);
        return $is_switch;
    }
    
    /* 获取数据库名与支付方式  */
    public function getAppkey($appid){
        if (empty($appid) || 0 > $appid) {
            return NULL;
        }
        
        $sql = "select app_key from ".$this->_dbConfig['db_prefix']."game where id=:appid ";
        $this->bind("appid", $appid);
        $appkey = $this->single($sql);
        return $appkey;
    }
    
    /* 获取client信息 */
    public function getClient($client_id){
        if (empty($client_id) || 0 > $client_id) {
            return NULL;
        }
        
        $sql = "select * from ".$this->_dbConfig['db_prefix']."game_client where id=:clientid ";
        $this->bind("clientid", $client_id);
        $clientinfo = $this->row($sql);
        return $clientinfo;
    }
    
    
    public function getAgentid($agentgame){
        if (empty($agentgame) || 'default' ==$agentgame) {
            return 0;
        }
    
        $sql = "SELECT agent_id FROM ".$this->_dbConfig['db_prefix']."agent_game where agentgame=:agentgame";
        $this->bind("agentgame", $agentgame);
        $agent_id = $this->row($sql);
        
        if (empty($agent_id['agent_id'])){
            $agent_id = 0;
        }
        return $agent_id['agent_id'];
    }
    
    public function setUsername(){
        $basenum = 10000;
        
        // 生成用户名
        $minsql = "select min(base) from ".$this->_dbConfig['db_prefix']."mem_base";
        $min = $this->single($minsql);
        
        $cntsql = "select count(id) from ".$this->_dbConfig['db_prefix']."mem_base where base=$min";
        $cnt = $this->single($cntsql) - 1;
        
        $limit = rand(0, $cnt);
        
        $upsql = "select id from ".$this->_dbConfig['db_prefix']."mem_base where base=$min limit $limit,1";
        $uid = $this->single($upsql);

        $upsql = "UPDATE ".$this->_dbConfig['db_prefix']."mem_base SET `base` = `base` + 1 WHERE `id` = $uid";
        $rs = $this->query($upsql);
        
        if(!empty($rs) && 0 < $rs){
            $username =  $basenum * $min + $uid;
        }
        $userinfo = $this->getUserinfo($username);
        $i = 0;
        //存在用户一直向下执行
        while($userinfo && $i < 20){
            
            $i ++;
            $upsql = "UPDATE ".$this->_dbConfig['db_prefix']."mem_base SET `base` = `base` + 1 WHERE `id` = $uid";
            $rs = $this->query($upsql);
            if(!empty($rs) && 0 < $rs){
                $username =  $basenum * ($min+$i) + $limit;
            }
            $userinfo = $this->getUserinfo($username);
        }
        
        return $username ;
    }
    
    public function getUserinfo($username){
        //0 为试玩状态 1为正常状态，2为冻结状态
        $sql = "select * from ".$this->_dbConfig['db_prefix']."members where username=:username";
        $this->bind("username", $username);
        $data = $this->row($sql);
        return $data;
    }
    
    public function getOauthinfo($userfrom, $openid){
        //1试玩 2 qq 3 微信 4微博
        $sql = "select * from ".$this->_dbConfig['db_prefix']."mem_oauth where `from`=:userfrom and openid=:openid";
        $this->bind("userfrom", $userfrom);
        $this->bind("openid", $openid);
        $data = $this->row($sql);
        return $data;
    }
    
    //通过用户ID获取用户信息
    public function getUserbyid($mem_id){
        //1 为试玩状态 2为正常状态，3为冻结状态
        $sql = "select * from ".$this->_dbConfig['db_prefix']."members where id=:mem_id";
        $this->bind("mem_id", $mem_id);
        $data = $this->row($sql);
        return $data;
    }
    
    //获取平台币余额
    public function getPtb($mem_id){
        $sql = "select * from ".$this->_dbConfig['db_prefix']."ptb_mem where mem_id=:mem_id";
        $this->bind("mem_id", $mem_id);
        $data = $this->row($sql);
        return $data;
    }
    
    //获取游戏币余额
    public function getGm($mem_id, $app_id){
        $sql = "select * from ".$this->_dbConfig['db_prefix']."gm_mem where mem_id=:mem_id AND app_id=:app_id";
        $this->bind("mem_id", $mem_id);
        $this->bind("app_id", $app_id);
        $data = $this->row($sql);
        return $data;
    }
    
    //更新支付方式
    public function updatePayway($order_id, $payway){
        $sql = "UPDATE ".$this->_dbConfig['db_prefix']."pay SET `payway` = :payway WHERE `order_id` =:order_id";
        $this->bind("order_id", $order_id);
        $this->bind("payway", $payway);
        $data = $this->query($sql);
        return true;
    }
    
    //获取订单信息
    public function getPayinfo($order_id){
        if (empty($order_id)){
            return false;
        }
        $sql = "select * from ".$this->_dbConfig['db_prefix']."pay where order_id=:order_id";
        $this->bind("order_id",$order_id);
        $paydata = $this->row($sql);
        return $paydata;
    }
    
    //获取订单扩展信息
    public function getPayextinfo($pay_id){
        if (empty($pay_id)){
            return false;
        }
        $sql = "select * from ".$this->_dbConfig['db_prefix']."pay_ext where pay_id=:pay_id";
        $this->bind("pay_id",$pay_id);
        $paydata = $this->row($sql);
        return $paydata;
    }
    
    public function getCpurl($appid, $cid = 0){
        if (!isset($appid) || 1 > $appid) {
            return NULL;
        }
        $sql = "select cpurl from ".$this->_dbConfig['db_prefix']."game where appid=:appid ";
        $this->bind("appid", $appid);
        $cpurl = $this->single($sql);      
       
        return $cpurl;
    }
    
    //获取支付方式
    public function getPayway($app_id){
        //获取游戏支付方式
        $sql = "SELECT pw_id FROM ".$this->_dbConfig['db_prefix']."payway_game where app_id=:app_id ";
        $this->bind("app_id", $app_id);
        $gpw_data = $this->column($sql);

        if (empty($gpw_data)){
            $pwsql = "SELECT payname AS a, disc AS b FROM ".$this->_dbConfig['db_prefix']."payway where status=2";
            $data = $this->query($pwsql);
        }else{
            $gpwstr = implode(',', $gpw_data);
            $pwsql = "SELECT payname AS a, disc AS b FROM ".$this->_dbConfig['db_prefix']."payway where status=2 AND id IN (".$gpwstr.")";

            $data = $this->query($pwsql);
        }
        
        return $data;
    }
    
    public function insertOauth($oauthdata){
        $insql = " INSERT INTO ".$this->_dbConfig['db_prefix']."mem_oauth";
        $insql .= " (`from`,`name`,`head_img`,`mem_id`,`create_time`,`last_login_time`,`last_login_ip`,`access_token`,`expires_date`,`openid`) ";
        $insql .= " VALUES ";
        $insql .= " (:from,:name,:head_img,:mem_id,:create_time,:last_login_time,:last_login_ip,:access_token,:expires_date,:openid)";
        $rs = $this->query($insql, $oauthdata);    
        if ($rs){
            return intval($this->lastInsertId());
        }else{
            return FALSE;
        }
    }
    
    public function insertRegist($regdata,$repassword=''){
        $regdata['pay_pwd'] = $regdata['password'];

        $insql = " INSERT INTO ".$this->_dbConfig['db_prefix']."members";
        $insql .= " (`username`,`password`,`pay_pwd`,`mobile`,`nickname`,`from`,`imei`,`agentgame`,`app_id`,`agent_id`,`status`,`reg_time`,`update_time`) ";
        $insql .= " VALUES ";
        $insql .= " (:username,:password,:pay_pwd, :mobile,:nickname,:from,:imei,:agentgame,:app_id,:agent_id,:status,:reg_time, :update_time)";
        $rs = $this->query($insql, $regdata);
        $mem_id = $this->lastInsertId();
        if($rs && UCENTER_ENABLED){
            $salt = $this->random(6);
            $ucpassword['password'] = md5(md5($repassword).$salt);
            $ucpassword['username'] = $regdata['username'];
            $ucpassword['regdate'] = $regdata['reg_time'];
            $ucpassword['salt'] = $salt;
            
            $uinsql = " INSERT INTO db_bbs.bbs_ucenter_members";
            $uinsql .= " (`username`,`password`,`regdate`,`salt`) ";
            $uinsql .= " VALUES ";
            $uinsql .= " (:username,:password,:regdate, :salt)";
            $res = $this->query($uinsql, $ucpassword);
        }
        if ($rs){
            return intval($mem_id);
        }else{
            return FALSE;
        }
    }
    
    //论坛同步加密salt
    function random($length, $numeric = 0) {
        PHP_VERSION < '4.2.0' && mt_srand((double)microtime() * 1000000);
        if($numeric) {
            $hash = sprintf('%0'.$length.'d', mt_rand(0, pow(10, $length) - 1));
        } else {
            $hash = '';
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
            $max = strlen($chars) - 1;
            for($i = 0; $i < $length; $i++) {
                $hash .= $chars[mt_rand(0, $max)];
            }
        }
        return $hash;
        exit;
    }
    
    /*
     * 插入登陆信息
     */
    public function insertLogin($logindata){
        $memdata['mem_id'] = $logindata['mem_id'];
        $memdata['app_id'] = $logindata['app_id'];
        $checksql = "SELECT id from ".$this->_dbConfig['db_prefix']."mem_game WHERE mem_id=:mem_id AND app_id=:app_id";
        $memgame = $this->single($checksql, $memdata);  

        if (empty($memgame)){
            $memext['game_cnt'] = 1;
            $logindata['flag'] = 1;
            $memdata['create_time'] = $logindata['login_time'];
            $memdata['update_time'] = $logindata['login_time'];
            
            //插入玩家游戏数据
            $memgamesql = "INSERT INTO ".$this->_dbConfig['db_prefix']."mem_game ";
            $memgamesql .= " (mem_id, app_id, create_time, update_time) ";
            $memgamesql .= " VALUES ";
            $memgamesql .= "(:mem_id, :app_id, :create_time, :update_time)";
            $this->query($memgamesql, $memdata);
        }else{
            $memext['game_cnt'] = 0;
            $logindata['flag'] = 0;
            
            //更新玩家游戏数据
            $memgamesql = "UPDATE ".$this->_dbConfig['db_prefix']."mem_game ";
            $memgamesql .= " SET update_time=:update_time WHERE id=:id";
            
            $this->bind('update_time', $logindata['login_time']);
            $this->bind('id',$memgame);
            $this->query($memgamesql);
        }

        //更新玩家其他数据
        $checkextsql = "SELECT mem_id from ".$this->_dbConfig['db_prefix']."mem_ext WHERE mem_id=:mem_id";
        $this->bind('mem_id', $logindata['mem_id']);
        $memextid = $this->single($checkextsql);
        
        $memext['mem_id'] = $logindata['mem_id'];
        $memext['last_login_time'] = $logindata['login_time'];
        $memext['user_token'] =  $logindata['user_token'];
        if (empty($memextid)){
            $memextsql = "INSERT INTO ".$this->_dbConfig['db_prefix']."mem_ext ";
            $memextsql .= " (`mem_id`,`last_login_time`,`game_cnt`,`sum_money`,`last_pay_time`,`last_money`,`order_cnt`,`login_cnt`,`user_token`) ";
            $memextsql .= " VALUES ";
            $memextsql .= " (:mem_id,:last_login_time,:game_cnt,0,0,0,0,1,:user_token)";
            $this->query($memextsql, $memext);
        }else{
            $memextsql = "UPDATE ".$this->_dbConfig['db_prefix']."mem_ext ";
            $memextsql .= " SET last_login_time=:last_login_time, game_cnt=game_cnt+:game_cnt, login_cnt=login_cnt+1, user_token=:user_token WHERE mem_id=:mem_id";
            $this->query($memextsql, $memext);
        }
        
        //更新玩家SESSION数据
        $_SESSION['user_token'] = $memext['user_token'];
        $_SESSION['mem_id'] = $memext['mem_id'];
        unset($logindata['user_token']);
        
        $loginsql = " INSERT INTO `".$this->_dbConfig['db_prefix']."login_log`";
        $loginsql .= " (`mem_id`,`app_id`,`agentgame`,`imei`,`deviceinfo`,`userua`,`from`,`flag`,`reg_time`,`login_time`,`agent_id`,`login_ip`,`ipaddrid`)";
        $loginsql .= " VALUES ";
        $loginsql .= " (:mem_id,:app_id,:agentgame,:imei,:deviceinfo,:userua,:from,:flag,:reg_time,:login_time,:agent_id,:login_ip,:ipaddrid)";
        return $this->query($loginsql, $logindata);
    }
    
    public function updateMemextpay($paydata){
        $userdata['last_pay_time'] = $paydata['create_time'];
        $userdata['last_money'] = $paydata['amount'];
        $userdata['last_money1'] = $userdata['last_money'];
        $userdata['mem_id'] = $paydata['mem_id'];
        $memextsql = "UPDATE ".$this->_dbConfig['db_prefix']."mem_ext ";
        $memextsql .= " SET sum_money=sum_money+:last_money, last_pay_time=:last_pay_time, last_money=:last_money1, order_cnt=order_cnt+1 WHERE mem_id=:mem_id";

        $this->query($memextsql, $userdata);
    }
    /* 
     * 插入退出信息
     */
    
    public function insertLogout($logoutdata){
        //轻松session
        session_destroy();
        $logoutsql = " INSERT INTO `".$this->_dbConfig['db_prefix']."logout_log`";
        $logoutsql .= " (`mem_id`,`app_id`,`agent_id`,`agentgame`,`imei`,`deviceinfo`,`userua`,`from`,`logout_time`,`logout_ip`,`ipaddrid`)";
        $logoutsql .= " VALUES ";
        $logoutsql .= "(:mem_id,:app_id,:agent_id,:agentgame,:imei,:deviceinfo,:userua,:from,:logout_time,:logout_ip,:ipaddrid)";
        return $this->query($logoutsql, $logoutdata);
    }
    
    /*
     * 插入支付数据
     */
    private function insertPay($paydata){
        $paysql = "INSERT INTO `".$this->_dbConfig['db_prefix']."pay`";
        $paysql .= " (`order_id`,`mem_id`,`agent_id`,`app_id`,`amount`,`from`,`status`,`cpstatus`,`create_time`,`update_time`,`attach`,`real_amount`,`rebate_cnt`)";
        $paysql .= " VALUES ";
        $paysql .= " (:order_id,:mem_id,:agent_id,:app_id,:amount,:from,:status,:cpstatus,:create_time,:update_time,:attach,:real_amount,:rebate_cnt)";
        $rs =  $this->query($paysql, $paydata);
        if ($rs){
            return (int)$this->lastInsertId();
        }else{
            return FALSE;
        }
    }
    
    /*
     * 插入支付数据扩展信息
     */
    private function insertPayext($payextdata){
        $payextsql = "INSERT INTO `".$this->_dbConfig['db_prefix']."pay_ext`";
        $payextsql .= " (`pay_id`,`role`,`productname`,`productdesc`,`deviceinfo`,`userua`,`agentgame`,`server`,`pay_ip`,`imei`,`cityid`) ";
        $payextsql .= " VALUES ";
        $payextsql .= " (:pay_id,:role,:productname,:productdesc,:deviceinfo,:userua,:agentgame,:server,:pay_ip,:imei,:cityid)";
        $rs =  $this->query($payextsql, $payextdata);
        return (int)$this->lastInsertId();
    }
    
    /*
     * 支付创建函数
     */
    public function doPay($paydata, $pay_extdata){
        $pay_id = $this->insertPay($paydata);
        if ($pay_id){
            $pay_extdata['pay_id'] = $pay_id;
            $this->insertPayext($pay_extdata);
            return $pay_id;
        }else{
            return FALSE;
        }
    }
    
    /*
     * 游戏币支付订单插入
     */
    public function doGmpay($order_id, $gm_cnt){
        $paysql = "INSERT INTO `".$this->_dbConfig['db_prefix']."gm_pay` ";
        $paysql .= "(`order_id`,`mem_id`,`agent_id`,`app_id`,`amount`,`gm_cnt`,`from`,`status`,`create_time`,`update_time`)";
        $paysql .= " SELECT ";
        $paysql .= "`order_id`,`mem_id`,`agent_id`,`app_id`,`amount`,:gm_cnt,`from`,`status`,`create_time`,`update_time`";
        $paysql .= " from ".$this->_dbConfig['db_prefix']."pay where order_id=:order_id";
        $this->bind("order_id",$order_id);
        $this->bind("gm_cnt",$gm_cnt);
        $rs =  $this->query($paysql);
        if ($rs){
            return (int)$this->lastInsertId();
        }else{
            return FALSE;
        }
    }
    
    public function doPtbpay($order_id, $ptb_cnt){
        $paysql = "REPLACE INTO `".$this->_dbConfig['db_prefix']."ptb_pay` ";
        $paysql .= "(`order_id`,`mem_id`,`agent_id`,`app_id`,`amount`,`ptb_cnt`,`from`,`status`,`create_time`,`update_time`)";
        $paysql .= " SELECT ";
        $paysql .= "`order_id`,`mem_id`,`agent_id`,`app_id`,`amount`,:ptb_cnt,`from`,`status`,`create_time`,`update_time`";
        $paysql .= " FROM ".$this->_dbConfig['db_prefix']."pay where order_id=:order_id";
        $this->bind("order_id",$order_id);
        $this->bind("ptb_cnt",$ptb_cnt);
        $rs =  $this->query($paysql);
        if ($rs){
            return (int)$this->lastInsertId();
        }else{
            return FALSE;
        }
    }
    
    //获取游戏信息
    public function getGameinfo($app_id){
        $sql = "SELECT * FROM ".$this->_dbConfig['db_prefix']."game WHERE id=:app_id";
        $this->bind("app_id", $app_id);
        $data = $this->row($sql);
        return $data;
    }
    
    public function doPaynotify($orderid, $amount, $paymark='') {
        if(empty($orderid) || empty($amount)){
            return FALSE;
        }
        $trade['orderid'] = $orderid;
        $trade['remark'] = $paymark;
        
        $time = time();

        // 1 通过订单号查询订单信息
        $paydata = $this->getPayinfo($orderid);
        if (empty($paydata)){
            return FALSE;
        }
//         $myamount = number_format($paydata['amount'], 2, '.', '');
        $myamount = number_format($paydata['real_amount'], 2, '.', '');
        $trade['real_amount'] = $myamount;
        $trade['rebate_cnt'] = $paydata['rebate_cnt'];
        if($paydata['payway'] =="gamepay" || $paydata['payway'] == "ptbpay"){
            $myamount = number_format($paydata['amount'], 2, '.', '');
            //游戏币与平台币支付 无折扣 无返利  实际支付人民币 0元
            $trade['real_amount'] = 0;
            $trade['rebate_cnt'] = 0;
        }
        $amount = number_format($amount, 2, '.', '');
        //2 验证订单数额的一致性与状态改变
        if (($myamount == $amount) && 2 != $paydata['status']) {
            //2.1 订单状态改变
            $sql = "UPDATE ".$this->_dbConfig['db_prefix']."pay SET status=2, remark=:remark,real_amount=:real_amount,rebate_cnt=:rebate_cnt WHERE order_id=:orderid";
            $rs = $this->query($sql, $trade);
            //只给非游戏币及平台币充值用户返利
            if($paydata['payway'] !="gamepay" && $paydata['payway'] !="ptbpay"){
                $this->dorebate($orderid,$paydata['mem_id'],$paydata['app_id']);
                //非官包充值插入渠道利益收入表
                if($paydata['agent_id']>0){
                    $_paydata = $paydata;
                    $_paydata['remark'] = $paymark;
                    $this->insert_statistics($_paydata);
                }
            }
            
            //2.2 回调CP
            if ($rs) {
                $this->updateMemextpay($paydata);
                //2.2.1 查询CP回调地址与APPKEY
                $game_data = $this->getGameinfo($paydata['app_id']); 
                $cpurl = $game_data['cpurl'];
                $app_key = $game_data['app_key'];
                
                $param['order_id'] = (string)$paydata['order_id'];
                $param['mem_id'] = (string)$paydata['mem_id'];
                $param['app_id'] = (string)$paydata['app_id'];
//                 $param['money'] = (string)$myamount;
                $param['money'] = (string)number_format($paydata['amount'], 2, '.', '');
                $param['order_status'] ='2';
                $param['paytime'] = (string)$paydata['create_time'];
                $param['attach'] = (string)$paydata['attach'];
                
                //2.2.2 拼接回调
                $signstr = "order_id=".$paydata['order_id']."&mem_id=".$paydata['mem_id']."&app_id=".$paydata['app_id'];
                $signstr .= "&money=".$param['money']."&order_status=2&paytime=".$paydata['create_time']."&attach=".$paydata['attach'];
                $md5str = $signstr."&app_key=".$app_key;

                $sign = md5($md5str);
                $param['sign'] = (string)$sign;
                
                //2.2.3 通知CP
                if ($paydata['cpstatus'] == 1 || $paydata['cpstatus'] == 3) {
                    $i = 0;
                    while (1) {
                        $cp_rs = Response::payback($cpurl, $param);
  
                        if ($cp_rs > 0) {
                            //$this->UpdateMgrole($paydata['id'],$paydata['mem_id'],$paydata['app_id'],$amount);
                            $cpstatus = 2;
                            break;
                        }else{
                            $cpstatus = 3;
                            $i ++;
                            sleep(2);
                        }
        
                        if ($i == 3) {
                            break;
                        }
                    }
                }
                
                //更新CP状态
                $sql = "UPDATE ".$this->_dbConfig['db_prefix']."pay SET cpstatus=:cpstatus WHERE order_id=:orderid";
                $this->bind('cpstatus', $cpstatus);
                $this->bind('orderid', $orderid);

                $this->query($sql);
            }
        }
        return TRUE;
    }
    
    public function getbenefit($mem_id,$agentid,$appid){
        //判断是否返利，$benefit_type为1为折扣
        $sql = "SELECT benefit_type,mem_rate,first_mem_rate,mem_rebate,first_mem_rebate FROM ".$this->_dbConfig['db_prefix']."agent_game_rate WHERE agent_id=:agentid AND app_id=:app_id";
        $this->bind("agentid", $agentid);
        $this->bind("app_id", $appid);
        $benefitdata = $this->row($sql);
        //如果设定了官包返利且agent_game_rate中查询不到数据
        if (empty($benefitdata) && DEFAULT_SET){
            $sql = "SELECT benefit_type,mem_rate,first_mem_rate,mem_rebate,first_mem_rebate FROM ".$this->_dbConfig['db_prefix']."game_rate WHERE app_id=:app_id";
            $this->bind("app_id", $appid);
            $benefitdata = $this->row($sql);
        }
        //首冲跟续冲
        $sql = "SELECT id FROM ".$this->_dbConfig['db_prefix']."pay WHERE mem_id=:mem_id AND app_id=:app_id AND payway!='ptbpay' AND payway!='yxbpay' AND status=2";
        $this->bind("mem_id", $mem_id);
        $this->bind("app_id", $appid);
        $checkfirst = $this->single($sql);
        
        //app充值去除
        $appsql = "SELECT id FROM ".$this->_dbConfig['db_prefix']."gm_charge WHERE mem_id=:mem_id AND app_id=:app_id AND payway!='ptb' AND status=2";
        $this->bind("mem_id", $mem_id);
        $this->bind("app_id", $appid);
        $checkappfirst = $this->single($appsql);
        $benefitdata['is_first'] = 0;
        if (empty($checkfirst) && empty($checkappfirst)){
            $benefitdata['is_first'] = 1;
        }
        return $benefitdata;
    }
    
    public function dorebate($orderid,$mem_id,$appid) {
        $sql = "SELECT rebate_cnt FROM ".$this->_dbConfig['db_prefix']."pay WHERE order_id=:order_id";
        $this->bind("order_id", $orderid);
        $gold = $this->single($sql);
        if ($gold == 0){
            return true;
        }
    
        $sql = "SELECT id FROM ".$this->_dbConfig['db_prefix']."gm_mem WHERE mem_id=:mem_id AND app_id=:app_id";
        $this->bind("mem_id", $mem_id);
        $this->bind("app_id", $appid);
        $id = $this->single($sql);
        $total = $gold;
        if($id > 0){
            //更新游戏币余额
            $upsql = "update ".$this->_dbConfig['db_prefix']."gm_mem set remain=remain+:gold,total=total+:total,update_time=:time where mem_id=:mem_id AND app_id=:app_id";
            $this->bind("gold",  $gold);
            $this->bind("total", $total);
            $this->bind("time", $time);
            $this->bind("mem_id", $mem_id);
            $this->bind("app_id", $appid);
            $rs = $this->query($upsql);
        }else{
            //账号不存在则插入
            $insql = " INSERT INTO ".$this->_dbConfig['db_prefix']."gm_mem";
            $insql .= " (`mem_id`,`remain`,`app_id`,`total`,`create_time`) ";
            $insql .= " VALUES ";
            $insql .= " (:mem_id,:gold,:app_id,:total,:create_time)";
            $this->bind("gold",  $gold);
            $this->bind("mem_id", $mem_id);
            $this->bind("app_id", $appid);
            $this->bind("total",  $total);
            $this->bind("create_time",$time);
            $rs = $this->query($insql);
        }

        $sql = "SELECT app_id FROM ".$this->_dbConfig['db_prefix']."gm WHERE app_id=:app_id";
        $this->bind("app_id", $appid);
        $checkid = $this->single($sql);
        $total = $gold;
        if($checkid){
            $upsql = "update ".DB_PREFIX."gm set mem_remain=mem_remain+:gold,update_time=:update_time where app_id=:app_id";
            $this->bind("gold",  $gold);
            $this->bind("update_time",$time);
            $this->bind("app_id", $appid);
            $rs_ptb = $this->query($upsql);
            return true;
        }else{
            $insql = " INSERT INTO ".$this->_dbConfig['db_prefix']."gm";
            $insql .= " (`mem_remain`,`app_id`,`create_time`) ";
            $insql .= " VALUES ";
            $insql .= " (:gold,:app_id,:create_time)";
            $this->bind("gold",  $gold);
            $this->bind("app_id", $appid);
            $this->bind("create_time",  $time);
            $rs = $this->query($insql);
            return true;
        }
    }
    
    public function UpdateMgrole($pay_id,$mem_id, $app_id, $amount){
        $payextinfo = $this->getPayextinfo($pay_id);
        $mgl_data['mem_id'] = $mem_id;
        $mgl_data['app_id'] = $app_id;
        $mgl_data['server'] = $payextinfo['server'];
        $mgl_data['role'] = $payextinfo['role'];
        $mgl_data['money'] = $amount;
        $mgl_data['create_time'] = time();
        
        $insql = " INSERT INTO ".$this->_dbConfig['db_prefix']."mg_role_log";
        $insql .= " (`mem_id`,`app_id`,`server`,`role`,`money`,`create_time`) ";
        $insql .= " VALUES ";
        $insql .= " (:mem_id,:app_id,:server,:role,:money,:create_time)";
        $rs = $this->query($insql, $mgl_data);
    }
    
    //查出此订单一级渠道，二级渠道分成比率，优惠类型
    public function lookupeachrate($appid,$agentid) {
        //查询是否有一级代理
        $sql = "SELECT ownerid FROM ".$this->_dbConfig['db_prefix']."users WHERE id=:agent_id";
        $this->bind("agent_id", $agentid);
        $ownerid = $this->single($sql);
        
        //查询游戏返利类型
        $sql = "SELECT benefit_type FROM ".$this->_dbConfig['db_prefix']."game_rate WHERE app_id=:app_id";
        $this->bind("app_id", $appid);
        $data['benefit_type'] = $this->single($sql);
        
        $data['is_first'] = 0;
        //ownerid为1则为admin发放代理
        if($ownerid>1){
            $data['is_first'] =1;
            $firstrate = $this->getagentrate($appid, $ownerid);
            $data['first_agent_rate'] = $firstrate;
            $data['first_agent_id']=$ownerid;
        }
        $secrate = $this->getagentrate($appid, $agentid);
        $data['sec_agent_rate'] = $secrate;
        return $data;
    }
    
    //通过agentid及appid查询优惠类型，比率
    public function getagentrate($appid,$agentid){
        //查询一级代理所得比率
        $sql = "SELECT agent_rate FROM ".$this->_dbConfig['db_prefix']."agent_game_rate WHERE agent_id=:agent_id and app_id=:app_id";
        $this->bind("agent_id", $agentid);
        $this->bind("app_id", $appid);
        $agentrate = $this->single($sql);
        return $agentrate;
    }
    
    public function insert_statistics($paydata) {
        //各级渠道比率
        $each_rate_data=$this->lookupeachrate($paydata['app_id'],$paydata['agent_id']);
        //benefit_type为1为折扣
        if($each_rate_data['benefit_type']==1){
            $secbenefit = 0;
            if ($each_rate_data['sec_agent_rate']>0){
                $secbenefit = $paydata['real_amount']-$paydata['amount']*$each_rate_data['sec_agent_rate'];
            }
            if($secbenefit<0){
                $secbenefit = 0;
            }
            $this->insert_agent_order($secbenefit,$paydata['agent_id'],$each_rate_data['sec_agent_rate'],$paydata);
            $this->insert_agent_gain($secbenefit,$paydata['agent_id'],$paydata);
            $this->insert_agent_day_gain($secbenefit,$paydata['agent_id'],$paydata);
            $this->insert_agent_ext($secbenefit,$paydata['agent_id'],$paydata);
            //含有一级渠道
            if($each_rate_data['is_first'] == 1){
                $firstbenefit = 0;
                if ($each_rate_data['first_agent_rate']>0){
                    $firstbenefit = $paydata['amount']*$each_rate_data['sec_agent_rate']-$paydata['amount']*$each_rate_data['first_agent_rate'];
                }
                if($firstbenefit<0){
                    $firstbenefit = 0;
                }
                $this->update_agent_order($firstbenefit,$each_rate_data['first_agent_id'],$each_rate_data['first_agent_rate'],$paydata);
                $this->insert_agent_gain($firstbenefit,$each_rate_data['first_agent_id'],$paydata);
                $this->insert_agent_day_gain($firstbenefit,$each_rate_data['first_agent_id'],$paydata);
                $this->insert_agent_ext($firstbenefit,$each_rate_data['first_agent_id'],$paydata);
            }
        }else{
            $realgetcnt = $paydata['amount']+$paydata['rebate_cnt'];
            $berforrate = $paydata['amount']/$realgetcnt;
            //$afterrate = 1/($each_rate_data['sec_agent_rate']+1);
            $afterrate = $each_rate_data['sec_agent_rate'];
            //$secbenefit = $paydata['amount']*($berforrate - $afterrate);
            $secbenefit = $realgetcnt*($berforrate - $afterrate);
            if ($secbenefit<0){
                $secbenefit = 0;
            }
            $this->insert_agent_order($secbenefit,$paydata['agent_id'],$each_rate_data['sec_agent_rate'],$paydata);
            $this->insert_agent_gain($secbenefit,$paydata['agent_id'],$paydata);
            $this->insert_agent_day_gain($secbenefit,$paydata['agent_id'],$paydata);
            $this->insert_agent_ext($secbenefit,$paydata['agent_id'],$paydata);
            //is_first为1则含有一级渠道
            if($each_rate_data['is_first'] == 1){
                //$rebate_first_benefitrate = 1/($each_rate_data['sec_agent_rate']+1) - 1/($each_rate_data['first_agent_rate']+1);
                $rebate_first_benefitrate = $each_rate_data['sec_agent_rate'] - $each_rate_data['first_agent_rate'];
                //$firstbenefit = $paydata['amount']*$rebate_first_benefitrate;
                $firstbenefit = $realgetcnt*$rebate_first_benefitrate;
                if ($rebate_first_benefitrate < 0){
                    $firstbenefit = 0;
                }
                $this->update_agent_order($firstbenefit,$each_rate_data['first_agent_id'],$each_rate_data['first_agent_rate'],$paydata);
                $this->insert_agent_gain($firstbenefit,$each_rate_data['first_agent_id'],$paydata);
                $this->insert_agent_day_gain($firstbenefit,$each_rate_data['first_agent_id'],$paydata);
                $this->insert_agent_ext($firstbenefit,$each_rate_data['first_agent_id'],$paydata);
            }
        }
    }
    
    public function insert_agent_order($leftbenefit,$agent_id,$agentrate,$orderdata) {
        $benefit_data['order_id'] = $orderdata['order_id'];
        $benefit_data['mem_id'] = $orderdata['mem_id'];
        $benefit_data['agent_id'] = $agent_id;
        $benefit_data['app_id'] = $orderdata['app_id'];
        $benefit_data['amount'] = $orderdata['amount'];
        $benefit_data['real_amount'] = $orderdata['real_amount'];
        $benefit_data['rebate_cnt'] = $orderdata['rebate_cnt'];
        $benefit_data['agent_rate'] = $agentrate;
        $benefit_data['agent_gain'] = $leftbenefit;
        $benefit_data['from'] = $orderdata['from'];
        $benefit_data['status'] = 1;
        $benefit_data['payway'] = $orderdata['payway'];
        $benefit_data['create_time'] = time();
        $benefit_data['update_time'] = $benefit_data['create_time'];
        $benefit_data['remark'] = $orderdata['remark'];
        
        $insql = " INSERT INTO ".$this->_dbConfig['db_prefix']."agent_order";
        $insql .= " (`order_id`,`mem_id`,`agent_id`,`app_id`,`amount`,`real_amount`,`rebate_cnt`,`agent_rate`,`agent_gain`,`from`,`status`,`payway`,`create_time`,`update_time`,`remark`) ";
        $insql .= " VALUES ";
        $insql .= " (:order_id,:mem_id,:agent_id,:app_id,:amount,:real_amount,:rebate_cnt,:agent_rate,:agent_gain,:from,:status,:payway,:create_time,:update_time,:remark)";
        $rs = $this->query($insql, $benefit_data);
    }
    
    
    public function update_agent_order($leftbenefit,$agent_id,$agentrate,$orderdata) {
        $benefit_data['parent_id'] = $agent_id;
        $benefit_data['parent_rate'] = $agentrate;
        $benefit_data['parent_gain'] = $leftbenefit;
        $benefit_data['order_id'] = $orderdata['order_id'];
    
        $upsql = " UPDATE  ".$this->_dbConfig['db_prefix']."agent_order";
        $upsql .= " SET `parent_id` = :parent_id, `parent_rate` = :parent_rate, `parent_gain` = :parent_gain";
        $upsql .= " WHERE `order_id`=:order_id ";
        $rs = $this->query($upsql, $benefit_data);
    }
    
    public function insert_agent_gain($leftbenefit,$agent_id,$orderdata) {
        //判断agent_game_gain表
        $sql = "SELECT * FROM ".$this->_dbConfig['db_prefix']."agent_game_gain WHERE agent_id=:agent_id AND app_id=:app_id";
        $this->bind("agent_id", $agent_id);
        $this->bind("app_id", $orderdata['app_id']);
        $checkgamegaindata = $this->row($sql);
        if(empty($checkgamegaindata)){
            $sql = "SELECT id FROM ".$this->_dbConfig['db_prefix']."agent_game WHERE app_id=:app_id and agent_id=:agent_id";
            $this->bind("app_id", $orderdata['app_id']);
            $this->bind("agent_id", $agent_id);
            $ag_id = $this->single($sql);
            if (empty($ag_id)){
                $agentgame_data['agent_id'] = $agent_id;
                $agentgame_data['app_id'] = $orderdata['app_id'];
                $agentgame_data['create_time'] = time();
                $agentgame_data['update_time'] = time();
                $insql = " INSERT INTO ".$this->_dbConfig['db_prefix']."agent_game";
                $insql .= " (`agent_id`,`app_id`,`create_time`,`update_time`) ";
                $insql .= " VALUES ";
                $insql .= " (:agent_id,:app_id,:create_time,:update_time)";
                $rs = $this->query($insql, $agentgame_data);
                $ag_id = $this->lastInsertId();
            }
            $agent_game_gain_data['ag_id'] = $ag_id;
            $agent_game_gain_data['agent_id'] = $agent_id;
            $agent_game_gain_data['app_id'] = $orderdata['app_id'];
            $agent_game_gain_data['sum_money'] = $orderdata['amount'];
            $agent_game_gain_data['sum_real_money'] = $orderdata['real_amount'];
            $agent_game_gain_data['sum_rebate_cnt'] = $orderdata['rebate_cnt'];
            $agent_game_gain_data['sum_agent_gain'] = $leftbenefit;
            $addagentgamegainsql = " INSERT INTO ".$this->_dbConfig['db_prefix']."agent_game_gain ";
            $addagentgamegainsql .= " (`ag_id`,`agent_id`,`app_id`,`sum_money`,`sum_real_money`,`sum_rebate_cnt`,`sum_agent_gain`) ";
            $addagentgamegainsql .= " VALUES ";
            $addagentgamegainsql .= " (:ag_id,:agent_id,:app_id,:sum_money,:sum_real_money,:sum_rebate_cnt,:sum_agent_gain)";
            $rrs = $this->query($addagentgamegainsql, $agent_game_gain_data);
        }else{
            $agent_game_gain_data['sum_money'] = $checkgamegaindata['sum_money']+$orderdata['amount'];
            $agent_game_gain_data['sum_real_money'] = $checkgamegaindata['sum_real_money']+$orderdata['real_amount'];
            $agent_game_gain_data['sum_rebate_cnt'] = $checkgamegaindata['sum_rebate_cnt']+$orderdata['rebate_cnt'];
            $agent_game_gain_data['sum_agent_gain'] = $checkgamegaindata['sum_agent_gain']+$leftbenefit;
            $agent_game_gain_data['agent_id'] = $agent_id;
            $agent_game_gain_data['app_id'] = $checkgamegaindata['app_id'];
            $agentgamegainsql = "UPDATE ".$this->_dbConfig['db_prefix']."agent_game_gain ";
            $agentgamegainsql .= " SET sum_money=:sum_money, sum_real_money=:sum_real_money, sum_rebate_cnt=:sum_rebate_cnt, sum_agent_gain=:sum_agent_gain WHERE agent_id=:agent_id AND app_id=:app_id";
            $rrs = $this->query($agentgamegainsql, $agent_game_gain_data);
        }
    }
    
    public function insert_agent_day_gain($leftbenefit,$agent_id,$orderdata) {
        $date = date("Y-m-d");
        $sql = "SELECT * FROM ".$this->_dbConfig['db_prefix']."agent_day_gain WHERE date=:date and agent_id=:agent_id and app_id=:app_id";
        $this->bind("date", $date);
        $this->bind("agent_id", $agent_id);
        $this->bind("app_id", $orderdata['app_id']);
        $adgData = $this->row($sql);
        if(!empty($adgData)){
            $agent_day_gain_data['sum_money'] = $adgData['sum_money']+$orderdata['amount'];
            $agent_day_gain_data['sum_real_money'] = $adgData['sum_real_money']+$orderdata['real_amount'];
            $agent_day_gain_data['sum_rebate_cnt'] = $adgData['sum_rebate_cnt']+$orderdata['rebate_cnt'];
            $agent_day_gain_data['sum_agent_gain'] = $adgData['sum_agent_gain']+$leftbenefit;
            $agent_day_gain_data['agent_id'] = $agent_id;
            $agent_day_gain_data['app_id'] = $adgData['app_id'];
            $agent_day_gain_data['date'] = $date;
            $addagentdaygainsql = "UPDATE ".$this->_dbConfig['db_prefix']."agent_day_gain ";
            $addagentdaygainsql .= " SET sum_money=:sum_money, sum_real_money=:sum_real_money, sum_rebate_cnt=:sum_rebate_cnt, sum_agent_gain=:sum_agent_gain WHERE agent_id=:agent_id AND app_id=:app_id AND date=:date";
            $rs = $this->query($addagentdaygainsql, $agent_day_gain_data);
        }else{
            $agent_day_gain_data['date'] = $date;
            $agent_day_gain_data['agent_id'] = $agent_id;
            $agent_day_gain_data['app_id'] = $orderdata['app_id'];
            $agent_day_gain_data['sum_money'] = $orderdata['amount'];
            $agent_day_gain_data['sum_real_money'] = $orderdata['real_amount'];
            $agent_day_gain_data['sum_rebate_cnt'] = $orderdata['rebate_cnt'];
            $agent_day_gain_data['sum_agent_gain'] = $leftbenefit;
            $addagentdaygainsql = " INSERT INTO ".$this->_dbConfig['db_prefix']."agent_day_gain ";
            $addagentdaygainsql .= " (`date`,`agent_id`,`app_id`,`sum_money`,`sum_real_money`,`sum_rebate_cnt`,`sum_agent_gain`) ";
            $addagentdaygainsql .= " VALUES ";
            $addagentdaygainsql .= " (:date,:agent_id,:app_id,:sum_money,:sum_real_money,:sum_rebate_cnt,:sum_agent_gain)";

            $rs = $this->query($addagentdaygainsql, $agent_day_gain_data);
        }
    }
    
    public function insert_agent_ext($leftbenefit,$agent_id,$orderdata) {
        $sql = "SELECT * FROM ".$this->_dbConfig['db_prefix']."agent_ext WHERE agent_id=:agent_id ";
        $this->bind("agent_id", $agent_id);
        $ageData = $this->row($sql);
        if(!empty($ageData)){
            $sql = "SELECT count(id) FROM ".$this->_dbConfig['db_prefix']."pay WHERE mem_id=:mem_id ";
            $this->bind("mem_id", $orderdata['mem_id']);
            $cnt = $this->single($sql);
            $addtimes = 1;
            if($cnt>=2){
                $addtimes = 0;
            }
            $agent_ext_data['agent_id'] = $agent_id;
            $agent_ext_data['share_total'] = $ageData['share_total']+$leftbenefit;
            $agent_ext_data['share_remain'] = $ageData['share_remain']+$leftbenefit;
            $upagentextsql = "UPDATE ".$this->_dbConfig['db_prefix']."agent_ext ";
            $upagentextsql .= " SET share_total=:share_total, share_remain=:share_remain WHERE agent_id=:agent_id";
            $rs = $this->query($upagentextsql, $agent_ext_data);
        }else{
            $agent_ext_data['agent_id'] = $agent_id;
            $agent_ext_data['share_total'] = $leftbenefit;
            $agent_ext_data['share_remain'] = $leftbenefit;
            $addagentextsql = " INSERT INTO ".$this->_dbConfig['db_prefix']."agent_ext ";
            $addagentextsql .= " (`agent_id`,`share_total`,`share_remain`) ";
            $addagentextsql .= " VALUES ";
            $addagentextsql .= " (:agent_id,:share_total,:share_remain)";
            $rs = $this->query($addagentextsql, $agent_ext_data);
        }
    }
}
