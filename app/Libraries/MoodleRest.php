<?php

namespace App\Libraries;

/**
 * MoodleRest (cURL Version - Optimized for Slow Servers)
 *
 * @version    2.5.0 (Timeout Optimized)
 */
class MoodleRest
{
    const RETURN_JSON = 'json';
    const RETURN_XML = 'xml';
    const RETURN_ARRAY = 'array';
    const METHOD_GET = 'get';
    const METHOD_POST = 'post';

    private $server_address;
    private $token;
    private $return_format = 'json';
    private $request_return;
    private $parsed_return;
    private $url;
    private $url_decoded;
    private $output_header;
    private $print_on_request = false;
    private $method = 'get';
    private $debug = false;

    public function __construct($server_address = null, $token = null, $return_format = self::RETURN_ARRAY)
    {
        $this->server_address = $server_address;
        $this->token = $token;
        if (!is_null($return_format) && $return_format <> 'json' && $return_format <> 'xml' && $return_format <> 'array') {
            trigger_error("Invalid return format: '$return_format'", E_USER_WARNING);
        }
        $this->return_format = $return_format;
    }

    public function setServerAddress($server_address)
    {
        $this->server_address = $server_address;
        return $this;
    }

    public function getServerAddress()
    {
        return $this->server_address;
    }

    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function setReturnFormat($return_format)
    {
        if ($return_format <> 'json' && $return_format <> 'xml' && $return_format <> 'array') {
            trigger_error("Invalid return format: '$return_format'", E_USER_WARNING);
        }
        $this->return_format = $return_format;
        return $this;
    }

    public function getReturnFormat()
    {
        return $this->return_format;
    }

    private function setRawData($request_return)
    {
        $this->request_return = $request_return;
    }

    public function getRawData()
    {
        return $this->request_return;
    }

    private function setData($parsed_return)
    {
        $this->parsed_return = $parsed_return;
    }

    public function getData()
    {
        return $this->parsed_return;
    }

    private function setUrl($url)
    {
        $this->url = $url;
        $this->url_decoded = urldecode($url);
    }

    public function getUrl($decoded = true)
    {
        return $decoded ? $this->url_decoded : $this->url;
    }

    private function setHeader($output_header)
    {
        $this->output_header = $output_header;
    }

    public function getHeader()
    {
        return $this->output_header;
    }

