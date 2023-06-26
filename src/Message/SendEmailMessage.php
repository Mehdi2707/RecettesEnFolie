<?php

namespace App\Message;

final class SendEmailMessage
{
    /*
     * Add whatever properties and methods you need
     * to hold the data for this message class.
     */

     private $from;
     private $to;
     private $subject;
     private $template;
     private $context;

     public function __construct(string $from, string $to, string $subject, string $template, array $context)
     {
         $this->from = $from;
         $this->to = $to;
         $this->subject = $subject;
         $this->template = $template;
         $this->context = $context;
     }

     public function getFrom(): string
     {
         return $this->from;
     }

    public function getTo(): string
    {
        return $this->to;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function getContext(): array
    {
        return $this->context;
    }
}
