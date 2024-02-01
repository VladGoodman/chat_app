<?php

namespace App\Console\Commands;

use App\Services\RabbitMQService;
use DateTime;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpAmqpLib\Message\AMQPMessage;

class TestCommand extends Command
{
    protected $signature = 'start';

    private ?EventInfoDTO $currentEvent = null;

    private int $eventCount = 0;

    public function handle(): void
    {
        $rabbitService = new RabbitMQService();

        $rabbitService->consume(function (AMQPMessage $msg) {
            $this->readCurrentMessage($msg);
        });
    }

    private function readCurrentMessage(AMQPMessage $msg)
    {
        sleep(1);

        $this->setCurrentEvent(self::decodeMessageEvent($msg->body));

        if ($this->checkExistBeforeUnsentMessageFromAccount()) {
            (new RabbitMQService())->publish($msg->body);
            return;
        }

        echo DateTime::createFromFormat('U.u', microtime(true))->format("[H:i:s.u]") .
            ' [account: ' . $this->currentEvent->account_id .
            ' | event: ' . $this->currentEvent->event_id . ']' .
            PHP_EOL;

        $this->deleteCurrentMessageFromDB();
        $this->eventCount++;
    }

    private function setCurrentEvent(EventInfoDTO $event)
    {
        $this->currentEvent = $event;
    }

    private static function decodeMessageEvent(string $body): EventInfoDTO
    {
        return new EventInfoDTO(...json_decode($body, true));
    }

    private function checkExistBeforeUnsentMessageFromAccount(): bool
    {
        return DB::table('message_progress')
            ->where('account_id', '=', $this->currentEvent->account_id)
            ->where('event_id', '<', $this->currentEvent->event_id)
            ->exists();
    }

    private function deleteCurrentMessageFromDB(): void
    {
        DB::table('message_progress')
            ->where('account_id', '=', $this->currentEvent->account_id)
            ->where('event_id', '=', $this->currentEvent->event_id)
            ->delete();
    }
}
