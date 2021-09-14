<?php

/*
 * This file is part of the Linkify library.
 *
 * (c) University of Cambridge
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Misd\Linkify;

/**
 * Converts URLs and/or email addresses into HTML links.
 */
interface LinkifyInterface
{
    /**
     * Add HTML links to both URLs and email addresses.
     *
     * @param string $text    Text to process.
     * @param array  $options Options.
     *
     * @return string Processed text.
     */
    public function process($text, array $options = array());

    /**
     * Add HTML links to URLs.
     *
     * @param string $text    Text to process.
     * @param array  $options Options.
     *
     * @return string Processed text.
     */
    public function processUrls($text, array $options = array());

    /**
     * Add HTML links to email addresses.
     *
     * @param string $text    Text to process.
     * @param array  $options Options.
     *
     * @return string Processed text.
     */
    public function processEmails($text, array $options = array());
}
