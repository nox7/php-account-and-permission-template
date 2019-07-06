<?php
	/**
	* Class RoleCollection
	*
	* @author Garet C. Green
	*/

	require_once("Database.php");
	require_once("Role.php");

	/**
	* Used to fetch multiple instances of a Role
	*/
	class RolesCollection{

		const SORT_ALPHABETICAL = 0;

		/**
		* Fetches an array of all Roles on the system
		*
		* @param int $sort
		* @return array Filled with Role objects
		*/
		public static function getAll(int $sort = self::SORT_ALPHABETICAL){
			$roles = [];
			$db = SQLDatabase::connect();

			$orderClause = "";

			if ($sort === self::SORT_ALPHABETICAL){
				$orderClause = "ORDER BY `name` ASC";
			}

			$result = $db->query("
				SELECT * FROM `roles`
				$orderClause
			");

			foreach($result as $row){
				$role = new Role(0);
				$role->setRow($row);
				$roles[] = $role;
			}

			return $roles;
		}

	}
?>
