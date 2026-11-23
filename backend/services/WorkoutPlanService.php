<?php
require_once __DIR__ . '/CrudService.php';
require_once __DIR__ . '/../dao/WorkoutPlanDao.php';
require_once __DIR__ . '/../dao/UserDao.php';

class WorkoutPlanService extends CrudService
{
    public function __construct(WorkoutPlanDao $dao, private UserDao $users)
    {
        parent::__construct($dao);
    }

    protected function validate(array $input, bool $partial): array
    {
        $this->fields($input, ['trainer_id', 'client_id', 'title', 'focus', 'level',
                'sessions_per_week', 'notes', 'nutrition_guidance'],
            ['trainer_id','client_id','title','focus'], $partial);
        $this->integer($input, 'trainer_id', 1, PHP_INT_MAX);
        $this->integer($input, 'client_id', 1, PHP_INT_MAX);
        $this->integer($input, 'sessions_per_week', 1, 7);
        foreach (['title' => 150, 'focus' => 120, 'level' => 40,
                'notes' => 10000, 'nutrition_guidance' => 10000] as $key => $max) {
            $this->string($input, $key, $max, in_array($key, ['notes','nutrition_guidance'], true));
        }
        foreach (['title', 'focus'] as $key) {
            if (array_key_exists($key, $input) && $input[$key] === '') throw new ApiException("{$key} cannot be empty.");
        }
        $this->enum($input, 'level', ['Beginner','Intermediate','Advanced','All levels']);
        $this->checkRole($input, 'trainer_id', 'trainer');
        $this->checkRole($input, 'client_id', 'client');
        return $input;
    }

    private function checkRole(array $input, string $key, string $role): void
    {
        if (!isset($input[$key])) return;
        $user = $this->users->findById($input[$key]);
        if (!$user || $user['role'] !== $role) {
            throw new ApiException("{$key} must reference an existing {$role}.");
        }
    }
}
