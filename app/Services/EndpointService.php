<?php

namespace App\Services;

class EndpointService
{
    private string $chars = 'abcdefghijklmnopqrstuvwxyz';
    public function makeEndpoint(int $num): string
    {
        $len = strlen($this->chars);
        if ($num <= $len - 1) {
            return $this->chars[$num];
        } elseif ($num <= ($len ** 2) + $len - 1) { //combinations of 2 chars
            $char_percent = 1 * 100 / $len; // percent of one char
            
            if (!is_float($char_modulo = $num / $len)) {
                $first_char_index = $char_modulo - 1;
                $second_char_index = $len;
            } else {
                $first_char_index = intval($char_modulo); // cut floated part
                
                // calculate second char index
                $reminder = substr(strval($char_modulo), strpos(strval($char_modulo), '.') + 1, 3);
                
                if (str_starts_with($reminder, '0')) {
                    $floatval = (float)('0.'. substr($reminder, 1));
                    $second_char_index = round($floatval * 10);
                } else {
                    $second_char_index = round(intval((int)$reminder / 10) / $char_percent);
                }
            }

            return $this->chars[(int)$first_char_index - 1] . '' . $this->chars[(int)$second_char_index - 1];
        }
    }
}

// 26z // 3.84
// 27 aa
// 28 ab
// 29 ac
// 52 az
// 53 ba
// 78 bz //
// 79 ca // 3.84
// 80 cb // 7.68
// 81 cc // 11.52
// 82 cd // 15.36
// 83 ce // 19.2
// 84 cf // 23.04
// 85 cg // 26.88