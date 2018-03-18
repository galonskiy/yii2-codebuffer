<?php

namespace galonskiy\codebuffer;

use Yii;
use yii\db\Query;

/**
 * CodeBuffer - component for generated and validation SMS, e-mail and other codes.
 *
 * @author Galonskiy Artem <mailbox@galonskiy.com>
 *
 */
class CodeBuffer{

    /**
     * @var yii\db\connection
     */
    private $dbConnection;

    private $tableName = 'ga_code_buffer';

    public function __construct(){

        $this->setConnectionId();

    }

    /**
     * Change default Yii DB conection
     *
     * @param string $connectionID
     *
     * @return CodeBuffer
     */
    public function setConnectionId($connectionID = 'db'){

        $this->dbConnection = Yii::$app->$connectionID;

        return $this;

    }

    /**
     * Change default Yii DB conection
     *
     * @param int $numberOfSymbols
     *
     * @return string
     */
    public static function generateRandomCode(int $numberOfSymbols): string
    {
        $numberOfSymbols--;

        //TODO add max number of symbols
        $n1 = pow(10, $numberOfSymbols);
        $n2 = pow(10, ($numberOfSymbols + 1)) - 1;

        return (string)rand($n1, $n2);
    }

    /**
     * Generates and save new code in DB.
     *
     * @param string $identifier - phone number, e-mail or some other method of data transmission
     * @param string $entityID
     * @param integer $numberOfSymbols
     * @param integer $lifetimeInMinutes
     * @param integer $amountOfAttempts
     *
     * @return string the generated code
     */
    public function generate(string $identifier, string $entityID, int $numberOfSymbols = 4, int $lifetimeInMinutes = 15, int $amountOfAttempts = 3): string
    {
        $code = $this->generateRandomCode($numberOfSymbols);

        $identifierHash = md5($identifier.$entityID);
        $codeHash = md5($code);
        $validatyAt = Yii::$app->formatter->asTimestamp('now + '.$lifetimeInMinutes.' minute');

        $this->delete($identifierHash);

        $insertCommand = $this->dbConnection->createCommand()->insert($this->tableName,[
            'identifier_hash' => $identifierHash,
            'code_hash' => $codeHash,
            'validity_at' => $validatyAt ,
            'attempts_count' => 0,
            'number_attempts' => $amountOfAttempts
        ]);

        if ($insertCommand->execute()){
            return $code;
        }

        return false;
    }

    /**
     * Validate code.
     *
     * @param string|integer $identifier
     * @param string|integer|null $entityID
     * @param string|integer $code
     *
     * @param null $error
     * @return true or false
     */
    public function validate(string $identifier, string $entityID, string $code, &$error = null): bool
    {

        $identifierHash = md5($identifier.$entityID);
        $codeHash = md5($code);

        $bufferRow = (new Query())
            ->from($this->tableName)
            ->where(['identifier_hash' => $identifierHash])
            ->andWhere('validity_at > ' . Yii::$app->formatter->asTimestamp('now'))
            ->andWhere('attempts_count < number_attempts')
            ->one();

        if ($bufferRow !== false){

            if ($bufferRow['code_hash'] === $codeHash){

                $this->delete($identifierHash);

                return true;

            } else {

                if ($bufferRow['number_attempts'] > $bufferRow['attempts_count']){

                    $attemptsCount = $bufferRow['attempts_count'] + 1;
                    $numberAttemptsLeft = $bufferRow['number_attempts'] - $attemptsCount;

                    Yii::$app->db->createCommand()->update($this->tableName, [ 'attempts_count' => $attemptsCount ], 'identifier_hash = \''.$identifierHash.'\'')->execute();

                    $error = 'Wrong code. ' . $numberAttemptsLeft . ' attempts left.';

                } else {

                    $this->delete($identifierHash);
                    $error = 'Attempts are over';
                }
            }

        } else {

            $error = 'Identifier not found.';
        }

        return false;
    }

    /**
     * Delete row.
     *
     * @param string $identifierHash
     *
     */
    private function delete(string $identifierHash)
    {
        $this->dbConnection->createCommand()->delete($this->tableName, ['identifier_hash' => $identifierHash])->execute();
    }

    /**
     * Delete all old row.
     *
     */
    public function deleteAll()
    {

        $this->dbConnection->createCommand()->delete($this->tableName, 'validity_at < '.Yii::$app->formatter->asTimestamp('now').' OR attempts_count >= number_attempts')->execute();
    }
}
