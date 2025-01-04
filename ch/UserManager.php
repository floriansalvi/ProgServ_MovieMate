<?php

namespace ch;

require_once 'lib/vendor/autoload.php';
require_once 'config/base_url.php';

use Exception;
use \PDO;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;

/**
 * UserManager class handles operations related to users in the database.
 * It extends the DbManager class to inherit the database connection functionality.
 */
class UserManager extends DbManager implements I_User {

    /**
     * Class constructor
     * 
     * Initializes the UserManager by calling the parent constructor.
     * The parent constructor establishes the database connection by invoking the DbManager's __construct() method.
     * This allows UserManager to access the database connection and perform operations related to users.
     */
    public function __construct(){
        parent::__construct();
    }

    /**
    * Return a boolean that tell the user if the email is already used or not
    * 
    * @param string $email The user's email.
    * @return bool Returns true if the email is already used, false if the email is not already used
    */
    public function isEmailUsed($email):bool {
        $sql = "SELECT COUNT(*) FROM user WHERE email = :email;";
        $stmt = $this->getDB()->prepare($sql);
        $stmt->bindParam('email', $email, \PDO::PARAM_STR);
        try {
            // Attempt to execute the SQL query.
            $stmt->execute();
            // If the SQL query is successfuly executed, returns an boolean specifying if the email is already used or not.
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            // If an execption is thrown, log the error message.
            error_log($e->getMessage());
            // If the SQL query could not be executed, returns true.
            return true;
        }
    }

    /**
    * Return a boolean that tell the user if the username is already used or not
    * 
    * @param string $username The user's username.
    * @return bool Returns true if the username is already used, false if the username is not already used
    */
    public function isUserNameUsed($username):bool {
        $sql = "SELECT COUNT(*) FROM user WHERE username = :username;";
        $stmt = $this->getDB()->prepare($sql);
        $stmt->bindParam('username', $username, \PDO::PARAM_STR);
        try {
            // Attempt to execute the SQL query.
            $stmt->execute();
            // If the SQL query is successfuly executed, returns an boolean specifying if the username is already used or not.
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            // If an execption is thrown, log the error message.
            error_log($e->getMessage());
            // If the SQL query could not be executed, returns true.
            return true;
        }          
    }

    /**
    * Store a user and its information in the database.
    * 
    * @param string $username The username of the user.
    * @param string $firstname The first name of the user.
    * @param string $lastname The last name of the user.
    * @param string $email The email of the user.
    * @param string $password The hashed password of the user.
    *
    * @return bool Returns true if the user was stored, false if the user could not stored.
    */
    public function saveUser($username, $firstname, $lastname, $email, $password):bool {
        $saved = false;
        // Get the current date and time for the created_at field
        $creationTime = date("Y-m-d H:i:s");

        // Check if all fields are non-empty
        if(!empty($username) && !empty($firstname) && !empty($lastname) && !empty($email) && !empty($password)){
            // Prepare data for insertion
            $datas = [
                'username' => $username,
                'firstname' => $firstname,
                'lastname' => $lastname,
                'email' => $email,
                'password' => $password,
                'created_at' => $creationTime,
                'activated' => false, // by default. The user account is not activated
                'cover' => 0, // by default. The user cover image is the one named 0.
                'role' => "" // by default. The user has no role.
            ];

            // SQL query to insert the user information into the database
            $sql = "INSERT INTO user (username, firstname, lastname, email, password, created_at, activated, cover, role) VALUES "."(:username, :firstname, :lastname, :email, :password, :created_at, :activated, :cover, :role);";
            $stmt = $this->getDB()->prepare($sql);
            try {
                // Attempt to execute the SQL query.
                $stmt->execute($datas);
                // If the SQL query is successfuly executed, returns true.
                $saved = true;
            } catch (PDOException $e) {
                // If an execption is thrown, log the error message.
                error_log($e->getMessage());
                // If the SQL query could not be executed, returns false.
                $saved = false;
            }
            // Generate a token for email verification
            $token = $this->createToken($email);
            // Send a verification email to the user
            $this->sendVerificationMail($email, $token);
        }
        // Return whether the movie was saved successfully
        return $saved;
    }

