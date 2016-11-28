<?php

namespace Acelle\Extra;

class SendGrid
{
    const SENDGRID_BASE_URI = 'https://api.sendgrid.com/v3/';

    protected $client;
    protected $module;

    public function __construct($username, $password) 
    {
        $this->client = new \GuzzleHttp\Client(['base_uri' => self::SENDGRID_BASE_URI, 'auth' => [$username, $password]]);
    }

    private function getClient()
    {
        return $this->client;
    }

    public function subusers()
    {
        $this->module = 'subusers';
        return $this;
    }

    public function ips()
    {
        $this->module = 'ips';
        return $this;
    }

    public function api_keys()
    {
        $this->module = 'api_keys';
        return $this;
    }

    public function request($method, $params = [])
    {
        if (is_null($this->module)) {
            throw new \Exception('method not allowed');
        }
        $response = $this->getClient()->request($method, $this->module, ['json' => $params ]);
        return $response;
    }

    public function delete($subuser_name)
    {
        if ($this->module == 'subusers') {
            $this->getClient()->request('DELETE', 'subusers/' . urlencode($subuser_name));
        } else {
            throw new \Exception('method not allowed');
        }
    }
}

?>
