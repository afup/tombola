<?php

namespace Afup\Tombola;

class UserRepository
{
    private $mysqli;

    public function __construct(\mysqli $mysqli)
    {
        $this->mysqli = $mysqli;
    }

    public function getUsers()
    {
        return $this->mysqli->query(
            'SELECT * FROM users WHERE date_game = CURDATE()'
        )->fetch_all(MYSQLI_ASSOC);
    }

    public function insertUser($user)
    {
        $stmt = $this->mysqli
            ->prepare('
INSERT INTO users (`nickname`, `avatar`, `date_game`, `name`, `admin`)
VALUES (?, ?, CURDATE(), ?, ?) ON DUPLICATE KEY UPDATE id=id')
        ;

        $stmt
            ->bind_param('sssi', $user['nickname'], $user['avatar'], $user['name'], $user['admin'])
        ;
        return $stmt
            ->execute()
            ;
    }
}