    /**
    * Generate a token and link it to the associated user.
    * It retrieves the id of the user associated to the email adress and generated a token that's associated to the user id.
    * 
    * @param string $email The email of the user we want to associate a token with.
    *
    * @return string Returns the generated token
    */
    public function createToken($email): string {
        // SQL query to retrives the id of the user
        $sql = "SELECT id FROM user WHERE email = :email;";
        $stmt = $this->getDB()->prepare($sql);
        $stmt->bindParam('email', $email, \PDO::PARAM_STR);
        try {
            // Attempt to execute the SQL query.
            $stmt->execute();
        } catch (PDOException $e) {
            // If an execption is thrown, log the error message.
            error_log($e->getMessage());
        }  

        // Retrieves the user's id
        $id = $stmt->fetchColumn();
        
        // Check if the id is not empty
        if(!$id){
            // If the id is empty, throw an exception with a custom message.
            throw new Exception("Le token n'a pas pu être généré.");
        }

        // Prepare data for insertion
        $tokenDatas = [
            'user_id' => $id,
            'content' => bin2hex(random_bytes(16)) // generated a 32 caracters long token
        ];

        // SQL query to insert the token into the database
        $sql = "INSERT INTO token (user_id, content) VALUES "."(:user_id, :content);";
        
        // Execute the SQL query.
        $this->getDB()->prepare($sql)->execute($tokenDatas);

        // Return the generated token
        return $tokenDatas['content'];
    }

    /**
    * Send a verification email to the user.
    * The email contains a link to the activation page and the token as an url parameter
    * 
    * @param string $email The email of the user we want to send a mail to.
    * @param string $token The token we want to send to the user.
    *
    * @return bool Returns whether the email was send or not.
    */
    public function sendVerificationMail($email, $token):bool {
        
        // Create a mail transport using SMTP protocol, connecting to localhost on port 1025
        $transport = Transport::fromDsn('smtp://localhost:1025');
        
        // Create a new Mailer instance with the specified transport
        $mailer = new Mailer($transport);
        
        // Construct the email message with sender, recipient, subject, and HTML content
        $message = (new Email())
        ->from('admin@movie-mate.ch') // sender email adress
        ->to($email) // recipient email adress
        ->subject('Inscription à MovieMate') // subject of the email
        ->html('<h1>Bienvenue sur MovieMate</h1><br><p>Cliquez sur le lien ci-dessous afin de valider votre inscription</p><br><a href="' . BASE_URL . 'activation.php?token=' . urlencode($token) . '">Activer votre compte</a>'); // HTML body of the email
        
        // Attempt to send the email
        $result = $mailer->send($message);
        if($result===null){
            // If the mail is successfuly sent, returns true.
            return true;
        }else{
            // If the mail could not be sent, throw an exception with a custom message.
            throw new Exception("L'envoi du mail de confirmation a échoué.");
        }
    }

    /**
    * Activate the user account.
    * Update the column activated of the user tab and delete the token related to the user.
    * 
    * @param string $token The token related to the user we want to activate.
    *
    * @return bool Returns whether the user was activated or not.
    */
    public function activateUser($token): bool {
        // Begin the SQL transaction.
        $this->getDB()->beginTransaction();
        try {
            // SQL query to update the activated column in the user table.
            $sqlUpdate = "UPDATE user SET activated = true WHERE id = (SELECT user_id FROM token WHERE content = :token);";
            $stmtUpdate = $this->getDB()->prepare($sqlUpdate);
            $stmtUpdate->bindParam('token', $token, \PDO::PARAM_STR);
            // Attempt to execute the update SQL query.
            $stmtUpdate->execute();
            
            // SQL query to delete the token in the database.
            $sqlDelete = "DELETE FROM token WHERE content = :token;";
            $stmtDelete = $this->getDB()->prepare($sqlDelete);
            $stmtDelete->bindParam('token', $token, \PDO::PARAM_STR);
            
            // Attempt to execute the delete SQL query.
            $stmtDelete->execute();
            
            // Close the SQL transaction.
            $this->getDB()->commit();

            //Return true if only one user was updated
            return $stmtUpdate->rowCount() == 1;
        } catch (\PDOException $e) {
            // If an execption is thrown, log the error message, return false and roll back the transaction.
            $this->getDB()->rollBack();
            error_log($e->getMessage());
            return false;
        }
    }

