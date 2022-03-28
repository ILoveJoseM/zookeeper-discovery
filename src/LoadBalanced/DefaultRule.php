<?php
/**
 * Created by PhpStorm.
 * User: chenyu
 * Date: 2022-03-25
 * Time: 12:19
 */

namespace JoseChan\Cloud\Zookeeper\Discovery\LoadBalanced;


use JoseChan\Cloud\Commons\Client\LoadBalancer\LoadBalancedClient;
use JoseChan\Cloud\Commons\Client\LoadBalancer\Rule;

class DefaultRule extends AbstractRule
{
    public function choose($serviceNodes)
    {
        // TODO: Implement choose() method.
    }

}
