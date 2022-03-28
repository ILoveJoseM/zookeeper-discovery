<?php
/**
 * Created by PhpStorm.
 * User: chenyu
 * Date: 2022-03-25
 * Time: 11:31
 */

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
