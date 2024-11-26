<?php

namespace Ngenius\NgeniusCommon;

class NgeniusOrderStatuses
{
    static public function orderStatuses($label = 'N-Genius'): array
    {
        return [
            [
                'status' => 'wc-ng-pending',
                'label'  => $label . ' Pending',
            ],
            [
                'status' => 'wc-ng-processing',
                'label'  => $label . ' Processing',
            ],
            [
                'status' => 'wc-ng-failed',
                'label'  => $label . ' Failed',
            ],
            [
                'status' => 'wc-ng-complete',
                'label'  => $label . ' Complete',
            ],
            [
                'status' => 'wc-ng-authorised',
                'label'  => $label . ' Authorised',
            ],
            [
                'status' => 'wc-ng-captured',
                'label'  => $label . ' Captured',
            ],
            [
                'status' => 'wc-ng-part-refunded',
                'label'  => $label . ' Partially Refunded',
            ],
            [
                'status' => 'wc-ng-auth-reversed',
                'label'  => $label . ' Auth Reversed',
            ],
            [
                'status' => 'wc-ng-refunded',
                'label'  => $label . ' Fully Refunded',
            ],
        ];
    }

    static public function magentoOrderStatuses($label = 'N-Genius'): array
    {
        return [
            [
                'status' => 'ngenius_pending',
                'label'  => $label . ' Pending',
            ],
            [
                'status' => 'ngenius_processing',
                'label'  => $label . ' Processing',
            ],
            [
                'status' => 'ngenius_failed',
                'label'  => $label . ' Failed',
            ],
            [
                'status' => 'ngenius_complete',
                'label'  => $label . ' Complete',
            ],
            [
                'status' => 'ngenius_authorised',
                'label'  => $label . ' Authorised',
            ],
            [
                'status' => 'ngenius_fully_captured',
                'label'  => $label . ' Fully Captured',
            ],
            [
                'status' => 'ngenius_partially_captured',
                'label'  => $label . ' Partially Captured',
            ],
            [
                'status' => 'ngenius_fully_refunded',
                'label'  => $label . ' Fully Refunded',
            ],
            [
                'status' => 'ngenius_partially_refunded',
                'label'  => $label . ' Partially Refunded',
            ],
            [
                'status' => 'ngenius_auth_reversed',
                'label'  => $label . ' Auth Reversed',
            ],
            [
                'status' => 'ngenius_declined',
                'label'  => $label . ' Declined',
            ],
        ];
    }
}
