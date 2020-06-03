<?php
namespace phpkit\tools;


class http
{

    //默认UA
    const DEFAULT_USER_AGENT = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36';

    //public function __construct(){}

    //Get请求
    public static function get($url,$data=null){
        $c = curl_init();
        if ($data) { //Get参数传递
            $arguments = http_build_query($data);
            $url .= '?' . $arguments;
        }
        curl_setopt($c, CURLOPT_URL, $url);
        curl_setopt($c, CURLOPT_USERAGENT,self::DEFAULT_USER_AGENT);
        curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);//绕过ssl验证
        curl_setopt($c, CURLOPT_SSL_VERIFYHOST, false);
        //执行命令
        $response = curl_exec($c);
        //关闭URL请求
        curl_close($c);
        //显示获得的数据
        //print_r($response);
        return $response;
    }


    //Post请求
    public static function post($url,$data){
        $c = curl_init();
        curl_setopt($c, CURLOPT_URL, $url);
        curl_setopt($c, CURLOPT_USERAGENT,self::DEFAULT_USER_AGENT);
        curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);//绕过ssl验证
        curl_setopt($c, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($c, CURLOPT_POST, 1);
        curl_setopt($c, CURLOPT_POSTFIELDS, $data);
        //执行命令
        $response = curl_exec($c);
        //关闭URL请求
        curl_close($c);
        //显示获得的数据
        //print_r($response);
        return $response;
    }


    /*
     * 解析下载文件的后缀名
     * */
    public static function getFileExt($content_type)
    {
        if (!$content_type) return null;
        $exts = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'audio/mp3' => 'mp3',
            'video/avi' => 'avi',
            'video/mpeg4' => 'mp4',
        ];
        if (array_key_exists($content_type,$exts)) {
            $ext = $exts[$content_type];
        } else {
            $ext = null;
        }
        return $ext;
    }


    /*
     * 文件下载
     * */
    public static function download($url, $save_path, $filename = null, $timeout = 6, $retry = 2)
    {
        if (!$url) {
            return false;
        }
        $i = 0;
        while ($i <= $retry) {
            $filename = $filename ? $filename : 'download_' . time();
            if (!file_exists($save_path)) { //目录不存在
                mkdir($save_path,0755,true); //创建目录
            }
            $file = $save_path . $filename; //全路径
            $fp = fopen($file, 'w');
            $c = curl_init();
            curl_setopt($c, CURLOPT_URL, $url);
            curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);//绕过ssl验证
            curl_setopt($c, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($c, CURLOPT_HEADER, 0);
            curl_setopt($c, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($c, CURLOPT_FILE, $fp);
            curl_exec($c);
            $content_type = curl_getinfo($c, CURLINFO_CONTENT_TYPE);
            $http_code = curl_getinfo($c, CURLINFO_HTTP_CODE);
            //请求成功判断
            if ($http_code == 200) {
                //修改文件后缀
                $ext = self::getFileExt($content_type);
                if ($ext) {
                    $filename .= '.' . $ext; //加后缀
                    rename($file, $save_path . $filename); //重命名
                }
                $data = [
                    'save_path' => $save_path,
                    'filename' => $filename,
                    'size' => filesize($save_path . $filename),
                    'content_type' => $content_type,
                    'http_code' => $http_code
                ];
                curl_close($c);
                fclose($fp);
                break; //跳出循环
            } else { //请求失败
                if (filesize($file) == 0) {
                    unlink($file); //文件大小为0时删除该文件
                }
                $i++;
                $data = null;
                $err = curl_error($c);
                curl_close($c);
                fclose($fp);
            }
        }
        return $data;
    }

}
