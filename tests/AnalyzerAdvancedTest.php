<?php
use PHPUnit\Framework\TestCase;

require __DIR__.'/classes/RobotTester.php';
require __DIR__.'/classes/RobotQResolve.php';

class AnalyzerAdvancedTestCase extends TestCase {

    public function testCreation(){

        $SimpleLogger = new \Phpobia\SimpleLogger();

        $HiveMind = new \Phpobia\AnalyzerAdvanced(array(
            'logger' => &$SimpleLogger
        ));

        $this->assertInstanceOf('Phpobia\Analyzer',$HiveMind);

        $i=1;
        while(class_exists('RobotQResolveValue'.$i)){
            $HiveMind->register('RobotQResolveValue'.$i);
            $i++;
        }
        $i=2;
        while(class_exists('RobotQResolveValueEven'.$i)){
            $HiveMind->register('RobotQResolveValueEven'.$i);
            $i+=4;
        }

        $hal = new RobotTester(array('logger'=>$SimpleLogger));
        $hal->phpobiaSetAnalyzer($HiveMind);
        $hal->inject('name','HAL 3000');
        $hal->inject('value_1',101);

        //echo PHP_EOL;
        //echo $hal.PHP_EOL;

        $hal->needs('value_6');
        $start = microtime(true);
        $hal->resolve();
        $time_elapsed_secs = microtime(true) - $start;
        echo PHP_EOL.'Resolve timing: '.$time_elapsed_secs.PHP_EOL.PHP_EOL;

        //echo $hal.PHP_EOL;

    }

}
