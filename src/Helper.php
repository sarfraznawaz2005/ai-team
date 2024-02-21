<?php

namespace Sarfraznawaz2005\AiTeam;

class Helper
{
    // ANSI color codes
    public static array $colors = [
        'reset' => "\033[0m",
        'red' => "\033[31m",
        'green' => "\033[32m",
        'yellow' => "\033[33m",
        'blue' => "\033[34m",
        'magenta' => "\033[35m",
        'cyan' => "\033[36m",
        'white' => "\033[37m",
        'bold' => "\033[1m",
        'underline' => "\033[4m",
    ];

    public static function Text($text, $color, $style = '')
    {
        if (PHP_SAPI !== 'cli') {
            return $text;
        }

        $colorCode = self::$colors[$color] ?? '';
        $styleCode = self::$colors[$style] ?? '';

        return $styleCode . $colorCode . $text . self::$colors['reset'] . PHP_EOL . PHP_EOL;
    }
}
