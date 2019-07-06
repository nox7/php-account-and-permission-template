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
		* Creates an Account object from an account ID
		*
		* @param int $accountID
		* @param bool $findDeletedAccounts If true, will also return an account row that is marked as having been "deleted"
		* @return Account
		*/
		public function __construct(int $accountID, bool $findDeletedAccounts = false){
			if ($accountID !== 0){
				$deletedFlag = !$findDeletedAccounts ? 0 : 1;
				$db = SQLDatabase::connect();
				$statement = $db->prepare("
					SELECT * FROM `" . self::ACCOUNTS_TABLE_NAME . "`
					WHERE `id` = ? AND `marked_as_deleted` = ?
					LIMIT 1
				");
				$statement->bind_param("ii", $accountID, $deletedFlag);
				$statement->execute();
				$result = $statement->get_result();

				if ($result->num_rows > 0){
					$row = $result->fetch_assoc();
					$this->row = $row;
				}
			}
		}

		/**
		* Creates a new account in the database
		*
		* @param string $username
		* @param string $email
		* @param string $firstName
		* @param string $lastName
		* @param string $password Unhashed password
		* @param string $roleName The role the new account will have
		* @return int The new user's ID
		*/
		public static function createNew(string $username, string $email, string $firstName, string $lastName, string $password, string $roleName){
			$db = SQLDatabase::connect();

			$passwordHashed = password_hash($password, PASSWORD_DEFAULT);

			$statement = $db->prepare("
				INSERT INTO `" . self::ACCOUNTS_TABLE_NAME . "`
				(`username`, `email`, `first_name`, `last_name`, `password`, `creation_timestamp`, `role`)
				VALUES
				(?,?,?,?,?,unix_timestamp(),?)
			");
			$statement->bind_param("ssssss", $username, $email, $firstName, $lastName, $passwordHashed, $roleName);
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
				$account = new self(0);
				$account->setRow($row);
				return $account;
			}else{
				return new self(0);
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
		* Sets the SQL row of the Account instance
		*
		* @param array $row
		* @return void
		*/
		public function setRow(array $row){
			$this->row = $row;
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
		* Marks the account as "deleted"
		*
		* This is useful when you want the account to be inaccessible, but for recording or logging reasons still need information about the ID of this account. This flag, in your application, should be checked to make sure this account cannot be logged into or profiles shown.
		*
		* @param void
		*/
		public function flagAsDeleted(){
			$db = SQLDatabase::connect();
			$statement = $db->prepare("
				UPDATE `" . self::ACCOUNTS_TABLE_NAME . "`
				SET `marked_as_deleted` = 1
				WHERE `id` = ?
			");
			$accountID = $this->getID();
			$statement->bind_param("i", $accountID);
			$statement->execute();
		}

		/**
		* Get the Account's ID
		*
		* @return int
		*/
		public function getID(){
			return $this->exists() ? (int) $this->row['id'] : "";
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
