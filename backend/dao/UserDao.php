<?php
require_once __DIR__ . '/BaseDao.php';

class UserDao extends BaseDao
{
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo, 'users',
            ['name', 'email', 'password_hash', 'role'],
            ['id', 'name', 'email', 'role', 'created_at']
        );
    }

    /** Internal use for future login: never return password_hash in API responses. */
    public function findByEmailForLogin(string $email): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, name, email, password_hash, role, created_at FROM users WHERE email = :email'
        );
        $statement->execute(['email' => $email]);
        $user = $statement->fetch();
        return $user === false ? null : $user;
    }
}
