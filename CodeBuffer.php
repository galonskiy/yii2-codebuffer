<?php

namespace galonskiy\codebuffer;

use Yii;
use yii\base\Component;
 
class CodeBuffer extends Component {
	
	private $dbConnection;
	
	public function __construct(){
		
		$this->setConnectionId();
	
	}
	
	private function getOptionalWhere(){
		
		return ' OR validity_at < '.Yii::$app->formatter->asTimestamp('now').' OR attempts_count >= number_attempts';
		
	}
	
	/*
	 * Метод запускается для устанавливания ID коннекта к БД, по умолчанию стандартная.
	 */
	public function setConnectionId($connectionID = 'db'){
		
		$this->dbConnection = Yii::$app->$connectionID;
		
		return $this;
		
	}
	
	/*
	 * $identifier - телефон, e-mail или какой-либо другой способо передачи кода
	 * $sendId - какой угодно ID пользователя или либой другой сущности
	 * $shelfLifeInMinutes - количество минут на подтверждение
	 * $numberOfAttempts - количество попыток для подтверждения
	 *
	 */
    public function set($identifier, $sendId, $numberSigns = 3, $shelfLifeInMinutes = 15, $numberOfAttempts = 3) {
	    
        //TODO надо сделать ограничение по максимально возможному кол-ву символов
        $n1 = 10 ** $numberSigns;
        $n2 = (10 ** ($numberSigns + 1) - 1);
        
        $code = rand($n1, $n2);
        
        $identifierHash = md5($identifier.$sendId);
        $codeHash = md5($code);
        $validatyAt = Yii::$app->formatter->asTimestamp('now + '.$shelfLifeInMinutes.' minute');

        
        //Проверил на 100 записях что удалять одну запись что 100 тратится 3 миллисекунды. По этому сразу очищаем буффер от ненужного хламма
        $this->dbConnection->createCommand()->delete('ga_code_buffer', 'identifier_hash = \''.$identifierHash.'\''.$this->getOptionalWhere())->execute();
      
        if (Yii::$app->db->createCommand('INSERT INTO `ga_code_buffer` (`identifier_hash`, `code_hash`, `validity_at`, `attempts_count`, `number_attempts`) VALUES (\''.$identifierHash.'\', \''.$codeHash.'\', \''.$validatyAt.'\', 0, \''.$numberOfAttempts.'\')')->execute()){
	    	
	    	return $code;
        
        }
	    	
	    return false; 
		    
    }


	/*
	 * Метод валидации промокода
	 */
    public function validate($identifier, $sendId, $code) {
	    
	    $identifierHash = md5($identifier.$sendId);
	    $codeHash = md5($code);
	    
	    /*
		 * Запрашиваем по $identifierHash и ограничениям по сроку жизни и кол-ву проб. 
		 * По коду не запрашиваем потому что надо проставлять кол-во попыток, а если заправшивать сразу через код то кол-во попыток не удастся увеличить.
		 */
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