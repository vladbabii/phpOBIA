<?php

for($i=1;$i<=10;$i++) {

eval("
    class RobotQResolveValue".$i." extends \\Phpobia\\Resolve
    {
        static public \$canSolveCheckOrder = array('properties', 'true');
        static public \$requires = array(
            'properties' => array(
                'value_".$i."'
            )
        );
        static public \$provides = array(
            'properties' => array(
                'value_".($i+1)."'
            )
        );

        public function &resolve(&\$need, &\$model)
        {
            \$result = new \\Phpobia\\ResolverResult;
            \$result->setFailed();
            \$result->setReason('notdone');
            try {
                \$v = \$model->phpobiaGetData('value_".$i."');
                \$v++;
                \$model->phpobiaSetData('value_".($i+1)."',\$v);
                \$result->setDone();
            } catch (Exception \$e) {
            }
            return \$result;
        }
    }
    ");
}

for($i=2;$i<=12;$i+=4) {

    eval("
    class RobotQResolveValueEven".$i." extends \\Phpobia\\Resolve
    {
        static public \$canSolveCheckOrder = array('properties', 'true');
        static public \$requires = array(
            'properties' => array(
                'value_".$i."'
            )
        );
        static public \$provides = array(
            'properties' => array(
                'value_".($i+4)."'
            )
        );

        public function &resolve(&\$need, &\$model)
        {
            \$result = new \\Phpobia\\ResolverResult;
            \$result->setFailed();
            \$result->setReason('notdone');
            if(".$i." != 2){
                try {
                    \$v = \$model->phpobiaGetData('value_".$i."');
                    \$v++;
                    \$model->phpobiaSetData('value_".($i+4)."',\$v);
                    \$result->setDone();
                } catch (Exception \$e) {
                }
            }
            return \$result;
        }
    }
    ");
}