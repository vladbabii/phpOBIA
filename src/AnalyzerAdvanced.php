<?php

namespace Phpobia;

class AnalyzerAdvancedStepRunException extends Exception {
    public $step        = 'unknown';
    public $properties  = array();
}

class AnalyzerAdvanced extends Analyzer {

    /**
     * @param array $options
     * @return bool
     */
    public function resolve(&$options=array()){
        if(!isset($options['model'])){
            throw new Exception('modelparammissing');
        }
        if(!isset($options['resolveSession'])){
            $options['resolveSession']=@time().'x'.uniqid();
            $this->log(LogLevel::INFO,'Analyzer {class} resolving {modelClass} session {resolveSession}',array(
                 'class'            => get_class($this)
                ,'modelClass'       => get_class($options['model'])
                ,'resolveSession'   => $options['resolveSession']
            ));
        }else{
            $this->log(LogLevel::INFO,'Analyzer {class} continuing resolving {modelClass} session {resolveSession}',array(
                 'class'            => get_class($this)
                ,'modelClass'       => get_class($options['model'])
                ,'resolveSession'   => $options['resolveSession']
            ));
        }
        if(!isset($options['chains'])) {
            $options['chains'] = array();
            $options['chains'][0] = new \Phpobia\AnalyzerAdvancedChain;

            /* Inject needs */
            $needList = $options['model']->getNeeds();
            $n = array();
            foreach ($needList as $index => &$needObject) {
                $n = array_merge($n, $needObject->propertiesList());
            }
            foreach ($n as $k => $v) {
                $options['chains'][0]->addNeed($v);
            }
            unset($n);

            /* Inject existing properties */
            $current = $options['model']->phpobiaVariableList();
            foreach ($current as $i => $property) {
                $options['chains'][0]->addProperty($property);
            }
            unset($current);
            $options['next']=1;
        }else{
            if(!isset($options['next'])){
                throw new Exception('notsetparam next');
            }
        }
        $result = $this->tryResolve($options);
        return $result;
    }

