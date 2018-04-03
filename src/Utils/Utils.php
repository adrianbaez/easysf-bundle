<?php

namespace AdrianBaez\Bundle\EasySfBundle\Utils;

class Utils
{
    /**
     * Obtains the FQCN from a string
     * @param  string $string
     * @return string
     */
    public static function getClassFromString($string): string
    {
        $class = [];
        $tokens = @token_get_all($string);
        $count = count($tokens);
        for ($i = 0;$i < $count;$i++) {
            if ($tokens[$i][0] === T_NAMESPACE) {
                for ($j = $i + 1; $j < $count; $j++) {
                    if ($tokens[$j][0] === T_STRING) {
                        $class[] = $tokens[$j][1];
                    } elseif ($tokens[$j] === ';' || $tokens[$j] === '{') {
                        break;
                    }
                }
                $i = $j;
            } elseif ($tokens[$i][0] === T_CLASS) {
                for ($j = $i + 1; $j < $count; $j++) {
                    if ($tokens[$j] === '{') {
                        $class[] = $tokens[$i + 2][1];
                        break;
                    }
                }
                $i = $j;
            }
        }

        return implode('\\', $class);
    }
    /**
     * Obtains the FQCN from a file
     * @param  string $file
     * @return string
     */
    public static function getClassFromFile($file): string
    {
        if ($fp = @fopen($file, 'rb')) {
            $buffer = '';
            while (strpos($buffer, '{') === false && !feof($fp)) {
                $buffer .= fread($fp, 64);
            }
            fclose($fp);
            return static::getClassFromString($buffer);
        }
        return '';
    }
}
