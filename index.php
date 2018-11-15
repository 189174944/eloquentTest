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
//    echo $k;
    $data = mysqli_query($ecshopConn, "select goods_id,goods_sn,goods_name,shop_price,keywords,market_price,cat_id from sanshiyuan.ecs_goods where goods_id=$k ");
    $data1 = mysqli_fetch_array($data, MYSQLI_ASSOC);
    $result222 = implode(',', array_values($data1));

    $a = $data1['goods_id'];
    $b = $data1['goods_sn'];
    $c = $data1['goods_name'];
    $d = $data1['shop_price'];
    $e = $data1['keywords'];
    $f = $data1['market_price'];
    $g = $data1['cat_id'];

    Database($redBird)::table('shop_goods')->insert([
        'goods_id' => $a,
        'goods_sn' => $b,
        'goods_name' => $c,
        'goods_price' => $d,
        'goods_detail' => $e,
        'goods_max_price' => $f,
        'type_id' => $g
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
class Database{
    private $instance;
    public function getInstance(){
        if ($this->instance!=null){
            return $this->instance;
        }
        return new self();
    }
    public function RedConnection(){

    }
}