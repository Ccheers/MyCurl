<?php

/**
 * Class PHPCurl
 *
 * 防止连接超时，可以在文件头部加上
 * ignore_user_abort(true);
 * set_time_limit(0);
 */
class PHPCurl{
    //请求地址
    private $_url = "";
    //cookie串
    private $_cookie = "";
    //cookie文件名  dirname(__FILE__) . '/cookie.txt';
    private $_cookie_file = "";
    //是否是https
    private $_is_https = false;
    //代理
    private $_user_agent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322; .NET CLR 2.0.50727)";

    //连接句柄
    private $_ch = null;

    /**
     * PHPCurl constructor.
     * @param string $url 网址
     * @param bool $is_https 是否是https
     */
    public function __construct($url="", $is_https=true)
    {
        $this->_url = $url;
        $this->_is_https = $is_https;
        $this->_ch = curl_init();
        curl_setopt($this->_ch,CURLOPT_URL,$this->_url);
        curl_setopt($this->_ch,CURLOPT_RETURNTRANSFER,true);

        if($this->_is_https === true){
            curl_setopt($this->_ch,CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($this->_ch,CURLOPT_SSL_VERIFYHOST, FALSE);
        }
        //模拟浏览器代理
        curl_setopt($this->_ch,CURLOPT_USERAGENT, $this->_user_agent);
    }

    /**
     * 执行get请求
     * @param bool $is_json 返回的是否是json
     * @return string
     * @throws Exception
     */
    public function execGet($is_json = false){
        if($is_json == true){
            $headers = array(
                "Content-type: application/json;charset='utf-8'",
                "Accept: application/json",
                "Cache-Control: no-cache",
                "Pragma: no-cache",
            );
            curl_setopt($this->_ch, CURLOPT_HTTPHEADER, $headers);
        }
        $res = curl_exec($this->_ch);
        if($res === FALSE){
            throw new Exception("CURL Error:".curl_error($this->_ch));
        }else{
            return $res;
        }
    }
    /**
     * 执行post请求
     * @param array $data 表单数据
     * @param bool $is_json 返回的是否是json
     * @return string
     * @throws Exception
     */
    public function execPost($data, $is_json=false){
        curl_setopt($this->_ch, CURLOPT_HEADER, false);
        curl_setopt($this->_ch, CURLOPT_POST,true);
        curl_setopt($this->_ch, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($this->_ch, CURLOPT_RETURNTRANSFER,true);
        if($is_json == true){
            $headers = array(
                "Content-type: application/json;charset='utf-8'",
                "Accept: application/json",
                "Cache-Control: no-cache",
                "Pragma: no-cache",
            );
            curl_setopt($this->_ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($this->_ch, CURLOPT_POSTFIELDS, json_encode($data));
        }else{
            curl_setopt($this->_ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }

        $res = curl_exec($this->_ch);
        if($res === FALSE){
            throw new Exception("CURL Error:".curl_error($this->_ch));
        }else{
            return $res;
        }
    }

    /**
     * 通过post下载文件
     * @param $file_path 文件的下载路径
     * @param $is_post bool 是否是post
     * @param $post array post参数
     * @return bool
     * @throws Exception
     */
    public function download($file_path, $is_post=false, $post=array()){
        //存储到文件中
        $fp = fopen ($file_path, 'w+');
        if($is_post == true){
            curl_setopt($this->_ch, CURLOPT_POST,true);
            curl_setopt($this->_ch, CURLOPT_POSTFIELDS, http_build_query($post));
        }
        curl_setopt($this->_ch, CURLOPT_FILE, $fp);
        $res = curl_exec($this->_ch);
        fclose($fp);

        if($res === FALSE ){
            throw new Exception("CURL Error:".curl_error($this->_ch));
        }else{
            return $res;
        }
    }

    /**
     * Post模拟登录--token和302不同时存在
     * @param string $url
     * @param array $post
     * @param $cookie_file  $cookie_file = dirname(__FILE__) . '/cookie.txt';
     * 如果$cookie_file是数组 $cookie_file[0]存储访问login界面的cookie $cookie_file[1]存储登录成功的cookie
     * @param bool $is_https
     * @param bool $is_ajax 是否是ajax请求
     * @return string
     */
    public function loginPost($url, $post, $cookie_file, $is_https=true, $is_ajax=false){

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //是否自动显示返回的信息
        if($is_https === true){
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        if(is_array($cookie_file)){
            $this->setCookieFile($cookie_file[1]);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file[0]);
            curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file[1]);
        }else{
            $this->setCookieFile($cookie_file);
            curl_setopt($ch, CURLOPT_COOKIEJAR, $this->_cookie_file); //设置Cookie信息保存在指定的文件中
        }
        if($is_ajax === true){
            $headers = array(
                "X-Requested-With: XMLHttpRequest",
            );
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        curl_setopt($ch, CURLOPT_POST, true); //post方式提交
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post)); //要提交的信息
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_USERAGENT,$this->_user_agent);
        if(($res = curl_exec($ch)) === FALSE){
            die("登录失败!" .curl_error($ch));
        } //执行cURL
        curl_close($ch); //关闭cURL资源，并且释放系统资源
        return $res;
    }

    /**
     * 模拟登录--有token和302
     * @param array $url $url[0]login界面链接  $url[1]登录链接
     * @param array $post
     * @param array $cookie_file $cookie_file[0]存储访问login界面的cookie $cookie_file[1]存储登录成功的cookie
     * @param array $token 匹配token
     * $token[0] 表单name
     * $token[1] = ($pattern)'/<input type="hidden" name="_csrf_token" value="(.*?)">/'
     * @param bool $is_https
     * @return string
     */
    public function loginToken($url=array(), $post=array(), $cookie_file=array(), $token = array(), $is_https=true){
        $ch = curl_init();
        if($is_https == true){
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //是否自动显示返回的信息

        curl_setopt($ch, CURLOPT_USERAGENT,$this->_user_agent);
        //获取token
        curl_setopt($ch, CURLOPT_URL, $url[0]);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file[0]); //设置Cookie信息保存在指定的文件中
        $data = curl_exec($ch);
        preg_match_all($token[1], $data, $matches);
        $post[$token[0]] = $matches[1][0];
        curl_close($ch);
        //登录
        return $this->loginPost($url[1], $post, $cookie_file, $is_https);
    }


    /**
     * 过滤html中的换行
     */
    public static function trimHtml($html){
        return preg_replace("/[\t\n\r]+/","",$html);
    }
    /**
     * @param $url
     */
    public function setUrl($url)
    {
        $this->_url = $url;
        curl_setopt($this->_ch, CURLOPT_URL, $this->_url);
    }

    public function getUrl()
    {
        return $this->_url;
    }

    public function setCookieFile($cookie_file){
        $this->_cookie_file = $cookie_file;
        curl_setopt($this->_ch, CURLOPT_COOKIEFILE, $this->_cookie_file);
    }

    public function getCookieFile()
    {
        return $this->_cookie_file;
    }


    public function setCookie($cookie)
    {
        $this->_cookie = $cookie;
        curl_setopt($this->_ch,CURLOPT_COOKIE,$this->_cookie);
    }

    public function getCookie()
    {
        return $this->_cookie;
    }

    /**
     * @param bool $is_https
     */
    public function setIsHttps($is_https)
    {
        $this->_is_https = $is_https;
        if($this->_is_https === true){
            curl_setopt($this->_ch,CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($this->_ch,CURLOPT_SSL_VERIFYHOST, FALSE);
        }
    }

    /**
     * @return bool
     */
    public function getIsHttps()
    {
        return $this->_is_https;
    }

    public function setUserAgent($user_agent){
        $this->_user_agent = $user_agent;
        curl_setopt($this->_ch, CURLOPT_USERAGENT,$this->_user_agent);
    }

    public function getUserAgent(){
        return $this->_user_agent;
    }

    public function __destruct()
    {
        curl_close($this->_ch);
    }


}
