<?php
 
header("Content-type: text/html; charset=utf-8");
 
require_once 'db.class.php.php';//数据库
error_reporting(0);//屏蔽错误警告
ignore_user_abort(); //忽略关闭浏览器
set_time_limit(0); //永远执行
 
 
//设置网络请求配置
function curl_request($curl,$https=true,$method='GET',$data=null,$aHeader=array()){
    // 创建一个新cURL资源
    $ch = curl_init();
 
    // 设置URL和相应的选项
    curl_setopt($ch, CURLOPT_URL, $curl);    //要访问的网站
    curl_setopt($ch, CURLOPT_HEADER, false);    //启用时会将头文件的信息作为数据流输出。
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  //将curl_exec()获取的信息以字符串返回，而不是直接输出。
 
    if( count($aHeader) >= 1 ){
        curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);
    }
 
    if($https){
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  //FALSE 禁止 cURL 验证对等证书（peer's certificate）。
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);  //验证主机
    }
    if(strtoupper($method) == 'POST'){
        curl_setopt($ch, CURLOPT_POST, true);  //发送 POST 请求
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);  //全部数据使用HTTP协议中的 "POST" 操作来发送。
    }
 
 
    // 抓取URL并把它传递给浏览器
    $content = curl_exec($ch);
    //关闭cURL资源，并且释放系统资源
    if($content){
        curl_close($ch);
        return $content;
    }else {
        $error = curl_errno($ch);
        echo "call faild, errorCode:$error\n";
        curl_close($ch);
        return false;
    }
 
}
 
/**
 * 数据输出返回
 * @param null $data
 * @param int $errCode
 * author 江南极客
 */
function msgReturn($data=null,$errCode=0){
    $returnArr = array(
        'error_no'=>$errCode,
    );
    if($errCode===0 || is_array($data)){
        $returnArr['data'] = $data;
    }else{
        $returnArr['error_msg'] = $data;
    }
    die(json_encode($returnArr));
}
 
/**
 * 车站的电码信息编号
 * 数据格式:@bjb|北京北|VAP|beijingbei|bjb|0
 * 车站名缩写:bjb
 * 车站名称:北京北
 * 车站电码编号:VAP
 * 车站数字编号:0
 * author 江南极客
 */
function get_station_code(){
    $url = 'https://kyfw.12306.cn/otn/resources/js/framework/station_name.js';
    $data = curl_request($url);
    if(!$data){
        msgReturn('获取数据失败',-1);
    }
    //var station_names ='@bjb|北京北|VAP|beijingbei|bjb|0...';
    $station_data = strrchr($data,'=');
    $station_name = ltrim($station_data,"='@");
    $station_name = rtrim($station_name,"';");
    $station_arr = explode('@',$station_name);
    if(empty($station_arr)){
        msgReturn('获取数据失败',-1);
    }
 
    return $station_arr;
}
 
/**
 * 导入车站信息(至数据库)
 * author 江南极客
 * @return array
 */
function import_train_station(){
    $station_arr = get_station_code();
    $station_data = array();
    $time = date('Y-m-d H:i:s');
    $month = date('n');
 
    $db_config = array(
        'hostname'  => '127.0.0.1',
        'username'  => 'root',
        'password'  => 'root',
        'database'  => 'dmx_train',
        'pconnect'  => 0,
        'log'  => 0
    );
    $db = new DB($db_config);
    $update = false; //是否要更新数据
    //数据一个月更新一次
    $one_sql = 'select * from dmx_station_info where update_month='.$month;
    $curr_month_data = $db->get_one($one_sql);
    if(empty($curr_month_data)){
        //当前月份没有数据,则截断表然后插入数据
        $update = true;
        $truncate_sql = 'TRUNCATE dmx_station_info';
        $db->query($truncate_sql);
    }
    foreach ($station_arr as $item){
        $temp = explode('|',$item);
        $temp_arr = array(
            'station_no' => $temp[5],
            'station_abbr' => $temp[0],
            'station_name' => $temp[1],
            'station_telecode' => $temp[2],
            'ch_pinyin' => $temp[3],
            'simp_pinyin' => $temp[4],
            'origin_info' => $item,
            'update_time' => $time,
            'update_month' => $month,
        );
        $station_data[] = $temp_arr;
        if($update){
            $db->insert('dmx_station_info',$temp_arr);
        }
    }
    return $station_data;
}
 
