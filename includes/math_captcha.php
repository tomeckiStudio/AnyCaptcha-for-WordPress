<?php
class MathCaptcha {
    private $result;
    private $number1;
    private $number2;
    private $operator;
    private $symbols = array('+', '-', '*');
    
    private static $instance;

    public static function get_instance()
    {
        if (self::$instance == null)
            self::$instance = new self();
		
		if(empty($_SESSION['tsac_math_captcha']))
			self::$instance->new_captcha();
			
        return self::$instance;
    }
	
    public function new_captcha() {
        $this->number1 = rand(0, 10);
        $this->number2 = rand(0, 10);
        $this->operator = $this->symbols[rand(0, (count($this->symbols) - 1))];
        
        $this->get_result();
		
		$_SESSION['tsac_math_captcha'] = $this->result;
    }
	
    private function get_result() {
        switch ($this->operator) {
            case '+':
                $this->result = ($this->number1 + $this->number2);
                break;
            
            case '-':
                $this->result = ($this->number1 - $this->number2);
                break;
            
            case '*':
                $this->result = ($this->number1 * $this->number2);
                break;
        }
    }
    
    public function verify($val) {
        if(empty($val))
            return false;
		
		if (in_array('contact-form-7/wp-contact-form-7.php', get_option('active_plugins'))){
			if(!empty($_POST['tsac_math_captcha_result'])){
				if (hash_hmac('md5', $val, get_option('tsac_hash_key')) == $_POST['tsac_math_captcha_result']) 
					return true;
				else
					return false;
			}
		}
		
		if(empty($_SESSION['tsac_math_captcha']))
			return false;
			
        if ($val == $_SESSION['tsac_math_captcha']) 
            return true;
       
        return false;
    }
	
	public function get_captcha_result(){
		return $_SESSION['tsac_math_captcha'];
	}
   
    public function get_captcha_text() {
        return sprintf("%d %s %d", $this->number1, $this->operator, $this->number2);
    }
	
}
