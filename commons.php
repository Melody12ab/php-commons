<?php

//发送短信
function send_sms($mobile, $msg)
{

    $authKey = "XXXXXXXXXXX";
    date_default_timezone_set("Asia/Shanghai");
    $date = strftime("%Y-%m-%d %H:%M:%S");
    //Multiple mobiles numbers separated by comma
    $mobileNumber = $mobile;
    //Sender ID,While using route4 sender id should be 6 characters long.
    $senderId = "IKOONK";
    //Your message to send, Add URL encoding here.
    $message = urlencode($msg);
    //Define route
    $route = "template";
    //Prepare you post parameters
    $postData = array(
        'authkey' => $authKey,
        'mobiles' => $mobileNumber,
        'message' => $message,
        'sender' => $senderId,
        'route' => $route
    );
    //API URL
    $url = "https://control.msg91.com/sendhttp.php";
    // init the resource
    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $postData
        //,CURLOPT_FOLLOWLOCATION => true
    ));
    //Ignore SSL certificate verification
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    //get response
    $output = curl_exec($ch);
    //Print error if any
    if (curl_errno($ch)) {
        echo 'error:' . curl_error($ch);
    }
    curl_close($ch);
}

//防止sql注入
function clean($input)
{
    if (is_array($input)) {
        foreach ($input as $key => $val) {
            $output[$key] = clean($val);
            // $output[$key] = $this->clean($val);
        }
    } else {
        $output = (string)$input;
        // if magic quotes is on then use strip slashes
        if (get_magic_quotes_gpc()) {
            $output = stripslashes($output);
        }
        // $output = strip_tags($output);
        $output = htmlentities($output, ENT_QUOTES, 'UTF-8');
    }
    // return the clean text
    return $output;
}

//获取web页面的源码
function display_sourcecode($url)
{
    return file_get_html($url);
}

//获取图片的主导色
function dominant_color($image)
{
    $total = 0;
    $rTotal = 0;
    $gTotal = 0;
    $bTotal = 0;
    $i = imagecreatefromjpeg($image);
    for ($x = 0; $x < imagesx($i); $x++) {
        for ($y = 0; $y < imagesy($i); $y++) {
            $rgb = imagecolorat($i, $x, $y);
            $r = ($rgb >> 16) & 0xFF;
            $g = ($rgb >> 8) & 0xFF;
            $b = $rgb & 0xFF;
            $rTotal += $r;
            $gTotal += $g;
            $bTotal += $b;
            $total++;
        }
    }
    $rAverage = round($rTotal / $total);
    $gAverage = round($gTotal / $total);
    $bAverage = round($bTotal / $total);
}

//验证邮箱地址
function is_validemail($email)
{
    $check = 0;
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $check = 1;
    }
    return $check;
}

