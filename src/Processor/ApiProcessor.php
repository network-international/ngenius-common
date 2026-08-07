<?php

namespace Ngenius\NgeniusCommon\Processor;

class ApiProcessor
{
    private array $response;

    public const NGENIUS_CAPTURE_LITERAL  = 'cnp:capture';
    public const NGENIUS_PURCHASED        = 'PURCHASED';
    public const NGENIUS_STATES_ABANDONED = ['STARTED', 'PENDING', 'AWAIT3DS', 'CANCELLED'];
    public const NGENIUS_STATES_SUCCESS   = ['AUTHORISED', self::NGENIUS_PURCHASED, 'CAPTURED', 'PARTIALLY_CAPTURED'];

    public function __construct(array $response)
    {
        $this->response = $response;
    }

    /**
     * Gets Last Transaction
     *
     * @return string|array
     */
    public function getLastTransaction(): string|array
    {
        $lastTransaction = '';
        if (isset($this->response['_embedded']['payment'][0]['_embedded'][self::NGENIUS_CAPTURE_LITERAL])
            && is_array($this->response['_embedded']['payment'][0]['_embedded'][self::NGENIUS_CAPTURE_LITERAL])
        ) {
            $lastTransaction = end(
                $this->response['_embedded']['payment'][0]
                ['_embedded'][self::NGENIUS_CAPTURE_LITERAL]
            );
        }

        return $lastTransaction;
    }

    /**
     * Gets payment id
     *
     * @return string
     */
    public function getPaymentId(): string
    {
        $paymentId = '';
        if (isset($this->response['_embedded']['payment'][0]['_id'])) {
            $transactionIdRes = explode(":", $this->response['_embedded']['payment'][0]['_id']);
            $paymentId        = end($transactionIdRes);
        }

        return $paymentId;
    }

    /**
     * Gets transaction Id
     *
     * @return string
     */
    public function getTransactionId(): string
    {
        $lastTransaction = $this->getLastTransaction();
        $transactionId   = '';
        if (isset($lastTransaction['_links']['self']['href'])) {
            $transactionArr = explode('/', $lastTransaction['_links']['self']['href']);
            $transactionId  = end($transactionArr);
        } elseif ($lastTransaction['_links']['cnp:refund']['href'] ?? false) {
            $transactionArr = explode('/', $lastTransaction['_links']['cnp:refund']['href']);
            $transactionId  = $transactionArr[count($transactionArr) - 2];
        }

        return $transactionId;
    }

    /**
     * Gets Captured Amount
     *
     * Sums up all successful (state == 'SUCCESS') captures rather than
     * overwriting the running total, so partial captures accumulate correctly.
     *
     * @return int|string
     */
    public function getCapturedAmount(): int|string
    {
        $captureAmount = 0;
        if (isset($this->response['_embedded']['payment'][0]['_embedded'][self::NGENIUS_CAPTURE_LITERAL])
            && is_array($this->response['_embedded']['payment'][0]['_embedded'][self::NGENIUS_CAPTURE_LITERAL])
        ) {
            foreach (
                $this->response['_embedded']['payment'][0]['_embedded']
                [self::NGENIUS_CAPTURE_LITERAL] as $capture
            ) {
                if (isset($capture['state']) && ($capture['state'] == 'SUCCESS')
                    && isset($capture['amount']['value'])
                ) {
                    $captureAmount += $capture['amount']['value'];
                }
            }
        }

        return $captureAmount;
    }

    /**
     * Gets the ID/reference of the latest successful capture.
     *
     * Iterates all captures under _embedded['payment'][0]['_embedded']['cnp:capture']
     * and returns the identifier of the last one with state == 'SUCCESS'.
     * Falls back to an empty string when no successful capture is found.
     *
     * @return string
     */
    public function getCaptureId(): string
    {
        $captureId = '';

        if (isset($this->response['_embedded']['payment'][0]['_embedded'][self::NGENIUS_CAPTURE_LITERAL])
            && is_array($this->response['_embedded']['payment'][0]['_embedded'][self::NGENIUS_CAPTURE_LITERAL])
        ) {
            foreach (
                $this->response['_embedded']['payment'][0]['_embedded']
                [self::NGENIUS_CAPTURE_LITERAL] as $capture
            ) {
                if (isset($capture['state']) && ($capture['state'] == 'SUCCESS')) {
                    if (isset($capture['_id'])) {
                        $idParts   = explode(':', $capture['_id']);
                        $captureId = end($idParts);
                    } elseif (isset($capture['_links']['self']['href'])) {
                        $hrefParts = explode('/', $capture['_links']['self']['href']);
                        $captureId = end($hrefParts);
                    }
                }
            }
        }

        return $captureId;
    }

    /**
     * Get the state of the N-Genius order
     *
     * @return string|null
     */
    public function getState(): ?string
    {
        return $this->response['_embedded']['payment'][0]['state'];
    }


    /**
     * @return array
     */
    public function getResponse(): array
    {
        return $this->response;
    }

    /**
     * @param array $response
     */
    public function setResponse(array $response): void
    {
        $this->response = $response;
    }

    /**
     * @param string &$paymentAction
     * @param string &$paymentState
     */
    public function processPaymentAction(&$paymentAction, &$paymentState)
    {
        if (isset($this->response['_embedded']['payment'][0]['paymentMethod']['name']) &&
            $this->response['_embedded']['payment'][0]['paymentMethod']['name'] === 'CHINA_UNION_PAY' &&
            $paymentAction === 'SALE') {
            $paymentAction = 'PURCHASE';
            $paymentState  = self::NGENIUS_PURCHASED;
        }
    }

    /**
     * Extracts payment result
     *
     * @return array|null
     */
    public function getPaymentResult(): ?array
    {
        return $this->response['_embedded']['payment'][0];
    }

    /**
     * Check if N-Genius order has been approved
     *
     * @return bool
     */
    public function isPaymentConfirmed(): bool
    {
        return in_array($this->getState(), self::NGENIUS_STATES_SUCCESS);
    }

    /**
     * Check if N-Genius order has been abandoned
     *
     * @return bool
     */
    public function isPaymentAbandoned(): bool
    {
        return in_array($this->getState(), self::NGENIUS_STATES_ABANDONED);
    }
}
