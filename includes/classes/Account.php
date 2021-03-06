<?php

class Account {

	private $con;
	private $errorArray = array();

	public function __construct($con){
		$this->con = $con;
	}

	public function register($fn, $ln , $un , $num , $em , $pw, $cpw ) {

		$this->validateFirstName($fn);
		$this->validateLastName($ln);
		$this->validateUsername($un);
		$this->validatePhonenumber($num);
		$this->validateEmail($em);
		$this->validatePassword($pw, $cpw);

		if (empty($this->errorArray)) {
			return $this->insertUserDetails($fn, $ln , $un , $num, $em, $pw);

		}
			return false;
	}

	public function login($num, $pw ){

		$pw = hash("sha512", $pw);

		$query = $this->con->prepare("SELECT * FROM users WHERE phonenumber=:num AND  password=:pw");
		$query->bindValue(":num",$num);
		$query->bindValue(":pw",$pw);

		 $query->execute();
		 if ($query->rowCount() == 1) {
		 	return true;
		 }
		 array_push($this->errorArray, Constants::$loginFailed);
		 return false;
	}

	private function insertUserDetails($fn, $ln , $un , $num, $em, $pw){

		$pw = hash("sha512", $pw);

		$query = $this->con->prepare("INSERT INTO users (firstName, lastName , username , phonenumber , email , password) 
			VALUES(:fn, :ln , :un, :num, :em, :pw )");
		$query->bindValue(":fn",$fn);
		$query->bindValue(":ln",$ln);
		$query->bindValue(":un",$un);
		$query->bindValue(":num",$num);
		$query->bindValue(":em",$em);
		$query->bindValue(":pw",$pw);

		return $query->execute();
	}

private function validateFirstName($fn) {
	if (strlen($fn) < 2 || strlen($fn) > 15)  {
	array_push($this->errorArray, Constants::$firstName);
	}
}

private function validateLastName($ln) {
	if (strlen($ln) < 1 || strlen($ln) > 8)  {
	array_push($this->errorArray, Constants::$lastName);
		return;
	}
}

private function validateUsername($un) {
	if (strlen($un) < 2 || strlen($un) > 10)  {
	array_push($this->errorArray, Constants::$username);
	return;
	}
	 $query = $this->con->prepare("SELECT * FROM users WHERE username=:un");
	 $query->bindValue(":un", $un);
	 $query->execute();

	 if($query->rowCount() != 0){
		 array_push($this->errorArray, Constants::$usernameTaken);
	 }
}

private function validatePhoneNumber($num) {
	if (strlen($num) < 10  || strlen($num) > 10)  {
	array_push($this->errorArray, Constants::$number);
	return;
	}
	 $query = $this->con->prepare("SELECT * FROM users WHERE phonenumber=:num");
	 $query->bindValue(":num", $num);
	 $query->execute();

	 if($query->rowCount() != 0){
		 array_push($this->errorArray, Constants::$numberTaken);
	 }
}

private function validateEmail($em) {
	if (!filter_var($em, FILTER_VALIDATE_EMAIL)) {
		 array_push($this->errorArray, Constants::$emailInvalid);
		 return;
	}
	 $query = $this->con->prepare("SELECT * FROM users WHERE email=:em");
	 $query->bindValue(":em", $em);
	 $query->execute();

	 if($query->rowCount() != 0){
		 array_push($this->errorArray, Constants::$emailTaken);
	 }
	
}

private function validatePassword($pw, $cpw) {
	if ($pw != $cpw) {
		 array_push($this->errorArray, Constants::$passwordsDontMatch);
		 return;
	}

	if (strlen($pw) < 6 || strlen($pw) > 25)  {
	array_push($this->errorArray, Constants::$passwordlength);
	}

}

public function getError($error) {
	if(in_array($error, $this->errorArray)){
		return "<span class='errorMessage'>$error</span>";
	}
}
}


?>