<?php

/**
 * regmyudid.cc API class
 */
class RMU {

    /**
     * API URL
     */
    const API_URL = 'https://secure.regmyudid.ru/API/v2.0/';

    /**
     * WEBSITE URL
     */
    const WEBSITE_URL = 'https://regmyudid.cc/';

    /**
     * @var Client ID
     */

    /**
     * @var Client's API password
     */
    private $CLIENT_ID, $API_PASSWORD;

    /**
     * Class constructor
     * @param $CLIENT_ID Client ID
     * @param $API_PASSWORD Client API password
     */
    function __construct($CLIENT_ID, $API_PASSWORD) {
        $this->CLIENT_ID = $CLIENT_ID;
        $this->API_PASSWORD = $API_PASSWORD;
        $this->_fail = false;

        $valid = $this->api_query(array('mode' => 'auth'));

        if (!$valid || !$valid['auth']) {
            $this->_fail = true;
        }
    }

    /**
     * Preforms API query to regmyudid.cc
     * @param $params Array of parameters
     * @return array Response array
     */
    function api_query($params) {

        $params['api_client_id'] = $this->CLIENT_ID;
        $params['api_password'] = $this->API_PASSWORD;
        $query = $this::API_URL . '?' . http_build_query($params);

        $result = @file_get_contents($query);
        //var_dump($result);
        return json_decode($result, true);
    }

    /**
     * Gets API information associated with account
     * @return array Array of information
     */
    function get_api_info() {
        $response = $this->api_query(array('mode' => 'api_info'));

        if ($response['error']) {
            return false;
        }
        return ($response['data']);
    }

    /**
     * Sets API info to account
     * @param array Associative array of information to be set
     * @return boolean True on success or false on failed
     */
    function set_api_info($info) {
        $response = $this->api_query(array('mode' => 'set_api_info', 'data' => $info));

        if ($response['error']) {
            return false;
        }
        return ($response['data']);
    }

    /**
     * Gets statistics of account
     * @return array Array of statistics
     */
    function get_statistics() {
        $response = $this->api_query(array('mode' => 'statistics'));

        if ($response['error']) {
            return false;
        }
        return ($response['data']);
    }

    /**
     * Deletes UDID from RMU database, only for failed REGs/CERTs
     * @param string $key Delete key
     * @return boolean True on success, false on failure.
     */
    function delete_udid($key) {
        $response = $this->api_query(array('mode' => 'delete', 'delete_key' => $key));

        if ($response['error']) {
            return false;
        }
        return ($response['data']);
    }

    /**
     * Gets status of UDID activation
     * @param $udid UDID or EMAIL to check
     * @return array Array of UDIDs for this emails. See more on RMU API DOCS
     */
    function get_status($udid = '', $txid = '', $added = 0, $start = 0, $limit = 10) {
        $query['mode'] = 'status';
        if ($udid) {
            $query['udid'] = $udid;
        }

        if ($txid) {
            $query['transaction_id'] = $txid;
        }
        if ($added) {
            $query['added'] = $added;
        }


        $query['start'] = $start;
        $query['limit'] = $limit;
        $response = $this->api_query($query);

        //var_dump($query);

        if ($response['error']) {
            return false;
        }
        return ($response['data']);
    }

    /**
     * Gets full RMU url to download files
     * @param $path Path on RMU servers
     * @return string Full URL
     */
    static function get_rmu_link($path) {
        return RMU::WEBSITE_URL . $path;
    }

    /**
     * Validates UDID
     * @param $udid UDID itself
     * @return bool true on success, false on fail
     */
    static function validate_udid($udid) {
        return (preg_match('/^([a-z0-9\-+]{25,40})$/si', $udid) && !preg_match('/fffff/', $udid));
    }

    /**
     * Sends query to RMU servers to register UDID.
     * @param $email Email of user
     * @param $udid UDID to register
     * @param $type Type of registration REG or CERT
     * @param $transaction_id Transaction ID in your payment system
     * @return array response from RMU servers or emulated local response.
     */
    function register_udid($email, $udid, $type, $transaction_id) {
        if (!$this->validate_udid($udid))
            return array('success' => false, 'error' => 'Invalid UDID');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))
            return array('success' => false, 'error' => 'Invalid email');
        if (!in_array($type, array('CERT', 'REG')))
            return array('success' => false, 'error' => 'Invalid registration type');
        $params = array('email' => $email, 'udid' => $udid, 'type' => $type, 'transaction_id' => $transaction_id, 'mode' => 'register');

        $response = $this->api_query($params);

        if ($response['error']) {
            return array('success' => false, 'error' => $response['error']);
        }
        return ($response['data']);
    }

}