//获取用户IP
function getRealIpAddr()
{
    if (!emptyempty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!emptyempty($_SERVER['HTTP_X_FORWARDED_FOR'])) //to check ip is pass from proxy
    {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

//IP黑名单
function checkIp($ip)
{
    if (!file_exists('blocked_ips.txt')) {
        $deny_ips = array(
            '127.0.0.1',
            '192.168.1.1',
            '83.76.27.9',
            '192.168.1.163'
        );
    } else {
        $deny_ips = file('blocked_ips.txt');
    }
// read user ip adress:
    $ip = isset($_SERVER['REMOTE_ADDR']) ? trim($_SERVER['REMOTE_ADDR']) : '';
// search current IP in $deny_ips array
    if ((array_search($ip, $deny_ips)) !== FALSE) {
        // address is blocked:
        echo 'Your IP adress (' . $ip . ') was blocked!';
        exit;
    }
}

//下载文件
function force_download($file)
{
    $dir = "../log/exports/";
    if ((isset($file)) && (file_exists($dir . $file))) {
        header("Content-type: application/force-download");
        header('Content-Disposition: inline; filename="' . $dir . $file . '"');
        header("Content-Transfer-Encoding: Binary");
        header("Content-length: " . filesize($dir . $file));
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $file . '"');
        readfile("$dir$file");
    } else {
        echo "No file selected";
    }
}

//创建zip文件
function create_zip($files = array(), $destination = '', $overwrite = false)
{
    //if the zip file already exists and overwrite is false, return false
    if (file_exists($destination) && !$overwrite) {
        return false;
    }
    //vars
    $valid_files = array();
    //if files were passed in...
    if (is_array($files)) {
        //cycle through each file
        foreach ($files as $file) {
            //make sure the file exists
            if (file_exists($file)) {
                $valid_files[] = $file;
            }
        }
    }
    //if we have good files...
    if (count($valid_files)) {
        //create the archive
        $zip = new ZipArchive();
        if ($zip->open($destination, $overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true) {
            return false;
        }
        //add the files
        foreach ($valid_files as $file) {
            $zip->addFile($file, $file);
        }
        //debug
        //echo 'The zip archive contains ',$zip->numFiles,' files with a status of ',$zip->status;

        //close the zip -- done!
        $zip->close();

        //check to make sure the file exists
        return file_exists($destination);
    } else {
        return false;
    }
}

//ps code
$files = array('file1.jpg', 'file2.jpg', 'file3.gif');
create_zip($files, 'myzipfile.zip', true);
unzip('test.zip', 'unziped/test');
//解压文件
function unzip($location, $newLocation)
{
    if (exec("unzip $location", $arr)) {
        mkdir($newLocation);
        for ($i = 1; $i < count($arr);
             $i++) {
            $file = trim(preg_replace("~inflating: ~", "", $arr[$i]));
            copy($location . '/' . $file, $newLocation . '/' . $file);
            unlink($location . '/' . $file);
        }
        return TRUE;
    } else {
        return FALSE;
    }
}

//压缩图片
function resize_image($filename, $tmpname, $xmax, $ymax)
{
    $ext = explode(".", $filename);
    $ext = $ext[count($ext) - 1];

    if ($ext == "jpg" || $ext == "jpeg")
        $im = imagecreatefromjpeg($tmpname);
    elseif ($ext == "png")
        $im = imagecreatefrompng($tmpname);
    elseif ($ext == "gif")
        $im = imagecreatefromgif($tmpname);

    $x = imagesx($im);
    $y = imagesy($im);

    if ($x <= $xmax && $y <= $ymax)
        return $im;

    if ($x >= $y) {
        $newx = $xmax;
        $newy = $newx * $y / $x;
    } else {
        $newy = $ymax;
        $newx = $x / $y * $newy;
    }

    $im2 = imagecreatetruecolor($newx, $newy);
    imagecopyresized($im2, $im, 0, 0, 0, 0, floor($newx), floor($newy), $x, $y);
    return $im2;
}

//使用mail发送邮件
function send_mail($to, $subject, $body)
{
    $headers = "From: KOONK\r\n";
    $headers .= "Reply-To: blog@koonk.com\r\n";
    $headers .= "Return-Path: blog@koonk.com\r\n";
    $headers .= "X-Mailer: PHP5\n";
    $headers .= 'MIME-Version: 1.0' . "\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    mail($to, $subject, $body, $headers);
}

//将秒转为天，小时，分钟
function secsToStr($secs)
{
    if ($secs >= 86400) {
        $days = floor($secs / 86400);
        $secs = $secs % 86400;
        $r = $days . ' day';
        if ($days <> 1) {
            $r .= 's';
        }
        if ($secs > 0) {
            $r .= ', ';
        }
    }
    if ($secs >= 3600) {
        $hours = floor($secs / 3600);
        $secs = $secs % 3600;
        $r .= $hours . ' hour';
        if ($hours <> 1) {
            $r .= 's';
        }
        if ($secs > 0) {
            $r .= ', ';
        }
    }
    if ($secs >= 60) {
        $minutes = floor($secs / 60);
        $secs = $secs % 60;
        $r .= $minutes . ' minute';
        if ($minutes <> 1) {
            $r .= 's';
        }
        if ($secs > 0) {
            $r .= ', ';
        }
    }
    $r .= $secs . ' second';
    if ($secs <> 1) {
        $r .= 's';
    }
    return $r;
}

//列出目录下文件
function list_files($dir)
{
    if (is_dir($dir)) {
        if ($handle = opendir($dir)) {
            while (($file = readdir($handle)) !== false) {
                if ($file != "." && $file != ".." && $file != "Thumbs.db"/*pesky windows, images..*/) {
                    echo '<a target="_blank" href="' . $dir . $file . '">' . $file . '</a>' . "\n";
                }
            }
            closedir($handle);
        }
    }
}

//检查用户使用的语言
function get_client_language($availableLanguages, $default = 'en')
{
    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $langs = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
        foreach ($langs as $value) {
            $choice = substr($value, 0, 2);
            if (in_array($choice, $availableLanguages)) {
                return $choice;
            }
        }
    }
    return $default;
}

//查看csv文件
function readCSV($csvFile)
{
    $file_handle = fopen($csvFile, 'r');
    while (!feof($file_handle)) {
        $line_of_text[] = fgetcsv($file_handle, 1024);
    }
    fclose($file_handle);
    return $line_of_text;
}

//创建csv文件
function generateCsv($data, $delimiter = ',', $enclosure = '"')
{
    $contents = "";
    $handle = fopen('php://temp', 'r+');
    foreach ($data as $line) {
        fputcsv($handle, $line, $delimiter, $enclosure);
    }
    rewind($handle);
    while (!feof($handle)) {
        $contents .= fread($handle, 8192);
    }
    fclose($handle);
    return $contents;
}

//登录跳转
function current_url()
{
    $url = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $validURL = str_replace("&", "&", $url);
    return validURL;
}

//递归删除内容
function Delete($path)
{
    if (is_dir($path) === true) {
        $files = array_diff(scandir($path), array('.', '..'));
        foreach ($files as $file) {
            Delete(realpath($path) . '/' . $file);
        }
        return rmdir($path);
    } else if (is_file($path) === true) {
        return unlink($path);
    }
    return false;
}

//计算日期差
//todo


//高亮搜索字符串
function highlighter_text($text, $words)
{
    $split_words = explode(" ", $words);
    foreach ($split_words as $word) {
        $color = "#4285F4";
        $text = preg_replace("|($word)|Ui",
            "<span style=\"color:" . $color . ";\"><b>$1</b></span>", $text);
    }
    return $text;
}

//根据url下载图片
function imagefromURL($image, $rename)
{
    $ch = curl_init($image);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
    $rawdata = curl_exec($ch);
    curl_close($ch);
    $fp = fopen("$rename", 'w');
    fwrite($fp, $rawdata);
    fclose($fp);
}

//检测url是否有效
function isvalidURL($url)
{
    $check = 0;
    if (filter_var($url, FILTER_VALIDATE_URL) !== false) {
        $check = 1;
    }
    return $check;
}

//生成二维码
//todo

//计算地图坐标之间距离
function getDistanceBetweenPointsNew($latitude1, $longitude1, $latitude2, $longitude2)
{
    $theta = $longitude1 - $longitude2;
    $miles = (sin(deg2rad($latitude1)) * sin(deg2rad($latitude2))) + (cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * cos(deg2rad($theta)));
    $miles = acos($miles);
    $miles = rad2deg($miles);
    $miles = $miles * 60 * 1.1515;
    $feet = $miles * 5280;
    $yards = $feet / 3;
    $kilometers = $miles * 1.609344;
    $meters = $kilometers * 1000;
    return compact('miles', 'feet', 'yards', 'kilometers', 'meters');
}

//添加 th,st,nd 或者 rd 作为数字的后缀
function ordinal($cdnl)
{
    $test_c = abs($cdnl) % 10;
    $ext = ((abs($cdnl) % 100 <
        21 && abs($cdnl) % 100 >
        4) ? 'th'
        : (($test_c < 4) ? ($test_c < 3) ? ($test_c < 2) ? ($test_c < 1)
            ? 'th' : 'st' : 'nd' : 'rd' : 'th'));
    return $cdnl . $ext;
}

//将文件转化为图片
//todo
function file2img()
{
    header("Content-type: image/png");
    $string = $_GET['text'];
    $im = imagecreatefrompng("images/button.png");
    $color = imagecolorallocate($im, 255, 255, 255);
    $px = (imagesx($im) - 7.5 * strlen($string)) / 2;
    $py = 9;
    $fontSize = 1;
    imagestring($im, fontSize, $px, $py, $string, $color);
    imagepng($im);
    imagedestroy($im);
}

//将pdf转为图像  安装convert
function pdf2img()
{
    $pdf_file = './pdf/demo.pdf';
    $save_to = './jpg/demo.jpg';     //make sure that apache has permissions to write in this folder! (common problem)
//execute ImageMagick command 'convert' and convert PDF to JPG with applied settings
    exec('convert "' . $pdf_file . '" -colorspace RGB -resize 800 "' . $save_to . '"', $output, $return_var);
    if ($return_var == 0) {              //if exec successfuly converted pdf to jpg
        print "Conversion OK";
    } else print "Conversion failed." . $output;
}

//使用tinyurl生成短连接
function get_tiny_url($url)
{
    $ch = curl_init();
    $timeout = 5;
    curl_setopt($ch,CURLOPT_URL,'http://tinyurl.com/api-create.php?url='.$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}
//生成随机颜色
function randomColor() {
    $str = '#';
    for($i = 0 ; $i < 6 ; $i++) {
        $randNum = rand(0 , 15);
        switch ($randNum) {
            case 10: $randNum = 'A'; break;
            case 11: $randNum = 'B'; break;
            case 12: $randNum = 'C'; break;
            case 13: $randNum = 'D'; break;
            case 14: $randNum = 'E'; break;
            case 15: $randNum = 'F'; break;
        }
        $str .= $randNum;
    }
    return $str;
}
