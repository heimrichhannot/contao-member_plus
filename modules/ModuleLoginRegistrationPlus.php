<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2015 Heimrich & Hannot GmbH
 *
 * @package member_plus
 * @author  Oliver Janke <o.janke@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\MemberPlus;


use HeimrichHannot\FormHybrid\FormHelper;
use HeimrichHannot\FormHybrid\FormSession;
use HeimrichHannot\Request\Request;

class ModuleLoginRegistrationPlus extends \ModuleRegistration
{
	protected $strTemplate = 'mod_login_registration_plus';
	
	protected $objForm;
	
	public function generate()
	{
		if (TL_MODE == 'BE') {
			$objTemplate           = new \BackendTemplate('be_wildcard');
			$objTemplate->wildcard = '### ' . utf8_strtoupper($GLOBALS['TL_LANG']['FMD']['login_registration_plus'][0]) . ' ###';
			$objTemplate->title    = $this->headline;
			$objTemplate->id       = $this->id;
			$objTemplate->link     = $this->name;
			$objTemplate->href     = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;
			
			return $objTemplate->parse();
		}
		
		$strFormId = FormHelper::getFormId($this->formHybridDataContainer, $this->id);
		
		// get id from FormSession
		if ($_POST) {
			$intId = FormSession::getSubmissionId($strFormId);
		}
		
		// Logout and redirect to the website root if the current page is protected
		if (Request::getPost('FORM_SUBMIT') == 'tl_logout') {
			global $objPage;
			
			$this->import('FrontendUser', 'User');
			$strRedirect = \Environment::get('request');
			
			// Redirect to last page visited
			if ($this->redirectBack && strlen($_SESSION['LAST_PAGE_VISITED'])) {
				$strRedirect = $_SESSION['LAST_PAGE_VISITED'];
			} // Redirect home if the page is protected
			elseif ($objPage->protected) {
				$strRedirect = \Environment::get('base');
			}
			
			// Logout and redirect
			if ($this->User->logout()) {
				$this->redirect($strRedirect);
			}
			
			$this->reload();
		}
		
		$this->objForm = new MemberLoginRegistrationPlusForm($this->objModel, $intId ?: 0);
		
		$this->editable = $this->objForm->getEditableFields();
		
		// Return if there are no editable fields
		if (!is_array($this->editable) || empty($this->editable)) {
			return '';
		}
		
		return parent::generate();
	}
	
	protected function compile()
	{
		// Show logout form
		if (FE_USER_LOGGED_IN) {
			$this->import('FrontendUser', 'User');
			$this->strTemplate = ($this->cols > 1) ? 'mod_logout_2cl' : 'mod_logout_1cl';
			
			$this->Template = new \FrontendTemplate($this->strTemplate);
			$this->Template->setData($this->arrData);
			
			$this->Template->slabel     = specialchars($GLOBALS['TL_LANG']['MSC']['logout']);
			$this->Template->loggedInAs = sprintf($GLOBALS['TL_LANG']['MSC']['loggedInAs'], $this->User->username);
			$this->Template->action     = ampersand(\Environment::get('indexFreeRequest'));
			
			if ($this->User->lastLogin > 0) {
				global $objPage;
				$this->Template->lastLogin = sprintf(
					$GLOBALS['TL_LANG']['MSC']['lastLogin'][1],
					\Date::parse($objPage->datimFormat, $this->User->lastLogin)
				);
			}
			
			return;
		}
		
		if ($this->domainCheck) {
			$this->Template->domainList = $this->getDomainList(false);
		}
		
		
		// render messages before if existing, otherwise error messages will not be displayed after redirect/reload
		if (MemberMessage::hasMessages()) {
			$this->Template->message = MemberMessage::generate();
		}
		
		// Activate account
		if (\Input::get('token') != '') {
			$this->activateAcount();
		}
		
		$this->Template->form = $this->objForm->generate();
		
	}
	
	/**
	 * Activate an account
	 */
	protected function activateAcount()
	{
		$hasError     = false;
		$strReloadUrl = preg_replace('/(&|\?)token=[^&]*/', '', \Environment::get('request')); // remove token from url
		
		$objMember = \MemberModel::findByActivation(MEMBER_ACTIVATION_ACTIVATED_FIELD_PREFIX . \Input::get('token'));
		
		// member with this token already activated
		if ($objMember !== null) {
			$hasError = true;
			MemberMessage::addDanger($GLOBALS['TL_LANG']['MSC']['alreadyActivated']);
		}
		
		// check for invalid token
		if (!$hasError) {
			$objMember = \MemberModel::findByActivation(\Input::get('token'));
			
			if ($objMember === null) {
				$hasError = true;
				MemberMessage::addDanger($GLOBALS['TL_LANG']['MSC']['invalidActivationToken']);
			}
		}
		
		// if has errors, remove token from url and redirect to current page without token parameter
		if ($hasError) {
			$this->redirect($strReloadUrl);
		}
		
		// Update the account
		$objMember->disable    = '';
		$objMember->activation = MEMBER_ACTIVATION_ACTIVATED_FIELD_PREFIX . $objMember->activation;
		$objMember->save();
		
		$this->accountActivatedMessage = $GLOBALS['TL_LANG']['MSC']['accountActivated'];
		
		// HOOK: post activation callback
		if (isset($GLOBALS['TL_HOOKS']['activateAccount']) && is_array($GLOBALS['TL_HOOKS']['activateAccount'])) {
			foreach ($GLOBALS['TL_HOOKS']['activateAccount'] as $callback) {
				$this->import($callback[0]);
				$this->{$callback[0]}->{$callback[1]}($objMember, $this);
			}
		}
		
		// Log activity
		$this->log('User account ID ' . $objMember->id . ' (' . $objMember->email . ') has been activated', __METHOD__, TL_ACCESS);
		
		// Redirect to the jumpTo page
		if (($objTarget = $this->objModel->getRelated('reg_jumpTo')) !== null) {
			$this->redirect($this->generateFrontendUrl($objTarget->row()));
		} // redirect to current page without token parameter
		else {
			MemberMessage::addSuccess($this->accountActivatedMessage);
			$this->redirect($strReloadUrl);
		}
	}
	
}