/**
 * 通过12306月排班表拿到所有的车次信息
 * 铁道部 每日排班车次1万多条数据 , 每月30多万 , 一次取出来数据量非常庞大,可以取出来然后做缓存一个月更新一次
 * author 江南极客
 * @return mixed
 */
function get_train_list(){
    $cache_file = __DIR__.'/train_list.json';
    CHECKFILE:
    if(file_exists($cache_file)){
        $cache_time = filemtime($cache_file);
        $month = date('n');
        //一个月缓存一次
        if(date('n',$cache_time) != $month){
            @unlink($cache_file);
            goto CHECKFILE;
        }
        $train_list = file_get_contents($cache_file);
    }else{
        $url = 'https://kyfw.12306.cn/otn/resources/js/query/train_list.js?scriptVersion=1.0';
        $data = curl_request($url);
        $train_data = strrchr($data,'=');
        $train_list = ltrim($train_data,"=");
        $train_list = rtrim($train_list,",");
        @file_put_contents($cache_file,$train_list);
    }
    $train_arr = json_decode($train_list,true);
    return $train_arr;
}
 
/**
 * 导入车次信息(至数据库)
 * author 江南极客
 */
function import_train_list(){
    //数据库配置
    $db_config = array(
        'hostname'  => '127.0.0.1',
        'username'  => 'root',
        'password'  => 'root',
        'database'  => 'dmx_train',
        'pconnect'  => 0,
        'log'  => 0
    );
    $db = new DB($db_config);
 
    //获取一个月内列车排班信息列表
    $station_arr = get_train_list();
    $station_arr_count = count($station_arr);
 
    //一次取出来 一个月的信息 一次只操作一天的数据
    $temp_times = 'youqijun';
    session_start() ;
    if(isset($_SESSION[$temp_times])){
        $temp = $_SESSION[$temp_times];
        $temp += 1;
        $_SESSION[$temp_times] = $temp;
    }else{
        $temp = 0;
        $_SESSION[$temp_times] = $temp;
    }
    if($temp >= $station_arr_count){
        msgReturn('导入完毕',-1);
    }
    //截取数组的一部分(一次取一天的数据)
    $station_arr = array_slice($station_arr,$temp,1);
    if(empty($station_arr)){
        msgReturn('暂无数据',-1);
    }
 
    $temp_arr = array();
    $pattern = "/^(.*?)\((.*?)\-(.*?)\)$/";
    $month = date('n');
    foreach ($station_arr as $key1 => $date_arr){
        $time = $key1;
        foreach ($date_arr as $key2 => $train_arr){
            $type = $key2;
            foreach ($train_arr as $train){
                $train['train_date'] = $time.' 00:00:00';
                $train['train_type'] = $type;
                $train['update_month'] = $month;
                //K580(长沙-成都东)  匹配出车次 和 始发站,终到站
                preg_match_all($pattern, $train['station_train_code'], $match);
                //$train_sn = strstr($train['station_train_code'],'(',true);
                $train['train_sn'] = $match[1][0];
                $train['from_station'] = $match[2][0];
                $train['to_station'] = $match[3][0];
                $train['between_station'] = $match[2][0].'-'.$match[3][0];
                $temp_arr[] = $train;
                $db->insert('dmx_train_list',$train);
            }
        }
    }
    print_r($temp_arr);
}
 
/**
 * 获取列车时刻表
 * @param string $train_sn 车次(如:G1207)
 * @param string $date 日期
 * train_no: 车次编号,从步骤1中的数据获取
 * from_station_telecode: 起始站点的电码编号
 * to_station_telecode: 目的站点的电码编号
 * depart_date: 查询日期
 * author 江南极客
 * @return mixed
 */
