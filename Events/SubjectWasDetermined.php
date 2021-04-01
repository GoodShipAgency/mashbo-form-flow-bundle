<?php

namespace Mashbo\FormFlowBundle\Events;

class SubjectWasDetermined
{
    private $subject;

    public function __construct($subject)
    {
        $this->subject = $subject;
    }

    public function getSubject(): object
    {
        return $this->subject;
    }
}