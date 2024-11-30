<?php

namespace ch;

interface I_User {
    public function isEmailUsed($email):bool;
    public function isUsernameUsed($username):bool;
    public function saveUser($username, $firstname, $lastname, $email, $password):bool;
    public function createToken($email):string;
    public function sendVerificationMail($email, $token):bool;
    public function activateUser($token):bool;
    public function getAllUsers():array;
    public function getUserDatas($email, $password):array;
    public function updateUsername($userId, $newUsername):bool;
    public function updatePassword($userId, $newHashedPassword):bool;
    public function updateProfileCover($userId, $newCoverId):bool;
    public function updateRole($userId, $role):bool;
    public function deleteUser($id):bool;
}