    /**
    * Retrieves detailed information about a specific user from the database.
    * 
    * @param int $id The ID of the user to retrieve.
    *
    * @return array Returns an associative array containing the user data.
    *               If the user is not found or an error occurs, returns an empty array.
    */
    public function getUserById($id):array {
        // Define the SQL query to retrieve a user by its ID.
        $sql = "SELECT id, username, cover FROM user WHERE id = :id;";
        $stmt = $this->getDB()->prepare($sql);
        $stmt->bindParam('id', $id, \PDO::PARAM_STR);
        try{
            // Attempt to execute the SQL query.
            $stmt->execute();
            // Fetch the user data as an associative array.
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            // Return the user data if found; otherwise, return an empty array.
            return $user ? $user : [];
        } catch (\PDOException $e) {
            // If an exception occurs, log the error and return an empty array.
            error_log($e->getMessage());
            return [];
        }
    }

    /**
     * Retrieves a list of users and their information stored in the database.
     * 
     * @param string|null $sortColumn The column by which to sort the users. 
     *                                If null, no sorting is applied.
     * @param string|null $sortOrder The order to sort the users by. Can be 'ASC' or 'DESC'. 
     *                               If null, the default sorting order is applied.
     * @param int|null $role The role to filter the users by. If null, no filtering is applied.
     * @param int|null $limit The number of users to retrieve. If null, no limit is applied.
     * @param int|null $offset The offset from which to start retrieving users. If null, no offset is applied.
     *
     * @return array Returns an array of users that match the specified parameters. If no user match, returns an empty array.
     */
    public function getUsers(?string $sortColumn, ?string $sortOrder, ?string $role, ?int $limit, ?int $offset):array {
        
        // Initialize an empty array to hold the data for the query parameters.
        $datas = [];

        // Define the initial SQL query to select specific columns from the user table.
        $sql = "SELECT id, username, email, created_at, cover, role FROM user";

        // Define an array of valid columns for sorting.
        $validColumns = ['username', 'firstname', 'lastname', 'email', 'created_at'];

        // Define an array of valid sorting orders
        $validOrders = ['ASC', 'DESC'];

        // If the provided sort column is valid, use it; otherwise, default to 'created_at'.
        $sortColumn = in_array($sortColumn, $validColumns) ? $sortColumn : 'created_at';

        // If the provided sort order is valid, use it; otherwise, default to 'DESC'.
        $sortOrder = in_array($sortOrder, $validOrders) ? $sortOrder : 'DESC';

        // If a role is provided, filter the users by role.
        if(!is_null($role)){
            $sql .= " WHERE role = :role";
            $datas['role'] = $role;
        }
        
        // Append the ORDER BY clause to sort the results by the selected column and order.
        $sql .= " ORDER BY $sortColumn $sortOrder";

        // If a limit is provided, restrict the number of movies returned.
        if(!is_null($limit)){
            $sql .= " LIMIT :limit";
            $datas['limit'] = $limit;
        }

        // If an offset is provided, set the starting point for the results.
        if(!is_null($offset)){
            $sql .= " OFFSET :offset";
            $datas['offset'] = $offset;
        }
        
        // Prepare the SQL query for execution.
        $stmt = $this->getDB()->prepare($sql);

        try {
            // Attempt to execute the SQL query.
            $stmt->execute($datas);
            // If the SQL query is successfuly executed, returns the list of users.
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $users;
        } catch (PDOException $e) {
            // If an execption is thrown, log the error message.
            error_log($e->getMessage());
            // If the SQL query could not be executed, returns an empty array.
            return[];
        }
    }

