<?php
require_once __DIR__ . '/CrudService.php';
require_once __DIR__ . '/../dao/UserDao.php';

class UserService extends CrudService
{
    public function __construct(UserDao $dao)
    {
        parent::__construct($dao);
    }

    protected function validate(array $input, bool $partial): array
    {
        $this->fields($input, ['name', 'email', 'password', 'role'], ['name', 'email', 'password', 'role'], $partial);
        foreach (['name' => 100, 'email' => 190] as $field => $length) {
            $this->string($input, $field, $length);
        }
        if (array_key_exists('name', $input) && $input['name'] === '') throw new ApiException('Name is required.');
        if (array_key_exists('email', $input) && !filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            throw new ApiException('Invalid email address.');
        }
        $this->enum($input, 'role', ['admin', 'trainer', 'client']);
        if (array_key_exists('password', $input)) {
            $this->string($input, 'password', 100);
            if (strlen($input['password']) < 8) throw new ApiException('Password must contain at least 8 characters.');
            $input['password_hash'] = password_hash($input['password'], PASSWORD_DEFAULT);
            unset($input['password']);
        }
        return $input;
    }
}
