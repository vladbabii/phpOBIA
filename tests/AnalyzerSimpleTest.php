<?php
use PHPUnit\Framework\TestCase;

require __DIR__.'/classes/SimpleTestClasses.php';

class AnalyzerSimpleTestCase extends TestCase {

    public function testCreation(){


        $AI = new \Phpobia\AnalyzerSimple();

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

        $alice->needs('death_ts');
        $alice->resolve();

        $this->assertEquals(1531350624,$alice->stuff['death_ts']);

        $bob->needs('death_ts');
        $bob->resolve();

        $this->assertEquals(1397551584,$bob->stuff['death_ts']);
    }

}
