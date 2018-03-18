<?php

namespace yiiunit\codebuffer;

use Yii;
use yii\console\controllers\MigrateController;
use galonskiy\codebuffer\CodeBuffer;

/**
 * Unit test for [[\galonskiy\codebuffer]].
 *
 * @group console
 */
class CodebufferTest extends TestCase
{
    protected $numberOfSymbols = 4;

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

        $this->mockApplication([
            'components' => [
                'db' => [
                    'class' => 'yii\db\Connection',
                    'dsn' => 'pgsql:host=localhost;dbname=codebuffer_test',
                    'username' => 'postgres',
                    'password' => '',
                    'charset' => 'utf8',
                ]
            ]
        ]);

        $migrationController =  new MigrateController('id', Yii::$app);
        $migrationController->run('up', ['migrationPath' => dirname(__DIR__).'/migrations', 'interactive' => 0]);

    }

    public function testRandomCodeFunction()
    {
        $newCode = CodeBuffer::generateRandomCode($this->numberOfSymbols);

        $this->assertTrue(is_string($newCode));
        $this->assertTrue(strlen($newCode) === $this->numberOfSymbols);

        $newNewCode = CodeBuffer::generateRandomCode($this->numberOfSymbols);

        $this->assertTrue($newCode !== $newNewCode);

    }

    public function testCodeGeneration()
    {
        $newCode = (new CodeBuffer)->generate('XXX', 'XXX');

        $this->assertTrue((new CodeBuffer)->validate('XXX','XXX', $newCode));
    }

    public function testWrongCodeValidation()
    {
        (new CodeBuffer)->generate('XXX', 'XXX');

        $this->assertFalse((new CodeBuffer)->validate('XXX','XXX','not_code', $error));
        $this->assertEquals($error,'Wrong code. 2 attempts left.');

        $this->assertFalse((new CodeBuffer)->validate('XXX','XXX','not_code', $error));
        $this->assertEquals($error,'Wrong code. 1 attempts left.');

        $this->assertFalse((new CodeBuffer)->validate('XXX','XXX','not_code', $error));
        $this->assertEquals($error,'Wrong code. 0 attempts left.');

        $this->assertFalse((new CodeBuffer)->validate('XXX','XXX','not_code', $error));
        $this->assertEquals($error,'Identifier not found.');
    }

    public function testWrongIdentifierValidation()
    {
        $newCode = (new CodeBuffer)->generate('XXX', 'XXX');

        $this->assertFalse((new CodeBuffer)->validate('ZZZ','XXX', $newCode, $error));
        $this->assertEquals($error,'Identifier not found.');
    }

}
