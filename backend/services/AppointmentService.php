<?php
require_once __DIR__ . '/CrudService.php';
require_once __DIR__ . '/../dao/AppointmentDao.php';
require_once __DIR__ . '/../dao/UserDao.php';

class AppointmentService extends CrudService
{
    public function __construct(AppointmentDao $dao, private UserDao $users)
    {
        parent::__construct($dao);
    }

    protected function validate(array $input, bool $partial): array
    {
        $this->fields($input, ['trainer_id','client_id','scheduled_at','duration_minutes','status','meeting_url'],
            ['trainer_id','client_id','scheduled_at'], $partial);
        $this->integer($input, 'trainer_id', 1, PHP_INT_MAX);
        $this->integer($input, 'client_id', 1, PHP_INT_MAX);
        $this->integer($input, 'duration_minutes', 15, 240);
        $this->date($input, 'scheduled_at', true);
        $this->enum($input, 'status', ['pending','confirmed','completed','cancelled']);
        $this->string($input, 'meeting_url', 500, true);
        if (!empty($input['meeting_url']) && !filter_var($input['meeting_url'], FILTER_VALIDATE_URL)) {
            throw new ApiException('Invalid meeting URL.');
        }
        foreach (['trainer_id'=>'trainer','client_id'=>'client'] as $key => $role) {
            if (isset($input[$key]) && ($this->users->findById($input[$key])['role'] ?? '') !== $role) {
                throw new ApiException("{$key} must reference a {$role}.");
            }
        }
        return $input;
    }
}
