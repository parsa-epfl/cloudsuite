<?php

/*
 * This file is part of the Linkify library.
 *
 * (c) University of Cambridge
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Misd\Linkify\Test;

use Misd\Linkify\Linkify;

/**
 * This makes sure that Linkify::processUrls() converts URLs into links.
 */
class ProcessUrlsTest extends LinkifyTest
{
    /**
     * Convert URLs into links test.
     *
     * @test
     * @dataProvider urlProvider
     */
    public function makeUrlLinks(array $data)
    {
        $linkify = new Linkify($data['options']);

        foreach ($data['tests'] as $test) {
            $this->assertEquals(
                $test['expected'],
                $linkify->processUrls($test['test'], array_key_exists('options', $test) ? $test['options'] : array())
            );
        }
    }

    /**
     * Avoid turning email address into URL links test.
     *
     * This makes sure that email addresses are not turned into links by
     * Linkify::processUrls().
     *
     * @test
     * @dataProvider emailProvider
     */
    public function avoidEmailLinks(array $data)
    {
        $linkify = new Linkify($data['options']);

        foreach ($data['tests'] as $test) {
            $this->assertEquals(
                $test['test'],
                $linkify->processUrls(
                    $test['test'],
                    array_key_exists('options', $test) ? $test['options'] : array()
                )
            );
        }
    }

    /**
     * Avoid turning non-URLs into links.
     *
     * This makes sure that things that look like either URLs or email
     * addresses are not turned into links by Linkify::processUrls().
     *
     * @test
     * @dataProvider ignoreProvider
     */
    public function avoidNonLinks(array $data)
    {
        $linkify = new Linkify($data['options']);

        foreach ($data['tests'] as $test) {
            $this->assertEquals($test, $linkify->processUrls($test));
        }
    }
}
