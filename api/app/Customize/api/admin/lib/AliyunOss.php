<?php


namespace App\Customize\api\admin\lib;


use Exception;
use OSS\OssClient;

class AliyunOss
{
    /*
     * @var string
     */
    protected $key;

    /*
     * @var string
     */
    protected $secret;

    /*
     * @var string
     */
    protected $endpoint;

    /**
     * @var \Oss\OssClient
     */
    protected $client;

    function __construct(string $key , string $secret , string $endpoint)
    {
        $this->key = $key;
        $this->secret = $secret;
        $this->endpoint = $endpoint;

        $this->init();
    }

    protected function init()
    {
        $this->client = new OssClient($this->key , $this->secret , $this->endpoint);
    }

    /**
     * 文件上传
     * @param string $bucket
     * @param string $filename
     * @param string $file
     * @param array $options
     * @return array
     */
    public function upload(string $bucket , string $filename , string $file , array $options = []): array
    {
        try {
            if (file_exists($file)) {
                // 如果是文件
                $res = $this->client->uploadFile($bucket , $filename , $file , $options);
            } else {
                $res = $this->client->putObject($bucket , $filename , $file , $options);
            }
            $url = $res['oss-request-url'] ?? '';
            return $this->success('' , $url);
        } catch(Exception $e) {
            return $this->error($e->getMessage() , $e->getTrace());
        }
    }

    protected function getPathname(string $url): string
    {
        $data = parse_url($url);
        $path = $data['path'];
        $path = urldecode($path);
        return $path;
    }

    public function delete(string $bucket , string $url): array
    {
        $name = $this->getPathname($url);
        try {
            $res = $this->client->deleteObject($bucket , $name);
            return $this->success('操作成功' , $res['oss-request-url'] ?? '');
        } catch(Exception $e) {
            return $this->error($e->getMessage() , $e->getTrace());
        }
    }

    protected function success($message = '' , $data = '' , int $code = 0): array
    {
        return compact('code' , 'message' , 'data');
    }

    protected function error($message = '' , $data = '' , int $code = 500): array
    {
        return compact('code' , 'message' , 'data');
    }
}
