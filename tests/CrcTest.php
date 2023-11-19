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

test('char checksum calculation', function () {
    $crc = new Crc;

    // The algorithm behind the hood performs exclusive OR against the two
    // inputs. The result is the offset of the memoization table. If we
    // pass 0 as one of the values, the result is the other argument.
    expect($crc->char(0, 0))->toBe(Crc::TABLE[0]);
    expect($crc->char(1, 0))->toBe(Crc::TABLE[1]);
    expect($crc->char(2, 0))->toBe(Crc::TABLE[2]);

    // For example, we break it down with two arguments, 1 and 7 ...
    //
    //   1(10)  =  0000 0001 (2)
    //   7(10)  =  0000 0111 (2)
    // ----------------------------
    //   XOR    =  0000 0110 (2)
    //          =          6 (10)
    //
    // The XOR calculation result is 6 and is offset number of the memoization
    // table. We make an expectation here that the result is the same as we
    // expected. To prevent overflowing, mask `0xFF` is appled to offset.
    expect($crc->char(1, 7))->toBe(Crc::TABLE[6]);
});

test('string checksum calculation', function () {
    $crc = Mockery::mock(Crc::class)->makePartial();
    // ASCII table
    // ...
    // a: 97
    // b: 98
    // c: 99
    // ...
    $crc->expects()->char(97, 0)->andReturns(0);
    $crc->expects()->char(98, 0)->andReturns(0);
    $crc->expects()->char(99, 0)->andReturns(0);
    expect($crc->string('abc', 0))->toBe(0);
});

test('int32 checksum calculation', function () {
    $crc = Mockery::mock(Crc::class)->makePartial();
    $value = 0b1001_0011_1100_0101_1010_1111_0000_0101;
    $crc->expects()->char(0b0000_0101, 0)->andReturns(0);
    $crc->expects()->char(0b1010_1111, 0)->andReturns(0);
    $crc->expects()->char(0b1100_0101, 0)->andReturns(0);
    $crc->expects()->char(0b1001_0011, 0)->andReturns(0);
    expect($crc->int32($value, 0))->toBe(0);
});

test('int64 checksum calculation', function () {
    $crc = Mockery::mock(Crc::class)->makePartial();
    $value = 0b0011_1100_1001_0110_1111_0000_0101_1010_1010_0101_0000_1111_0110_1001_1100_0011;
    $crc->expects()->int32(0b1010_0101_0000_1111_0110_1001_1100_0011, 0)->andReturns(0);
    $crc->expects()->int32(0b0011_1100_1001_0110_1111_0000_0101_1010, 0)->andReturns(0);
    expect($crc->int64($value, 0))->toBe(0);
});

test('double checksum calculation', function () {
    $crc = Mockery::mock(Crc::class)->makePartial();
    $value = 3.14;
    $crc->expects()->char(Mockery::any(), 0)->times(8)->andReturns(0);
    expect($crc->double(3.14, 0))->toBe(0);
});
