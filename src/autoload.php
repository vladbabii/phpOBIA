<?php
function PhpobiaAutoload($className)
{
    $className = ltrim($className, '\\');
    if ($lastNsPos = strrpos($className, '\\')) {
        $namespace = substr($className, 0, $lastNsPos);
        if($namespace!=='Phpobia'){
          return ;
        }
        $className = substr($className, $lastNsPos + 1);
        $fileName=__DIR__.DIRECTORY_SEPARATOR.$className.'.php';
        if(file_exists($fileName) && is_readable($fileName)){
          require_once($fileName);
        }
    }
}
spl_autoload_register('PhpobiaAutoload');
