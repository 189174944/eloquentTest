<?php
$mysql = require './config.php';
include './vendor/autoload.php';
include './helpers.php';

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
    $ecshopResult = mysqli_query($ecshopConn, 'select goods_id from ecs_goods');
//    mysqli_close($ecshopConn);
    while ($row = mysqli_fetch_array($ecshopResult, MYSQLI_NUM)) {
        $client->sadd('ecshop', $row[0]);
    }

//redBird id集合
    $redBirdResult = mysqli_query($redBirdConn, 'select goods_id from shop_goods');
//    mysqli_close($redBirdConn);
    while ($row = mysqli_fetch_array($redBirdResult, MYSQLI_NUM)) {
        $client->sadd('redBird', $row[0]);
    }
}
//die();
//求两个数据表的差集
$xxxx = $client->sdiff(['ecshop', 'redBird']);

foreach ($xxxx as $k) {
    $data = mysqli_query($ecshopConn, "select * from sanshiyuan.ecs_goods where goods_id=$k ");
    $data1 = mysqli_fetch_array($data, MYSQLI_ASSOC);
    Database($redBird)::table('shop_goods')->insert([
        'goods_id' => $data1['goods_id'],
        'goods_sn' => $data1['goods_sn'],
        'goods_name' => $data1['goods_name'],
        'goods_price' => $data1['shop_price'],
        'goods_detail' => $data1['keywords'],
        'goods_max_price' => $data1['market_price'],
        'type_id' => $data1['cat_id'],
        'store_id'=>17,
        'goods_img'=>$data1['goods_thumb'],
        'goods_images'=>$data1['goods_img'],
        'user_id'=>1052,
        'market_price'=>$data1['market_price'],
        'tb_url'=>$data1['tb_url'],
        'goods_img2'=>$data1['goods_img'],
        'goods_img3'=>$data1['original_img'],
    ]);
}
//dd($result);


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


//
class Database
{
    private $instance;

    public function getInstance()
    {
        if ($this->instance != null) {
            return $this->instance;
        }
        return new self();
    }

    public function RedConnection()
    {

    }
}