<?php
	/**
	* Configurations for the MySQL database connection
	*
	* @author Garet C. Green
	* @since 0.0.1
	*/
	class DatabaseConfig{

		/**
		* @var string DATABASE_NAME The name of the database to connect to in the host
		*/
		const DATABASE_NAME = "lumen-cms";

		/**
		* @var string DATABASE_HOST The host to make a connection attempt to (usually localhost)
		*/
		const DATABASE_HOST = "localhost";

		/**
		* @var string DATABASE_USERNAME
		*/
		const DATABASE_USERNAME = "root";

		/**
		* @var string DATABASE_PASSWORD
		*/
		const DATABASE_PASSWORD = "";
	}
