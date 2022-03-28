<?php
/**
 * Created by PhpStorm.
 * User: chenyu
 * Date: 2022-03-25
 * Time: 11:40
 */

namespace JoseChan\Cloud\Zookeeper\Discovery\LoadBalanced;


use JoseChan\Cloud\Commons\Client\LoadBalancer\LoadBalancedClient;
use JoseChan\Cloud\Commons\Client\LoadBalancer\Rule;

class RandomRule extends AbstractRule
{
    public function choose($serviceNodes)
    {
        if (empty($serviceNodes)) {
            return null;
        }

        shuffle($serviceNodes);
        return $serviceNodes[0];
    }

}
