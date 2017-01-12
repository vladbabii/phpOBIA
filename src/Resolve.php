<?php

namespace Phpobia;

class Resolve
{

    public function __construct($options=array()){
        if(!is_array($options)){
            $options=array();
        }
        if(method_exists($this,'log') && property_exists($this,'logger')) {
            if (isset($options['logger'])) {
                $this->logger = &$options['logger'];
            }
        }
    }

    static public $requires = array();
    static public $canSolveCheckOrder = array('object', 'properties', 'custom');
    static public $provides = array();

    static public function getRequires(){
        return static::$requires;
    }
    static public function getSolveCheckOrder(){
        return static::$canSolveCheckOrder;
    }
    static public function getProvides(){
        return static::$provides;
    }

    /*
     * @param Need $need
     * @param Resolvable $model
     *
     * @return ResolverResult
     */
    static function &canSolve(&$need, &$model, $vars = null)
    {
        if (!is_array($vars)) {
            $vars = $model->phpobiaVariableList();
        }
        $result = new ResolverResult;

        foreach (static::$canSolveCheckOrder as $resTypeIndex => $resType) {
            switch ($resType) {
                case 'custom':
                    $cr = self::canBeResolved($need, $model, $vars);
                    if ($cr === true || $cr === false) {
                        $result->setBool($cr);
                        $result->setReason($resType);
                        return $result;
                    }
                    break;

                case 'properties':
                case 'object':
                    if (isset(static::$requires) && is_array(static::$requires)) {
                        switch ($resType) {
                            case 'properties':
                                if (isset(static::$requires['properties'])) {
                                    foreach (static::$requires['properties'] as $varIndex => $varName) {
                                        if (!in_array($varName, $vars)) {
                                            $result->setBool(false);
                                            $result->setReason($resType.' missing '.$varName);
                                            return $result;
                                        }
                                    }
                                }
                                break;

                            case 'object':
                                if (isset(static::$requires['object'])) {
                                    $types=$model->phpobiaObjectTypeAlias();
                                    $typeFound=false;
                                    foreach($types as $typeIndex=>$type){
                                        if(in_array($type,static::$requires['object'])){
                                            $typeFound=true;
                                            break;
                                        }
                                    }
                                    if($typeFound===false){
                                        $result->setBool(false);
                                        $result->setReason($resType);
                                        return $result;
                                    }
                                }
                                break;
                        }

                    }
                    break;

                case true:
                case 'true':
                    $result->setBool(true);
                    $result->setReason($resType);
                    return $result;
                    break;

                case false:
                case 'false':
                    $result->setBool(false);
                    $result->setReason($resType);
                    return $result;
                    break;

                default:
                    // log unknown type ??
                    break;
            }
        }

        $result->setBool(false);
        $result->setReason('end');
        return $result;
    }

    /** Override this in extends */
    static function canBeResolved($need, $model, $vars)
    {
        return null;
    }

    /**
     * Override this in extends
     * @param Need $need
     * @param Resolvable $model
     */
    public function &resolve(&$need, &$model)
    {
        $result = new ResolverResult;
        $result->setBool(false);
        $result->setReason('notimplemented');
        return $result;
    }

    /**
     * Override this in extends when needed
     */
    public function createdInCache()
    {
    }

    /**
     * Override this in extends when needed
     */
    public function alreadyInCache()
    {
    }
}