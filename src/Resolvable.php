<?php

namespace Phpobia;

trait Resolvable
{

    /* @var Need[] $PhpobiaNeeds */
    protected $PhpobiaNeeds=array();
    /* @var Analyser $PhpobiaAnalyser   */
    protected $PhpobiaAnalyser=null;

    protected $PhpobiaDelayedResolve=false;

    /* get a property value */
    public function &phpobiaGetData($key=null){
        throw new Exception(get_class($this).' '.__FUNCTION__.' notimplemented');
        return false;
    }

    /* check if a property exists */
    public function phpobiaHasData($key=null){
        throw new Exception(get_class($this).' '.__FUNCTION__.' notimplemented');
        return false;
    }

    /* set a property */
    public function phpobiaSetData($key=null,$value=null){
        throw new Exception(get_class($this).' '.__FUNCTION__.' notimplemented');
        return false;
    }

    /* return array of keys */
    public function phpobiaVariableList(){
        throw new Exception(get_class($this).' '.__FUNCTION__.' notimplemented');
        return array();
    }

    public function phpobiaObjectTypeAlias(){
        return array(get_class($this));
    }

    public function phpobiaSetAnalyzer(&$resolver){
        $this->PhpobiaAnalyser=&$resolver;
    }

    function needs($list=array()){
        if(is_string($list) && strlen($list)>0){
            $list=array(new Need($list));
        }elseif(is_array($list)){
            foreach($list as $k=>$v){
                if(is_string($v)){
                    $list[$k]=new Need($list);
                }
            }
        }

        $counter=0;

        foreach($list as $index=>$n){
            $needed=0;
            foreach($n->fields() as $i=>$field){
                if(!isset($this->$field)){
                    $needed++;
                }
            }
            if($needed>0){
                $this->phpobiaAddNeed($n);
                $counter++;
            }
        }

        if($counter>0 && $this->PhpobiaDelayedResolve==true){
            $this->resolve();
        }

    }

    protected function phpobiaAddNeed($n){
        $this->PhpobiaNeeds[]=$n;
    }

    protected function phpobiaDropNeeds($list=array()){
        if(is_string($list) && strlen($list)>0){
            $list=array($list);
        }
        foreach($list as $k=>$v){
            unset($this->PhpobiaNeeds[$v]);
        }
    }

    public function unresolvedCount(){
        return count($this->PhpobiaNeeds);
    }

    public function hasUnresolved(){
        return 0==count($this->PhpobiaNeeds);
    }

    public function &getNeeds(){
        return $this->PhpobiaNeeds;
    }

    public function resolve(){
        if(count($this->PhpobiaNeeds)==0){
            return true;
        }
        if(!is_object($this->PhpobiaAnalyser)){
            throw new Exception('PhpobiaAnalyser not an object');
        }
        if(!method_exists($this->PhpobiaAnalyser,'resolve')){
            throw new Exception('PhpobiaAnalyser has no resolve method');
        }
        $resolveOptions=array('model'=>$this);
        $result=$this->PhpobiaAnalyser->resolve($resolveOptions);
        foreach($this->PhpobiaNeeds as $index=>$need){
            if(true==$need->isDone()){
                unset($this->PhpobiaNeeds[$index]);
            }
        }
        return $result;
    }

}