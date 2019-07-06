<?php
	/**
	* Multi-account collector
	*
	* @author Garet C. Green
	*/

	require_once("Database.php");
	require_once("Account.php");

	class AccountsCollection{

		const SORT_ALPHABETICAL = 0;

		/**
		* Gets all accounts
		*
		* @param int $sort
		* @return array<Account>
		*/
		public static function getAllAccounts(int $sort = self::SORT_ALPHABETICAL){
			$accounts = [];
			$db = SQLDatabase::connect();

			$orderClause = "";

			if ($sort === self::SORT_ALPHABETICAL){
				$orderClause = "ORDER BY `username` ASC";
			}

			$result = $db->query("
				SELECT * FROM `users`
				$orderClause
			");

			foreach($result as $row){
				$accounts[] = new Account($row);
			}

			return $accounts;
		}
	}
