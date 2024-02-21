<?php

namespace Sarfraznawaz2005\AiTeam;

class Task
{
    private $description;

    public function __construct(string $description)
    {
        $this->description = $description;
    }

    public function getDescription()
    {
        return $this->description;
    }

}
