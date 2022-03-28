## 基于zookeeper的服务注册与发现

#### 安装

````shell
composer require jose-chan/cloud-zookeeper-discovery
````

#### 发布

````bash
php artisan vendor:publish --tag=zookeeper-discovery
````

修改配置项

````php
<?php

return [
    "service_name" => env("APP_NAME", "service"), // 当前服务名称
    "service_host" => env("APP_URL", "127.0.0.1:80"), // 当前服务访问地址
    "host" => "127.0.0.1:2181", // zookeeper服务器，集群用逗号分隔
    "register_path" => "/service", // 当前服务注册到哪个节点
    "discovery" => [
        "service_watches" => [
            "/service", // 监听哪个节点下的服务，支持多个监控器
//            "/service2", // 监听哪个节点下的服务，支持多个监控器
        ],
    ],
    "load_balanced_rule" => \JoseChan\Cloud\Zookeeper\Discovery\LoadBalanced\RandomRule::class // 负载均衡器
];


````

#### 服务注册

````php
<?php

// 服务
$service = app()->make(\JoseChan\Cloud\Zookeeper\Discovery\ServiceInstance::class);

// 注册器
/** @var \JoseChan\Cloud\Zookeeper\Discovery\Registry\ZookeeperRegistry $register */
$register = app()->make(\JoseChan\Cloud\Commons\Client\ServiceRegistry\ServiceRegistry::class);

// 注册
$register->register($service);

````

#### 服务发现

````php
<?php
/** @var \JoseChan\Cloud\Zookeeper\Discovery\Discovery\ZookeeperDiscovery $discovery 服务发现器 */
$discovery = app()->make(\JoseChan\Cloud\Commons\Client\Discovery\DiscoveryClient::class);

$discovery->discovery();

var_dump($discovery->getServiceNode());

````

#### 配合guzzle http

````php
<?php
// 创建负载均衡中间件，并注册到client中
$stack = new \GuzzleHttp\HandlerStack();
$stack->setHandler(new \GuzzleHttp\Handler\CurlHandler());
$stack->push($app->make(\JoseChan\Cloud\Zookeeper\Discovery\Middlewares\LoadBalanceMiddleware::class));

$client = new \GuzzleHttp\Client(['handler' => $stack]);

````


