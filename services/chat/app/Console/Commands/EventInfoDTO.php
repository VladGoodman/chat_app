<?php

namespace App\Console\Commands;

class EventInfoDTO
{
    public function __construct(
        public readonly int $account_id,
        public readonly int $event_id,
    )
    {
    }
}
