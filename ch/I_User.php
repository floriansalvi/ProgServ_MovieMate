<?php

namespace ch;

interface I_User {
    public function isEmailUsed($email):bool;
    public function isPseudonymUsed($pseudonym):bool;
    public function saveUser($pseudonym, $firstname, $lastname, $email, $password):bool;
    public function createToken($email):string;
    public function sendVerificationMail($email, $token):bool;
    public function activateUser($token):bool;
    public function getAllUsers():array;
    public function getUserDatas($email, $password):array;
    public function updatePseudonym($userId, $newPseudonym):bool;
    public function updatePassword($userId, $newHashedPassword):bool;
    public function updateProfileCover($userId, $newCoverId):bool;
    public function updateRole($userId, $role):bool;
    public function deleteUser($id):bool;
}