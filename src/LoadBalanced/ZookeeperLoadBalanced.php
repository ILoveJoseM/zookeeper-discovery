<?php
/**
 * Created by PhpStorm.
 * User: chenyu
 * Date: 2022-03-25
 * Time: 11:06
 */

namespace JoseChan\Cloud\Zookeeper\Discovery\LoadBalanced;


use JoseChan\Cloud\Commons\Client\LoadBalancer\LoadBalancedClient;
use JoseChan\Cloud\Commons\Client\LoadBalancer\Rule;

/**
 * 负载均衡器
 * Class ZookeeperLoadBalanced
 * @package JoseChan\Cloud\Zookeeper\Discovery\LoadBalanced
 */
class ZookeeperLoadBalanced implements LoadBalancedClient
{
    /** @var array $services 服务列表 */
    private $services;
    /** @var Rule 选择规则 */
    private $rule;

    /**
     * ZookeeperLoadBalanced constructor.
     * @param $rule
     */
    public function __construct(Rule $rule)
    {
        $this->rule = $rule;
        $this->rule->setLoadBalance($this);
    }

    /**
     * 添加服务
     * @param $services
     */
    public function addServices($services)
    {
        array_merge($this->services, $services);
    }

    /**
     * 设置服务列表
     * @param $services
     */
    public function setServices($services)
    {
        $this->services = $services;
    }

    /**
     * 选择节点
     * @param $service
     * @return mixed
     */
    public function choose($service)
    {
        if(isset($this->services[$service])){
            $service = $this->rule->choose($this->services[$service]);
        }

        return $service;
    }
}
