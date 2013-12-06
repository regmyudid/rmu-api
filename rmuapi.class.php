<?php
/**
 * RegMyUDID.com API class
 */

class RMU {
    /**
     * API URL
     */
    const API_URL = 'http://regmyudid.com/API/v1/';
    /**
     * WEBSITE URL
     */
    const WEBSITE_URL = 'http://regmyudid.com/';

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
    function __construct($CLIENT_ID,$API_PASSWORD) {
        $this->CLIENT_ID = $CLIENT_ID;
        $this->API_PASSWORD = $API_PASSWORD;


        $valid = $this->api_query(array('mode'=>'auth'));

        if (!$valid||!$valid['auth']) die('Unable to connect to RegMyUDID API. Check credentials or service status.');
    }

    /**
     * Preforms API query to regmyudid.com
     * @param $params Array of parameters
     * @return array Response array
     */
    private function api_query($params) {

        $params['client_id'] = $this->CLIENT_ID;
        $params['api_password'] = $this->API_PASSWORD;
        $query = $this::API_URL.'?'.http_build_query($params);

        return json_decode(@file_get_contents($query),true);
    }

    /**
     * Gets status of UDID activation
     * @param $udid UDID or EMAIL to check
     * @return array Array of UDIDs for this emails. See more on RMU API DOCS
     */
    function get_status($udid) {
        $response = $this->api_query(array('mode'=>'status','udid'=>$udid));

        return $response;
    }

    /**
     * Gets full RMU url to download files
     * @param $path Path on RMU servers
     * @return string Full URL
     */
    function get_rmu_link($path) {
        return $this::WEBSITE_URL.$path;
    }

    /**
     * Validates UDID
     * @param $udid UDID itself
     * @return bool true on success, false on fail
     */
    static function validate_udid($udid) {
        return (preg_match('#([a-z0-9+]{40})#s', $udid)&&!preg_match('/fffff/',$udid));
    }

    /**
     * Sends query to RMU servers to register UDID.
     * @param $email Email of user
     * @param $udid UDID to register
     * @param $type Type of registration REG or CERT
     * @param $transaction_id Transaction ID in your payment system
     * @return array response from RMU servers or emulated local response.
     */
    function register_udid($email,$udid,$type,$transaction_id) {
        if (!$this->validate_udid($udid)) return array('success'=>false,'error'=>'Invalid UDID');
        if (!filter_var($email,FILTER_VALIDATE_EMAIL)) return array('success'=>false,'error'=>'Invalid email');
        if (!in_array($type,array('CERT','REG'))) return array('success'=>false,'error'=>'Invalid registration type');
        $params = array('email'=>$email,'udid'=>$udid,'type'=>$type,'transaction_id'=>$transaction_id,'mode'=>'register');

        $result = $this->api_query($params);

        if ($result['error']) return array('success'=>false,'error'=>$result['error']);


        return ($result['data']);
    }
}
?>