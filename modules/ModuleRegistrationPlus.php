<?php
/**
 * Contao Open Source CMS
 * 
 * Copyright (c) 2015 Heimrich & Hannot GmbH
 * @package member_plus
 * @author Oliver Janke <o.janke@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\MemberPlus;


class ModuleRegistrationPlus extends \ModuleRegistration
{
	protected $strTemplate = 'mod_registration_plus';

	protected $objForm;

	public function generate()
	{
		if (TL_MODE == 'BE') {
			$objTemplate           = new \BackendTemplate('be_wildcard');
			$objTemplate->wildcard = '### '.utf8_strtoupper($GLOBALS['TL_LANG']['FMD']['registration_plus'][0]).' ###';
			$objTemplate->title    = $this->headline;
			$objTemplate->id       = $this->id;
			$objTemplate->link     = $this->name;
			$objTemplate->href     = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id='.$this->id;

			return $objTemplate->parse();
		}

		$this->objForm = new MemberRegistrationPlusForm($this->objModel);
		$this->editable = $this->objForm->getEditableFields();

		// Return if there are no editable fields
		if (!is_array($this->editable) || empty($this->editable)) {
			return '';
		}

		return parent::generate();
	}

	protected function compile()
	{
		// render messages before if existing, otherwise error messages will not be displayed after redirect/reload
		if(MemberMessage::hasMessages())
		{
			$this->Template->message = MemberMessage::generate();
		}

		// Activate account
		if (\Input::get('token') != '')
		{
			$this->activateAcount();
		}

		$this->Template->form = $this->objForm->generate();
	}


	/**
	 * Activate an account
	 */
	protected function activateAcount()
	{
		$hasError = false;
		$strReloadUrl = preg_replace('/(&|\?)token=[^&]*/', '', \Environment::get('request')); // remove token from url
		
		$objMember = \MemberModel::findByActivation(MEMBER_ACTIVATION_ACTIVATED_FIELD_PREFIX . \Input::get('token'));

		// member with this token already activated
		if ($objMember !== null)
		{
			$hasError = true;
			MemberMessage::addDanger($GLOBALS['TL_LANG']['MSC']['alreadyActivated']);
		}

		// check for invalid token
		if(!$hasError)
		{
			$objMember = \MemberModel::findByActivation(\Input::get('token'));

			if ($objMember === null)
			{
				$hasError = true;
				MemberMessage::addDanger($GLOBALS['TL_LANG']['MSC']['invalidActivationToken']);
			}
		}

		// if has errors, remove token from url and redirect to current page without token parameter
		if($hasError)
		{
			$this->redirect($strReloadUrl);
		}

		// Update the account
		$objMember->disable = '';
		$objMember->activation = MEMBER_ACTIVATION_ACTIVATED_FIELD_PREFIX . $objMember->activation;
		$objMember->save();

		$this->accountActivatedMessage = $GLOBALS['TL_LANG']['MSC']['accountActivated'];

		// HOOK: post activation callback
		if (isset($GLOBALS['TL_HOOKS']['activateAccount']) && is_array($GLOBALS['TL_HOOKS']['activateAccount']))
		{
			foreach ($GLOBALS['TL_HOOKS']['activateAccount'] as $callback)
			{
				$this->import($callback[0]);
				$this->$callback[0]->$callback[1]($objMember, $this);
			}
		}

		// Log activity
		$this->log('User account ID ' . $objMember->id . ' (' . $objMember->email . ') has been activated', __METHOD__, TL_ACCESS);

		MemberMessage::addSuccess($this->accountActivatedMessage);

		// Redirect to the jumpTo page
		if (($objTarget = $this->objModel->getRelated('reg_jumpTo')) !== null)
		{
			$this->redirect($this->generateFrontendUrl($objTarget->row()));
		}
		// redirect to current page without token parameter
		else
		{
			$this->redirect($strReloadUrl);
		}
	}
}