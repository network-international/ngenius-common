<?php

namespace Ngenius\NgeniusCommon\Formatter;

class ValueFormatter
{
    /**
     * Uniform API amount response
     *
     * @param $currencyCode
     * @param $amount
     * @return float|int
     */
    public static function formatOrderStatusAmount($currencyCode, $amount): float|int
    {
        if (in_array($currencyCode, ['UGX', 'XOF'])) {
            $amount *= 100;
        } elseif (in_array($currencyCode, ['KWD', 'BHD', 'OMR'])) {
            $amount /= 10.000;
        }
        return $amount;
    }

    /**
     * Formats currency dependent amount
     *
     * @param $currencyCode
     * @param $amount
     * @return void
     */
    public static function formatCurrencyAmount($currencyCode, &$amount): void
    {
        if (in_array($currencyCode, ['UGX', 'XOF'])) {
            $amount /= 100;
        } elseif (in_array($currencyCode, ['KWD', 'BHD', 'OMR'])) {
            $amount *= 10;
        }
    }
}
