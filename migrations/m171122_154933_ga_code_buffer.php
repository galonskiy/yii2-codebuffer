<?php

use yii\db\Migration;

/**
 * Class m171122_154933_ga_code_buffer
 */
class m171122_154933_ga_code_buffer extends Migration
{
    

    
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
		$this->createTable('ga_code_buffer', [
            'identifier_hash' => $this->string(32),
            'code_hash' => $this->string(32)->notNull(),
            'validity_at' => $this->integer(11)->notNull(),
            'attempts_count' => $this->integer(1)->notNull()->defaultValue(0),
            'number_attempts' => $this->integer(1)->notNull(),
            'PRIMARY KEY(identifier_hash)',
        ]);
    }

    public function down()
    {
        $this->dropTable('ga_code_buffer');
    }
}
