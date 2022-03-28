<?php
/**
 * Created by PhpStorm.
 * User: chenyu
 * Date: 2022-03-25
 * Time: 11:31
 */

return [
    "host" => "47.92.23.201:2181", // zookeeper服务器，集群用逗号分隔
    "register" => [
        "path" => "/service" // 当前服务注册到哪个节点
    ],
    "discovery" => [
        "service_watches" => [
            ["path" => "/service",], // 监听哪个节点下的服务，支持多个监控器
//            ["path" => "/service2",], // 监听哪个节点下的服务，支持多个监控器
        ],
    ],
    "load_balanced" => [
        "rule" => \JoseChan\Cloud\Zookeeper\Discovery\LoadBalanced\RandomRule::class // 负载均衡器
    ]
];
