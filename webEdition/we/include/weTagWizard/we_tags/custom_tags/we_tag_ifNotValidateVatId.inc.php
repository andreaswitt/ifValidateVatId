<?php

//NOTE you are inside the constructor of weTagData.class.php

$this->NeedsEndTag  = true;
$this->Description  = "Validates the given sales tax identification number by its syntax and via Webservice: http://ec.europa.eu/taxation_customs/vies/ <br/><br/>";
$this->Description .= "If you choose the value <em>shopfield</em> of the field <em>from</em> you also have to fill in <em>reference</em> and <em>shopname</em>.";
$this->Attributes[] = new weTagData_textAttribute('namefrom', true, '');
$this->Attributes[] = new weTagData_selectAttribute('from', array(
    new weTagDataOption('global'),
    new weTagDataOption('request'),
    new weTagDataOption('post'),
    new weTagDataOption('get'),
    new weTagDataOption('session'),
    new weTagDataOption('shopfield'),
    new weTagDataOption('sessionfield'),
), true, '');
$this->Attributes[] = new weTagData_selectAttribute('reference', array(
    new weTagDataOption('article'),
    new weTagDataOption('cart'),
), false, '');
$this->Attributes[] = new weTagData_textAttribute('shopname', false, '');
