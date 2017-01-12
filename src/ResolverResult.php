<?php

namespace Phpobia;

class ResolverResult
{

    use LoggerAware;

    protected $resultBool;
    protected $reason;
    protected $removable;
    protected $failed;

    public function __construct($options=array())
    {
        if(is_bool($options)){
            $options['result']=$options;
        }
        $this->setFailed(null);
        $this->setRemoveNeed(false);
        if(isset($options['result'])){
            $this->setBool($options['result']);
        }else{
            $this->setBool(false);
        }
        $this->setReason('');
    }

    public function setRemoveNeed($result = false)
    {
        $this->removable = $result;
    }

    public function isRemovable()
    {
        return $this->removable;
    }

    public function setFailed($failed = true)
    {
        $this->failed = $failed;
    }

    public function setDone($text = 'done'){
        $this->resultBool=true;
        $this->failed=false;
        $this->reason=$text;
    }

    public function failed()
    {
        return $this->failed;
    }

    public function __toString()
    {
        $t = 'Result is [';
        if ($this->resultBool) {
            $t .= 'TRUE';
        } else {
            $t .= 'FALSE';
        }
        $t .= ']';
        if (is_string($this->reason) && strlen($this->reason) > 0) {
            $t .= 'because of [' . $this->reason . ']';
        } else {
            $t .= ' (unknown reason) ';
        }
        return $t;
    }

    public function setReason($text = '')
    {
        $this->reason = $text;
    }

    public function setBool($result = false)
    {
        $this->resultBool = $result;
    }

    public function bool()
    {
        return $this->resultBool;
    }
}