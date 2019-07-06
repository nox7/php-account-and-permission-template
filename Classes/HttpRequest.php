<?php
	class HttpRequest{

		public function __construct(){

		}

		public function getPostValue($value, $default){
			if (isset($_POST[$value])){
				return $_POST[$value];
			}else{
				return $default;
			}
		}

		public function getGetValue($value, $default){
			if (isset($_GET[$value])){
				return $_GET[$value];
			}else{
				return $default;
			}
		}

		public function getCookieValue($value, $default){
			if (isset($_COOKIE[$value])){
				return $_COOKIE[$value];
			}else{
				return $default;
			}
		}

		public function getFileValue($value){

			if (empty($_FILES)){
				return false;
			}

			if (!isset($_FILES[$value])){
				return false;
			}

			// tmp_name can be an array for multiple files
			if (is_array($_FILES[$value]['tmp_name'])){
				foreach($_FILES[$value]['tmp_name'] as $tmp_name){
					if (!is_uploaded_file($tmp_name)){
						return false;
					}
				}
			}else{
				if (!is_uploaded_file($_FILES[$value]['tmp_name'])){
					return false;
				}
			}

			// Error can also be an array
			if (is_array($_FILES[$value]['error'])){
				foreach($_FILES[$value]['error'] as $error){
					if ($error != 0){
						return false;
					}
				}
			}else{
				if ($_FILES[$value]['error'] != 0){
					return false;
				}
			}

			return $_FILES[$value];
		}

		public function getIP(){
			if (!empty($_SERVER['HTTP_CLIENT_IP'])){
				// IP is from shared internet
				return $_SERVER['HTTP_CLIENT_IP'];
			}elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
				// IP is from a proxy
				return $_SERVER['HTTP_X_FORWARDED_FOR'];
			}else{
				// IP is from a remote address
				return $_SERVER['REMOTE_ADDR'];
			}
		}

	}
