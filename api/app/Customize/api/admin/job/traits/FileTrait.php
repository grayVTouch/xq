<?php


namespace App\Customize\api\admin\job\traits;


use function core\format_path;

trait FileTrait
{
    protected function generateRealPath(string $dir , string $path): string
    {
        return format_path(rtrim($dir , '/') . '/' . ltrim($path , '/'));
    }
}
