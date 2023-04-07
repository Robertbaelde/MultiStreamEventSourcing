<?php

namespace EventStream;

interface MessageHandler
{
    public function handle(Message $message): void;
}
