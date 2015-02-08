<?php
/**
 * @author      Andreas Witt <witt@andreas-witt.net>
 * @since       webEdition 6.4.0
 * @version     1.0
 * @category    webEdition
 * @param       array $attribs possible values are: from, name, reference, shopname
 * @return      bool
 */
function we_tag_ifValidateVatId($attribs)
{

    if (($foo = attributFehltError($attribs, array('name' => false, 'from' => false), __FUNCTION__))) {
        print($foo);
        return false;
    }

    if (!class_exists("SOAPClient")) {
        t_e('warning', 'we:ifValidateVatId', 'PHP Class: SOAPClient missing');
        return false;
    }

    /** @var string $name */
    $name = weTag_getAttribute('name', $attribs, '', we_base_request::STRING);

    /** @var string $from */
    $from = weTag_getAttribute('from', $attribs, '', we_base_request::STRING);

    switch ($from) {
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
            $atts = removeAttribs($attribs, array('from'));
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

    /** @var string|bool $vatId */
    $vatId = (!empty($vatId) && preg_match('/^([A-Z]{2})([A-Za-z0-9\+\*\.]{2,12})$/', trim($vatId), $matches)) ? $matches[0] : false;

    if (!$vatId) {
        t_e('warning', 'we:ifValidateVatId', 'Not allowed literals within given VatId');
        return false;
    }

    /** @var string $webservice */
    $webservice = "http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl";

    /** @var object $soapClient */
    $soapClient = new SoapClient($webservice);

    if ($soapClient) {

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

            /**
             * TODO handle different error messages
             * if ($response->valid == true) { // VAT-ID is valid
             *
             * } else { // VAT-ID is NOT valid
             *
             * }
             */

        } catch (SoapFault $e) {
            t_e('warning', 'we:ifValidateVatId', $e->faultstring);
            return false;
        }
    } else {
        // Connection to host not possible, europe.eu down?
        t_e('warning', 'we:ifValidateVatId', 'Connection to host not possible: ' . $webservice);
        return false;
    }
}