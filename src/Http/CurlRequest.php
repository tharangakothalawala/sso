<?php
/**
 * @author : Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date   : 30-12-2018
 */

namespace TSK\SSO\Http;

class CurlRequest
{
    /**
     * @var resource
     */
    private $curl;

    public function __construct()
    {
        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, 120);
        curl_setopt($this->curl, CURLOPT_TIMEOUT, 120);
    }

    public function __destruct()
    {
        if (!empty($this->curl)) {
            curl_close($this->curl);
        }
    }

    /**
     * makes GET request to a given endpoint
     *
     * @param string $url external url
     * @param array $headers http headers if any [optional]
     * @return string
     */
    public function get($url, array $headers = array())
    {
        return $this->request($url, $headers);
    }

    /**
     * makes POST request to a given endpoint
     *
     * @param string $url external url
     * @param array $data data to post
     * @param array $headers http headers if any [optional]
     * @return string
     */
    public function post($url, array $data, array $headers = array())
    {
        if (!empty($postData)) {
            curl_setopt($this->curl, CURLOPT_POST, count($postData));
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, $postData);
        }

        return $this->request($url, $headers);
    }

    private function request($url, array $headers)
    {
        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_REFERER, $url);

        if (!empty($headers)) {
            curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers);
        }

        return curl_exec($this->curl);
    }
}
