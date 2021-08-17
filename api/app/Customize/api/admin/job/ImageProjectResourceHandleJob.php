<?php

namespace App\Customize\api\admin\job;

use App\Customize\api\admin\facade\AliyunOss;
use App\Customize\api\admin\handler\ImageProjectHandler;
use App\Customize\api\admin\job\middleware\BootMiddleware;
use App\Customize\api\admin\job\traits\FileTrait;
use App\Customize\api\admin\model\ImageModel;
use App\Customize\api\admin\model\ImageProjectModel;
use App\Customize\api\admin\model\ResourceModel;
use App\Customize\api\admin\model\SystemSettingsModel;
use App\Customize\api\admin\repository\StorageRepository;
use App\Customize\api\admin\repository\FileRepository;
use App\Customize\api\admin\repository\ResourceRepository;
use Core\Lib\File;
use Core\Lib\ImageProcessor;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use stdClass;
use function api\admin\my_config;
use function core\get_extension;
use function core\random;

class ImageProjectResourceHandleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    use FileTrait;

    private $imageProjectId = 0;

    /**
     * @var ImageProcessor
     */
    private $imageProcessor;

    /**
     * @var SystemSettingsModel
     */
    private $settings;

    /**
     * @var stdClass
     */
    private $imageProject;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $image_project_id)
    {
        $this->imageProjectId = $image_project_id;
    }

    public function middleware()
    {
        return [
            new BootMiddleware() ,
        ];
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle()
    {
        ImageProjectModel::updateById($this->imageProjectId , [
            // 处理中
            'process_status' => 1 ,
        ]);
        $settings = SystemSettingsModel::first();
        // 仅在本地存储模式下才执行移植任务
        $image_project = ImageProjectModel::find($this->imageProjectId);
        if (empty($image_project)) {
            throw new Exception('图片专题不存在【' . $this->imageProjectId . '】');
        }
        ImageProjectHandler::module($image_project);
        if (empty($image_project->module)) {
            throw new Exception("图片专题所属模块不存在【{$image_project->module_id}】");
        }
        $origin_save_dir = $image_project->directory;
        $dir_prefix = '';
        if ($image_project->type === 'pro') {
            $dir_prefix = my_config('app.dir')['image_project'];
        } else {
            $dir_prefix = my_config('app.dir')['image'] . '/' . date('Ymd' , strtotime($image_project->created_at));
        }
        // 保存目录
        $relative_dir = StorageRepository::getRelativeDirectoryByImageProjectId($image_project->id);
        // 原图目录
        $origin_relative_dir = $relative_dir . '/原图';
        // 预览图目录
        $preview_relative_dir = $relative_dir . '/预览图';
        // 绝对路径 - 保存目录
        $save_dir = FileRepository::generateRealPathByWithoutPrefixRelativePath($relative_dir);
        if (!File::exists($save_dir)) {
            File::mkdir($save_dir , 0777 , true);
        }
        ResourceRepository::create('' , $save_dir , 'local' , 1 , 0);
        ImageProjectModel::updateById($image_project->id , [
            'directory' => $save_dir ,
        ]);
        $origin_dir = '';
        $preview_dir = '';
        if ($image_project->type === 'pro') {
            $origin_dir = FileRepository::generateRealPathByWithoutPrefixRelativePath($origin_relative_dir);;
            $preview_dir = FileRepository::generateRealPathByWithoutPrefixRelativePath($preview_relative_dir);;
        } else {
            $origin_dir = $save_dir;
            $preview_dir = $save_dir;
        }
        if (!File::exists($origin_dir)) {
            File::mkdir($origin_dir , 0777 , true);
        }
        if (!File::exists($preview_dir)) {
            File::mkdir($preview_dir , 0777 , true);
        }
        ImageProjectHandler::images($image_project);

        $image_processor = new ImageProcessor($save_dir);

        $this->imageProject = $image_project;
        $this->imageProcessor = $image_processor;
        $this->settings = $settings;

        $handle_res = null;
        if ($image_project->disk === 'local') {
            $handle_res = $this->localHandle($save_dir , $origin_dir , $preview_dir);
        } else {
            if ($image_project->disk === 'aliyun'){
                $handle_res = $this->aliyunCloudHandle($relative_dir , $origin_relative_dir , $preview_relative_dir);
            } else {
                // todo 预留
            }
            if (File::isDir($save_dir)) {
                File::delete($save_dir);
            }
        }

        if (
            !empty($origin_save_dir) &&
            $origin_save_dir !== $save_dir &&
            File::isDir($origin_save_dir)
        )
        {
            File::delete($origin_save_dir);
        }
        ImageProjectModel::updateById($this->imageProjectId , [
            // 处理已完成
            'process_status' => 2 ,
            'process_message' => $handle_res['message'] ,
            'process_data' => $handle_res['data'] ,
        ]);
    }

    public function imageProcess($file , $compress_extension = ''): string
    {
        $compress_file = $this->imageProcessor->compress($file , [
            'mode'      => 'fix-width' ,
            // 质量
            'quality'     => 95 ,
            // 处理后图片宽度
            'width'     => 1920 ,
            // 输出文件类型（如果指定，那么将会以这种类型输出，否则以源文件类型输出）
            'extension' => $compress_extension ,
        ] , false);
        return $compress_file;
    }

    // 云存储处理
    private function aliyunCloudHandle(string $save_dir , string $origin_dir , string $preview_dir)
    {
        $index = 0;
        $total = 0;
        $failed_count = 0;
        $success_count = 0;
        foreach ($this->imageProject->images as $v)
        {
            $index++;
            $total++;
            try {
                DB::beginTransaction();
                $o_resource = ResourceModel::findByUrlOrPath($v->original_src);
                if (empty($o_resource)) {
                    $failed_count++;
                    DB::rollBack();
                    continue ;
                }
                $random_value = date('YmdHis') . random(6 , 'letter' , true);
                $extension      = get_extension($v->original_src);
                if ($this->imageProject->type === 'pro') {
                    $original_filename   = "{$this->imageProject->name}【{$index}】.{$extension}";
                } else {
                    $original_filename   =  $random_value . '【原图】.' . $extension;
                }
                // 相对路径
                $original_file = $origin_dir . '/' . $original_filename;
                $p_compress_extension = 'webp';

                // 目标图
                if ($this->imageProject->type === 'pro') {
                    $filename       = "{$this->imageProject->name}【{$index}】【预览图】.{$p_compress_extension}";
                } else {
                    $filename       = $random_value . '【预览图】.' . $p_compress_extension;
                }
                $preview_file = $preview_dir . '/' . $filename;

                $original_ext = $extension;
                $original_src = '';
                $preview_src = '';
                if ($o_resource->disk === 'local') {
                    /**
                     * *******************
                     * 预览图
                     * *******************
                     */
                    /**
                     * *******************
                     * 移动预览图
                     * *******************
                     */

                    $p_resource = null;
                    if (empty($v->src)) {

                        // 原图上传
                        $o_upload_res = AliyunOss::upload($this->settings->aliyun_bucket , $original_file , $o_resource->path);
                        if ($o_upload_res['code'] > 0) {
                            $failed_count++;
                            DB::rollBack();
                            continue ;
                        }
                        $original_src = $o_upload_res['data'];
                        ResourceRepository::createAliyun($original_src , $this->settings->aliyun_bucket , 0 , 0);
                        if (in_array($original_ext , ['gif'])) {
                            ImageModel::updateById($v->id , [
                                'original_src'  => $original_src ,
                                'src'           => $original_src ,
                            ]);
                            ResourceRepository::used($original_src);
                            DB::commit();
                            $success_count++;
                            continue ;
                        }

                        // 生成预览图
                        $p_source_file = $this->imageProcess($o_resource->path , $p_compress_extension);

                        // 预览图上传
                        $p_upload_res = AliyunOss::upload($this->settings->aliyun_bucket , $preview_file , $p_source_file);
                        if ($p_upload_res['code'] > 0) {
                            $failed_count++;
                            DB::rollBack();
                            ResourceRepository::createAliyun($original_src , $this->settings->aliyun_bucket , 0 , 1);
                            continue ;
                        }
                        $preview_src = $p_upload_res['data'];

                        ResourceRepository::delete($o_resource->path);
                        ResourceRepository::create('' , $p_source_file , 'local' , 0 , 1);
                    } else {
                        $p_resource = ResourceModel::findByUrlOrPath($v->src);
                        if (empty($p_resource)) {
                            $failed_count++;
                            DB::rollBack();
                            continue ;
                        }
                        if ($p_resource->disk === 'local') {
                            // 原图上传
                            $o_upload_res = AliyunOss::upload($this->settings->aliyun_bucket , $original_file , $o_resource->path);
                            if ($o_upload_res['code'] > 0) {
                                $failed_count++;
                                DB::rollBack();
                                continue ;
                            }
                            $original_src = $o_upload_res['data'];

                            // 预览图上传
                            $p_upload_res = AliyunOss::upload($this->settings->aliyun_bucket , $preview_file , $p_resource->path);
                            if ($p_upload_res['code'] > 0) {
                                $failed_count++;
                                DB::rollBack();
                                ResourceRepository::createAliyun($original_src , $this->settings->aliyun_bucket , 0 , 1);
                                continue ;
                            }
                            $preview_src = $p_upload_res['data'];

                            ResourceRepository::delete($o_resource->path);
                            ResourceRepository::delete($p_resource->path);
                        } else if ($p_resource->disk === 'aliyun') {
                            // 原图上传
                            $o_upload_res = AliyunOss::upload($this->settings->aliyun_bucket , $original_file , $o_resource->path);
                            if ($o_upload_res['code'] > 0) {
                                $failed_count++;
                                DB::rollBack();
                                continue ;
                            }
                            $original_src = $o_upload_res['data'];

                            // 预览图移动
                            $to_preview_file = $preview_file;
                            $from_preview_file = AliyunOss::getPathname($p_resource->url);

                            if ($p_resource->aliyun_bucket != $this->settings->aliyun_bucket || $from_preview_file != $to_preview_file) {
                                $preview_copy_res = AliyunOss::copy($p_resource->aliyun_bucket , $from_preview_file , $this->settings->aliyun_bucket , $to_preview_file);
                                if ($preview_copy_res['code'] > 0) {
                                    $failed_count++;
                                    DB::rollBack();
                                    ResourceRepository::createAliyun($original_src , $this->settings->aliyun_bucket , 0 , 1);
                                    continue ;
                                }
                                $preview_src = $preview_copy_res['data'];
                                ResourceRepository::delete($p_resource->url);
                            } else {
                                $preview_src = $p_resource->url;
                            }
                        } else {
                            // todo 预留
                        }
                    }
                } else if ($o_resource->disk === 'aliyun') {
                    $from_original_file = AliyunOss::getPathname($o_resource->url);
                    $to_original_file   = $original_file;
                    if ($o_resource->aliyun_bucket != $this->settings->aliyun_bucket || $from_original_file != $to_original_file) {
                        $original_copy_res = AliyunOss::copy($o_resource->aliyun_bucket , $from_original_file , $this->settings->aliyun_bucket , $to_original_file);
                        if ($original_copy_res['code'] > 0) {
                            $failed_count++;
                            DB::rollBack();
                            continue ;
                        }
                        $original_src = $original_copy_res['data'];
                        ResourceRepository::delete($o_resource->url);
                    } else {
                        $original_src = $o_resource->url;
                    }
                    $p_resource = ResourceModel::findByUrlOrPath($v->src);
                    if (empty($p_resource)) {
                        $failed_count++;
                        DB::rollBack();
                        ResourceRepository::createAliyun($original_src , $this->settings->aliyun_bucket , 0 , 1);
                        continue ;
                    }
                    if ($p_resource->disk === 'local') {
                        // 预览图上传
                        $p_upload_res = AliyunOss::upload($this->settings->aliyun_bucket , $preview_file , $p_resource->path);
                        if ($p_upload_res['code'] > 0) {
                            $failed_count++;
                            DB::rollBack();
                            ResourceRepository::createAliyun($original_src , $this->settings->aliyun_bucket , 0 , 1);
                            continue ;
                        }
                        $preview_src = $p_upload_res['data'];
                        ResourceRepository::delete($p_resource->path);
                    } else if ($p_resource->disk === 'aliyun') {
                        $to_preview_file = $preview_file;
                        $from_preview_file = AliyunOss::getPathname($p_resource->url);
                        if ($p_resource->aliyun_bucket != $this->settings->aliyun_bucket || $from_preview_file != $to_preview_file) {
                            $preview_copy_res = AliyunOss::copy($p_resource->aliyun_bucket , $from_preview_file , $this->settings->aliyun_bucket , $to_preview_file);
                            if ($preview_copy_res['code'] > 0) {
                                $failed_count++;
                                DB::rollBack();
                                ResourceRepository::createAliyun($original_src , $this->settings->aliyun_bucket , 0 , 1);
                                continue ;
                            }
                            $preview_src = $preview_copy_res['data'];
                            ResourceRepository::delete($p_resource->url);
                        } else {
                            $preview_src = $p_resource->url;
                        }
                    } else {
                        // todo 预留
                    }
                } else {
                    // todo 预留
                }
                ImageModel::updateById($v->id , [
                    'original_src'  => $original_src ,
                    'src'           => $preview_src ,
                ]);
                ResourceRepository::createAliyun($original_src , $this->settings->aliyun_bucket , 1 , 0);
                ResourceRepository::createAliyun($preview_src , $this->settings->aliyun_bucket , 1 , 0);
                $success_count++;
                DB::commit();
            } catch(Exception $e) {
                DB::rollBack();
                throw $e;
            }
        }
        return [
            'message' => "总处理数：{$total}；失败数：{$failed_count}；成功数：{$success_count}" ,
            'data' => json_encode(compact('total' , 'failed_count' , 'success_count'))
        ];
    }

    // 本地磁盘处理
    private function localHandle(string $save_dir , string $origin_dir , string $preview_dir)
    {
        $index = 0;
        $total = 0;
        $failed_count = 0;
        $success_count = 0;
        foreach ($this->imageProject->images as $v)
        {
            $total++;
            $index++;
            try {
                DB::beginTransaction();
                /**
                 * *******************
                 * 移动原图
                 * *******************
                 */
                $resource = ResourceModel::findByUrlOrPath($v->original_src);
                if (empty($resource)) {
                    $failed_count++;
                    DB::rollBack();
                    continue ;
                }
                $random_value = date('YmdHis') . random(6 , 'letter' , true);
                $extension      = get_extension($v->original_src);
                if ($this->imageProject->type === 'pro') {
                    $filename   = "{$this->imageProject->name}【{$index}】.{$extension}";
                } else {
                    $origin_dir = $save_dir;
                    $filename   =  $random_value . '【原图】.' . $extension;
                }
                $source_file    = $resource->path;
                $target_file    = $this->generateRealPath($origin_dir , $filename);
                $target_url     = FileRepository::generateUrlByRealPath($target_file);
                if ($source_file !== $target_file) {
                    if (File::exists($target_file)) {
                        // 文件已经存在，删除
                        File::dFile($target_file);
                    }
                    // 移动文件
                    File::move($source_file , $target_file);
                    // 删除源文件
                    ResourceRepository::delete($source_file);
                    ResourceRepository::create($target_url , $target_file , 'local' , 0 , 0);
                }

                // 更新原图地址
                $original_file = $target_file;
                $original_src  = $target_url;
                $original_ext  = $extension;

                /**
                 * *******************
                 * 移动预览图
                 * *******************
                 */
                if (in_array($original_ext , ['gif'])) {
                    ImageModel::updateById($v->id , [
                        'original_src'  => $original_src ,
                        'src'           => $original_src ,
                    ]);
                    ResourceRepository::used($original_src);
                    DB::commit();
                    $success_count++;
                    continue ;
                }
                $extension = 'webp';
                if (empty($v->src)) {
                    // 生成预览图
                    $source_file = $this->imageProcess($original_file , $extension);
                } else {
                    $resource = ResourceModel::findByUrlOrPath($v->src);
                    if (empty($resource)) {
                        $failed_count++;
                        DB::rollBack();
                        continue ;
                    }
                    if ($resource->disk !== 'local') {
                        // 跳过非本地存储的资源
                        $failed_count++;
                        DB::rollBack();
                        continue ;
                    }
                    $source_file = $resource->path;
                }
                if ($this->imageProject->type === 'pro') {
                    $filename       = "{$this->imageProject->name}【{$index}】【预览图】.{$extension}";
                } else {
                    $filename       = $random_value . '【预览图】.' . $extension;
                }
                $target_file    = $this->generateRealPath($preview_dir , $filename);
                $target_url     = FileRepository::generateUrlByRealPath($target_file);
                if ($source_file !== $target_file) {
                    if (File::exists($target_file)) {
                        // 文件已经存在，删除
                        File::dFile($target_file);
                    }
                    File::move($source_file , $target_file);
                    // 删除旧文件
                    ResourceRepository::delete($source_file);
                    ResourceRepository::create($target_url , $target_file , 'local' , 0 , 0);
                }

                // 更新预览图地址
                $src = $target_url;

                ImageModel::updateById($v->id , [
                    'original_src'  => $original_src ,
                    'src'           => $src ,
                ]);
                ResourceRepository::used($original_src);
                ResourceRepository::used($src);
                $success_count++;
                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }
        }
        return [
            'message' => "总处理数：{$total}；失败数：{$failed_count}；成功数：{$success_count}" ,
            'data' => json_encode(compact('total' , 'failed_count' , 'success_count'))
        ];
    }

    public function failed(Exception $e)
    {
        ImageProjectModel::updateById($this->imageProjectId , [
            // 处理失败
            'process_status' => -1 ,
            'process_message' => $e->getMessage() ,
            'process_data' => $e->getTraceAsString() ,
        ]);
    }
}
