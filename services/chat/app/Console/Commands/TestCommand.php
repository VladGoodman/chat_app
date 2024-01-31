<?php

namespace App\Console\Commands;

use App\Services\RabbitMQService;
use Illuminate\Console\Command;
use PhpAmqpLib\Message\AMQPMessage;

class TestCommand extends Command
{
    protected $signature = 'test';

    public function handle(): void
    {
        $rabbitService = new RabbitMQService();

        $rabbitService->consume(function (AMQPMessage $msg) {
            echo "Received message: " . $msg->body . "\n";
        });
    }
}
