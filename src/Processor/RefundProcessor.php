<?php

namespace Ngenius\NgeniusCommon\Processor;

use stdClass;

class RefundProcessor
{
    /**
     * Resolves the refund URL for a payment from its HATEOAS links.
     *
     * Handles UAE and KSA (Card and Wallet) response shapes across the
     * PURCHASED, PARTIALLY_REFUNDED, PARTIALLY_CAPTURED and CAPTURED states.
     *
     * @param stdClass $payment
     *
     * @return string The refund URL, or an empty string when none is available.
     */
    public static function extractUrl(stdClass $payment): string
    {
        $cnpCapture = 'cnp:capture';
        $cnpRefund  = 'cnp:refund';

        $state = $payment->state ?? '';

        if (in_array($state, ['PURCHASED', 'PARTIALLY_REFUNDED', 'PARTIALLY_CAPTURED'], true)) {
            if (isset($payment->_embedded->{$cnpCapture}[0]->_links->{$cnpRefund}->href)) {
                return $payment->_embedded->{$cnpCapture}[0]->_links->{$cnpRefund}->href;
            }

            if (isset($payment->_links->{$cnpRefund}->href)) {
                return $payment->_links->{$cnpRefund}->href;
            }

            if (isset($payment->_embedded->{$cnpCapture}[0]->_links->self->href)) {
                return $payment->_embedded->{$cnpCapture}[0]->_links->self->href . '/refund';
            }

            if (isset($payment->_links->self->href)) {
                return $payment->_links->self->href . '/refund';
            }
        }

        if ($state === 'CAPTURED') {
            if (isset($payment->_links->{$cnpRefund}->href)) {
                return $payment->_links->{$cnpRefund}->href;
            }

            if (isset($payment->_embedded->{$cnpCapture}[0]->_links->{$cnpRefund}->href)) {
                return $payment->_embedded->{$cnpCapture}[0]->_links->{$cnpRefund}->href;
            }

            if (isset($payment->_embedded->{$cnpCapture}[0]->_links->self->href)) {
                return $payment->_embedded->{$cnpCapture}[0]->_links->self->href . '/refund';
            }
        }

        if (isset($payment->_links->{$cnpRefund}->href)) {
            return $payment->_links->{$cnpRefund}->href;
        }

        if (isset($payment->_embedded->{$cnpCapture}[0]->_links->{$cnpRefund}->href)) {
            return $payment->_embedded->{$cnpCapture}[0]->_links->{$cnpRefund}->href;
        }

        return '';
    }
}
