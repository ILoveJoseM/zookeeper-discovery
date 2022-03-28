<?php
/**
 * Created by PhpStorm.
 * User: chenyu
 * Date: 2022-03-23
 * Time: 11:26
 */

namespace JoseChan\Cloud\Commons\Client\Discovery\Tests;

use PHPUnit\Framework\TestCase;

class ZookeeperTest extends TestCase
{
    /** @var \Zookeeper */
    public $client;
    public function testCommon()
    {
//        try{
            //链接zookeeper
            $this->client = new \Zookeeper("127.0.0.1:2181");

            $this->client->get("/zget", [$this, "watcher"]);
//        } catch (\Exception $exception){
//            echo $exception->getMessage();
//        }


        while (true){
            echo ".\n";
            sleep(1);
        }
    }

    public function watcher($type, $state, $key){
        var_dump($type);
        var_dump($state);
        var_dump($key);

        if ($type == 3) {
            var_dump($this->client->get('/zget'));
            // Watcher gets consumed so we need to set a new one
            $this->client->get('/zget', array($this, 'watcher'));
        }
    }
}
