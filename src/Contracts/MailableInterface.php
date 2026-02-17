<?php

namespace RubberSolutions\Mailer\Contracts;

interface MailableInterface
{
    public function getContent(): string;
    
    public function getSubject(): string;
    
    public function getStoreCode(): string;
}
