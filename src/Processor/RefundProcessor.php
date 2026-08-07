<?php

namespace Ngenius\NgeniusCommon\Processor;

use stdClass;

class RefundProcessor
{
    public static function extractUrl(stdClass $payment)
    {
        $cnpCapture = "cnp:capture";
        $cnpRefund  = 'cnp:refund';

        // 1. Handle PURCHASED / PARTIALLY_REFUNDED / PARTIALLY_CAPTURED States
        // Added PARTIALLY_CAPTURED based on KSA Wallet/Card logs
        if (in_array($payment->state, ["PURCHASED", "PARTIALLY_REFUNDED", "PARTIALLY_CAPTURED"])) {
            // KSA Card: cnp:refund nested inside cnp:capture[0]._links takes highest priority.
            // This covers both PARTIALLY_CAPTURED and PARTIALLY_REFUNDED after a card capture.
            // NOTE: cnp:cancel in _links is for voiding the remaining authorisation, NOT for
            // refunding a capture — never use it here.
            if (isset($payment->_embedded->{$cnpCapture}[0]->_links->$cnpRefund->href)) {
                return $payment->_embedded->{$cnpCapture}[0]->_links->$cnpRefund->href;
            }

            // UAE / KSA Wallet PURCHASE flows: cnp:refund at root _links level.
            if (isset($payment->_links->$cnpRefund->href)) {
                return $payment->_links->$cnpRefund->href;
            }

            // KSA Card fallback: derive refund URL from capture self link.
            if (isset($payment->_embedded->{$cnpCapture}[0]->_links->self->href)) {
                return $payment->_embedded->{$cnpCapture}[0]->_links->self->href . '/refund';
            }

            // Generic fallback using payment self link.
            if (isset($payment->_links->self->href)) {
                return $payment->_links->self->href . '/refund';
            }
        }

        // 2. Handle CAPTURED State (Common in Auth -> Capture flows)
        if ($payment->state === "CAPTURED") {
            // Check Root Level (UAE and KSA Wallet responses)
            if (isset($payment->_links->$cnpRefund->href)) {
                return $payment->_links->$cnpRefund->href;
            }

            // Check Nested Capture Array (KSA Card flows)
            if (isset($payment->_embedded->{$cnpCapture}[0]->_links->$cnpRefund->href)) {
                return $payment->_embedded->{$cnpCapture}[0]->_links->$cnpRefund->href;
            }

            // Fallback for Capture Self link (KSA fallback)
            if (isset($payment->_embedded->{$cnpCapture}[0]->_links->self->href)) {
                return $payment->_embedded->{$cnpCapture}[0]->_links->self->href . '/refund';
            }
        }

        // 3. Global Fallback (Catch-all for any other state where cnp:refund might exist)
        if (isset($payment->_links->$cnpRefund->href)) {
            return $payment->_links->$cnpRefund->href;
        }

        if (isset($payment->_embedded->{$cnpCapture}[0]->_links->$cnpRefund->href)) {
            return $payment->_embedded->{$cnpCapture}[0]->_links->$cnpRefund->href;
        }

        return "";
    }
}
