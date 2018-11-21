<?php
$mysql = require './config.php';
include './vendor/autoload.php';
include './helpers.php';

ini_set('memory_limit', '1280M');
@set_time_limit(0);
@ignore_user_abort(1);

$client = new Predis\Client([
    'scheme' => 'tcp',
    'host' => '127.0.0.1',
    'port' => 6379
]);

$ecshop = $mysql['ecshop'];
$redBird = $mysql['redBird'];

$ecshopConn = Sqlconn($ecshop);
$redBirdConn = Sqlconn($redBird);
$tag = false;
$client->flushall();

if (true) {
    //ECshop id集合
    $ecshopResult = mysqli_query($ecshopConn, 'select goods_id,updated_at from ecs_goods');
//    mysqli_close($ecshopConn);

    while ($row = mysqli_fetch_array($ecshopResult, MYSQLI_NUM)) {
        $client->sadd('ecshop', $row[0]);
        $client->sadd('ecshopUpdate', $row[0] . '*' . $row[1]);
    }

//redBird id集合
    $redBirdResult = mysqli_query($redBirdConn, 'select goods_id,updated_at from shop_goods');
//    mysqli_close($redBirdConn);
    while ($row = mysqli_fetch_array($redBirdResult, MYSQLI_NUM)) {
        $client->sadd('redBird', $row[0]);
        $client->sadd('redBirdUpdate', $row[0] . '*' . $row[1]);
    }
}

//求两个数据表的差集
$xxxx = $client->sdiff(['ecshop', 'redBird']);
//
//Database($redBird)::table('shop_goods')->whereIn('goods_id', $xxxx)->delete();

$ids = implode(',', $xxxx);

$data = mysqli_query($ecshopConn, "select * from sanshiyuan.ecs_goods where goods_id in ($ids)");
if ($data) {
    $dataArray = [];
    while ($row = mysqli_fetch_array($data, MYSQLI_ASSOC)) {
        array_push($dataArray, [
            'goods_id' => $row['goods_id'],
            'goods_sn' => $row['goods_sn'],
            'goods_name' => $row['goods_name'],
            'goods_price' => $row['shop_price'],
            'goods_detail' => $row['keywords'],
            'goods_max_price' => $row['market_price'],
            'type_id' => $row['cat_id'],
            'store_id' => 17,
            'goods_img' => 'http://ovuhrv8k3.bkt.clouddn.com/' . $row['goods_thumb'],
            'goods_imgs' => (function () use ($row) {
                $arr = [];
                for ($i = 1; $i < 8; $i++) {
                    $url = 'http://ovuhrv8k3.bkt.clouddn.com/images/processing/' . $row['goods_sn'] . '/' . $row['goods_sn'] . '00' . $i . '.JPG';
                    array_push($arr, $url);
                }
                return implode(',', $arr);
            })(),
            'user_id' => 1052,
            'market_price' => $row['market_price'],
            'tb_url' => $row['tb_url'],
            'goods_img2' => 'http://ovuhrv8k3.bkt.clouddn.com/' . $row['goods_img'],
            'goods_img3' => 'http://ovuhrv8k3.bkt.clouddn.com/' . $row['original_img'],
            'updated_at' => $row['updated_at'],
            'goods_stock' => $row['goods_number']
        ]);
    }
    $table = Database($redBird)::table('shop_goods');
    $shop_goods_sku = Database($redBird)::table('shop_goods_sku');
    $shop_goods_spec = Database($redBird)::table('shop_goods_spec');

    foreach ($dataArray as $k) {
        $table->insert($k);
        $shop_goods_sku->insert([
            'goods_id' => $k['goods_id'],
            'spec_ids' => $k['goods_id'],
            'price' => $k['goods_price'],
            'market_price' => $k['market_price'],
            'spec_text' => "数量:1个",
            'stock' => 1,
            'store_id' => 17
        ]);
        $shop_goods_spec->insert([
            'id' => $k['goods_id'],
            'goods_id' => $k['goods_id'],
            'spec_group' => "数量",
            'spec_value' => "1个",
            'store_id' => 17,
            'create_time' => "1533353650",
            'update_time' => "1533353650"
        ]);
        echo '正在迁移数据 goods_id=' . $k['goods_id'] . ' 的数据!' . PHP_EOL;
    }
}
//求需要更新的数据项
$yyyy = $client->sdiff(['ecshopUpdate', 'redBirdUpdate']);

