<?php
declare(strict_types=1);

namespace Besanek\LaravelFirebaseNotifications\Tests\Fixtures;

use Illuminate\Notifications\Notification;
use Kreait\Firebase\Messaging\CloudMessage;

class TestNotification extends Notification
{
    public function via(): array
    {
        return ['firebase'];
    }

    public function toFirebase(): CloudMessage
    {
        return CloudMessage::new()->withData([
            'test' => 'foo',
        ]);
    }
}
