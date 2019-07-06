<?php
	/**
	* Backend account class
	*
	* @author Garet C. Green
	*/

	require_once("Database.php");
	require_once("Role.php");

	/**
	* Creates account and sets/gets data from them.
	*/
	class Account{

		/** @var string The table name accounts will use */
		const ACCOUNTS_TABLE_NAME = "users";

		/** @var array The cookie name to store in browsers for login account usage */
		const LOGIN_COOKIE_NAME = "sg_elpsycongroo";

		/** @var array The current Account instance's SQL row */
		private $row;

		/**
		* Creates an Account object from an SQL row
		*
		* @param array $tableRow
		*/
		public function __construct(array $tableRow){
			$this->row = $tableRow;
		}

		/**
		* Creates a new account in the database
		*
		* @param string $username
		* @param string $email
		* @param string $firstName
		* @param string $lastName
		* @param string $password Unhashed password
		* @return int The new user's ID
		*/
		public static function createNew(string $username, string $email, string $firstName, string $lastName, string $password){
			$db = SQLDatabase::connect();

			$passwordHashed = password_hash($password, PASSWORD_DEFAULT);

			$statement = $db->prepare("
				INSERT INTO `" . self::ACCOUNTS_TABLE_NAME . "`
				(`username`, `email`, `first_name`, `last_name`, `password`, `creation_timestamp`)
				VALUES
				(?,?,?,?,?,unix_timestamp())
			");
			$statement->bind_param("sssss", $username, $email, $firstName, $lastName, $passwordHashed);
			$statement->execute();
			$statement->store_result();

			return $statement->insert_id;
		}

		/**
		* Fetches an account by username
		*
		* @param string $username
		* @return Account
		*/
		public static function getAccountByUsername(string $username){
			$db = SQLDatabase::connect();
			$statement = $db->prepare("
				SELECT * FROM `" . self::ACCOUNTS_TABLE_NAME . "`
				WHERE `username` = ?
				LIMIT 1
			");
			$statement->bind_param("s", $username);
			$statement->execute();
			$result = $statement->get_result();

			if ($result->num_rows > 0){
				$row = $result->fetch_assoc();
				return new self($row);
			}else{
				return new self([]);
			}
		}

		/**
		* Determines if the current Account object exists as a real user in the database
		*
		* @return bool
		*/
		public function exists(){
			return isset($this->row) && is_array($this->row) && !empty($this->row);
		}

		/**
		* Determines if an account's assigned role has a permission
		*
		* @param string $permission
		* @param bool
		*/
		public function hasPermission(string $permission){
			$role = Role::getRoleFromName($this->getRole());

			// No role, so cannot possibly have the permission
			if (!$role->exists()){
				return false;
			}

			return $role->hasPermission($permission);
		}

		/**
		* Get the Account's username
		*
		* @return string
		*/
		public function getUsername(){
			return $this->exists() ? $this->row['username'] : "";
		}

		/**
		* Get the Account's role
		*
		* @return string
		*/
		public function getRole(){
			return $this->exists() ? $this->row['role'] : "";
		}

		/**
		* Get the Account's first name
		*
		* @return string
		*/
		public function getFirstName(){
			return $this->exists() ? $this->row['first_name'] : "";
		}

		/**
		* Get the Account's last name
		*
		* @return string
		*/
		public function getLastName(){
			return $this->exists() ? $this->row['last_name'] : "";
		}

		/**
		* Gets Account's full name (combination of first and last)
		*
		* @return string
		*/
		public function getName(){
			return $this->exists() ? $this->row['first_name'] . " " . $this->row['last_name'] : "";
		}

		/**
		* Gets Account's email
		*
		* @return string
		*/
		public function getEmail(){
			return $this->exists() ? $this->row['email'] : "";
		}

		/**
		* Gets the creation time of the Account
		*
		* @return int
		*/
		public function getCreationTime(){
			return $this->exists() ? (int) $this->row['creation_timestamp'] : 0;
		}
	}
