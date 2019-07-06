# PHP Account & Role-Permissions Template
This is a simple set of classes that allow projects to begin without the need to continuously create an account system or a permission system for the accounts.

These classes use MySQL as the back-end database and a **Database** class has been packaged to interface using PHP's MySQLi class.


# Setting Up the Database
In the /Configs directory, you can find a DatabaseConfig.php file which holds constants to input your MySQL database login and desired database name to be used.

In the **Database** class file there is a static method:

```php
SQLDatabase::createInternalTables();
```

You can either call this function when beginning your application, or review the tables and columns to create in your MySQL database defined below.

If not using the static method above to create the tables system template needs, here are the tables and columns to create in MySQL (or using phpMyAdmin)

**Table Name:** roles (utf8mb4_unicode_ci

| Column | Type | Auto Increment |
| --- | --- | --- |
| id (PRIMARY) | int(11) | yes |
| name | varchar(191) |  |
| creation_time | int(11) |  |

**Table Name:** role_permissions (utf8mb4_unicode_ci)

| Column | Type | Auto Increment |
| --- | --- | --- |
| id (PRIMARY) | int(11) | yes |
| role_id | varchar(191) |  |
| permission_name | varchar(191) |  |

**Table Name:** users (utf8mb4_unicode_ci)

| Column | Type | Auto Increment |
| --- | ---| --- |
| id (PRIMARY) | int(11) | yes |
| username | varchar(128) |  |
| password | varchar(255) |  |
| first_name | varchar(128) |  |
| last_name| varchar(128) |  |
| email | varchar(191) |  |
| role | varchar(128) |  |
| creation_timestamp | int(11) |  |

## Customizing the Table Names Used

In each class file (Account and Role) there are constants that are used to determine which tables (such as Account class using the `users` table) that can be changed if you would like to name your MySQL tables something different.

## Creating a New Role With Permissions

```php
require_once "Role.php";
$newRoleID = Role::createNewRole("Admin");
$role = new Role($newRoleID);

// Please note, these aren't built-in permissions and are just made-up for this example
// It is up to your website or application to check permissions for the features you make
$permissionsToGiveAdmin = ["Edit Accounts", "Something Else"];
$role->setPermissions($permissionToGiveAdmin);

// The "Admin" role now has those permissions. The role can be assigned to accounts now.
```

## Creating an Account

```php
require_once "Account.php";
$newAccountID = Account::createNew(
	"username",
	"example@email.com",
	"John",
	"Doe",
	"Mypassword123",
	"Admin"
);
```

## Checking if An Account Has Permissions

Assuming we know we want to check account ID 1 (the first user), then it can be done like so:
```php
$account = new Account(1);

if ($account->hasPermission("Edit Accounts")){
	// This account can "Edit Accounts" (or whatever permission you want)
}
```

## Other Methods

The class files aren't too extensive and are documented in a PHPDoc style. Feel free to explore the other handful of methods these classes have (such as Account get* functions for getting information about an account).

No method currently exists for finding an account by a cookie yet.
