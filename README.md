PHP Kit
=======
Tools for PHP


## Support for Yii2


#### 配置

```php
<?php

// config/main.php

return [
    'components' => [
        'kit' => [
            'class' => 'phpkit\kit',
        ],
    ]
];
```


#### 应用1：生成随机数

```php
<?php
$len = 12;
$random_str = Yii::$app->kit->gen_random_str($len);
```


#### 应用2：生成二维码

```php
<?php

function actionQrcode(){
    $text = Yii::$app->request->get('text','Hello world!');
    $type = Yii::$app->request->get('type','text'); // text or url
    if ($type=='url') {
        $text = urldecode($text);
    }
    Yii::$app->response->headers->set('Content-type','image/png');
    Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
    Yii::$app->kit->qrcode($text);
}

```


#### 应用3：解析IP地址

```php
<?php

$ip = Yii::$app->kit->get_client_ip(); //IPv4，例如：121.51.19.218
$location = Yii::$app->kit->get_ip_location($ip); //中国 广东 深圳 腾讯云

```

> 使用IP地址解析功能需要下载 `qqwry.dat` 到 `vendor/luxiaok/phpkit/tools` 目录。


#### 应用4：校验商品价格

```php
<?php
$isPrice = Yii::$app->kit->validatePrice(666); // true
$isPrice = Yii::$app->kit->validatePrice(0.01); // true
$isPrice = Yii::$app->kit->validatePrice(0.012); // false
$isPrice = Yii::$app->kit->validatePrice(-1); // false
$isPrice = Yii::$app->kit->validatePrice(0); // true
```


#### 应用5：http请求

```php
<?php
use phpkit/tools/http;

$result = http::get($url,$data);
$result = http::post($url,$data);
$result = http::download($url, $save_path, $filename);

```
