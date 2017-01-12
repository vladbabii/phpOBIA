<?php

class RobotTester {
    public $stuff;
    public function __construct($options=array()){
        $this->stuff=array();
        if(isset($options['logger'])){
            $this->logger=&$options['logger'];
        }
    }
    public function inject($key,$value){
        $this->stuff[$key]=$value;
    }
    public function __toString(){
        $string=array();
        $string[]='('.$this->stuff['name'].')';
        foreach($this->stuff as $k=>$v){
            if($k!=='name'){
                if(is_string($v) || is_numeric($v)) {
                    $string[] = $k . '=' . $v;
                }elseif(is_bool($v)){
                    $string[] = $k . '=' . ($v == true ? 'true' : 'false');
                }
            }
        }
        $string=implode(' ',$string);
        return $string;
    }

    /* Phpobia necessary code */
    use \Phpobia\LoggerAware;
    use \Phpobia\Resolvable;
    public function &phpobiaGetData($key=null){
        return $this->stuff[$key];
    }
    public function phpobiaSetData($key=null,$value=null){
        $this->stuff[$key]=$value;
        return true;
    }
    public function phpobiaHasData($key=null){
        return array_key_exists($key,$this->stuff);
    }
    public function phpobiaVariableList(){
        return array_keys($this->stuff);
    }
    public function phpobiaObjectTypeAlias(){
        return array('human','humanoid',get_class($this));
    }
    /* End of Phpobia necessary code */
}