if (!$yyyy) {
    echo '没有需要更新的数据' . PHP_EOL;
}

//开始更新
//$redBird1 = Database($redBird)::table('shop_goods');
//$ecshop1 = Database($ecshop)::table('ecs_goods');

$willUpdateData = [];
foreach ($yyyy as $k) {
    $id = explode('*', $k)[0];
    array_push($willUpdateData, $id);
//    $data = Database($ecshop)::table('ecs_goods')->where('goods_id', $id)->first();
}
$dataSet = Database($ecshop)::table('ecs_goods')->whereIn('goods_id', $willUpdateData)->get();

$redBirdDataBase = Database($redBird);

foreach ($dataSet as $data) {
    $redBirdDataBase::table('shop_goods')->where('goods_id', $data->goods_id)->update([
//        'goods_id' => $data->goods_id,
        'goods_sn' => $data->goods_sn,
        'goods_name' => $data->goods_name,
        'goods_price' => $data->shop_price,
        'goods_detail' => $data->keywords,
        'goods_max_price' => $data->market_price,
        'type_id' => $data->cat_id,
        'store_id' => 17,
        'goods_img' => 'http://ovuhrv8k3.bkt.clouddn.com/' . $data->goods_thumb,
        'goods_imgs' => (function () use ($data) {
            $arr = [];
            for ($i = 1; $i < 8; $i++) {
                $url = 'http://ovuhrv8k3.bkt.clouddn.com/images/processing/' . $data->goods_sn . '/' . $data->goods_sn . '00' . $i . '.JPG';
                array_push($arr, $url);
            }
            return implode(',', $arr);
        })(),
        'user_id' => 1052,
        'market_price' => $data->market_price,
        'tb_url' => $data->tb_url,
        'goods_img2' => 'http://ovuhrv8k3.bkt.clouddn.com/' . $data->goods_img,
        'goods_img3' => 'http://ovuhrv8k3.bkt.clouddn.com/' . $data->original_img,
        'updated_at' => $data->updated_at,
        'goods_stock' => $data->goods_number
    ]);
    $redBirdDataBase::table('shop_goods_sku')->where('goods_id', $data->goods_id)->update([
        'spec_ids' => $data->goods_id,
        'price' => $data->shop_price,
        'market_price' => $data->market_price,
        'spec_text' => "数量:1个",
        'stock' => 1,
        'store_id' => 17
    ]);
    $redBirdDataBase::table('shop_goods_spec')->where('goods_id', $data->goods_id)->update([
        'id' => $data->goods_id,
        'spec_group' => "数量",
        'spec_value' => "1个",
        'store_id' => 17,
        'create_time' => "1533353650",
        'update_time' => "1533353650"
    ]);
    echo '正在更新 goods_id=' . $data->goods_id . '的数据' . PHP_EOL;
}

function Sqlconn($config)
{
    $conn = mysqli_connect($config['host'], $config['user'], $config['password'], $config['database'], 3306);
    mysqli_query($conn, "SET NAMES UTF8");
    if (!$conn) {
        die('Could not connect: ' . mysqli_error());
    } else {
        return $conn;
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


foreach ($client->smembers('redBirdUpdate') as $k) {
    $client->srem('redBirdUpdate', $k);
}
foreach ($client->smembers('ecshopUpdate') as $k) {
    $client->srem('ecshopUpdate', $k);
}
foreach ($client->smembers('ecshop') as $k) {
    $client->srem('ecshop', $k);
}
foreach ($client->smembers('ecshopUpdate') as $k) {
    $client->srem('redBird', $k);
}

