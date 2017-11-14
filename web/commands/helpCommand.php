<?php
namespace Longman\TelegramBot\Commands\UserCommands;
use Longman\TelegramBot\Commands\Command;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Request;

class HelpCommand extends UserCommand
{

    protected $name = 'help';
    protected $description = 'Show bot commands help';
    protected $usage = '/help';
    protected $version = '1.0.0';
    public function execute()
    {
        $message = $this->getMessage();
        $chat_id = $message->getChat()->getId();
        $text    =  '/help - information about commands'. PHP_EOL
                    '/publish - create new advert'. PHP_EOL
                    '/search <text> - search advert by description';
        $data = [
            'chat_id' => $chat_id,
            'text'    => $text,
        ];
        return Request::sendMessage($data);
    }
}