<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 30-12-2018
 */

namespace TSK\SSO\Http;

/**
 * @codeCoverageIgnore
 * @internal
 * @package TSK\SSO\Storage
 *
 * This can send GET & POST requests. @todo: Add Guzzle
 */
class CurlRequest
{
    /**
     * @var resource
     */
    private $curl;

    public function __construct()
    {
        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, true);
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
     * makes a GET request to a given endpoint
     *
     * @param string $url external url
     * @param array $headers http headers if any [optional]
     * @return string
     */
    public function get($url, array $headers = array())
    {
        curl_setopt($this->curl, CURLOPT_POST, false);

        return $this->request($url, $headers);
    }

    /**
     * makes a POST request to a given endpoint
     *
     * @param string $url external url
     * @param array $data [optional] data to post
     * @param array $headers [optional] http headers if any
     * @return string
     */
    public function post($url, array $data = array(), array $headers = array())
    {
        if (!empty($data)) {
            curl_setopt($this->curl, CURLOPT_POST, count($data));
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, $data);
        }

        return $this->request($url, $headers);
    }

    /**
     * makes a POST request to a given endpoint using URL encoded data
     *
     * @param string $url external url
     * @param string $rawData [optional] data to post
     * @param array $headers [optional] http headers if any
     * @return string
     */
    public function postUrlEncoded($url, $urlEncodedData = null, array $headers = array())
    {
        if (!empty($urlEncodedData)) {
            curl_setopt($this->curl, CURLOPT_POST, strlen($urlEncodedData));
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, $urlEncodedData);
        }

        $headers['Content-Type'] = 'application/x-www-form-urlencoded';

        return $this->request($url, $headers);
    }

    /**
     * makes a DELETE request to a given endpoint using Basic Authentication as per RFC-2617
     * @see https://www.ietf.org/rfc/rfc2617.txt
     *
     * @param string $url external url
     * @param string $basicAuthData this should be in format username:password
     * @param array $headers http headers if any [optional]
     * @return string
     */
    public function deleteWithBasicAuth($url, $basicAuthData, array $headers = array())
    {
        curl_setopt($this->curl, CURLOPT_POST, false);
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($this->curl, CURLOPT_USERPWD, $basicAuthData);

        return $this->request($url, $headers);
    }

    private function request($url, array $headers)
    {
        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_REFERER, $url);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);

        if (!empty($headers)) {
            curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers);
        }

        return curl_exec($this->curl);
    }
}
