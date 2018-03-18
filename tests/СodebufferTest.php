<?php

namespace yiiunit\codebuffer;

use Yii;
use galonskiy\codebuffer\CodeBuffer;

/**
 * Unit test for [[\galonskiy\codebuffer]].
 *
 * @group console
 */
class CodebufferTest extends TestCase
{

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        if (defined('HHVM_VERSION')) {
            // https://github.com/facebook/hhvm/issues/1447
            $this->markTestSkipped('Can not test on HHVM because require is cached.');
        }

        $this->mockApplication();
    }

    public function testRandomCodeGeneration()
    {
        $numberOfSymbols = 4;

        $newCode = CodeBuffer::generateRandomCode($numberOfSymbols);

        $this->assertTrue(is_string($newCode));
        $this->assertTrue(strlen($newCode) === $numberOfSymbols);

        $newNewCode = CodeBuffer::generateRandomCode($numberOfSymbols);

        $this->assertTrue($newCode !== $newNewCode);

    }
}
