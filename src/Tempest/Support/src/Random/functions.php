<?php

declare(strict_types=1);

namespace Tempest\Support\Random {
    use InvalidArgumentException;

    use function log;

    /**
     * Returns a securely generated random string of the given length. The string is
     * composed of characters from the given alphabet string.
     *
     * If the alphabet argument is not specified, the returned string will be composed of
     * the alphanumeric characters.
     *
     * @param int<0, max> $length The length of the string to generate.
     *
     * @throws InvalidArgumentException If $alphabet length is outside the [2^1, 2^56] range.
     */
    function secure_string(int $length, ?string $alphabet = null): string
    {
        if ($length === 0) {
            return '';
        }

        $alphabet ??= '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $alphabet_size = mb_strlen($alphabet);
        $bits = (int) \ceil(log($alphabet_size, 2.0));

        if ($bits < 1 || $bits > 56) {
            throw new InvalidArgumentException('$alphabet\'s length must be in [2^1, 2^56]');
        }

        $ret = '';
        while ($length > 0) {
            /** @var int<0, max> $urandom_length */
            $urandom_length = (int) ceil(((float) (2 * $length * $bits)) / 8.0);
            $data = random_bytes($urandom_length);

            $unpacked_data = 0;
            $unpacked_bits = 0;
            for ($i = 0; $i < $urandom_length && $length > 0; ++$i) {
                // Unpack 8 bits
                /** @var array<int, int> $v */
                $v = unpack('C', $data[$i]);
                $unpacked_data = ($unpacked_data << 8) | $v[1];
                $unpacked_bits += 8;

                // While we have enough bits to select a character from the alphabet, keep
                // consuming the random data
                for (; $unpacked_bits >= $bits && $length > 0; $unpacked_bits -= $bits) {
                    $index = $unpacked_data & ((1 << $bits) - 1);
                    $unpacked_data >>= $bits;
                    // Unfortunately, the alphabet size is not necessarily a power of two.
                    // Worst case, it is 2^k + 1, which means we need (k+1) bits and we
                    // have around a 50% chance of missing as k gets larger
                    if ($index < $alphabet_size) {
                        $ret .= $alphabet[$index];
                        --$length;
                    }
                }
            }
        }

        return $ret;
    }
}
