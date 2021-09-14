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

use PHPUnit_Framework_TestCase as TestCase;

/**
 * Abstract Linkify test.
 */
abstract class LinkifyTest extends TestCase
{
    private function loadData($filename)
    {
        $data = json_decode(file_get_contents(__DIR__ . '/../../../data/' . $filename), true);
        if (null === $data) {
            $this->markTestIncomplete('Failed to read test data file ' . $filename);
        }

        return $data;
    }

    public function urlProvider()
    {
        return array(
            array($this->loadData('url.json')),
            array($this->loadData('url-options.json')),
        );
    }

    public function emailProvider()
    {
        return array(
            array($this->loadData('email.json')),
            array($this->loadData('email-options.json')),
        );
    }

    public function ignoreProvider()
    {
        return array(
            array($this->loadData('ignore.json')),
            array($this->loadData('ignore-options.json')),
        );
    }

    public function callbackProvider()
    {
        return $this->loadData('callback.json');
    }
}
