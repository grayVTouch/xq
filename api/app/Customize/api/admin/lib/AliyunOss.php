<?php


namespace App\Customize\api\admin\lib;


use Exception;
use OSS\OssClient;
use function core\random;

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
        $options = array_merge([
            OssClient::OSS_HEADERS => [
                'x-oss-object-acl' => 'public-read' ,
            ] ,
        ] , $options);
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

    public function copy(string $from_bucket , string $from_name , string $to_bucket , string $to_name , array $options = []): array
    {
        $options = array_merge([
            OssClient::OSS_HEADERS => [
                'x-oss-object-acl' => 'public-read' ,
            ] ,
        ] , $options);
        try {
            // 复制新文件
            $copy_res = $this->client->copyObject($from_bucket , $from_name , $to_bucket , $to_name , $options);
            // 删除源文件
            if (!$this->isRepeat($from_bucket , $from_name , $to_bucket , $to_name)) {
                $this->client->deleteObject($from_bucket , $from_name);
            }
            $url = $copy_res['oss-request-url'] ?? '';
            return $this->success('' , $url);
        } catch(Exception $e) {
            return $this->error($e->getMessage() , $e->getTrace());
        }
    }

    public function getPathname(string $url): string
    {
        $data = parse_url($url);
        $path = $data['path'];
        $path = urldecode($path);
        $path = ltrim($path , '/\\');
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

    // 生成文件名（带目录）
    public function generateFilename(string $extension): string
    {
        return date('Ymd') . '/' . date('Ymdhis') . random(6 , 'mixed' , true) . '.' . $extension;
    }

    public function isRepeat(string $from_bucket , string $from_name , string $to_bucket , string $to_name): bool
    {
        return $from_bucket === $to_bucket && $from_name === $to_name;
    }
}
