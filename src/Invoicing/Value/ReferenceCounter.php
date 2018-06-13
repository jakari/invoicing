<?php

namespace Invoicing\Value;

class ReferenceCounter
{
    public function checksum($base) : int {
        $values = [7, 3, 1];
        $total = 0;

        foreach (array_reverse(str_split($base)) as $i => $number) {
            $calcValue = $values[$i % 3];

            $total += $number * $calcValue;
        }

        $nextTenth = (int) ceil($total / 10) * 10;
        $checksum = $nextTenth - $total;

        if ($checksum === 10) {
            return 0;
        }

        return $checksum;
    }
}
