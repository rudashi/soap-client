<?php

declare(strict_types=1);

namespace Rudashi\Client;

use RuntimeException;
use ValueError;

enum SoapVersion
{
    case SOAP_1_1;
    case SOAP_1_2;

    public static function determine(string $string): self
    {
        foreach (self::cases() as $case) {
            if (str_contains($string, $case->schema())) {
                return $case;
            }
        }

        throw new ValueError('Unknown SOAP XML type.');
    }

    public function findPrefix(string $string): string
    {
        if (preg_match('/xmlns:([\w-]*)="' . preg_quote($this->schema(), '/') . '/', $string, $matches)) {
            return $matches[1];
        }

        throw new RuntimeException('SOAP namespace prefix not found.');
    }

    public function schema(): string
    {
        return match ($this) {
            self::SOAP_1_1 => 'http://schemas.xmlsoap.org/soap/envelope',
            self::SOAP_1_2 => 'http://www.w3.org/2003/05/soap-envelope',
        };
    }
}
