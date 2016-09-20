<?php

namespace Kodus\Mail\Test\Unit;

use Kodus\Mail\SMTP\SMTPDotStuffingFilter;
use UnitTester;

/**
 * This is a pretty complex test for a fairly simple piece of code, but I had to be sure.
 *
 * The dot-stuffing filter has to maintain a two-byte trailing buffer from the previous
 * chunk, so as not to miss an "\r\n." sequence that started in the previous chunk.
 *
 * The test below attempts many different buffer-sizes, so that we're sure to hit the
 * edge-cases where an "\r\n." sequence spans two chunks.
 */
class SMTPDotStuffingCest
{
    public function performDotStuffing(UnitTester $I)
    {
        $data = str_repeat("aaaaa.aaaaa\r\n.", 100);

        $expected = $this->stuffDots($data);

        for ($chunk_size=1; $chunk_size<100; $chunk_size++) {
            $I->assertSame(
                $expected,
                $this->stuffDotsWithFilter($data, $chunk_size),
                "testing with chunk-size of {$chunk_size}"
            );
        }
    }

    /**
     * Correctly stuffs dots using a non-chunked str_replace() - guaranteed to work
     *
     * @param string $data
     *
     * @return mixed
     */
    private function stuffDots($data)
    {
        return str_replace("\r\n.", "\r\n..", $data);
    }

    /**
     * Simulate a specified buffer-size and processes data in chunks.
     *
     * @param string $data
     * @param int $chunk_size
     *
     * @return string
     */
    private function stuffDotsWithFilter($data, $chunk_size)
    {
        $stream = fopen("php://temp", "rw+");

        stream_set_write_buffer($stream, $chunk_size);

        $filter = stream_filter_append($stream, SMTPDotStuffingFilter::FILTER_NAME, STREAM_FILTER_WRITE);

        $chunks = str_split($data, $chunk_size);

        foreach ($chunks as $chunk) {
            fwrite($stream, $chunk);
        }

        stream_filter_remove($filter);

        rewind($stream);

        $contents = stream_get_contents($stream);

        fclose($stream);

        return $contents;
    }
}
