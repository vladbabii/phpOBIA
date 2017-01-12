<?php
require '../src/autoload.php';

class Humanoid {
    public $stuff;

    public function __construct(){
        $this->stuff=array();
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
}

class Human extends Humanoid {

    /* Phpobia necessary code */
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

class AiPredictDeathOfHuman extends Phpobia\Resolve {

    static public $canSolveCheckOrder = array( 'object','properties','true');

    static public $requires = array(
        'properties' => array(
            'birth_ts'
        )
        ,'object'     => array(
            'human'
        )
    );

    static public $provides = array(
        'properties' => array(
            'death_ts'
        )
    );

    /**
     * @var \Phpobia\Resolvable $model
     * @var \Phpobia\Need $need
     */
    public function &resolve( &$need, &$model){
        $result = new \Phpobia\ResolverResult;
        $result->setFailed();
        $result->setReason('notdone');

        try{
            $birth=$model->phpobiaGetData('birth_ts');
            $age_ts = 3600*24*356*29;

            if($model->phpobiaHasData('is_smoker')) {
                $smoker = $model->phpobiaGetData('is_smoker');
                if ($smoker === true){
                    $age_ts = (int)($age_ts * 0.85);
                }
            }

            $model->phpobiaSetData('death_ts',$birth+$age_ts);
            $result->setDone();
        }catch(Exception $e){ }

        return $result;
    }

}


class DumbAI extends \Phpobia\AnalyzerSimple { };

$AI = new DumbAI();

$AI->register('AiPredictDeathOfHuman');

$alice = new Human();
$alice->inject('name','Alice A.');
$alice->inject('birth_ts',@mktime(23,10,24,4,5,1990));
$alice->inject('is_smoker',false);
$alice->phpobiaSetAnalyzer($AI);

$bob = new Human();
$bob->inject('name','Bobby B.');
$bob->inject('birth_ts',@mktime(23,10,24,4,5,1990));
$bob->inject('is_smoker',true);
$bob->phpobiaSetAnalyzer($AI);

echo $alice.PHP_EOL;
echo $bob.PHP_EOL;
echo '--------'.PHP_EOL;

$alice->needs('death_ts');
$alice->resolve();
$bob->needs('death_ts');
$bob->resolve();


echo $alice.PHP_EOL;
echo $bob.PHP_EOL;

echo PHP_EOL.PHP_EOL;