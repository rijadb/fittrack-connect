<?php
require_once __DIR__ . '/ApiException.php';
require_once __DIR__ . '/../dao/BaseDao.php';

abstract class CrudService
{
    public function __construct(protected BaseDao $dao)
    {
    }

    public function list(): array
    {
        return $this->dao->findAll();
    }

    public function get(int $id): array
    {
        $record = $this->dao->findById($id);
        if ($record === null) {
            throw new ApiException('Record not found.', 404);
        }
        return $record;
    }

    public function create(array $input): array
    {
        $record = $this->validate($input, false);
        $id = $this->dao->create($record);
        return $this->get($id);
    }

    public function update(int $id, array $input): array
    {
        $this->get($id);
        $record = $this->validate($input, true);
        if (!$record) {
            throw new ApiException('Provide at least one field to update.');
        }
        $this->dao->update($id, $record);
        return $this->get($id);
    }

    public function delete(int $id): array
    {
        if (!$this->dao->delete($id)) {
            throw new ApiException('Record not found.', 404);
        }
        return ['message' => 'Record deleted.'];
    }

    abstract protected function validate(array $input, bool $partial): array;

    protected function fields(array $input, array $allowed, array $required, bool $partial): array
    {
        foreach ($input as $key => $value) {
            if (!in_array($key, $allowed, true)) {
                throw new ApiException("Unknown field: {$key}.");
            }
        }
        if (!$partial) {
            foreach ($required as $key) {
                if (!array_key_exists($key, $input) || $input[$key] === null || $input[$key] === '') {
                    throw new ApiException("Missing required field: {$key}.");
                }
            }
        }
        return $input;
    }

    protected function string(array &$data, string $key, int $limit, bool $nullable = false): void
    {
        if (!array_key_exists($key, $data)) return;
        if ($nullable && $data[$key] === null) return;
        if (!is_string($data[$key])) throw new ApiException("{$key} must be text.");
        $data[$key] = trim($data[$key]);
        if (strlen($data[$key]) > $limit) throw new ApiException("{$key} is too long.");
    }

    protected function integer(array &$data, string $key, int $min, int $max, bool $nullable = false): void
    {
        if (!array_key_exists($key, $data)) return;
        if ($nullable && $data[$key] === null) return;
        $num = filter_var($data[$key], FILTER_VALIDATE_INT);
        if ($num === false || $num < $min || $num > $max) {
            throw new ApiException("{$key} must be an integer from {$min} to {$max}.");
        }
        $data[$key] = $num;
    }

    protected function numeric(array &$data, string $key, float $min, float $max, bool $nullable = false): void
    {
        if (!array_key_exists($key, $data)) return;
        if ($nullable && $data[$key] === null) return;
        if (!is_numeric($data[$key]) || (float) $data[$key] < $min || (float) $data[$key] > $max) {
            throw new ApiException("{$key} must be a number from {$min} to {$max}.");
        }
        $data[$key] = (float) $data[$key];
    }

    protected function enum(array &$data, string $key, array $choices): void
    {
        if (!array_key_exists($key, $data)) return;
        if (!in_array($data[$key], $choices, true)) {
            throw new ApiException("Invalid {$key}.");
        }
    }

    protected function date(array &$data, string $key, bool $withTime = false): void
    {
        if (!array_key_exists($key, $data)) return;
        if (!is_string($data[$key])) throw new ApiException("Invalid {$key}.");
        $value = trim($data[$key]);
        if ($withTime) {
            $value = str_replace('T', ' ', $value);
            if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $value)) $value .= ':00';
            $format = 'Y-m-d H:i:s';
        } else {
            $format = 'Y-m-d';
        }
        $date = DateTimeImmutable::createFromFormat('!' . $format, $value);
        if (!$date || $date->format($format) !== $value) throw new ApiException("Invalid {$key}.");
        $data[$key] = $value;
    }
}
