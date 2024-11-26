<?php

namespace ch;

interface I_User {
    public function isEmailUsed($email):bool;
    public function isPseudonymUsed($pseudonym):bool;
    public function saveUser($pseudonym, $firstname, $lastname, $email, $password):bool;
    public function activateUser($token):bool;
    public function getAllUsers():array;
    public function getUserDatas($email, $password):array;
    public function updatePseudonym($newPseudonym):bool;
    public function updatePassword($newPassword):bool;
    public function updateProfileCover($newCoverId):bool;
    public function updateRole($email, $role):bool;
    public function deleteUser($email):bool;
}