function get_train_timetable($train_sn='',$date=''){
    $url = 'https://kyfw.12306.cn/otn/czxx/queryByTrainNo?';
    if(empty($train_sn)){
        msgReturn('请填写车次',-1);
    }
    if(empty($date)) {
        $date = date('Y-m-d');
    }else{
        $date_str = strtotime($date);
        $date = date('Y-m-d',$date_str);
    }
    $db_config = array(
        'hostname'  => '127.0.0.1',
        'username'  => 'root',
        'password'  => 'root',
        'database'  => 'dmx_train',
        'pconnect'  => 0,
        'log'  => 0
    );
    $db = new DB($db_config);
    $train_date = $date.' 00:00:00';
    $train_sn = strtoupper($train_sn);
    //根据输入的车次 拿到 车次排班编号,始发站和终到站
    $train_sql = "select * from dmx_train_list where train_sn='{$train_sn}' and train_date='{$train_date}'";
    $train = $db->get_one($train_sql);
    if(empty($train)){
        msgReturn('未查到当日当次列车信息',-1);
    }
    //取出始发站和终到站的电码编号
    $from_station_name = $train['from_station'];
    $to_station_name = $train['to_station'];
    $from_station_sql = "select * from dmx_station_info where station_name='{$from_station_name}'";
    $to_station_sql = "select * from dmx_station_info where station_name='{$to_station_name}'";
    $from_station = $db->get_one($from_station_sql);
    if(empty($from_station)){
        msgReturn('未查到始发车站',-1);
    }
    $to_station = $db->get_one($to_station_sql);
    if(empty($to_station)){
        msgReturn('未查到终点车站',-1);
    }
    $param = [
        'train_no' => $train['train_no'],
        'from_station_telecode' => $from_station['station_telecode'],
        'to_station_telecode' => $to_station['station_telecode'],
        'depart_date' => $date,
    ];
    $http_param = http_build_query($param);
    $url = $url.$http_param;
    $header = array('User-Agent:Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.132 Safari/537.36');
    $data = curl_request($url,true,'GET',null,$header);
    if(!$data){
        msgReturn('网络错误',-1);
    }
    $data = json_decode($data,true);
    if(empty($data['data']['data'])){
        msgReturn('列车时刻表数据不存在',-1);
    }
    return $data['data']['data'];
}
 
 
/*$checi = 'g1207';
$data = get_train_timetable($checi,'2019-5-18');
print_r($data);die;*/
 
