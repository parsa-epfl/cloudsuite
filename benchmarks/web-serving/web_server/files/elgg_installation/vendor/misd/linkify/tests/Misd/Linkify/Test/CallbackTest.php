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
 * This makes sure that Linkify::process(array('callback' => …)) calls the callback with the right parameters
 *
 * @author Arthur Darcet <arthur@darcet.fr>
 */
class CallbackTest extends LinkifyTest
{
    /**
     * Convert URLs into links with a callback.
     *
     * @test
     * @dataProvider callbackProvider
     */
    public function makeLinkWithCallback($test, $expected)
    {
        $linkify = new Linkify(array(
            'callback' => function ($url, $caption, $isEmail) {
                return sprintf('<callback href="%s" mail="%s">%s</callback>', $url, $isEmail, $caption);
            },
        ));

        $this->assertEquals($expected, $linkify->process($test));
    }
}
