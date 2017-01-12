<?php

namespace Phpobia;

class AnalyzerAdvancedChain {

    use LoggerAware;

    public $properties=array();
    public $steps=array();
    public $need=array();
    public $used=array();

    public function __construct(){
        if(isset($options['logger'])){
            $this->logger=&$options['logger'];
        }
    }

    function addProperty($name){
        $this->properties[$name]=true;
        if(isset($this->need[$name])){
            unset($this->need[$name]);
        }
    }

    function addNeed($name){
        $this->need[$name]=true;
        if(isset($this->properties[$name])){
            unset($this->properties[$name]);
        }
    }

    function phpobiaVariableList(){
        return array_keys($this->properties);
    }

    function __toString()
    {
        $str='';
        $str.=' properties('.implode(array_keys($this->properties),',').') ';
        $str.=' need('.implode(array_keys($this->need),',').') ';
        if(count($this->steps)>0){
            $str.=' steps(';
            foreach($this->steps as $stepClass=>$values){
                $str.=$stepClass;
                if(count($values)>0) {
                    $str .= ':'.implode($values, ',');
                }
                $str.=';';
            }
            $str=rtrim($str,';');
            $str.=') ';
        }
        $str=str_replace('  ',' ',$str);
        return trim($str);
    }
}