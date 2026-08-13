<?php

namespace App\Helpers;

class Search
{

    public static function highlightMatch(string $text, string $search): string
    {

        if (strlen($search) < 2) {

            return e($text);

        }

        return preg_replace(
            '/(' . preg_quote($search, '/') . ')/i',
            '<mark class="bg-yellow-200 rounded px-0.5">$1</mark>',
            e($text)
        );

    }

}