$now = date('Y-m-d');
 
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $train_no = trim($_POST['value1']);
    $train_date = trim($_POST['value2']);
    $data = get_train_timetable($train_no,$train_date);
    msgReturn($data);
}
 
 
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="列车时刻表">
    <title>列车时刻表-高铁动车运行时间查询</title>
    <script src="https://code.jquery.com/jquery-3.0.0.min.js"></script>
    <style>
        /* 全局样式 */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Microsoft YaHei', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f7fa;
        }

        /* 导航栏 */
        .navbar {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 1rem 5%;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logo i {
            color: #ffd700;
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 2rem;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .nav-links a:hover {
            color: #ffd700;
        }

        .nav-links a.active {
            color: #ffd700;
            font-weight: bold;
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .search-box {
            position: relative;
        }

        .search-box input {
            padding: 0.5rem 1rem 0.5rem 2.5rem;
            border: none;
            border-radius: 20px;
            font-size: 0.9rem;
        }

        .search-box i {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }

        .login-btn {
            background-color: transparent;
            color: white;
            border: 2px solid white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .login-btn:hover {
            background-color: white;
            color: #2a5298;
        }

        /* 英雄区域 */
        .hero {
            background: linear-gradient(rgba(30, 60, 114, 0.8), rgba(42, 82, 152, 0.8)), 
                        url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 300"><rect fill="%231e3c72" width="1000" height="300"/><path d="M0,250 Q250,200 500,250 T1000,250 L1000,300 L0,300 Z" fill="%232a5298"/><path d="M0,220 Q250,180 500,220 T1000,220 L1000,250 L0,250 Z" fill="%233273dc"/><path d="M0,200 L50,180 L100,200 L150,180 L200,200 L250,180 L300,200 L350,180 L400,200 L450,180 L500,200 L550,180 L600,200 L650,180 L700,200 L750,180 L800,200 L850,180 L900,200 L950,180 L1000,200 L1000,220 L0,220 Z" fill="%2341b883"/></svg>');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 8rem 5% 4rem;
            text-align: center;
            margin-top: 70px;
        }

        .hero-content {
            max-width: 800px;
            margin: 0 auto;
        }

        .hero h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .hero p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        /* 查询区域 */
        .search-section {
            padding: 4rem 5%;
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-title {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 3rem;
            color: #1e3c72;
        }

        .search-container {
            background-color: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            max-width: 600px;
            margin: 0 auto;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
            color: #1e3c72;
        }

        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }

        .cta-button {
            background: linear-gradient(135deg, #ffd700, #ffed4e);
            color: #333;
            padding: 1rem 2rem;
            border: none;
            border-radius: 30px;
            font-size: 1.1rem;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 215, 0, 0.4);
            cursor: pointer;
            width: 100%;
        }

        .cta-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(255, 215, 0, 0.6);
        }

        /* 结果区域 */
        .results-section {
            padding: 0 5% 4rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .results-container {
            background-color: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            margin-top: 2rem;
            display: none;
        }

        .result-info {
            margin-bottom: 1.5rem;
            padding: 1rem;
            background-color: #f0f7ff;
            border-left: 4px solid #2a5298;
            border-radius: 5px;
        }

        .result-info h3 {
            color: #1e3c72;
            margin-bottom: 0.5rem;
        }

        .train-table {
            width: 100%;
            border-collapse: collapse;
        }

        .train-table th,
        .train-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .train-table th {
            background-color: #f5f7fa;
            color: #1e3c72;
            font-weight: bold;
        }

        .train-table tr:hover {
            background-color: #f0f7ff;
        }

        /* 页脚 */
        footer {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 3rem 5% 1rem;
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }

        .footer-about h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .footer-links h4 {
            margin-bottom: 1rem;
            color: #ffd700;
        }

        .footer-links ul {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 0.5rem;
        }

        .footer-links a {
            color: white;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-links a:hover {
            color: #ffd700;
        }

        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .social-link {
            width: 40px;
            height: 40px;
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: white;
            transition: all 0.3s ease;
        }

        .social-link:hover {
            background-color: #ffd700;
            color: #1e3c72;
            transform: translateY(-3px);
        }

        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            margin-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
        }

        /* 响应式设计 */
        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }

            .hero h1 {
                font-size: 2rem;
            }

            .hero p {
                font-size: 1rem;
            }

            .section-title {
                font-size: 2rem;
            }

            .footer-container {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .social-links {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- 导航栏 -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">
                <i class="fas fa-train"></i>
                天天动车组社区
            </div>
            <ul class="nav-links">
                <li><a href="index.html">首页</a></li>
                <li><a href="#">讨论区</a></li>
                <li><a href="train-time.php" class="active">列车时刻</a></li>
                <li><a href="canshu.html">参数配置</a></li>
                <li><a href="#">技术交流</a></li>
                <li><a href="#">活动专区</a></li>
                <li><a href="#">关于我们</a></li>
            </ul>
            <div class="nav-actions">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="搜索内容...">
                </div>
                <button class="login-btn">登录/注册</button>
            </div>
        </div>
    </nav>

    <!-- 英雄区域 -->
    <section class="hero">
        <div class="hero-content">
            <h1>列车时刻表查询</h1>
            <p>实时查询全国列车时刻表信息，让您的出行更加便捷</p>
        </div>
    </section>

    <!-- 查询区域 -->
    <section class="search-section">
        <h2 class="section-title">查询列车时刻</h2>
        <div class="search-container">
            <form method="POST" action="" id="train-form">
                <div class="form-group">
                    <label for="train-number">列车车次</label>
                    <input type="text" id="train-number" name="value1" placeholder="例如：G1" required>
                </div>
                <div class="form-group">
                    <label for="departure-date">发车日期</label>
                    <input type="date" id="departure-date" name="value2" value="<?php echo $now; ?>" required>
                </div>
                <button type="submit" class="cta-button">查询</button>
            </form>
        </div>
    </section>

    <!-- 结果区域 -->
    <section class="results-section" id="results-section">
        <div class="results-container" id="results-container">
            <div class="result-info" id="result-info">
                <h3>查询结果</h3>
                <p id="train-info">...</p>
            </div>
            <table class="train-table" id="train-table">
                <thead>
                    <tr>
                        <th>序号</th>
                        <th>车站</th>
                        <th>到达时间</th>
                        <th>发车时间</th>
                        <th>停留时间</th>
                    </tr>
                </thead>
                <tbody id="table-body">
                    <!-- 结果将通过JavaScript动态插入 -->
                </tbody>
            </table>
        </div>
    </section>

    <!-- 页脚 -->
    <footer>
        <div class="footer-container">
            <div class="footer-about">
                <h3>
                    <i class="fas fa-train"></i>
                    天天动车组社区
                </h3>
                <p>中国最大的铁路爱好者交流平台，致力于分享铁路资讯、列车体验和技术知识，连接全国的铁路迷朋友。</p>
                <div class="social-links">
                    <a href="#" class="social-link"><i class="fab fa-weibo"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-weixin"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-qq"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            <div class="footer-links">
                <h4>快速链接</h4>
                <ul>
                    <li><a href="#">首页</a></li>
                    <li><a href="#">讨论区</a></li>
                    <li><a href="#">列车时刻</a></li>
                    <li><a href="#">图片分享</a></li>
                    <li><a href="#">技术交流</a></li>
                </ul>
            </div>
            <div class="footer-links">
                <h4>关于我们</h4>
                <ul>
                    <li><a href="#">社区简介</a></li>
                    <li><a href="#">用户协议</a></li>
                    <li><a href="#">隐私政策</a></li>
                    <li><a href="#">联系我们</a></li>
                    <li><a href="#">加入我们</a></li>
                </ul>
            </div>
            <div class="footer-links">
                <h4>技术支持</h4>
                <ul>
                    <li><a href="#">常见问题</a></li>
                    <li><a href="#">帮助中心</a></li>
                    <li><a href="#">反馈建议</a></li>
                    <li><a href="#">技术文档</a></li>
                    <li><a href="#">API接口</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 天天动车组社区. 保留所有权利.</p>
        </div>
    </footer>

    <!-- Font Awesome 图标 -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

    <script>
        $(function(){
            $('#train-form').submit(function(e){
                e.preventDefault();
                
                var data = {
                    value1: $('#train-number').val(),
                    value2: $('#departure-date').val()
                };
                
                // 显示加载状态
                $('#result-info').css('color', '#666');
                $('#train-info').text('正在查询...');
                $('#results-section').show();
                $('#results-container').show();
                
                $.ajax({
                    url: 'train-time.php',
                    data: data,
                    dataType: 'json',
                    type: 'post',
                    success: function(ret){
                        if(ret.error_no == 0){
                            // 构建结果信息
                            var train_info = ret.data[0];
                            var train_text = train_info.station_train_code + ' ( ' + train_info.start_station_name + ' - ' + train_info.end_station_name + ' )';
                            
                            // 构建表格内容
                            var table_html = '';
                            $.each(ret.data, function(key, val){
                                table_html += '<tr>';
                                table_html += '<td>' + val.station_no + '</td>';
                                table_html += '<td>' + val.station_name + '</td>';
                                table_html += '<td>' + val.arrive_time + '</td>';
                                table_html += '<td>' + val.start_time + '</td>';
                                table_html += '<td>' + val.stopover_time + '</td>';
                                table_html += '</tr>';
                            });
                            
                            // 更新页面内容
                            $('#train-info').text(train_text);
                            $('#table-body').html(table_html);
                            $('#result-info').css('color', '#1e3c72');
                        } else {
                            // 显示错误信息
                            $('#train-info').text('错误：' + ret.error_msg);
                            $('#result-info').css('color', '#e74c3c');
                            $('#train-table').hide();
                        }
                    },
                    error: function(err){
                        // 显示错误信息
                        $('#train-info').text('查询失败，请稍后重试');
                        $('#result-info').css('color', '#e74c3c');
                        $('#train-table').hide();
                    }
                });
            });
        });
    </script>
</body>
</html>