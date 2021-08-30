> This bundle is abandoned and not actively maintained anymore. Please use [https://github.com/heimrichhannot/contao-member-bundle](https://github.com/heimrichhannot/contao-member-bundle).

# Member Plus

A collection of enhancements for contao members.

## Features

- additional fields (headline, alias, academicTitle, position, addressText, image)
- tl_content support for members, to add additional member content
- memberlist content element, with jumpTo Detail Page (show member reader module on custom page, article_reader - show reader on article only!, article or external page)
- memberreader module

### Login after Activation
- added reg_activate_login checkbox for Registration Module, that enables automatic login after account activation

### Registration & Login with one Module (requires `heimrichhannot/contao-formhybrid`)
- domain whitelist
- optionally: show allowed domains in login form, or hide them but still check against them
- optionally: hide allowed domains list in frontend, but still check against them
- optionally: permanent redirect to jumpTo page after user was logged

### Better Activation (requires `heimrichhannot/contao-formhybrid`)
- Registration will leave used activation keys in database and add a "ACTIVATED:" prefix before
- Tell the user if his token has already been used
- Tell user if token is invalid
- You can now overwrite "accountActivatedMessage" in "activateAccount" Hook
- Always redirect after activation or activation error to current page without token parameter in url (or reg_jumpTo page), and than display messages 

## Fields



## Hooks


### modifyDCRegistrationPlusForm (requires `heimrichhannot/contao-formhybrid`)

Modify the formhybrid Datacontainer array.

```
// config.php
$GLOBALS['TL_HOOKS']['modifyDCRegistrationPlusForm'][] = array('MyClass', 'modifyDCRegistrationPlusFormHook');

```

```
// MyClass.php

public function modifyDCRegistrationPlusFormHook(&$arrDca, \Model $objModule)
{
	// manipulate the datacontainer and add fields, change labels and more
	$arrDca['fields']['firstname']['eval']['placeholder'] = &$GLOBALS['TL_LANG']['tl_member']['myCustomFirstnamePlaceholder'];
}
```
