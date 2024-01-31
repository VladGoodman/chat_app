<?php

namespace App\Services;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQService
{
    private AMQPStreamConnection $connection;
    private AMQPChannel $channel;

    private string $exchange = 'application';
    private string $queue = 'chat';
    private string $routingKey = 'chat';

    public function __construct()
    {
        $this->connection = new AMQPStreamConnection(
            env('RABBITMQ_HOST'),
            env('RABBITMQ_PORT'),
            env('RABBITMQ_LOGIN'),
            env('RABBITMQ_PASSWORD'),
        );

        $this->channel = $this->connection->channel();

        $this->channel->exchange_declare($this->exchange, 'direct', false, true, false);
        $this->channel->queue_declare($this->queue, false, true, false, false);
        $this->channel->queue_bind($this->queue, $this->exchange, $this->routingKey);
    }

    public function publish($message): void
    {
        $msg = new AMQPMessage($message);
        $this->channel->basic_publish($msg, $this->exchange, $this->routingKey);
    }

    public function consume(callable $callback): void
    {
        $this->channel->basic_consume($this->queue, '', false, true, false, false, $callback);

        while ($this->channel->is_consuming()) {
            $this->channel->wait();
        }
    }

    public function __destruct()
    {
        $this->channel->close();
        $this->connection->close();
    }
}

//
//ChatGPT (1:55:23 AM): Есть несколько вариантов для обработки 10 000 событий для разных пользователей в порядке их создания без создания отдельной очереди для каждого пользователя.
//
//Один из подходов - использовать routing_key, чтобы маршрутизировать сообщения в одну общую очередь, откуда потребители будут забирать их и обрабатывать. При этом важно, чтобы каждое сообщение содержало информацию о пользователе, например, идентификатор пользователя, и потребители могли фильтровать сообщения по этому параметру.
//
//Пример настройки и отправки сообщений с использованием routing_key:
//
//python
//import pika
//
//connection = pika.BlockingConnection(pika.ConnectionParameters('localhost'))
//channel = connection.channel()
//
//# Определение общей очереди
//channel.queue_declare(queue='events_queue', durable=True)
//
//# Отправка 10000 событий с различными routing_key для каждого пользователя
//for i in range(10000):
//    user_id = i % 100  # Пример распределения пользователей по ключам
//    message = f"Scheduled event {i} for user {user_id}"
//    channel.basic_publish(exchange='', routing_key=f"user_{user_id}", body=message,
//                          properties=pika.BasicProperties(
//                                  delivery_mode=2,  # Сообщения сохраняются при перезапуске брокера
//                              ))
//
//connection.close()
//
//
//В этом примере каждое сообщение маршрутизируется в общую очередь `events_queue`, используя различные `routing_key` на основе информации о пользователе. Потребители могут затем подключиться к этой общей очереди и фильтровать сообщения на основе `routing_key`.
//
//Потребители могут создать несколько потребителей, каждый из которых будет прослушивать сообщения с конкретным `routing_key`, таким образом, обеспечивая обработку сообщений для различных пользователей параллельно.
