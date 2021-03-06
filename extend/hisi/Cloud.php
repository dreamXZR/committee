<?php

namespace hisi;

use GuzzleHttp\Client;
use Env;

class Cloud {

    // 错误信息
    private $error = '应用市场出现未知错误';

    // 请求的数据
    private $data = [];

    // 接口
    private $api = '';

    // 站点标识
    private $identifier = '';

    // 升级锁
    public $lock = '';

    // 升级目录路径
    public $path = './';

    // 请求类型
    public $type = 'POST';

    //服务器地址
    const API_URL ='http://47.92.150.84/';
    
    /**
     * 架构函数
     * @param string $path  目录路径
     */
    public function __construct($identifier = '', $path = './') {
        $this->identifier = $identifier;
        $this->path = $path;
        $this->lock = '.'.ROOT_DIR.'upload/cloud.lock';

        $this->client = new Client([
            'base_uri' =>self::API_URL.'api/',
            'timeout'  => 5.0,
        ]);
    }

    /**
     * 获取服务器地址
     * @return string
     */
    public function apiUrl()
    {
        return self::API_URL;
    }

    /**
     * 获取错误信息
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * 需要发送的数据
     * @param  array $data 数据
     * @return obj
     */
    public function data($data = [])
    {
        $this->data = $data;
        return $this;
    }

    /**
     * api 请求接口
     * @param  string $api 接口
     * @return array
     */
    public function api($api = '')
    {
        $response = $this->client->request($this->type, $api,[
            'query'=>$this->data
        ]);
        return self::_response($response);
    }

    /**
     * type 请求类型
     * @param  string $type 请求类型(get,post)
     * @return obj
     */
    public function type($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * 文件下载
     */
    public function down()
    {

        $saveFile   = $this->path.time().'.zip';

        $upload_path=self::API_URL.'storage/'.$this->data['file_path'];

        // 执行下载
        $client = new Client(['verify'=>false]);
        $client->get($upload_path,['save_to'=>$saveFile]);

        if (is_file($this->lock)) {
            @unlink($this->lock);
        }

        return $saveFile;
    }

    /**
     * 执行接口
     * @return array
     */
    private function run($down = false)
    {
        $params['format']       = 'json';
        $params['timestamp']    = time();
        $params['ip']           = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : get_client_ip();
        $params['identifier']   = $this->identifier;
        $params['frame_version']      = config('committee.version');
        $params                 = array_merge($params, $this->data);
        $params                 = array_filter($params);


        if (is_file($this->lock)) {
            @unlink($this->lock);
        }

        file_put_contents($this->lock, $params['timestamp']);

        if ($down === true) {
            $result             = [];
            $result['url']      = $this->api;
            $result['params']   = http_build_query($params);
            return $result;
        }

        $type   = $this->type;
        $result = Http::$type($this->api, $params);

        return self::_response($result);
    }

    /**
     * 以数组格式返回
     * @return array
     */
    private static function _response($response)
    {

        return \json_decode($response->getBody()->getContents(),true);
    }
}
