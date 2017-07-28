<?php

namespace Acelle\Extra;

class SendGrid
{
    const API_ENDPOINT = 'https://api.sendgrid.com/v3/';

    protected $client;

    public function __construct($auth)
    {
        $params = [
            'base_uri' => self::API_ENDPOINT,
            'headers' => [
                'Content-Type' => 'application/json',
            ]
        ];

        if (array_key_exists('api', $auth)) {
            $params['headers']['Authorization'] =  "Bearer {$auth['api']}";
        } else {
            $params['auth'] = [ $auth['username'], $auth['password']];
        }

        $this->client = new \GuzzleHttp\Client($params);
    }

    public function getClient()
    {
        return $this->client;
    }

    public function getAllIps()
    {
        $ips = $this->request('GET', 'ips');
        $ips = array_map(function($ip) {
            return $ip['ip'];
        }, $ips);
        return $ips;
    }

    public function request($method, $module, $params = [])
    {
        if ($method == 'GET') {
            $uri = $module . "?" . http_build_query($params);
            $data = [];
        } else {
            $uri = $module;
            $data = ['json' => $params];
        }
        $response = $this->getClient()->request($method, $uri, $data);
        return json_decode($response->getBody()->read(1024 * 1024), true);
    }

    public function delete($subuser_name)
    {
        $this->request('DELETE', 'subusers/' . urlencode($subuser_name));
    }

    public function createSubUser($params)
    {
        $params['ips'] = $this->getAllIps();
        $this->request('POST', 'subusers', $params);
    }

    public function subUserExists($username)
    {
        return !empty($this->request('GET', 'subusers', ['username' => $username]));
    }

    public function createApiKey($params)
    {
        $response = $this->request('POST', 'api_keys', [
            'name' => $params['name'],
            'scopes' => [
                'user.webhooks.event.settings.read',
                'user.webhooks.event.settings.update',
                'user.webhooks.parse.settings.create',
                'user.webhooks.parse.settings.delete',
                'user.webhooks.parse.settings.read',
                'user.webhooks.parse.settings.update',
                'user.webhooks.parse.stats.read',
                'mail.batch.create',
                'mail.batch.delete',
                'mail.batch.read',
                'mail.batch.update',
                'mail.send',
            ]
        ]);

        return $response;
    }
}
