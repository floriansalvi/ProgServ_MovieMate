<?php

namespace ch;

/**
 * Interface I_User provides a contract for users-related operations.
 */
interface I_User {

    /**
    * Return a boolean that tell the user if the email is already used or not
    * 
    * @param string $email The user's email.
    * @return bool Returns true if the email is already used, false if the email is not already used
    */
    public function isEmailUsed($email):bool;

    /**
    * Return a boolean that tell the user if the username is already used or not
    * 
    * @param string $username The user's username.
    * @return bool Returns true if the username is already used, false if the username is not already used
    */
    public function isUsernameUsed($username):bool;

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
    public function saveUser($username, $firstname, $lastname, $email, $password):bool;

    /**
    * Generate a token and link it to the associated user.
    * It retrieves the id of the user associated to the email adress and generated a token that's associated to the user id.
    * 
    * @param string $email The email of the user we want to associate a token with.
    *
    * @return string Returns the generated token
    */
    public function createToken($email):string;

    /**
    * Send a verification email to the user.
    * The email contains a link to the activation page and the token as an url parameter
    * 
    * @param string $email The email of the user we want to send a mail to.
    * @param string $token The token we want to send to the user.
    *
    * @return bool Returns whether the email was send or not.
    */
    public function sendVerificationMail($email, $token):bool;

    /**
    * Activate the user account.
    * Update the column activated of the user tab and delete the token related to the user.
    * 
    * @param string $token The token related to the user we want to activate.
    *
    * @return bool Returns whether the user was activated or not.
    */
    public function activateUser($token):bool;

    /**
    * Retrieves detailed information about a specific user from the database.
    * 
    * @param int $id The ID of the user to retrieve.
    *
    * @return array Returns an associative array containing the user data.
    *               If the user is not found or an error occurs, returns an empty array.
    */
    public function getUserById($id):array;

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
    public function getUsers(?string $sortColumn, ?string $sortOrder, ?string $role, ?int $limit, ?int $offset):array;

    /**
     * Retrieves detailed information about a specific user from the database, 
     * 
     * @param string $username The username of the user to retrieve.
     * @param string $password The password of the user to retrieve.
     * 
     * @return array Returns an array that contains user information. If the username and password do not match a stored user, it returns a array specifying which of these information was wrong.
     */
    public function getUserDatas($username, $password):array;

    /**
     * Updates a specific field of a user in the database.
     * 
     * @param int $userId The ID of the user to update.
     * @param string $field The field to update.
     * @param mixed $value The new value to assign to the field.
     * 
     * @return bool Returns true if the update was successful, false otherwise.
     */
    public function updateUserField($userId, $field, $value):bool;

    /**
     * Retrieves the total count of users in the database.
     * 
     * @return int Returns the total count of users. If an error occurs, returns 0.
     */
    public function getUsersCount():int;

    /**
     * Deletes a user and its associated data from the database.
     * 
     * @param int $userId The ID of the user to delete.
     * 
     * @return bool Returns true if the user was deleted successfully, false otherwise.
     */
    public function deleteUser($id):bool;
}