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
     * @var Yii DB conection.
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
     * @param string $connectionID - индетификатор подключения к БД
     *
     * @return this class
     */
    public function setConnectionId($connectionID = 'db'){
        
        $this->dbConnection = Yii::$app->$connectionID;
        
        return $this;
        
    }
    
    /**
     * Generates and save new code in DB.
     *
     * @param string|integer $identifier - телефон, e-mail или какой-либо другой способо передачи кода
     * @param string|integer|null $entityID - какой угодно ID пользователя или либой другой сущности
     * @param integer $lifetimeInMinutes - количество минут на подтверждение
     * @param integer $amountOfAttempts - количество попыток для подтверждения
     *
     * @return string the generated code
     */
    public function generate($identifier, $entityID, $numberOfSymbols = 3, $lifetimeInMinutes = 15, $amountOfAttempts = 3) {
        
        //TODO надо сделать ограничение по максимально возможному кол-ву символов
        $n1 = 10 ** $numberOfSymbols;
        $n2 = (10 ** ($numberOfSymbols + 1) - 1);
        
        $code = rand($n1, $n2);
        
        $identifierHash = md5($identifier.$entityID);
        $codeHash = md5($code);
        $validatyAt = Yii::$app->formatter->asTimestamp('now + '.$lifetimeInMinutes.' minute');

        
        //TODO Проверил на 100 записях что удалять одну запись что 100 тратится 3 миллисекунды. По этому сразу очищаем буффер от ненужного хламма
        $this->dbConnection->createCommand()->delete('ga_code_buffer', 'identifier_hash = \''.$identifierHash.'\''.$this->getOptionalWhere())->execute();
      
        if (Yii::$app->db->createCommand('INSERT INTO `ga_code_buffer` (`identifier_hash`, `code_hash`, `validity_at`, `attempts_count`, `number_attempts`) VALUES (\''.$identifierHash.'\', \''.$codeHash.'\', \''.$validatyAt.'\', 0, \''.$amountOfAttempts.'\')')->execute()){
            
            return $code;
        
        }
            
        return false; 
            
    }

    /**
     * Validate code.
     *
     * @param string|integer $identifier - телефон, e-mail или какой-либо другой способо передачи кода
     * @param string|integer|null $entityID - какой угодно ID пользователя или либой другой сущности
     * @param string|integer $code - код который надо провалидировавть
     * 
     * Запрашиваем по $identifierHash и ограничениям по сроку жизни и кол-ву проб. 
     * По коду не запрашиваем потому что надо проставлять кол-во попыток, а если заправшивать сразу через код то кол-во попыток не удастся увеличить.
     *
     * @return true or false
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