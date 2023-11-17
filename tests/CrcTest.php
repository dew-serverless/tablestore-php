<?php

use Dew\Tablestore\Crc;

test('memoization table', function () {
    expect(Crc::TABLE)->toBeArray()->toHaveLength(256);
});

test('memoization validation', function ($i, $expected) {
    expect(Crc::TABLE[$i])->toBe($expected);
})->with(function () {
    $calculate = function (int $uint8) {
        // The polynomial x^8 + x^2 + x + 1 in binary representation.
        // Let's simply substitute x = 10 and evaluate ...
        //
        //   10^8 + 10^2 + 10 + 1
        // = 1_0000_0000 + 100 + 10 + 1
        // = 1_0000_0111
        //   ~~~~~~~~~~~
        //   Overflow cause of value stored in unsigned integer with 8 bit.
        //
        // The final value of the polynomial after stripping the overflowed digit
        // becomes 0000_0111 which is a decimal number 111 and we could see it
        // in binary form that the value is 7. So how about set it in hex :)
        $polynomial = 0x07;

        // The mask represents a value of 128.
        $mask = 0x80;

        for ($i = 0; $i < 8; $i++) {
            $uint8 = ($uint8 << 1) ^ ($uint8 & $mask ? $polynomial : 0);
        }

        return $uint8 & 0xFF;
    };

    $data = [];

    for ($i = 0; $i < 256; $i++) {
        $data['index '.$i] = [$i, $calculate($i)];
    }

    return $data;
});
