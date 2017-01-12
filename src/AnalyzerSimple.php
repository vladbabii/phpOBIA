<?php

namespace Phpobia;

class AnalyzerSimple extends Analyzer {

    public function resolve(&$options=array()){
        if(!isset($options['model'])){
            throw new Exception('modelnotpassed');
        }
        $model=&$options['model'];
        $vars=$model->phpobiaVariableList();
        ksort($vars);
        $varHash=md5(serialize($vars));

        $resolverCache=array();

        $changes=true;

        $needList=$model->getNeeds();
        $needCount=$this->countNeeds($needList);

        while(
            true === $changes
            && count($needList)>0
            && $needCount>0
        ){
            $changes=false;

            foreach($needList as $index=>$need) {
                $need=&$needList[$index];

                if(isset($needList[$index]) && $need->notFinished()) {

                    /* @var Need $need */
                    foreach ($this->ResolveList as $resIndex => $resClass) {
                        if (
                            isset($needList[$index])
                            && $need->notFinished()
                            && $need->satisfiedBy($resClass::$provides)
                        ) {
                            /*
                             * @var $resClass Resolve
                             * @var $canResult ResolverResult
                             * @var $resResult ResolverResult
                             */
                            $canResult = $resClass::canSolve($need, $model, $vars);
                            if (true === $canResult->bool()) {
                                $this->instantiateClass($resClass);
                                $resResult = $this->ResolveObjectCache[$resClass]->resolve($need, $model);
                                // echo($resResult . PHP_EOL);
                                //if (true === $resResult->isRemovable()) {
                                //   unset($needList[$index]);
                                //}

                                $vars=$model->phpobiaVariableList();
                                ksort($vars);
                                $oldVarHash=$varHash;
                                $varHash=md5(serialize($vars));;
                                if($oldVarHash!==$varHash){
                                    $changes=true;
                                }
                            }
                        }
                    }
                }
            }

            if($changes==true) {
                $needList = $model->getNeeds();
            }
            $needCount=$this->countNeeds($needList);
        }
    }


}