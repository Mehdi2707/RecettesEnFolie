<?php

namespace App\MessageHandler;

use App\Message\SendEmailMessage;
use App\Service\SendMailService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class SendEmailMessageHandler implements MessageHandlerInterface
{
    private SendMailService $mailService;

    public function __construct(SendMailService $mailService)
    {
        $this->mailService = $mailService;
    }

    public function __invoke(SendEmailMessage $message)
    {
        // do something with your message
        $from = $message->getFrom();
        $to = $message->getTo();
        $subject = $message->getSubject();
        $template = $message->getTemplate();
        $context = $message->getContext();

        $this->mailService->send($from, $to, $subject, $template, $context);
    }


}
