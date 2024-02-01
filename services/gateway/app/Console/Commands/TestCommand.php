<?php

namespace App\Console\Commands;

use App\Services\RabbitMQService;
use DateTime;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestCommand extends Command
{
    private const EVENTS_AMOUNT = 10_000;
    private const ACCOUNTS_AMOUNT = 1_000;

    private ?array $currentEventList = null;

    protected $signature = 'start';

    public function handle(): void
    {
        $this->deleteBeforeStartEvents();

        echo DateTime::createFromFormat('U.u', microtime(true))->format("[H:i:s.u]") .
            ' | Generated ' . count($this->getOrSetCurrentEventList()) . ' events' . PHP_EOL;

        $this->saveCurrentEventListInDatabase();
        $this->publishCurrentEventListInQueue();
    }

    private function deleteBeforeStartEvents(): void
    {
        DB::table('message_progress')->delete();
    }


    /**
     * Получить сгенерированый список ивентов
     *
     * @return array
     */
    private function getOrSetCurrentEventList(): array
    {
        if (is_null($this->currentEventList)) {
            $this->currentEventList = self::generateEventList();
        }

        return $this->currentEventList;
    }

    /**
     * Генерирует ивенты для пользователей
     *
     * @return array
     * @throws \Exception
     */
    private function generateEventList(): array
    {
        for ($i = 0; $i <= self::EVENTS_AMOUNT; $i++) {
            $result[] = [
                'account_id' => random_int(1, self::ACCOUNTS_AMOUNT),
                'event_id' => random_int(1, self::EVENTS_AMOUNT),
            ];
        }

        return array_map('unserialize', array_unique(array_map('serialize', $result)));
    }

    /**
     * Сохранить в бд текущий список ивентов
     *
     * @return void
     */
    private function saveCurrentEventListInDatabase(): void
    {
        DB::table('message_progress')->insert($this->getOrSetCurrentEventList());
    }

    /**
     * Опубликовать в очередь текущий список ивентов
     *
     * @return void
     */
    private function publishCurrentEventListInQueue(): void
    {
        $rabbitService = new RabbitMQService();

        foreach ($this->getOrSetCurrentEventList() as $event) {
            $rabbitService->publish(json_encode($event));
        }
    }
}
