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

class ModuleRegistrationPlus extends \ModuleRegistration
{
	protected $strTemplate = 'mod_registration_plus';
	
	protected $objForm;
	
	public function generate()
	{
		if (TL_MODE == 'BE') {
			$objTemplate           = new \BackendTemplate('be_wildcard');
			$objTemplate->wildcard = '### ' . utf8_strtoupper($GLOBALS['TL_LANG']['FMD']['registration_plus'][0]) . ' ###';
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
		
		$this->objForm = new MemberRegistrationPlusForm($this->objModel, $intId ?: 0);
		
		
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
	
	/**
	 * Create a new user and redirect
	 *
	 * @param array $arrData
	 */
	protected function createNewUser($arrData)
	{
		$arrData['tstamp']     = time();
		$arrData['login']      = $this->reg_allowLogin;
		$arrData['activation'] = md5(uniqid(mt_rand(), true));
		$arrData['dateAdded']  = $arrData['tstamp'];
		
		// Set default groups
		if (!array_key_exists('groups', $arrData)) {
			$arrData['groups'] = $this->reg_groups;
		}
		
		// Disable account
		$arrData['disable'] = 1;
		
		// Send activation e-mail
		if ($this->reg_activate) {
			// Prepare the simple token data
			$arrTokenData             = $arrData;
			$arrTokenData['domain']   = \Idna::decode(\Environment::get('host'));
			$arrTokenData['link']     = \Idna::decode(\Environment::get('base')) . \Environment::get('request') . ((\Config::get('disableAlias')
																													|| strpos(
																														   \Environment::get(
																															   'request'
																														   ),
																														   '?'
																													   ) !== false) ? '&' : '?')
										. 'token=' . $arrData['activation'];
			$arrTokenData['channels'] = '';
			
			if (in_array('newsletter', \ModuleLoader::getActive())) {
				// Make sure newsletter is an array
				if (!is_array($arrData['newsletter'])) {
					if ($arrData['newsletter'] != '') {
						$arrData['newsletter'] = [$arrData['newsletter']];
					} else {
						$arrData['newsletter'] = [];
					}
				}
				
				// Replace the wildcard
				if (!empty($arrData['newsletter'])) {
					$objChannels = \NewsletterChannelModel::findByIds($arrData['newsletter']);
					
					if ($objChannels !== null) {
						$arrTokenData['channels'] = implode("\n", $objChannels->fetchEach('title'));
					}
				}
			}
			
			// Backwards compatibility
			$arrTokenData['channel'] = $arrTokenData['channels'];
			
			$objEmail = new \Email();
			
			$objEmail->from     = $GLOBALS['TL_ADMIN_EMAIL'];
			$objEmail->fromName = $GLOBALS['TL_ADMIN_NAME'];
			$objEmail->subject  = sprintf($GLOBALS['TL_LANG']['MSC']['emailSubject'], \Idna::decode(\Environment::get('host')));
			$objEmail->text     = \StringUtil::parseSimpleTokens($this->reg_text, $arrTokenData);
			$objEmail->sendTo($arrData['email']);
		}
		
		// Make sure newsletter is an array
		if (isset($arrData['newsletter']) && !is_array($arrData['newsletter'])) {
			$arrData['newsletter'] = [$arrData['newsletter']];
		}
		
		// Create the user
		$objNewUser = new \MemberModel();
		$objNewUser->setRow($arrData);
		$objNewUser->save();
		
		// Assign home directory
		if ($this->reg_assignDir) {
			$objHomeDir = \FilesModel::findByUuid($this->reg_homeDir);
			
			if ($objHomeDir !== null) {
				$this->import('Files');
				$strUserDir = standardize($arrData['username']) ?: 'user_' . $objNewUser->id;
				
				// Add the user ID if the directory exists
				while (is_dir(TL_ROOT . '/' . $objHomeDir->path . '/' . $strUserDir)) {
					$strUserDir .= '_' . $objNewUser->id;
				}
				
				// Create the user folder
				new \Folder($objHomeDir->path . '/' . $strUserDir);
				
				$objUserDir = \FilesModel::findByPath($objHomeDir->path . '/' . $strUserDir);
				
				// Save the folder ID
				$objNewUser->assignDir = 1;
				$objNewUser->homeDir   = $objUserDir->uuid;
				$objNewUser->save();
			}
		}
		
		// HOOK: send insert ID and user data
		if (isset($GLOBALS['TL_HOOKS']['createNewUser']) && is_array($GLOBALS['TL_HOOKS']['createNewUser'])) {
			foreach ($GLOBALS['TL_HOOKS']['createNewUser'] as $callback) {
				$this->import($callback[0]);
				$this->{$callback[0]}->{$callback[1]}($objNewUser->id, $arrData, $this);
			}
		}
		
		// Create the initial version (see #7816)
		$objVersions = new \Versions('tl_member', $objNewUser->id);
		$objVersions->setUsername($objNewUser->username);
		$objVersions->setUserId(0);
		$objVersions->setEditUrl('contao/main.php?do=member&act=edit&id=%s&rt=1');
		$objVersions->initialize();
		
		// Inform admin if no activation link is sent
		if (!$this->reg_activate) {
			$this->sendAdminNotification($objNewUser->id, $arrData);
		}
		
		// Check whether there is a jumpTo page
		if (($objJumpTo = $this->objModel->getRelated('jumpTo')) !== null) {
			$this->jumpToOrReload($objJumpTo->row());
		}
		
		$this->reload();
	}
}