<?php
	/**
	* Class SQLDatabase
	*
	* @author Garet C. Green
	*/

	require_once(__DIR__ . "/../Configs/DatabaseConfig.php");

	/**
	* Utilities to connect and manipulate an SQL database and its tables
	*/
	class SQLDatabase{

		/**
		* Attempts to establish and return a connection to an SQL database using MySQLi
		*
		* This class relies on the DatabaseConfig.php in the Configs folder for the information needed to connect
		*
		* @throws mysqli_sql_exception
		* @return mysqli
		*/
		public static function connect(){
			mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Set MySQLi to throw exceptions
			$connection = new mysqli(
				DatabaseConfig::DATABASE_HOST,
				DatabaseConfig::DATABASE_USERNAME,
				DatabaseConfig::DATABASE_PASSWORD,
				DatabaseConfig::DATABASE_NAME
			);
			$connection->set_charset("utf-8");
			return $connection;
		}

		/**
		* Runs a CREATE TABLE query with the provided parameters
		*
		* This function checks if a table already exists. If it does, it syncs the provided columns in the argument list.
		*
		* @param string $tableName The name of the table to create if it doesn't exist
		* @param array $columns An array of columns where the index is the column name and the value is the type string such as "varchar(255)"
		* @return void
		*/
		public static function createTable(string $tableName, array $columns){
			$connection = SQLDatabase::connect();

			// Check if the table exists, and sync columns if it does
			try{
				$connection->query("
					SELECT * FROM `$tableName` WHERE 1
				");
				// The table exists, verify all of the columns exist by attempting to add them
				foreach($columns as $columnName=>$columnDataType){
					try{
						$connection->query("ALTER TABLE `$tableName` ADD COLUMN `$columnName` $columnDataType");
					}catch(mysqli_sql_exception $e){
						// The column already existed, oh well
					}
				}
			}catch(mysqli_sql_exception $e){
				// Doesn't exist
				// Prepare columns string
				$columnString = "";
				foreach($columns as $columnName=>$columnDataType){
					if ($columnName !== "PRIMARY KEY"){
						$columnString .= "`$columnName` $columnDataType,";
					}else{
						$columnString .= "$columnName $columnDataType,";
					}
				}
				$columnString = rtrim($columnString, ","); // Trim the last comma
				$result = $connection->query("
					CREATE TABLE IF NOT EXISTS `$tableName` (
						$columnString
					)
				");
			}
			$connection->close();
		}

		/**
		* Creates all the internal database tables
		*
		* @return void
		*/
		public static function createInternalTables(){

			self::createTable("roles", [
				"id"=>"int(11) NOT NULL AUTO_INCREMENT",
				"name"=>"varchar(191)",
				"creation_time"=>"int(11)",
				"PRIMARY KEY"=>"(`id`)",
			]);

			self::createTable("role_permissions", [
				"id"=>"int(11) NOT NULL AUTO_INCREMENT",
				"role_id"=>"int(11)",
				"permission_name"=>"varchar(128)",
				"PRIMARY KEY"=>"(`id`)",
			]);

			self::createTable("users", [
				"id"=>"int(11) NOT NULL AUTO_INCREMENT",
				"username"=>"varchar(128)",
				"password"=>"varchar(255)",
				"first_name"=>"varchar(128)",
				"last_name"=>"varchar(128)",
				"email"=>"varchar(191)",
				"role"=>"varchar(128)",
				"creation_timestamp"=>"int(11)",
				"marked_as_deleted"=>"tinyint(1)",
				"PRIMARY KEY"=>"(`id`)",
			]);
		}
	}
?>
