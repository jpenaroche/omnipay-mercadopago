<?php

namespace Omnipay\MercadoPago\Message;

class PurchaseRequest extends AbstractRequest
{
    public function getItemData()
    {
        // This is for a single item offer gathered from shopaholic
        $items = [];
        $properties['title'] = 'Orden Nro. '.$this->getDescription();
        $properties['quantity'] = 1;
        $properties['currency_id'] = $this->getCurrency();
        $properties['unit_price'] = (double)($this->formatCurrency($this->getAmount()));
        $items [] = $properties;
        return $items;
    }

    public function getData()
    {
        $purchaseObject = [
            'items'              => $this->getItemData(),
            'auto_return'        => 'approved',
            'back_urls'          => [
                'success' => $this->getReturnUrl(),
                'pending' => $this->getReturnUrl(),
                'failure' => $this->getCancelUrl(),
            ],
            /* 'payment_methods'    => [
                'excluded_payment_types' => [
                    ["id" => "ticket"],
                    ["id" => "atm"]
                ]
            ] */
        ];
        return $purchaseObject;

    }

    protected function createResponse($data)
    {
        return $this->response = new PurchaseResponse($this, $data);
    }

    protected function getEndpoint()
    {
        return $this->getTestMode() ? ($this->testEndpoint . '/checkout/preferences') : ($this->liveEndpoint . '/checkout/preferences');
    }

}

?>
