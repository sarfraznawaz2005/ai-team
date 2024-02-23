<?php

namespace Sarfraznawaz2005\AiTeam\Contracts;

interface LLMProvider
{
    public function generateText(string $prompt): string;
}