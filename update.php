<?php
$config = require './config.php';
include './vendor/autoload.php';
include './helpers.php';

@ignore_user_abort(1);
@set_time_limit(0);
$client = new Predis\Client([
    'scheme' => 'tcp',
    'host' => '127.0.0.1',
    'port' => 6379
]);

while (1) {
    $id = $client->lpop('myTask');
    if ($id) {
        synchronous($config, $id);
    }
    if (!$id) {
        sleep(1);
    }
}


//同步函数
function synchronous($config, $id)
{
    $ecshop = $config['ecshop'];
    $redBird = $config['redBird'];
    $data = Database($ecshop)::table('ecs_goods')->where('goods_id', $id)->first();

    if (!$data) {
        echo 'ecshop指定数据不存在，无法同步!';
        return;
    }
    $data = [
        'goods_id' => $data->goods_id,
        'goods_sn' => $data->goods_sn,
        'goods_name' => $data->goods_name,
        'goods_price' => $data->shop_price,
        'goods_detail' => $data->keywords,
        'goods_max_price' => $data->market_price,
        'type_id' => $data->cat_id,
        'store_id' => 17,
        'goods_img' => 'http://ovuhrv8k3.bkt.clouddn.com/images/processing/' . $data->goods_sn . '/' . $data->goods_sn . '001.JPG',
        'goods_images' => (function () use ($data) {
            $str = '';
            for ($i = 1; $i < 8; $i++) {
                $url = 'http://ovuhrv8k3.bkt.clouddn.com/images/processing/' . $data->goods_sn . '/' . $data->goods_sn . '00' . $i . '.JPG';
                $str .= $url;
            }
            return $str;
        })(),
        'user_id' => 1052,
        'market_price' => $data->market_price,
        'tb_url' => $data->tb_url,
        'goods_img2' => $data->goods_img,
        'goods_img3' => $data->original_img,
        'updated_at' => $data->updated_at
    ];
    $redBirdTable = Database($redBird)::table('shop_goods')->where('goods_id', $id);
    if ($redBirdTable->count() > 0) {
        echo '更新了goods_id=' . $id . '的数据!' . PHP_EOL;
        $redBirdTable->update(array_except($data, 'goods_id'));
    } else {
        echo '插入了goods_id=' . $id . '的数据!' . PHP_EOL;
        $redBirdTable->insert($data);
    }
}


function Database($config)
{
    $database = [
        'driver' => 'mysql',
        'host' => $config['host'],
        'database' => $config['database'],
        'username' => $config['user'],
        'password' => $config['password'],
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix' => '',
    ];

    $capsule = new \Illuminate\Database\Capsule\Manager();
    $capsule->addConnection($database);
    $capsule->setAsGlobal();
    $capsule->bootEloquent();
    return $capsule;
}