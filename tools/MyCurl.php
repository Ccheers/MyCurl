<?php


namespace MyProject\tools;

class MyCurl
{
    private $url;
    private $agent;
    private $cookie_file;
    private $curl;
    private $is_https;

    /**
     * 构造函数
     * MyCurl constructor.
     * @param string $url url
     * @param bool $is_https 是否https协议
     * @param string $cookie_file cookie文件路劲
     */
    public function __construct($url = '', $is_https = true, $cookie_file = '')
    {
        $this->url = $url;
        $this->cookie_file = $cookie_file;
        $this->agent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322; .NET CLR 2.0.50727)";
        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_URL, $this->url);//url
        $this->is_https = $is_https;
        if ($this->is_https === true) {
            curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, FALSE);//禁用后cURL将终止从服务端进行验证
            curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, FALSE);//1.检查服务器SSL证书中是否存在一个公用名(common name)2.检查公用名是否存在，并且是否与提供的主机名匹配
        }
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);//将curl_exec()获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($this->curl, CURLOPT_COOKIEFILE, $this->cookie_file); //包含cookie数据的文件
        curl_setopt($this->curl, CURLOPT_COOKIEJAR, $this->cookie_file); //连接结束后保存cookie信息的文件
        curl_setopt($this->curl, CURLOPT_USERAGENT, $this->agent);//模拟浏览器代理
    }

    /**
     * 通过Post方法获取数据
     * @param string $url url
     * @param array $data post数据
     * @param bool $is_json 是否json数据
     * @return bool|string
     * @throws \Exception
     */
    public function getPostData($url, $data, $is_json = false)
    {
        $this->cookie();
        $this->setUrl($url);
        curl_setopt($this->curl, CURLOPT_HEADER, false);
        curl_setopt($this->curl, CURLOPT_POST, true);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        if ($is_json == true) {
            $headers = array(
                "Content-type: application/json;charset='utf-8'",
                "Accept: application/json",
                "Cache-Control: no-cache",
                "Pragma: no-cache",
            );
            curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers);
        }
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, json_encode($data));
        $res = curl_exec($this->curl);
        if ($res === FALSE) {
            throw new \Exception("CURL Error:" . curl_error($this->curl));
        } else {
            return $res;
        }
    }

    /**
     * 通过Get方法获取数据
     * @param string $url url
     * @param bool $is_json 是否json数据
     * @return bool|string
     * @throws \Exception
     */
    public function getGetData($url, $is_json = false)
    {
        $this->cookie();
        $this->setUrl($url);
        if ($is_json == true) {
            $headers = array(
                "Content-type: application/json;charset='utf-8'",
                "Accept: application/json",
                "Cache-Control: no-cache",
                "Pragma: no-cache",
            );
            curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers);
        }
        $res = curl_exec($this->curl);
        if ($res === FALSE) {
            throw new \Exception("CURL Error:" . curl_error($this->curl));
        } else {
            return $res;
        }
    }

    /**
     * 登录
     * @param string $login_url 登录url
     * @param array $login_data 表单数据
     * @param bool $is_ajax 是否ajax请求
     * @return bool|string
     */
    public function login($login_url, $login_data, $is_ajax = false)
    {
        $this->cookie();
        curl_setopt($this->curl, CURLOPT_URL, $login_url);
        if ($is_ajax === true) {
            $headers = array(
                "X-Requested-With: XMLHttpRequest",
            );
            curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers);
        }
        curl_setopt($this->curl, CURLOPT_POST, true); //post方式提交
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, http_build_query($login_data)); //要提交的信息
        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, true);//自动追踪重定向
        curl_setopt($this->curl, CURLOPT_AUTOREFERER, true);//当重定向发生时设置header里的Refer信息
        if (($res = curl_exec($this->curl)) === FALSE) {
            die("登录失败!" . curl_error($this->curl));
        } //执行cURL
        return $res;
    }

    /**
     * 获取token
     * @param string $token_url token的url
     * @param string $patten 正则匹配获取token的正则表达式
     * @param bool $is_ajax 是否ajax请求
     * @param array $data 获取token的数据
     * @return mixed
     */
    public function getToken($token_url, $patten, $data, $is_ajax = false)
    {
        $this->cookie();
        curl_setopt($this->curl, CURLOPT_URL, $token_url);
        if ($is_ajax === true) {
            $headers = array(
                "X-Requested-With: XMLHttpRequest",
            );
            curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers);
        }
        if ($data) {
            curl_setopt($this->curl, CURLOPT_POST, true); //post方式提交
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, http_build_query($data)); //要提交的信息
        }
        $data = curl_exec($this->curl);
        preg_match_all($patten, $data, $matches);
        return $matches;
    }

    /**
     * 设置cookie
     */
    private function cookie()
    {
        curl_setopt($this->curl, CURLOPT_COOKIEFILE, $this->cookie_file); //包含cookie数据的文件
        curl_setopt($this->curl, CURLOPT_COOKIEJAR, $this->cookie_file); //设置Cookie信息保存在指定的文件中
    }

    /**
     * 类销毁时关闭curl
     */
    public function __destruct()
    {
        curl_close($this->curl);
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     */
    public function setUrl($url): void
    {
        $this->url = $url;
        curl_setopt($this->curl, CURLOPT_URL, $this->url);
    }

    /**
     * @return string
     */
    public function getAgent(): string
    {
        return $this->agent;
    }

    /**
     * @param string $agent
     */
    public function setAgent(string $agent): void
    {
        $this->agent = $agent;
        curl_setopt($this->curl, CURLOPT_USERAGENT, $this->agent);
    }

    /**
     * @return mixed
     */
    public function getCookieFile()
    {
        return $this->cookie_file;
    }

    /**
     * @param mixed $cookie_file
     */
    public function setCookieFile($cookie_file): void
    {
        $this->cookie_file = $cookie_file;
        curl_setopt($this->curl, CURLOPT_COOKIEFILE, $this->cookie_file);
    }

    /**
     * @return false|resource
     */
    public function getCurl()
    {
        return $this->curl;
    }

    /**
     * @return bool
     */
    public function isIsHttps(): bool
    {
        return $this->is_https;
    }

    /**
     * @param bool $is_https
     */
    public function setIsHttps(bool $is_https): void
    {
        $this->is_https = $is_https;
        if ($this->is_https === true) {
            curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        }
    }
}