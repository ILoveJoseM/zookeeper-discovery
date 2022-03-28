<?php
/**
 * Created by PhpStorm.
 * User: chenyu
 * Date: 2022-03-27
 * Time: 16:07
 */

namespace JoseChan\Cloud\Zookeeper\Discovery\LoadBalanced;


use JoseChan\Cloud\Commons\Client\LoadBalancer\LoadBalancedClient;
use JoseChan\Cloud\Commons\Client\LoadBalancer\Rule;

abstract class AbstractRule implements Rule
{
    /** @var LoadBalancedClient $loadBalance */
    protected $loadBalance;

    public function setLoadBalance(LoadBalancedClient $loadBalance)
    {
        $this->loadBalance = $loadBalance;
    }
}
