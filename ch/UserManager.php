<?php

namespace ch;

require_once 'lib/vendor/autoload.php';
require_once 'config/base_url.php';

use Exception;
use \PDO;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;

class UserManager extends DbManager implements I_User {

    public function __construct(){
        parent::__construct();
    }

    public function isEmailUsed($email):bool {
        $sql = "SELECT COUNT(*) FROM user WHERE email = :email;";
        $stmt = $this->getDB()->prepare($sql);
        $stmt->bindParam('email', $email, \PDO::PARAM_STR);
        try {
            $stmt->execute();
        } catch (PDOException $e) {
            error_log($e->getMessage());
        }
        return $stmt->fetchColumn() > 0;
    }

    public function isUserNameUsed($username):bool {
        $sql = "SELECT COUNT(*) FROM user WHERE username = :username;";
        $stmt = $this->getDB()->prepare($sql);
        $stmt->bindParam('username', $username, \PDO::PARAM_STR);
        try {
            $stmt->execute();
        } catch (PDOException $e) {
            error_log($e->getMessage());
        }  
        return $stmt->fetchColumn() > 0;        
    }

    public function saveUser($username, $firstname, $lastname, $email, $password):bool {
        $saved = false;
        $creationTime = date("Y-m-d H:i:s");
        if(!empty($username) && !empty($firstname) && !empty($lastname) && !empty($email) && !empty($password)){
            $datas = [
                'username' => $username,
                'firstname' => $firstname,
                'lastname' => $lastname,
                'email' => $email,
                'password' => $password,
                'created_at' => $creationTime,
                'activated' => false,
                'cover' => 0,
                'role' => ""
            ];
            $sql = "INSERT INTO user (username, firstname, lastname, email, password, created_at, activated, cover, role) VALUES "."(:username, :firstname, :lastname, :email, :password, :created_at, :activated, :cover, :role);";
            $stmt = $this->getDB()->prepare($sql);
            try {
                $stmt->execute($datas);
            } catch (PDOException $e) {
                error_log($e->getMessage());
            }
            $saved = true;
            $token = $this->createToken($email);
            $this->sendVerificationMail($email, $token);
        }
        return $saved;
    }

    public function createToken($email): string {
        $sql = "SELECT id FROM user WHERE email = :email;";
        $stmt = $this->getDB()->prepare($sql);
        $stmt->bindParam('email', $email, \PDO::PARAM_STR);
        try {
            $stmt->execute();
        } catch (PDOException $e) {
            error_log($e->getMessage());
        }  
        $id = $stmt->fetchColumn();
        if(!$id){
            throw new Exception("Le token n'a pas pu être généré.");
        }
        $tokenDatas = [
            'user_id' => $id,
            'content' => bin2hex(random_bytes(16))
        ];
        $sql = "INSERT INTO token (user_id, content) VALUES "."(:user_id, :content);";
        $this->getDB()->prepare($sql)->execute($tokenDatas);
        return $tokenDatas['content'];
    }

    public function sendVerificationMail($email, $token):bool {
        $transport = Transport::fromDsn('smtp://localhost:1025');
        $mailer = new Mailer($transport);
        $message = (new Email())
        ->from('admin@movie-mate.ch')
        ->to($email)
        ->subject('Inscription à MovieMate')
        ->html('<h1>Bienvenue sur MovieMate</h1><br><p>Cliquez sur le lien ci-dessous afin de valider votre inscription</p><br><a href="' . BASE_URL . 'activation.php?token=' . urlencode($token) . '">Activer votre compte</a>');
        $result = $mailer->send($message);
        if($result===null){
            return true;
        }else{
            throw new Exception("L'envoi du mail de confirmation a échoué.");
        }
    }

