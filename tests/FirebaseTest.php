<?php
declare(strict_types=1);

namespace Besanek\LaravelFirebaseNotifications\Tests;

use Besanek\LaravelFirebaseNotifications\Exceptions\ChannelException;
use Besanek\LaravelFirebaseNotifications\Tests\Fixtures\TestNotifiable;
use Besanek\LaravelFirebaseNotifications\Tests\Fixtures\TestNotification;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Arr;
use Kreait\Firebase\Exception\Messaging\NotFound;
use Kreait\Firebase\Messaging\MulticastSendReport;
use Kreait\Firebase\Messaging\SendReport;
use stdClass;

class FirebaseTest extends TestCase
{
    /**
     * @var \Closure
     */
    private $assert;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app->extend('events', function (Dispatcher $dispatcher) {
            $dispatcher->listen(NotificationSent::class, function (NotificationSent $notification) {
                if (is_callable($this->assert)) {
                    call_user_func($this->assert, $notification);
                }
            });
            return $dispatcher;
        });

    }

    public function testEmptyTarget(): void
    {
        $this->assert = function (NotificationSent $notification) {
            /** @var MulticastSendReport $report */
            $report = $notification->response;

            $this->assertSame(0, $report->successes()->count());
            $this->assertSame(0, $report->failures()->count());
        };

        $notification = new TestNotification();

        $notifiable = new TestNotifiable(null);
        $notifiable->notify($notification);

        $notifiable = new TestNotifiable([]);
        $notifiable->notify($notification);
    }

    public function testOneTarget(): void
    {
        $this->assert = function (NotificationSent $notification) {
            /** @var MulticastSendReport $report */
            $report = $notification->response;

            $this->assertSame(0, $report->successes()->count());
            $this->assertSame(1, $report->failures()->count());

            $filtered = array_filter($report->failures()->getItems(), function(SendReport $result) {
                return $result->error() instanceof NotFound;
            });

            $this->assertCount(1, $filtered);
        };

        $notification = new TestNotification();

        $notifiable = new TestNotifiable($this->getDeviceId());
        $notifiable->notify($notification);


        $notifiable = new TestNotifiable([$this->getDeviceId()]);
        $notifiable->notify($notification);
    }


    public function testMultipleTargets(): void
    {
        $this->assert = function (NotificationSent $notification) {
            /** @var MulticastSendReport $report */
            $report = $notification->response;

            $this->assertSame(0, $report->successes()->count());
            $this->assertSame(2, $report->failures()->count());

            $filtered = array_filter($report->failures()->getItems(), function(SendReport $result) {
                return $result->error() instanceof NotFound;
            });

            $this->assertCount(2, $filtered);
        };

        $notification = new TestNotification();

        $notifiable = new TestNotifiable([$this->getDeviceId(), $this->getDeviceId()]);
        $notifiable->notify($notification);
    }

    public function testInvalidTarget(): void
    {
        $this->assert = function (NotificationSent $notification) {
            /** @var MulticastSendReport $report */
            $report = $notification->response;

            $this->assertSame(0, $report->successes()->count());
        };

        $notification = new TestNotification();

        $notifiable = new TestNotifiable(new stdClass());
        $notifiable->notify($notification);
    }

    private function getDeviceId() {
        $devices = [
            'f5uLIS-ntTM:APA91bGVxWyE9YiHZs_bgCkwEWYXC138Ju8Ah4BCC5_JQVOVSSZ7aZbiQuhk8npphOmaivTcRijysmfj9g1CY6bp5GTdaGHsZxtNdKV1JHtnqF4Y8pe2hlGnPBs9RdbJ_1D22HS2dq69',
            'cJHF3CEcOb4:APA91bEIrrWiCb1vzIC4qp_iv7IgGM0hT_TRx5GEbj06YwMyTIKqPq3JF4b1xUm005nDtIg0FYFVA1B3qeURk7vSr-m2KanmpZbDehwaj0itp0smwrRRf-naUtNwnmYa2n3AFTUvFAn7',
            'ceQkC6KEBKg:APA91bFWwiJ6JpSR-2lU0xqYWDYY5j2TxGtjQz4pQjZk6YkgxlHMmWQwMOT--qf3VhfrAcXYW-otuKZugyGRDkPNcxi4i8FWY2groOy4kqm4yO_LknlGiDs_N5dMYWR63NPvCwu4FY3r',
            'dre0Gm653ic:APA91bEJdCORqVhK_x-eKdIVEMKBkHUCw1T4NXiNmvZ-C-hOKP7_QDRpkyPjopVp0ElDCk56gjwoUfxaAIPbw1ZpuHwVJoi3DpXvY354cDMI2GCH_COWjjKOCmOWlZC1miC0tnjkp5Om',
        ];

        static $i = 0;

        return Arr::get($devices, $i++);
    }
}
