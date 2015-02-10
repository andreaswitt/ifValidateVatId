<?php
/**
 * @author      Andreas Witt <witt@andreas-witt.net>
 * @since       webEdition 6.4.0
 * @version     1.0
 * @category    webEdition
 * @param       array $attribs possible values are: from, namefrom, reference, shopname
 * @return      bool
 */
function we_tag_ifNotValidateVatId($attribs)
{
    return !we_tag('ifValidateVatId',$attribs);
}