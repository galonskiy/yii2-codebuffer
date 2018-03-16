<?php

use yii\db\Migration;

/**
 * Class m171122_154933_ga_code_buffer
 */
class m171122_154933_ga_code_buffer extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

		$this->createTable('ga_code_buffer', [
            'identifier_hash' => $this->string(32),
            'code_hash' => $this->string(32)->notNull(),
            'validity_at' => $this->integer()->notNull(),
            'attempts_count' => $this->integer(1)->notNull()->defaultValue(0),
            'number_attempts' => $this->integer(1)->notNull(),
            'PRIMARY KEY(identifier_hash)',
        ], $tableOptions);
    }

    public function down()
    {
        $this->dropTable('ga_code_buffer');
    }
}
