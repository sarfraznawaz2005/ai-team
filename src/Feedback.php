<?php

namespace Sarfraznawaz2005\AiTeam;

class Feedback
{
    public bool $success;
    public string $suggestion;

    /**
     * @param bool $success
     * @param string $suggestion
     */
    public function __construct(bool $success = true, string $suggestion = '')
    {
        $this->success = $success;
        $this->suggestion = $suggestion;
    }

    public function isSuccessful(): bool
    {
        return $this->success;
    }
}
