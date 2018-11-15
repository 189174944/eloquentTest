<?php


include '../vendor/autoload.php';
$mysql = require '../config.php';
$redBird = $mysql['redBird'];
$redBirdConn = Sqlconn($redBird);


$client = new Predis\Client([
    'scheme' => 'tcp',
    'host' => '127.0.0.1',
    'port' => 6379,
]);


//求两个数据表的差集
//$xxxx = $client->sdiff(['redBird', 'ecshop']);
//dd($xxxx);

foreach ( $client->sdiff(['redBird', 'ecshop']) as $item){
    echo 'ECSHOP数据id='.$item.'迁移到红鸟数据库成功!'.PHP_EOL;
}

echo '增量同步完成!'.PHP_EOL;

//TODO

echo '删除同步完成!'.PHP_EOL;

//TODO
echo '修改同步完成!'.PHP_EOL;


//insert into boa.shop_goods (goods_id,goods_sn,goods_name,goods_price,goods_detail,goods_max_price,type_id)
// select goods_id,goods_sn,goods_name,shop_price,keywords,market_price,cat_id from sanshiyuan.ecs_goods;
//




//取出差异数据

//插入数据库
//插入一条，redis中删除指定集合中的一条元素

//清空集合

//complete


function Sqlconn($config)
{
    $conn = mysqli_connect($config['host'], $config['user'], $config['password'], $config['database'], 3306);
    if (!$conn) {
        die('Could not connect: ' . mysqli_error());
    } else {
        return $conn;
    }
}
