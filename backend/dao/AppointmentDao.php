<?php
require_once __DIR__ . '/BaseDao.php';

class AppointmentDao extends BaseDao
{
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo, 'appointments',
            ['trainer_id', 'client_id', 'scheduled_at', 'duration_minutes', 'status', 'meeting_url'],
            ['id', 'trainer_id', 'client_id', 'scheduled_at', 'duration_minutes', 'status', 'meeting_url']
        );
    }
}
