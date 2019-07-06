<?php
	/**
	* Class Role
	*
	* @author Garet C. Green
	*/

	require_once("Database.php");

	/**
	* Creates, manipulates, and verifies roles and their permissions
	*/
	Class Role{

		/** @var string The name of the database table containing the roles */
		const ROLE_TABLE_NAME = "roles";

		/** @var string The name of the database table containing the permissions for roles */
		const PERMISSIONS_TABLE_NAME = "role_permissions";

		/** @var array A list of available permissions on this system. This is for reference only and is not enforced when saving permissions */
		const AVAILABLE_PERMISSIONS = [
			"Example",
		];

		/** @var array List of descriptions for each permission */
		const PERMISSION_DESCRIPTIONS = [
			"Example"=>"A description of the example permission.",
		];

		/** @var array The current instance's role SQL row */
		private $row = [];

		/**
		* Creates a new role
		*
		* @param string $roleName
		* @return int The new ID of the role
		*/
		public static function createNewRole(string $roleName){
			$db = SQLDatabase::connect();
			$statement = $db->prepare("
				INSERT INTO `" . self::ROLE_TABLE_NAME . "`
				(`name`, `creation_timestamp`)
				VALUES
				(?, unix_timestamp())
			");
			$statement->bind_param("s", $roleName);
			$statement->execute();
			$statement->store_result();

			return $statement->insert_id;
		}

		/**
		* Gets a Role instance form a string name
		*
		* @param string $roleName
		* @return Role
		*/
		public static function getRoleFromName(string $roleName){
			$db = SQLDatabase::connect();
			$statement = $db->prepare("
				SELECT * FROM `" . self::ROLE_TABLE_NAME . "`
				WHERE `name` = ?
			");
			$statement->bind_param("s", $roleName);
			$statement->execute();
			$result = $statement->get_result();

			if ($result->num_rows > 0){
				$row = $result->fetch_assoc();
				$role = new Role(0);
				$role->setRow($row);
				return $role;
			}else{
				return new self(0);
			}
		}

		/**
		* Creates a new instance with the public $row set
		*
		* @param int $roleID
		* @return Role
		*/
		public function __construct(int $roleID){

			if ($roleID !== 0){
				$db = SQLDatabase::connect();
				$statement = $db->prepare("
					SELECT * FROM `" . self::ROLE_TABLE_NAME . "`
					WHERE `id` = ?
				");
				$statement->bind_param("i", $roleID);
				$statement->execute();
				$result = $statement->get_result();

				if ($result->num_rows > 0){
					$row = $result->fetch_assoc();
					$this->row = $row;
				}
			}
		}

		/**
		* Sets the current Role instance's permissions
		*
		* This wipes all the current permissions for the role and resets them to the provided $permissions values
		*
		* @param array $permissions
		* @return void
		*/
		public function setPermissions(array $permissions){
			$roleID = $this->getID();
			$db = SQLDatabase::connect();
			$statement = $db->prepare("
				DELETE FROM `" . self::PERMISSIONS_TABLE_NAME . "`
				WHERE `role_id` = ?
			");
			$statement->bind_param("i", $roleID);
			$statement->execute();

			if (count($permissions) > 0){
				$insertionClause = "";

				foreach($permissions as $permission){
					$insertionClause .= "($roleID, \"" . $db->escape_string($permission) . "\"),";
				}

				// Trim off the trailing comma
				$insertionClause = rtrim($insertionClause, ",");

				$permissionInsertion = $db->query("
					INSERT INTO `" . self::PERMISSIONS_TABLE_NAME . "`
					(`role_id`, `permission_name`)
					VALUES
					$insertionClause
				");
			}
		}

		/**
		* Checks whether the current Role instance has a permission
		*
		* @param string $permission
		* @return bool
		*/
		public function hasPermission(string $permission){
			$roleID = $this->getID();
			$db = SQLDatabase::connect();
			$statement = $db->prepare("
				SELECT `id` FROM `" . self::PERMISSIONS_TABLE_NAME . "`
				WHERE `role_id` = ? AND `permission_name` = ?
			");
			$statement->bind_param("is", $roleID, $permission);
			$statement->execute();
			$statement->store_result();

			return $statement->num_rows > 0;
		}

		/**
		* Returns whether or not the current Role instance is valid and in existence
		*
		* @return bool
		*/
		public function exists(){
			return !empty($this->row);
		}

		/**
		* Set the Role instance's row
		*
		* @return int
		*/
		public function setRow(array $sqlRow){
			$this->row = $sqlRow;
		}

		/**
		* Gets the Role's ID
		*
		* @return int
		*/
		public function getID(){
			return $this->exists() ? (int) $this->row['id'] : 0;
		}

		/**
		* Gets the Role's name
		*
		* @return string
		*/
		public function getName(){
			return $this->exists() ? $this->row['name'] : "";
		}

		/**
		* Get the Role's permissions
		*
		* @return array
		*/
		public function getPermissions(){
			$permissions = [];
			if ($this->exists()){
				$db = SQLDatabase::connect();
				$statement = $db->prepare("
					SELECT `permission_name` FROM `" . self::PERMISSIONS_TABLE_NAME . "`
					WHERE `role_id` = ?
				");
				$roleID = $this->getID();
				$statement->bind_param("i", $roleID);
				$statement->execute();
				$result = $statement->get_result();

				while ($row = $result->fetch_assoc()){
					$permissions[] = $row['permission_name'];
				}

			}

			return $permissions;
		}

	}
