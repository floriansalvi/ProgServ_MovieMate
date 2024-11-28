<?php

namespace ch;

use Exception;
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
            $e->getMessage();
        }
        return $stmt->fetchColumn() > 0;
    }

    public function isPseudonymUsed($pseudonym):bool {
        $sql = "SELECT COUNT(*) FROM user WHERE pseudonym = :pseudonym;";
        $stmt = $this->getDB()->prepare($sql);
        $stmt->bindParam('pseudonym', $pseudonym, \PDO::PARAM_STR);
        try {
            $stmt->execute();
        } catch (PDOException $e) {
            $e->getMessage();
        }  
        return $stmt->fetchColumn() > 0;        
    }

    public function saveUser($pseudonym, $firstname, $lastname, $email, $password):bool {
        $saved = false;
        $creationTime = date("Y-m-d H:i:s");
        if(!empty($pseudonym) && !empty($firstname) && !empty($lastname) && !empty($email) && !empty($password)){
            $datas = [
                'pseudonym' => $pseudonym,
                'firstname' => $firstname,
                'lastname' => $lastname,
                'email' => $email,
                'password' => $password,
                'created_at' => $creationTime,
                'activated' => false,
                'cover' => 0,
                'role' => ""
            ];
            $sql = "INSERT INTO user (pseudonym, firstname, lastname, email, password, created_at, activated, cover, role) VALUES "."(:pseudonym, :firstname, :lastname, :email, :password, :created_at, :activated, :cover, :role);";
            $stmt = $this->getDB()->prepare($sql);
            try {
                $stmt->execute($datas);
            } catch (PDOException $e) {
                $e->getMessage();
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
            $e->getMessage();
        }  
        $id = $stmt->fetchColumn();
        if(!$id){
            throw new Exception("Le token n'a pas pu être généré.");
        }
        $tokenDatas = [
            'user_id' => $id,
            'content' => bin2hex(random_bytes(16))
        ];
        $sql = "INSERT INTO token (user_id, content) VALUE "."(:user_id, :content);";
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
        ->html('<h1>Bienvenue sur MovieMate</h1><br><p>Cliquez sur le lien ci-dessous afin de valider votre inscription</p><br><a href="http://localhost:8888/ProgServ_MovieMate/activation.php?token=' . urlencode($token) . '">Activer votre compte</a>');
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
            return true;
        } catch (\PDOException $e) {
            $this->getDB()->rollBack();
            $e->getMessage();
            return false;
        }
    }
    public function getAllUsers():array {
        $sql = "SELECT id, pseudonym, created_at, cover, role FROM user;";
        $stmt = $this->getDB()->prepare($sql);
        try {
            $stmt->execute();
        } catch (PDOException $e) {
            $e->getMessage();
        }
        $datas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $datas;
    }

    public function getUserDatas($email, $password): array {
        $sql = "SELECT * FROM user WHERE email = :email;";
        $stmt = $this->getDB()->prepare($sql);
        $stmt->bindParam('email', $email, \PDO::PARAM_STR);
        try {
            $stmt->execute();
        } catch (PDOException $e) {
            $e->getMessage();
        }  
        $datas = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if(!$datas) {
            $datas[0]["email_ok"] = false;
            $datas[0]["password_ok"] = false;
        } else {
            if(!password_verify($password, $datas[0]["password"])) {
                unset($datas[0]["password"]);
                $datas[0]["email_ok"] = true;
                $datas[0]["password_ok"] = false;
            } else {
                unset($datas[0]["password"]);
                $datas[0]["email_ok"] = true;
                $datas[0]["password_ok"] = true;
            }
        }
        return $datas[0];
    }

    public function updatePseudonym($userId, $newPseudonym):bool {
        $datas = [
            'id'=> $userId,
            'pseudonym'=> $newPseudonym
        ];
        $sql = "UPDATE user SET pseudonym = :pseudonym WHERE id = :id;";
        $stmt = $this->getDB()->prepare($sql);
        try {
            $stmt->execute($datas);
            if($stmt->rowCount() > 0){
                return true;
            }else{
                return false;
            }
        } catch (PDOException $e) {
            $e->getMessage();
            return false;
        }  
    }

    public function updatePassword($userId, $newPassword): bool {
        $datas = [
            'id'=> $userId,
            'password'=>$newPassword
        ];
        $sql = "UPDATE user SET password = :password WHERE id = :id;";
        $stmt = $this->getDB()->prepare($sql);
        try {
            $stmt->execute($datas);
            if($stmt->rowCount() > 0){
                return true;
            }else{
                return false;
            }
        } catch (PDOException $e) {
            $e->getMessage();
            return false;
        }        
    }

    public function updateProfileCover($userId, $newCoverId):bool {
        $datas = [
            'id'=> $userId,
            'cover'=>$newCoverId
        ];
        $sql = "UPDATE user SET cover = :cover WHERE id = :id;";
        $stmt = $this->getDB()->prepare($sql);
        try {
            $stmt->execute($datas);
            if($stmt->rowCount() > 0){
                return true;
            }else{
                return false;
            }
        } catch (PDOException $e) {
            $e->getMessage();
            return false;
        }         
    }

    public function updateRole($userId, $role): bool {
        $datas = [
            'id'=> $userId,
            'role'=>$role
        ];
        $sql = "UPDATE user SET role = :role WHERE id = :id;";
        $stmt = $this->getDB()->prepare($sql);
        try {
            $stmt->execute($datas);
            if($stmt->rowCount() > 0){
                return true;
            }else{
                return false;
            }
        } catch (PDOException $e) {
            $e->getMessage();
            return false;
        }       
    }

    public function deleteUser($id):bool {
        $sql = "DELETE FROM user WHERE id = :id;";
        $stmt = $this->getDB()->prepare($sql);
        $stmt->bindParam('id', $id, \PDO::PARAM_INT);
        try {
            $stmt->execute();
        } catch (PDOException $e) {
            $e->getMessage();
            return false;
        }
        return true;  
    }
}