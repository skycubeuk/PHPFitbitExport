<?php

class PHPFitBit
{

    public $client_id;
    public $client_secret;
    public $api_url;
    public $redirect_url;
    public $oauth_url;
    public $oauth_token_url;
    public $authorization_header;
    public $lang;
    public $access_token = null;
    public $refresh_token = null;
    public $token_expires = null;
    public $last_error = null;
    public $headers;
    public $last_http;

    public function __construct
    (
        $client_id, #Client ID, get this by creating an app
        $client_secret, #Client Secret, get this by creating an app
        $redirect_url, #Callback URL for getting an access token
        $oauth_url = 'https://www.fitbit.com/oauth2/',
        $oauth_token_url = 'https://api.fitbit.com/oauth2/token',
        $api_url = 'https://api.fitbit.com/1',
        $lang = 'en-GB'
    ) {
        $this->api_url = $api_url;
        $this->oauth_url = $oauth_url;
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->redirect_url = $redirect_url;
        $this->oauth_token_url = $oauth_token_url;
        $this->authorization_header = "Basic " . base64_encode($this->client_id . ":" . $this->client_secret);
        $this->authorization_bearer = null;
        $this->lang = $lang;
    }

    #Set the Access Token and Refresh Tokens.
    public function set_tokens($a_t, $r_t)
    {
        $this->access_token = $a_t;
        $this->refresh_token = $r_t;
        $this->authorization_bearer = "Authorization: Bearer " . $this->access_token;
    }

    #Generate an request URL
    public function requestURL()
    {
        $u = $this->oauth_url . 'authorize?response_type=code';
        $c = '&client_id=' . urlencode($this->client_id);
        $r = '&redirect_uri=' . urlencode($this->redirect_url);
        $s = '&scope=' . urlencode('activity heartrate location nutrition profile settings sleep social weight'); # Assuming we want full access
        $url = $u . $c . $s . $r;
        return $url;
    }

    #Get access_token
    public function auth($request_token)
    {
        return $this->auth_refresh($request_token, "authorization_code");
    }

    #Refresh access_token
    public function refresh($refresh_token = null)
    {
        if (is_null($refresh_token)) {
            $refresh_token = $this->refresh_token;
        }
        $t = $this->auth_refresh($refresh_token, "refresh_token");
        $this->set_tokens($t['access_token'], $t['refresh_token']);
        return $t;
    }

    private function auth_refresh($token, $type)
    {
        $u = $this->oauth_token_url;
        $d = array('grant_type' => $type, 'client_id' => $this->client_id);
        if ($type === "authorization_code") {
            $d['code'] = $token;
            $d['redirect_uri'] = $this->redirect_url;
        } elseif ($type === "refresh_token") {
            $d['refresh_token'] = $token;
        }
        $headers = array("Authorization: " . $this->authorization_header);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $u);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($d));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        curl_close($ch);
        $token = json_decode($result, true);
        return array('access_token' => $token['access_token'], 'refresh_token' => $token['refresh_token']);
    }

    #Base request
    public function get($endpoint, $parameters = null)
    {
        if ($parameters == null) {
            return json_decode($this->geturl($this->api_url . $endpoint), true);
        } else {
            return json_decode($this->geturl($this->api_url . $endpoint . $parameters), true);
        }
    }

    private function geturl($url)
    {
        $headers = array($this->authorization_bearer);
        $session = curl_init($url);
        curl_setopt($session, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($session, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($session, CURLOPT_HEADER, 1);
        $response = curl_exec($session);
        $header_size = curl_getinfo($session, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);
        $header = trim($header);
        $header = explode("\n", $header);
        $h2 = array();
        $data = substr($response, $header_size);
        foreach ($header as $h) {
            $x = explode(":", $h, 2);
            if (count($x) > 1) {
                $h2[$x[0]] = trim($x[1]);
            }
        }
        $this->headers = $h2;
        $res = curl_getinfo($session, CURLINFO_RESPONSE_CODE);
        $this->last_http = $res;
        if ($res !== 200) {
            if ($res == 401) {
                $msg = json_decode($data, true);
                if (isset($msg["errors"][0]["errorType"])) {
                    $this->last_error = $msg["errors"][0]["errorType"];
                    return false;
                }
            }
            curl_close($session);
        } else {
            curl_close($session);
            return $data;
        }
    }
}