<?php

namespace Mashbo\FormFlowBundle\Events;

class SubjectWasDetermined
{
    public function __construct(private object $subject) {}

    public function getSubject(): object
    {
        return $this->subject;
    }
}