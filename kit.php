<?php
namespace phpkit;

require_once 'tools/phpqrcode.php';
use phpkit\tools\QRcode;
use phpkit\tools\ip2Location;


class kit
{
    const __version__ = '0.0.5';

    public function hello()
    {
        return 'world';
    }

    /**
     * 获取客户端IP地址
     * @param integer $type 返回类型 0 返回IP地址 1 返回IPv4地址数字
     * @param boolean $adv 是否进行高级模式获取（有可能被伪装）
     * @return mixed
     */
    public static function get_client_ip($type=0,$adv=true){
        $type       =  $type ? 1 : 0;
        static $ip  =   NULL;
        if ($ip !== NULL) return $ip[$type];
        if($adv){
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                $pos    =   array_search('unknown',$arr);
                if(false !== $pos) unset($arr[$pos]);
                $ip     =   trim($arr[0]);
            }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $ip     =   $_SERVER['HTTP_CLIENT_IP'];
            }elseif (isset($_SERVER['REMOTE_ADDR'])) {
                $ip     =   $_SERVER['REMOTE_ADDR'];
            }
        }elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip     =   $_SERVER['REMOTE_ADDR'];
        }
        // IP地址合法验证
        $long = sprintf("%u",ip2long($ip));
        $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
        return $ip[$type];
    }


    public static function get_ip_location($ip=null){
        //首先判断是否局域网IP
        $ip = ip2long($ip);
        $net_a = ip2long('10.255.255.255') >> 24; //A类网预留ip的网络地址
        $net_b = ip2long('172.31.255.255') >> 20; //B类网预留ip的网络地址
        $net_c = ip2long('192.168.255.255') >> 16; //C类网预留ip的网络地址
        if ($ip >> 24 === $net_a || $ip >> 20 === $net_b || $ip >> 16 === $net_c) {
            return '本地局域网';
        }
        $resv = new ip2Location('qqwry.dat');
        $data = $resv->getlocation($ip);
        if ($data) {
            $city = $data['country'];
            $isp = $data['area'];
            $ret = iconv('gbk','utf-8',"$city [$isp]"); //转码，非常重要，如果网站和DB都是GBK编码就不用转换
        } else {
            $ret = null;
        }
        return $ret;
    }


    /*
     * 格式化日期（Yii2自带的不是那么好用）
     * */
    public static function date($format,$timestamp=null){
        if ($timestamp>0){
            $ret = date($format,$timestamp);
        } else if ($timestamp=="now"){
            $ret = date($format);
        } else {
            $ret = '';
        }
        return $ret;
    }


    /**
     * 生成随机字符串
     */
    public static function gen_random_str($len=8){
        $chars = array(
            "a", "b", "c", "d", "e", "f", "g",
            "h", "i", "j", "k", "l", "m", "n",
            "o", "p", "q", "r", "s", "t",
            "u", "v", "w", "x", "y", "z",
            "A", "B", "C", "D", "E", "F", "G",
            "H", "I", "J", "K", "L", "M", "N",
            "O", "P", "Q", "R", "S", "T",
            "U", "V", "W", "X", "Y", "Z",
            "0", "1", "2", "3", "4", "5", "6", "7", "8", "9"
        );

        $max = count($chars);
        shuffle($chars);    // 将数组打乱

        $output = "";
        for ($i=0; $i<$len; $i++)
        {
            $output .= $chars[mt_rand(0, $max-1)];
        }

        return $output;
    }


    /**
     * 生成不带横杠的UUID
     * @return string
     */
    public static function gen_uuid()
    {
        return sprintf('%04x%04x%04x%04x%04x%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),

            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,

            // 48 bits for "node"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }


    /*
     * 检测手机号
     * */
    public static function checkPhone($phone){
        if (!preg_match("/^1[345678]\d{9}$/", $phone)){
            return ['code'=>-1,'msg'=>'手机号格式不正确！'];
        }
        //特殊手机号
        $special = '/6666|8888|9999|1234|0000/';
        if (preg_match($special, $phone)){
            return ['code'=>-2,'msg'=>'手机号格式不正确！'];
        }
        return ['code'=>0];
    }


    /*
     * 生成二维码
     * */
    public static function qrcode($text='Hello,world',$size=10,$margin=1,$ecc='L',$filename=null)
    {
        /*
         * Writed By Xiaok
         * 2015-07-05 21:47:18
         *
         * $test 数据，如果是存储utf-8编码的中文，最多984个
         * $filename 保存的图片名称
         * $errorCorrectionLevel 错误处理级别，即ECC
         * $matrixPointSize 每个黑点的像素，这里用size代替
         * $margin 图片外围的白色边框像素
         *
         * ECC表示纠错级别，纠错级别越高，生成图片会越大
         * L水平     7%的字码可被修正
         * M水平    15%的字码可被修正
         * Q水平    25%的字码可被修正
         * H水平    30%的字码可被修正
         *
         */

        QRcode::png($text, $filename, $ecc, $size, $margin);
    }


    /*
     * 商品价格判断：正整数或保留两位小数
     * */
    public static function validatePrice($price)
    {
        return preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $price) ? true : false;
    }

}
