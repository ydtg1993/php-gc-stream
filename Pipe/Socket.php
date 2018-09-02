<?php
/**
 * Created by PhpStorm.
 * User: ydtg1
 * Date: 2018/8/20
 * Time: 23:08
 */

namespace Pipe;

use Mockery\Exception;

set_time_limit(60);
class Socket
{
    const BUFFER_LEN = 4096;
    private $ip;
    private $port;
    private $socket;
    protected static $boot = [];

    public function __construct($ip,$port)
    {
        $this->ip = $ip;
        $this->port = $port;
        $this->start();
    }

    public function start()
    {
        $this->socket = socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
        if($this->socket < 0){
            throw new Exception('create socket fail');
        }

        $result = socket_connect($this->socket,$this->ip,$this->port);
        if($result < 0){
            throw new Exception('socket connect server fail');
        }
        socket_set_nonblock($this->socket);
        return $this;
    }

    public function setHead($head)
    {
        return $this;
    }

    public function write($file)
    {
        if(!is_file($file)){
            throw new Exception('not found stream file');
        }

        $handel = fopen($file,"r");
        if($handel){
            while(($buffer = fgets($handel,self::BUFFER_LEN)) !== false){
                Filter::analyze($buffer);
                if($buffer) {
                    echo $buffer . "<br>";
                }
            }

            fclose($handel);
        }
    }

    public function setBoot()
    {
        //var_dump(self::$boot);exit;
    }

    public function __destruct()
    {
        $this->setBoot();
        socket_close($this->socket);
    }
}

class Filter extends Socket
{
    private static $at = ['=>','->'];
    public static function analyze(&$content)
    {
        $temp = strstr($content,self::$at[1]);
        if($temp){
            $content = $temp;
        }else{
            $content = strstr($content,self::$at[0]);
        }

        if($content) {
            $content_array = explode(' ', $content);
            $file = end($content_array);
            $num = strrpos($file,':');
            $file = substr($file,0,$num);
            if(false == in_array($file,self::$boot) && is_file($file)){
                self::$boot[] = $file;
            }
        }
    }
}