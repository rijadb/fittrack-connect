<?php
require_once __DIR__ . '/CrudService.php';
require_once __DIR__ . '/../dao/ExerciseDao.php';

class ExerciseService extends CrudService
{
    public function __construct(ExerciseDao $dao) { parent::__construct($dao); }

    protected function validate(array $input, bool $partial): array
    {
        $this->fields($input, ['name', 'muscle_group', 'equipment', 'difficulty', 'description'],
            ['name', 'muscle_group'], $partial);
        foreach (['name' => 150, 'muscle_group' => 80, 'equipment' => 120,
                  'difficulty' => 40, 'description' => 10000] as $key => $max) {
            $this->string($input, $key, $max, $key === 'description');
        }
        foreach (['name','muscle_group'] as $key) {
            if (array_key_exists($key, $input) && $input[$key] === '') throw new ApiException("{$key} cannot be empty.");
        }
        $this->enum($input, 'difficulty', ['Beginner', 'Intermediate', 'Advanced']);
        return $input;
    }
}
