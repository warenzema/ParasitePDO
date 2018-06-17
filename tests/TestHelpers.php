<?php

trait TestHelpers {
    public function providerTrueFalse1()
    {
        return [
            [true],
            [false],
        ];
    }
    
    public function providerTrueFalse2()
    {
        return $this->mergeDataProviders(
            $this->providerTrueFalse1(),
            $this->providerTrueFalse1()
        );
    }
    
    public function providerTrueFalse3()
    {
        return $this->mergeDataProviders(
            $this->providerTrueFalse2(),
            $this->providerTrueFalse1()
        );
    }
    
    public function mergeDataProviders($provider1,$provider2)
    {
        $provider = [];
        foreach ($provider1 as $testCaseArgs1) {
            foreach ($provider2 as $testCaseArgs2) {
                $allArgs = $testCaseArgs1;
                foreach ($testCaseArgs2 as $testCase2Arg) {
                    $allArgs[] = $testCase2Arg;
                }
                $provider[] = $allArgs;
            }
        }
        
        return $provider;
    }
}

