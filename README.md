# we:ifValidateVatId

Summary
---
CMS webEdition (http://www.webedition.org) Custom Tag for validating sales tax identification numbers

Installation
---

1. Put the files we_tag_ifValidateVatId.inc.php and we_tag_ifNotValidateVatId.inc.php from webEdition/we/include/we_tags/custom_tags into the folder /webEdition/we/include/we_tags/custom_tags of your webEdition Installation. 
2. Put the files we_tag_ifValidateVatId.inc.php and we_tag_ifNotValidateVatId.inc.php from webEdition/we/include/weTagWizard/we_tags/custom_tags into the folder webEdition/we/include/weTagWizard/we_tags/custom_tags of your webEdition Installation.

Usage
---

Use the webEdition Tags <we:ifValidateVatId> or <we:ifNotValidateVatId> within your webEdition Templates for validating sales tax identification numbers given by any frontend forumlar of your website.
 
You also could use this tag with AJAX jQuery validation functions or engine like jQuery.validationEngine.

### Using AJAX jQuery validation with jQuery.validationEngine

1. Downlaod and install the jQuery.validationEngine from https://github.com/posabsolute/jQuery-Validation-Engine
2. Add the jQuery.validationEngine to your web formular and use the Ajax field validation for the input field used for sales tax identification numbers

```html
<we:sessionField type="textinput" name="Rechnung_UStID" id="billingUstid" class="validate[ajax[ajaxVatIdCall]]"/>
```
3. Create a webEdition Template (e.g. ajax-validate-vat-ids.tmpl) for validate sales tax identification numbers an create an dynamic webEdition document (e.g. ajax-validate-vat-ids.php) based on your template

```html
<we:ifNotWebEdition>
	<we:setVar from="request" namefrom="fieldId" varType="string" to="global" nameto="fieldID"/>
	<we:ifValidateVatId from="request" namefrom="fieldValue">
		<?php echo json_encode(array($GLOBALS['fieldID'],true)); ?>
	<we:else/>
		<?php echo json_encode(array($GLOBALS['fieldID'],false)); ?>
	</we:ifValidateVatId>
</we:ifNotWebEdition>
```
4. Create the ajax custom function 'ajaxVatIdCall' in jQuery.validationEngine file called '/js/languages/jquery.validationEngine-de.js'

```html
"ajaxVatIdCall": {
		 "url": "/path/to/ajax-validate-vat-ids.php",
			"alertText": "* Invalid sales tax identification number",
			"alertTextLoad": "* Please wait, checking ..."
		 },
```

More about Ajax validation on http://www.position-absolute.com/articles/using-form-ajax-validation-with-the-jquery-validation-engine-plugin/