    public function setMethod($method)
    {
        $this->method = $method;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function setDebug($enabled = true)
    {
        $this->debug = $enabled;
    }

    private function debug($url, $webservice_function, $method, $returned_data)
    {
        if ($this->debug) {
            $line_break = php_sapi_name() == 'cli' ? "\n" : '<br />';
            $open_html_pre = php_sapi_name() == 'cli' ? '' : '<pre>';
            $close_html_pre = php_sapi_name() == 'cli' ? '' : '</pre>';
            echo $open_html_pre;
            echo $line_break;
            echo '[debug][' . strtoupper($method) . '] ' . get_class($this) . "::request( $webservice_function )$line_break";
            echo "$url $line_break";
            if (is_array($returned_data) || is_object($returned_data)) {
                print_r($returned_data);
            } else {
                if ((strlen(trim($returned_data)) > 0) && in_array($returned_data[0], array('[', '{'))) {
                    print_r(json_decode(trim($returned_data), true));
                } else {
                    echo gettype($returned_data) . " '$returned_data'$line_break";
                }
            }
            echo $line_break;
            echo $close_html_pre;
        }
    }

    public function setPrintOnRequest($print_on_request = true)
    {
        $this->print_on_request = $print_on_request;
    }

    public function getPrintOnRequest()
    {
        return $this->print_on_request;
    }

    public function printRequest()
    {
        if (($this->getReturnFormat() == 'json') || ($this->getReturnFormat() == 'xml')) {
            if (empty($this->output_header)) {
                if ($this->getReturnFormat() == 'json') {
                    header('Content-Type: application/json');
                } elseif ($this->getReturnFormat() == 'xml') {
                    header('Content-Type: application/xml');
                }
            }
            echo $this->getData();
        } else {
            print_r($this->getData());
        }
    }

    /**
     * Request dengan Timeout Agresif untuk Server Lambat
     */
    public function request($function, $parameters = null, $method = self::METHOD_GET)
    {
        // 1. Validasi
        $fatal = 0;
        if (empty($this->server_address)) { trigger_error('Empty server address.', E_USER_WARNING); $fatal = 1; }
        if (empty($this->token)) { trigger_error('Empty token.', E_USER_WARNING); $fatal = 1; }
        if (empty($this->return_format)) { trigger_error('Empty return format.', E_USER_WARNING); $fatal = 1; }
        if (empty($function)) { trigger_error('Empty function.', E_USER_WARNING); $fatal = 1; }
        if (!is_null($parameters) && !is_array($parameters)) { trigger_error('Parameters should be an array', E_USER_WARNING); $fatal = 1; }
        if ($fatal) { trigger_error('Fix above errors and try again', E_USER_ERROR); }

        // 2. Setup URL
        $return_format = ($this->getReturnFormat() == 'array' || $this->getReturnFormat() == 'json') ? 'json' : 'xml';
        $this->setMethod($method);
        $query_string = is_array($parameters) ? http_build_query($parameters) : '';

        $base_url = $this->getServerAddress() . 
                    '?wstoken=' . $this->getToken() . 
                    '&moodlewsrestformat=' . $return_format . 
                    '&wsfunction=' . $function;

        $this->setUrl($base_url . ($method == self::METHOD_GET ? '&' . $query_string : ''));

        // 3. cURL dengan Timeout SANGAT PANJANG
        $ch = curl_init();
        
        // === TIMEOUT AGRESIF UNTUK SERVER LAMBAT ===
        curl_setopt($ch, CURLOPT_TIMEOUT, 150);        // 2.5 menit total
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 45);  // 45 detik koneksi
        
        // Low-level socket timeout (backup)
        curl_setopt($ch, CURLOPT_NOSIGNAL, 1); // Penting untuk timeout bekerja di background
        
        // SSL & Basic Options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        // HTTP Version & Encoding
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1); // Force HTTP/1.1
        curl_setopt($ch, CURLOPT_ENCODING, ''); // Accept gzip/deflate
        
        // Keep-Alive untuk performa
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Connection: keep-alive',
            'Keep-Alive: 300'
        ]);

        // 4. POST vs GET
        if ($method == self::METHOD_POST) {
            curl_setopt($ch, CURLOPT_URL, $base_url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge(
                curl_getinfo($ch, CURLINFO_HEADER_OUT) ?: [],
                ['Content-type: application/x-www-form-urlencoded']
            ));
        } else {
            curl_setopt($ch, CURLOPT_URL, $base_url . '&' . $query_string);
            curl_setopt($ch, CURLOPT_HTTPGET, true);
        }

        // 5. Eksekusi
        $start_time = microtime(true);
        $moodle_request = curl_exec($ch);
        $execution_time = round(microtime(true) - $start_time, 2);
        
        $curl_errno = curl_errno($ch);
        $curl_error = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        curl_close($ch);

        // 6. Error Handling dengan Info Lengkap
        if ($curl_errno) {
            $error_msg = "Moodle cURL Error [$curl_errno]: $curl_error (took {$execution_time}s)";
            
            // Tambahkan info khusus untuk timeout
            if ($curl_errno == 28) { // CURLE_OPERATION_TIMEDOUT
                $error_msg .= " - Server Moodle terlalu lambat merespons. Coba kurangi batch size atau tingkatkan timeout.";
            }
            
            throw new \Exception($error_msg);
        }

        // 7. Cek HTTP Status
        if ($http_code >= 400) {
            throw new \Exception("Moodle HTTP Error: $http_code - Request took {$execution_time}s");
        }

        $this->debug($this->getUrl(), $function, $method, $moodle_request);
        $this->setRawData($moodle_request);

        // 8. Parse Result
        if ($this->getReturnFormat() == 'array') {
            $parsed = json_decode($moodle_request, true);
            
            // Deteksi error dari Moodle API
            if (isset($parsed['exception']) || isset($parsed['errorcode'])) {
                $error_detail = $parsed['message'] ?? $parsed['debuginfo'] ?? 'Unknown Moodle error';
                throw new \Exception("Moodle API Error: $error_detail");
            }
            
            $this->setData($parsed);
        } else {
            $this->setData($moodle_request);
        }

        if ($this->getPrintOnRequest()) {
            $this->printRequest();
        }

        return $this->getData();
    }
}