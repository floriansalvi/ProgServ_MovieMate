<?php

namespace ch;

interface I_User {
    public function isEmailUsed($email):bool;
    public function isUsernameUsed($username):bool;
    public function saveUser($username, $firstname, $lastname, $email, $password):bool;
    public function createToken($email):string;
    public function sendVerificationMail($email, $token):bool;
    public function activateUser($token):bool;
    public function getUserById($id):array;
    public function getUsers(?string $sortColumn, ?string $sortOrder, ?string $role, ?int $limit, ?int $offset):array;
    public function getUserDatas($username, $password):array;
    public function updateUserField($userId, $field, $value):bool;
    public function deleteUser($id):bool;
}