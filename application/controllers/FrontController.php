<?php
class FrontController {
	protected $_controller, $_action, $_params, $_body;
	static $_instance;
    static $config;

    public static function Init($config) {
        if(!empty($config))
            self::$config = $config;
        else
            die("Неверная конфигурация приложения");
        return self::getInstance();
    }

	public static function getInstance() {
		if(!(self::$_instance instanceof self)) 
			self::$_instance = new static();
		return self::$_instance;
	}
	private function __construct(){
		$request = $_SERVER['REQUEST_URI'];
		$splits = explode('/', trim($request,'/'));
		//Определяем контроллер
		$this->_controller = !empty($splits[0]) ? ucfirst($splits[0]).'Controller' : 'IndexController';
		//Определяем действие
		$this->_action = !empty($splits[1]) ? $splits[1].'Action' : 'indexAction';
		//Записываем параметры, если они есть
		if(!empty($splits[2])){
			$keys = $values = array();
				for($i=2, $cnt = count($splits); $i<$cnt; $i++){
					if($i % 2 == 0){
						//Чётное = ключ (параметр)
						$keys[] = $splits[$i];
					}else{
						//Значение параметра;
						$values[] = $splits[$i];
					}
				}
			$this->_params = array_combine($keys, $values);
		}
	}

    /**
     * This function calls current action in current controller
     *
     * @throws Exception Throws Exception in case, when action, controller or interface doesn't exist
     */
    public function run() {
		if(class_exists($this->getController())) {
			$rc = new ReflectionClass($this->getController());
			if($rc->implementsInterface('IController')) {
				if($rc->hasMethod($this->getAction())) {
					$controller = $rc->newInstance();
					$method = $rc->getMethod($this->getAction());
					$method->invoke($controller);
				} else {
					throw new Exception("Action");
				}
			} else {
				throw new Exception("Interface");
			}
		} else {
			throw new Exception("Controller ".$this->getController());
		}
	}

	public function getParams() {
		return $this->_params;
	}
	public function getController() {
		return $this->_controller;
	}
	public function getAction() {
		return $this->_action;
	}
	public function getBody() {
		return $this->_body;
	}
	public function setBody($body) {
		$this->_body = $body;
	}
}	