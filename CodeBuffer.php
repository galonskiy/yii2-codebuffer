<?php

namespace galonskiy\codebuffer;

use Yii;

/**
 * CodeBuffer - component for generated and validation SMS, e-mail and other codes.
 *
 * @author Galonskiy Artem <mailbox@galonskiy.com>
 * 
 */
class CodeBuffer{
    
    /**
     * @var Yii DB connection.
     */            
    private $dbConnection;
    
    public function __construct(){
        
        $this->setConnectionId();
    
    }
    
    private function getOptionalWhere(){
        
        return ' OR validity_at < '.Yii::$app->formatter->asTimestamp('now').' OR attempts_count >= number_attempts';
        
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
     * Generates and save new code in DB.
     *
     * @param string|integer $identifier - phone number, e-amil or some other method of data transmission
     * @param string|integer|null $entityID
     * @param int $numberOfSymbols
     * @param integer $lifetimeInMinutes
     * @param integer $amountOfAttempts
     *
     * @return string the generated code
     * @throws \yii\db\Exception
     */
    public function generate($identifier, $entityID, $numberOfSymbols = 3, $lifetimeInMinutes = 15, $amountOfAttempts = 3) {
        
        //TODO add max number of symbols
        $n1 = pow(10, $numberOfSymbols);
        $n2 = pow(10, ($numberOfSymbols + 1) - 1);
        
        $code = rand($n1, $n2);
        
        $identifierHash = md5($identifier.$entityID);
        $codeHash = md5($code);
        $validatyAt = Yii::$app->formatter->asTimestamp('now + '.$lifetimeInMinutes.' minute');


        $this->dbConnection->createCommand()->delete('ga_code_buffer', 'identifier_hash = \''.$identifierHash.'\''.$this->getOptionalWhere())->execute();
      
        if (Yii::$app->db->createCommand('INSERT INTO `ga_code_buffer` (`identifier_hash`, `code_hash`, `validity_at`, `attempts_count`, `number_attempts`) VALUES (\''.$identifierHash.'\', \''.$codeHash.'\', \''.$validatyAt.'\', 0, \''.$amountOfAttempts.'\')')->execute()){
            
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
     * @return true or false
     * @throws \yii\db\Exception
     */
    public function validate($identifier, $entityID, $code) {
        
        $identifierHash = md5($identifier.$entityID);
        $codeHash = md5($code);

        if ($bufferRow = Yii::$app->db->createCommand('SELECT * FROM ga_code_buffer WHERE identifier_hash = \''.$identifierHash.'\' AND validity_at > '.Yii::$app->formatter->asTimestamp('now').' AND attempts_count < number_attempts')->queryOne()){
            
            if ($bufferRow['code_hash'] === $codeHash){
                
                Yii::$app->db->createCommand()->delete('ga_code_buffer', 'identifier_hash = \''.$identifierHash.'\'')->execute();
                
                return true;
                
            } else {
                
                $attemptsCount = $bufferRow['attempts_count'] + 1;
                
                if ($bufferRow['number_attempts'] > $bufferRow['attempts_count']){
                    
                    Yii::$app->db->createCommand()->update('ga_code_buffer', [ 'attempts_count' => $attemptsCount ], 'identifier_hash = \''.$identifierHash.'\'')->execute();
                                    
                } else {
                    
                    Yii::$app->db->createCommand()->delete('ga_code_buffer', 'identifier_hash = \''.$identifierHash.'\'')->execute();

                }
            }
        
        }
            
        return false;
        
    }
 
}