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
 * This makes sure that Linkify::processEmails() converts email addresses into
 * links.
 */
class ProcessEmailsTest extends LinkifyTest
{
    /**
     * Convert email addresses into links test.
     *
     * @test
     * @dataProvider emailProvider
     */
    public function makeEmailLinks(array $data)
    {
        $linkify = new Linkify($data['options']);

        foreach ($data['tests'] as $test) {
            $this->assertEquals(
                $test['expected'],
                $linkify->processEmails(
                    $test['test'],
                    array_key_exists('options', $test) ? $test['options'] : array()
                )
            );
        }
    }

    /**
     * Avoid turning URLs into links test.
     *
     * This makes sure that URLs, which may contain parts that look like email
     * addresses, are not turned into links by Linkify::processEmails().
     *
     * @test
     * @dataProvider urlProvider
     */
    public function avoidUrlLinks(array $data)
    {
        $linkify = new Linkify($data['options']);

        foreach ($data['tests'] as $test) {
            $this->assertEquals(
                $test['test'],
                $linkify->processEmails(
                    $test['test'],
                    array_key_exists('options', $test) ? $test['options'] : array()
                )
            );
        }
    }

    /**
     * Avoid turning non-email addresses into links.
     *
     * This makes sure that things that look like either email addresses or
     * URLs are not turned into links by Linkify::processEmails().
     *
     * @test
     * @dataProvider ignoreProvider
     */
    public function avoidNonLinks(array $data)
    {
        $linkify = new Linkify($data['options']);

        foreach ($data['tests'] as $test) {
            $this->assertEquals($test, $linkify->processEmails($test));
        }
    }
}
