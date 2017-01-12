<?php
use PHPUnit\Framework\TestCase;

class AnalyzerSimpleTestCase extends TestCase {

    public function testCreation(){


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

    }

}
