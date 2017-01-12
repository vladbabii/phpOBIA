<?php

namespace Phpobia;

trait LoggerAware
{
    protected $logger;

    public function setLogger(&$logger){
        $this->logger=&$logger;
    }

    protected function log($level, $message, array $context = array()){
        if(is_object($this->logger) && method_exists($this->logger,'log')) {
            $this->logger->log($level, $message, $context);
        }
    }
}