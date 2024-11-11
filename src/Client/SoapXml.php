<?php

declare(strict_types=1);

namespace Rudashi\Client;

use RuntimeException;

final readonly class SoapXml
{
    private SoapVersion $version;

    public function __construct(
        private string $output,
    ) {
        $this->version = SoapVersion::determine($this->output);
    }

    public static function parse(string $string): string
    {
        $xml = new self($string);
        $prefix = $xml->version->findPrefix($string);

        if (preg_match('/<' . $prefix . ':Envelope[\s>].*?<\/' . $prefix . ':Envelope>/s', $xml->output, $matches)) {
            return $matches[0];
        }

        throw new RuntimeException('Missing mandatory "Envelope" element.');
    }
}
