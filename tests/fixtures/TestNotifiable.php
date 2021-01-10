<?php
declare(strict_types=1);

namespace Besanek\LaravelFirebaseNotifications\Tests\Fixtures;

use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;

class TestNotifiable
{
    use Notifiable;

    /** @var mixed */
    private $targets;

    /**
     * @param mixed $targets
     */
    public function __construct($targets)
    {
        $this->targets = $targets;
    }

    /**
     * @param Notification $notification
     * @return mixed
     */
    public function routeNotificationForFirebase(Notification $notification)
    {
        return $this->targets;
    }
}