    public function activateUser($token): bool {
        $this->getDB()->beginTransaction();
        try {
            $sqlUpdate = "UPDATE user SET activated = true WHERE id = (SELECT user_id FROM token WHERE content = :token);";
            $stmtUpdate = $this->getDB()->prepare($sqlUpdate);
            $stmtUpdate->bindParam('token', $token, \PDO::PARAM_STR);
            $stmtUpdate->execute();
            $sqlDelete = "DELETE FROM token WHERE content = :token;";
            $stmtDelete = $this->getDB()->prepare($sqlDelete);
            $stmtDelete->bindParam('token', $token, \PDO::PARAM_STR);
            $stmtDelete->execute();
            $this->getDB()->commit();
            return $stmtUpdate->rowCount() == 1;
        } catch (\PDOException $e) {
            $this->getDB()->rollBack();
            error_log($e->getMessage());
            return false;
        }
    }

    public function getUsers(?string $sortColumn, ?string $sortOrder, ?string $role, ?int $limit, ?int $offset):array {
        
        $datas = [];

        $sql = "SELECT id, username, email, created_at, cover, role FROM user";

        $validColumns = ['username', 'firstname', 'lastname', 'email', 'created_at'];
        $validOrders = ['ASC', 'DESC'];
        $sortColumn = in_array($sortColumn, $validColumns) ? $sortColumn : 'created_at';
        $sortOrder = in_array($sortOrder, $validOrders) ? $sortOrder : 'DESC';

        if(!is_null($role)){
            $sql .= " WHERE role = :role";
            $datas['role'] = $role;
        }
        
        $sql .= " ORDER BY $sortColumn $sortOrder";

        if(!is_null($limit)){
            $sql .= " LIMIT :limit";
            $datas['limit'] = $limit;
        }

        if(!is_null($offset)){
            $sql .= " OFFSET :offset";
            $datas['offset'] = $offset;
        }
        
        $stmt = $this->getDB()->prepare($sql);

        if (!is_null($limit)) {
            $stmt->bindParam(':limit', $limit, \PDO::PARAM_INT);
        }

        if (!is_null($offset)) {
            $stmt->bindParam(':offset', $offset, \PDO::PARAM_INT);
        }

        try {
            $stmt->execute($datas);
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return[];
        }
        return $users;
    }

    public function getUserDatas($username, $password): array {
        $sql = "SELECT * FROM user WHERE username = :username;";
        $stmt = $this->getDB()->prepare($sql);
        $stmt->bindParam('username', $username, \PDO::PARAM_STR);
        try {
            $stmt->execute();
        } catch (PDOException $e) {
            error_log($e->getMessage());
        }  
        $datas = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if(!$datas) {
            $datas[0]["username_ok"] = false;
            $datas[0]["password_ok"] = false;
        } else {
            if(!password_verify($password, $datas[0]["password"])) {
                unset($datas[0]["password"]);
                $datas[0]["username_ok"] = true;
                $datas[0]["password_ok"] = false;
            } else {
                unset($datas[0]["password"]);
                $datas[0]["username_ok"] = true;
                $datas[0]["password_ok"] = true;
            }
        }
        return $datas[0];
    }

    public function updateUserField($userId, $field, $value):bool {
        $validFields = ['username', 'password', 'cover', 'role'];
        if(in_array($field, $validFields)){
            $datas = [
                'id' => $userId,
                $field => $value
            ];
            $sql = "UPDATE user SET $field = :$field WHERE id = :id;";
            $stmt = $this->getDB()->prepare($sql);
            try {
                $stmt->execute($datas);
                if($stmt->rowCount()>0){
                    return true;
                }else{
                    return false;
                }
            } catch (\PDOException $e){
                error_log($e->getMessage());
                return false;
            }
        }else{
            return false;
        } 
    }

    public function deleteUser($id):bool {
        $sql = "DELETE FROM user WHERE id = :id;";
        $stmt = $this->getDB()->prepare($sql);
        $stmt->bindParam('id', $id, \PDO::PARAM_INT);
        try {
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }  
    }
}