<?php

namespace Sarfraznawaz2005\AiTeam\Contracts;

interface DataProviderInterface
{
    public function get(): string|callable;
}
