<?php

namespace Sarfraznawaz2005\AiTeam;

class Helper
{
    /**
     * @param $text
     * @param $color
     * @param $style
     * @return mixed|string
     */
    public static function Text($text, $color, $style = '')
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

    public static function ensureUtf8Encoding($data)
    {
        if (extension_loaded('mbstring')) {
            return $data;
        }

        if (is_string($data)) {
            return mb_convert_encoding($data, 'UTF-8', 'UTF-8');
        } elseif (is_array($data)) {
            $result = [];

            foreach ($data as $key => $value) {
                $result[$key] = self::ensureUtf8Encoding($value);
            }

            return $result;
        }

        return $data;
    }
}
