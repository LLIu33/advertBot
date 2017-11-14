<?php
namespace Longman\TelegramBot\Commands\SystemCommands;
use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Request;

class startCommand extends SystemCommand {
    protected $name = 'start';
    protected $description = 'Start command';
    protected $usage = '/start';
    protected $version = '1.0.0';

    public function execute()
    {
        $message = $this->getMessage();
        $chat_id = $message->getChat()->getId();
        $text    = 'Hi there!' . PHP_EOL . 'Type /help to see all commands!';
        $data = [
            'chat_id' => $chat_id,
            'text'    => $text,
        ];
        return Request::sendMessage($data);
    }
}