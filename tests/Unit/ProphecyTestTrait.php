<?php

namespace Tests\Unit;

use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\Prophet;

trait ProphecyTestTrait
{
    /**
     * @var Prophet
     */
    private $prophecy;

    protected function up()
    {
    }

    protected function down()
    {
    }

    final protected function init()
    {
        $this->prophecy = new Prophet();
    }

    final protected function deinit()
    {
        $this->prophecy->checkPredictions();
    }

    /**
     * @param  string         $classOrInterface
     * @return ObjectProphecy
     */
    protected function prophesize($classOrInterface = null)
    {
        return $this->prophecy->prophesize($classOrInterface);
    }

    /**
     * @param  string $classOrInterface
     * @return object
     */
    protected function dummy($classOrInterface)
    {
        return $this->prophesize($classOrInterface)->reveal();
    }
}
