<?php
/**
 * @author      Andreas Witt <witt@andreas-witt.net>
 * @since       webEdition 6.3.9
 * @version     1.1
 * @category    webEdition
 * @param       array $attribs possible values are: from, namefrom, reference, shopname
 * @return      bool
 * @TODO        handle different error types (e.g. 'Not allowed literals', 'Invalid Vat ID', 'Webservice unaviable')
 */
function we_tag_ifValidateVatId($attribs)
{

    if (($foo = attributFehltError($attribs, array('namefrom' => false, 'from' => false), __FUNCTION__))) {
        print($foo);
        return false;
    }

    if (!class_exists("SOAPClient")) {
        t_e('warning', 'we:ifValidateVatId', 'PHP Class: SOAPClient missing');
        return false;
    }

    /** @var string $name */
    $name = (defined(WE_VERSION) && floatval(WE_VERSION)>= '6.4') ? 
    	weTag_getAttribute('namefrom', $attribs, '', we_base_request::STRING) : //since WE 6.4
    	weTag_getAttribute('namefrom', $attribs); //up to WE 6.3.9

    /** @var string $from */
	$from = (defined(WE_VERSION) && floatval(WE_VERSION)>= '6.4') ? 
    	weTag_getAttribute('from', $attribs, '', we_base_request::STRING) : //since WE 6.4
    	weTag_getAttribute('from', $attribs); //up to WE 6.3.9

    switch($from){
        case 'global':
            $vatId = isset($GLOBALS[$name]) ? getArrayValue($GLOBALS, null, $name) : '';
            break;
        case 'request':
            $vatId = isset($_REQUEST[$name]) ? we_base_util::rmPhp(we_base_request::filterVar(getArrayValue($_REQUEST, null, $name), we_base_request::STRING)) : '';
            break;
        case 'post':
            $vatId = isset($_POST[$name]) ? we_base_util::rmPhp(we_base_request::filterVar(getArrayValue($_POST, null, $name), we_base_request::STRING)) : '';
            break;
        case 'get':
            $vatId = isset($_GET[$name]) ? we_base_util::rmPhp(we_base_request::filterVar(getArrayValue($_GET, null, $name), we_base_request::STRING)) : '';
            break;
        case 'session':
            $vatId = isset($_SESSION[$name]) ? getArrayValue($_SESSION, null, $name) : '';
            break;
        case 'shopfield':
            if (($foo = attributFehltError($attribs, array('reference' => false, 'shopname' => false), __FUNCTION__))) {
                print($foo);
                return false;
            }
            $atts = removeAttribs($attribs, array('from', 'namefrom'));
            $atts['name'] = $name;
            $atts['type'] = 'print';
            $vatId = we_tag('shopField', $atts);
            break;
        case 'sessionfield':
        default:
            $vatId = isset($_SESSION['webuser'][$name]) ? $_SESSION['webuser'][$name] : '';
            break;
    }

    /** @var array $matches */
    $matches = array();

    /**
     * first of all we make a syntax check
     * @var string|bool $vatId
     */
    
    $vatId = (!empty($vatId) && preg_match('/^([A-Z]{2})([A-Za-z0-9\+\*\.]{2,12})$/', trim($vatId), $matches)) ? $matches[0] : false;

    if (!$vatId) {
        t_e('warning', 'we:ifValidateVatId', 'Missing or not allowed literals within given VatId');
        return false;
    }

    /** @var string $webservice */
    $webservice = "http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl";

    /** @var object $soapClient */
    $soapClient = new SoapClient($webservice);

    if($soapClient) {

        /** @var string $isoCountryCode */
        $isoCountryCode = $matches[1];

        /** @var string $vatNumber */
        $vatNumber = $matches[2];

        /** @var array $params */
        $params = array('countryCode' => $isoCountryCode, 'vatNumber' => $vatNumber);
        try {
            /** @var object $response */
            $response = $soapClient->checkVat($params);

            return $response->valid;

        } catch (SoapFault $e) {
            switch($e->faultstring){
                default:
                case 'INVALID_INPUT': //The provided CountryCode is invalid or the VAT number is empty
                    t_e('warning', 'we:ifValidateVatId', $e->faultstring);
                    return false;
                case 'SERVICE_UNAVAILABLE': //The SOAP service is unavailable, try again later
                case 'MS_UNAVAILABLE': //The Member State service is unavailable, try again later or with another Member State
                case 'TIMEOUT': //The Member State service could not be reached in time, try again later or with another Member State
                case 'SERVER_BUSY': //The service cannot process your request. Try again later.
                    t_e('warning', 'we:ifValidateVatId', $e->faultstring);
                    return true;//because syntax check was OK
            }
        }
    } else {
        // Connection to host not possible, europe.eu down?
        t_e('warning', 'we:ifValidateVatId', 'Connection to host: ' . $webservice . ' not possible, but syntax check was OK');
        return true;//because syntax check was OK
    }
}