    /**
     * @param array $options
     * @return bool
     */
    public function tryResolve(&$options=array())
    {
        $model = &$options['model'];
        $resolveSession = &$options['resolveSession'];
        $chains = &$options['chains'];
        $next = &$options['next'];
        $this->log(LogLevel::DEBUG,'Resolve session {resolveSession} chain {chainId} with properties {chainProperties} and needs {chainNeeds}',array(
             'resolveSession'    => $resolveSession
            ,'chainId'           => 0
            ,'chainProperties'   => implode(array_keys($chains[0]->properties),',')
            ,'chainNeeds'        => implode(array_keys($chains[0]->need),',')
        ));

        $ResolveClassList = $this->ResolveList;
        /** @TODO filter by $model->phpobiaObjectTypeAlias() */

        $WorkDone=true;
        $StepCounter=0;
        $SolutionsFound=0;
        while($WorkDone==true){
            $StepCounter++;
            $WorkDone=false;
            $toApply=array();
            $toApplyCreated=array();
            $deleteChainsWithStep=array();
            $deleteChains=array();
            /** TODO: rewrite */
            $modified=array();

            if($StepCounter>1) {
                $this->log(LogLevel::DEBUG, 'Resolve session {resolveSession} step {stepCounter} with {chainCounter} chains', array(
                     'resolveSession' => $resolveSession
                    ,'stepCounter' => $StepCounter
                    ,'chainCounter' => count($chains)
                ));
            }

            /* Find resolvers for each chain, where possible */
            foreach ($ResolveClassList as $resolveIndex => $resolveClass) {

                $this->instantiateClass($resolveClass);
                foreach($chains as $chainIndex=>&$chain) {
                    if(!isset($chains[$chainIndex]->used[$resolveClass])) {

                        $apply = $this->ResolveObjectCache[$resolveClass]->canSolve(
                            $chains[$chainIndex]
                            , $model
                            , $chains[$chainIndex]->phpobiaVariableList()
                        );
                        if ($apply->bool() == true) {
                            if (!isset($toApply[$chainIndex])) {
                                $toApply[$chainIndex] = array();
                            }
                            $toApply[$chainIndex][] = $resolveClass;
                        }
                    }
                }
            }

            /* Apply found resolvers to existing chains, by duplicating chains and applying steps */
            if(count($toApply)>0){
                foreach($toApply as $cindex=>$classes){
                    foreach($classes as $classCounter=>$class){

                        $chains[$cindex]->used[$class]=true;
                        $chains[$next] = clone $chains[$cindex];
                        $chains[$next]->steps[$class]=array();

                        $props = $class::getProvides();
                        if(
                            isset($props['properties'])
                            && is_array($props['properties'])
                            && count($props['properties'])>0
                        ){
                            $chains[$next]->steps[$class]=$props['properties'];
                            foreach($props['properties'] as $index=>$prop){
                                $chains[$next]->addProperty($prop);
                            }
                        }

                        /** TODO: rewrite */
                        $modified[$cindex]=true;
                        $modified[$next]=true;

                        $toApplyCreated[]=$next;
                        $WorkDone = true;
                        $next++;
                    }
                }
            }

            /* Check only modified chains if any are done */
            if(count($toApplyCreated)>0){
                foreach($toApplyCreated as $toIndex=>$chainIndex){
                    if(count($chains[$chainIndex]->need)==0){
                        $this->log(LogLevel::DEBUG, 'Resolve session {resolveSession} found solution {chainId} {chainDetails}', array(
                             'resolveSession'   => $resolveSession
                            ,'chainId'          => $chainIndex
                            ,'chainDetails'     => ''.$chains[$chainIndex]->__toString()
                        ));

                        try {
                            $SolutionsFound++;
                            $result = $this->runChain($chains[$chainIndex], $model);
                            if ($result == true) {
                                $this->log(LogLevel::DEBUG, 'Resolve session {resolveSession} {status}', array(
                                    'resolveSession'    => $resolveSession
                                    ,'status'           => 'success'
                                ));
                                return true;
                            }
                        }catch(AnalyzerAdvancedStepRunException $e){
                            $this->log(LogLevel::WARNING, 'Resolve session {resolveSession} run error at {step} for {properties}', array(
                                 'resolveSession'   => $resolveSession
                                ,'chainId'         => $chainIndex
                                ,'step'            => $e->step
                                ,'properties'      => implode($e->properties,',')
                            ));

                            /* To delete broken chain */
                            $deleteChains[]=$chainIndex;

                            /* To delete chains with the broken step */
                            $deleteChainsWithStep[]=$e->step;

                            /* Remove broken step from further cases */
                            $pos=array_search($e->step,$ResolveClassList);
                            if($pos!==false){
                                unset($ResolveClassList[$pos]);
                            }
                        }
                        unset($chains[$chainIndex]);
                    }
                }
            }

            if(count($deleteChains)>0){
                foreach($deleteChains as $index=>$chainIndex){
                    unset($chains[$chainIndex]);
                    unset($toApplyCreated[$chainIndex]);
                    unset($modified[$chainIndex]);
                }
            }

            if(count($deleteChainsWithStep)>0){
                foreach($deleteChainsWithStep as $index=>$step){
                    foreach($chains as $chainIndex=>&$chain){
                        if(isset($chain->steps[$step])){
                            unset($chains[$chain]);
                            echo ' -- chain '.$chainIndex.PHP_EOL;
                        }
                    }
                }
            }

            /* Cleanup older non-used chains
                @TODO: optimize / rewrite
            */
            foreach($chains as $index=>&$chain){
                if(!isset($modified[$index])){
                    unset($chains[$index]);
                }
            }


            if(count($chains)==0){
                $WorkDone=false;
                $this->log(LogLevel::DEBUG, 'Resolve session {resolveSession} end because {reason}', array(
                     'resolveSession'   => $resolveSession
                    ,'reason'           => 'no more chains'
                ));
            }
        }
        if($SolutionsFound<=0) {
            $this->log(LogLevel::CRITICAL, 'Resolve session {resolveSession} has no solution', array(
                'resolveSession'    => $resolveSession
            , 'reason'              => 'no more chains'
            ));
        }else{
            $this->log(LogLevel::EMERGENCY, 'Resolve session {resolveSession} has no working solution out of {solutionCounter}', array(
                'resolveSession'    => $resolveSession
                ,'solutionFounder'  => $SolutionsFound
            ));
        }
        return false;
    }

    protected function runChain(&$chain,&$model){
        foreach($chain->steps as $resolveClass => $properties){
            $this->instantiateClass($resolveClass);
            $need = new Need(array('fields'=>$properties));
            $result=$this->ResolveObjectCache[$resolveClass]->resolve($need, $model);

            if($result->bool()!==true){
                $exception = new AnalyzerAdvancedStepRunException();
                $exception->step = $resolveClass;
                $exception->properties = $properties;
                throw $exception;
            }
        }
        return true;
    }
}