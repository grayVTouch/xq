<?php


namespace App\Customize\api\web\handler;


use App\Customize\api\web\model\Model;
use Illuminate\Contracts\Pagination\Paginator;
use stdClass;
use function core\convert_object;

class Handler
{
    /**
     * 处理可迭代结构
     *
     * @author running
     *
     * @param mixed $list  可迭代结构
     * @return array
     */
    public static function handleAll($list) :array
    {
        $res = [];
        foreach ($list as $v)
        {
            $res[] = static::handle($v);
        }
        return $res;
    }

    /**
     * 处理分页
     *
     * @author running
     *
     * @param Paginator $paginator
     * @return stdClass
     */
    public static function handlePaginator(Paginator $paginator): stdClass
    {
        $data   = static::handleAll($paginator->items());
        $object = convert_object($paginator);
        $object->data = $data;
        return $object;
    }

    /**
     * @param Model|stdClass|null $model
     * @return stdClass|null
     */
    public static function handle($model): ?stdClass
    {
        if (empty($model)) {
            return null;
        }
        return convert_object($model);
    }
}
