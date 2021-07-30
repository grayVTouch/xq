<?php


namespace App\Customize\api\web\model;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;

class SystemSettingsModel extends Model
{
    protected $table = 'xq_system_settings';

    public static function getValueByKey(string $key)
    {
        return self::first()->$key;
    }
}
