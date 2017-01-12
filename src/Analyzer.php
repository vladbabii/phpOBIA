<?php

namespace Phpobia;

class Analyzer {

    use LoggerAware;

    protected $ResolveList = array();
    protected $ResolveObjectCache = array();
    
    public function __construct($options=array()){
        if(!is_array($options)){
            throw new \Exception('Options expected to be array in constructor of '.get_class($this));
        }
        if(isset($options['logger'])){
            $this->logger=&$options['logger'];
        }
        $this->log(LogLevel::INFO,'Analyzer {class} constructed',array('class'=>get_class($this)));
    }

    public function register($resolveClassName=null){
        if(!in_array($resolveClassName,$this->ResolveList)) {
            $this->ResolveList[] = $resolveClassName;
            $this->log(LogLevel::DEBUG,'Analyzer {class} registered {resolveClass}',array('class'=>get_class($this),'resolveClass'=>$resolveClassName));
        }
    }

    public function hasResolvers(){
        if(count($this->ResolveList)>0){
            return true;
        }
        return false;
    }

    protected function instantiateClass($resClass){
        if(!class_exists($resClass)){
            $this->log(LogLevel::EMERGENCY,'Analyzer {class} cannot instantiate {newclass} because {reason}',array('class'=>get_class($this),'newclass'=>$resClass,'reason'=>'it does not exist'));
            throw new \Exception('classdoesnotexist '.$resClass);
        }
        if(!isset($this->ResolveObjectCache[$resClass])){
            $this->log(LogLevel::DEBUG,'Analyzer {class} instantiated {resolveClass}',array('class'=>get_class($this),'resolveClass'=>$resClass));
            $this->ResolveObjectCache[$resClass]=new $resClass;
            $this->ResolveObjectCache[$resClass]->createdInCache();
        }else{
            $this->ResolveObjectCache[$resClass]->alreadyInCache();
        }
        return true;
    }


    protected function countNeeds($needList){
        $needCount=0;
        foreach($needList as $index=>$need) {
            if(!$need->isFailed() && !$need->isDone()) {
                $needCount++;
            }
        }
        return $needCount;
    }

    /**
     * @param $model Resolvable
     */
    public function resolve(&$options=array()){
        $this->log(LogLevel::EMERGENCY,'Analyzer {class} function {function} {reason}',array('class'=>get_class($this),'function'=>__FUNCTION__,'reason'=>'not implemented'));
        throw new \Exception('notimplementedyet '.get_class($this).' '.__FUNCTION__);
    }
}