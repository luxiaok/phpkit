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


#### 应用1：生成二维码

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


#### 应用2：解析IP地址

```php
<?php

$ip = Yii::$app->kit->get_client_ip(); //IPv4，例如：121.51.19.218
$location = Yii::$app->kit->get_ip_location($ip); //中国 广东 深圳 腾讯云

```
