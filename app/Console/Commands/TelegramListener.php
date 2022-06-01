<?php

namespace App\Console\Commands;

use danog\MadelineProto\API;
use App\Models\User;
use danog\MadelineProto\Settings;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use \App\Handlers\TelegramEventHandler;
use Throwable;

class TelegramListener extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:listen';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $settings = new Settings;
        TelegramEventHandler::startAndLoop('bot.madeline', $settings);
    }
}