    /**
     * Retrieves detailed information about a specific user from the database, 
     * 
     * @param string $username The username of the user to retrieve.
     * @param string $password The password of the user to retrieve.
     * 
     * @return array Returns an array that contains user information. If the username and password do not match a stored user, it returns a array specifying which of these information was wrong.
     */
    public function getUserDatas($username, $password): array {
        
        // Define the SQL query to retrieves the user by its username.
        $sql = "SELECT * FROM user WHERE username = :username;";
        $stmt = $this->getDB()->prepare($sql);
        $stmt->bindParam('username', $username, \PDO::PARAM_STR);
        try {
            // Attempt to execute the SQL query.
            $stmt->execute();
        } catch (PDOException $e) {
            // If an exception occurs, log the error.
            error_log($e->getMessage());
        }  

        // Fetch the resulting data as an associative array.
        $datas = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if(!$datas) {
            // If no data is found, indicate that both username and password are incorrect.
            $datas[0]["username_ok"] = false;
            $datas[0]["password_ok"] = false;
        } else {
            // If data is found, verify the provided password against the stored hashed password.
            if(!password_verify($password, $datas[0]["password"])) {
                // If the password does not match, remove the password from the data and set flags.
                unset($datas[0]["password"]);
                $datas[0]["username_ok"] = true;
                $datas[0]["password_ok"] = false;
            } else {
                // If the password matches, remove it from the data and set flags.
                unset($datas[0]["password"]);
                $datas[0]["username_ok"] = true;
                $datas[0]["password_ok"] = true;
            }
        }
        // Return the processed user data or error flags.
        return $datas[0];
    }

    /**
     * Updates a specific field of a user in the database.
     * 
     * @param int $userId The ID of the user to update.
     * @param string $field The field to update.
     * @param mixed $value The new value to assign to the field.
     * 
     * @return bool Returns true if the update was successful, false otherwise.
     */
    public function updateUserField($userId, $field, $value):bool {
        // Define a list of valid fields that can be updated.
        $validFields = ['username', 'password', 'cover', 'role'];
        
        // Check if the field to be updated is valid.
        if(in_array($field, $validFields)){
            // Prepare data for the query.
            $datas = [
                'id' => $userId,
                $field => $value
            ];
            // Define the SQL query to update the specified field of the user.
            $sql = "UPDATE user SET $field = :$field WHERE id = :id;";
            $stmt = $this->getDB()->prepare($sql);
            try {
                // Attempt to execute the SQL query.
                $stmt->execute($datas);
                // Check if any rows were affected by the update.
                if($stmt->rowCount()>0){
                    return true;
                }else{
                    return false;
                }
            } catch (\PDOException $e){
                // If an exception occurs, log the error and return false.
                error_log($e->getMessage());
                return false;
            }
        }else{
            // If the field is not valid, return false.
            return false;
        } 
    }

    /**
     * Retrieves the total count of users in the database.
     * 
     * @return int Returns the total count of users. If an error occurs, returns 0.
     */
    public function getUsersCount():int {
    
        // Define the SQL query to count all users.
        $sql = "SELECT COUNT(*) as total FROM user";
        
        $stmt = $this->getDB()->prepare($sql);

        try {
            // Attempt to execute the SQL query.
            $stmt->execute();
            // Retrieve the count from the query result.
            $usersAmount = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
            // Return the total count of users.
            return $usersAmount;
        } catch (PDOException $e) {
            // If an exception occurs, log the error and return 0.
            error_log($e->getMessage());
            return 0;
        }
    }

    /**
     * Deletes a user and its associated data from the database.
     * 
     * @param int $userId The ID of the user to delete.
     * 
     * @return bool Returns true if the user was deleted successfully, false otherwise.
     */
    public function deleteUser($userId):bool {
        
        // Define the SQL query to delete the user by its ID.
        $sql = "DELETE FROM user WHERE id = :id;";
        $stmt = $this->getDB()->prepare($sql);
        $stmt->bindParam('id', $id, \PDO::PARAM_INT);
        try {
            // Attempt to execute the SQL query.
            $stmt->execute();
            // Return true if the user was deleted successfully.
            return true;
        } catch (PDOException $e) {
            // If an exception occurs, log the error and return false.
            error_log($e->getMessage());
            return false;
        }  
    }
}