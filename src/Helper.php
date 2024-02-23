<?php

namespace Sarfraznawaz2005\AiTeam;

class Helper
{
    /**
     * @param string $text
     * @param string $color
     * @param string $style
     * @return string
     */
    public static function Text(string $text, string $color, string $style = ''): string
    {
        if (PHP_SAPI !== 'cli') {
            return $text;
        }

        // ANSI color codes
        $colors = [
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

        $colorCode = $colors[$color] ?? '';
        $styleCode = $colors[$style] ?? '';

        return $styleCode . $colorCode . $text . $colors['reset'] . PHP_EOL . PHP_EOL;
    }

    public static function outputText(string $text, string $color, string $style = ''): string
    {
        echo self::Text($text, $color, $style);
    }
}
