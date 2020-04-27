<?php

namespace Omnipay\MercadoPago\Message;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\MercadoPago\Gateway;

abstract class AbstractRequest extends \Omnipay\Common\Message\AbstractRequest
{
    protected $liveEndpoint = 'https://api.mercadopago.com';
    protected $testEndpoint = 'https://api.mercadopago.com';

    public function sendData($data)
    {
        if ($this->getTestMode())
            $this->validate('test_access_token');
        else
            $this->validate('client_id', 'client_secret');

        $token = $this->getTestMode() ? $this->getTestAccessToken() : $this->getAccessToken();

        $url = $this->getEndpoint() . '?access_token=' . $token;

        //Llamo a pedir la preferencia de mercadopago
        $httpRequest = $this->httpClient->createRequest(
            'POST',
            $url,
            array(
                'Content-type' => 'application/json',
            ),
            $this->toJSON($data)
        );

        // Obtengo la preferencia
        $httpResponse = $httpRequest->send();
        return $this->createResponse((object) $httpResponse->json());
    }

    public function setAccessToken($value)
    {
        return $this->setParameter('access_token', $value);
    }

    public function getAccessToken()
    {
        //Si esta en la configuracion del plugin el access_token, lo traigo
        $access_token = $this->getParameter('access_token');
        if ($access_token)
            return $access_token;

        //Si no esta el parametro pues lo solicito a mercadopago
        $token_response = (new Gateway())->requestToken($this->getParameters());
        $params = $token_response->getData();
        return $token_response->sendData($params)->getData()->access_token;
    }

    public function setTestAccessToken($value)
    {
        return $this->setParameter('test_access_token', $value);
    }

    public function getTestAccessToken()
    {
        return $this->getParameter('test_access_token');
    }

    public function getClientId()
    {
        return $this->getParameter('client_id');
    }

    public function setClientId($value)
    {
        return $this->setParameter('client_id', $value);
    }

    public function getClientSecret()
    {
        return $this->getParameter('client_secret');
    }

    public function setClientSecret($value)
    {
        return $this->setParameter('client_secret', $value);
    }

    protected function getEndpoint()
    {
        return $this->getTestMode() ? $this->testEndpoint : $this->liveEndpoint;
    }

    public function toJSON($data, $options = 0)
    {
        if (version_compare(phpversion(), '5.4.0', '>=') === true) {
            return json_encode($data, $options | 64);
        }
        return str_replace('\\/', '/', json_encode($data, $options));
    }

    public function validate()
    {
        foreach (func_get_args() as $key) {
            $value = $this->parameters->get($key);
            if (!isset($value) || $value === '') {
                throw new InvalidRequestException("The $key parameter is required");
            }
        }
    }
}
