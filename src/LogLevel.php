<?php
namespace Phpobia;

class LogLevel
{
    const EMERGENCY = 'emergency';
    const ALERT     = 'alert';
    const CRITICAL  = 'critical';
    const ERROR     = 'error';
    const WARNING   = 'warning';
    const NOTICE    = 'notice';
    const INFO      = 'info';
    const DEBUG     = 'debug';

    static function numeric($level='debug'){
        switch($level){
            case ('emergency'):     return 0; break;
            case ('alert'):         return 1; break;
            case ('critical'):      return 2; break;
            case ('error'):         return 3; break;
            case ('warning'):       return 4; break;
            case ('notice'):        return 5; break;
            case ('info'):          return 6; break;
            case ('debug'):         return 7; break;
        }
        return null;
    }
}