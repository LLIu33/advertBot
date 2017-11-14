<?php
namespace Longman\TelegramBot\Commands\UserCommands;
use Longman\TelegramBot\Commands\Command;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Request;

class SearchCommand extends UserCommand
{

    protected $name = 'search';
    protected $description = 'Search published advert';
    protected $usage = '/search <text>';
    protected $version = '1.0.0';
    public function execute()
    {
        $message = $this->getMessage();
        $chat_id = $message->getChat()->getId();
        $text    = trim($message->getText(true));
        if ($text === '') {
            $text = 'Command usage: ' . $this->getUsage();
        }
        $data = [
            'chat_id' => $chat_id,
            'text'    => $text,
        ];
        return Request::sendMessage($data);
    }
}