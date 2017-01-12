<?php

namespace Phpobia;

class Need {

    use LoggerAware;

    protected $needs;
    protected $state;
    protected $satisfy;

    public function __construct($options=array()){
        $this->setFailed();
        $this->setState();
        $this->satisfy='full';
        $needs=array();
        if(is_string($options)){
            $options=array('fields'=>array($options));
        }
        if(is_array($options)){
            if(isset($options['fields'])){
                $this->needs=$options['fields'];
            }
        }
        if(isset($options['logger'])){
            $this->logger=&$options['logger'];
        }
    }

    public function propertiesList(){
        if(!is_array($this->needs)){
            return array();
        }
        return $this->needs;
    }

    public function setFailed(){ $this->state='failed'; }
    public function setDone(){ $this->state='done'; }

    public function satisfiedBy($provides=array()){
        if(!is_array($provides)){
            return false;
        }
        if(isset($this->needs) && count($this->needs)>0) {
            if (isset($provides['properties']) && is_array($provides['properties']) && count($provides['properties'])>0) {
                $todo=array_flip($this->needs);
                foreach($provides['properties'] as $index=>$name){
                    unset($todo[$name]);
                }
                if($this->satisfy=='full' && count($todo)!==0){
                    return false;
                }
            }else{
                return false;
            }
        }
        return true;
    }

    public function notFinished(){
        if(!$this->isFailed() && !$this->isDone()){
            return true;
        }
    }

    public function state(){
        return $this->state;
    }

    public function isFailed(){
        return ($this->state=='failed');
    }

    public function isDone(){
        return ($this->state=='done');
    }

    public function setState($state='need'){
        $this->state=$state;
    }

    public function fields(){
        return $this->needs;
    }

}