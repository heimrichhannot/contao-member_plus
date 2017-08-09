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


use Contao\Frontend;
use HeimrichHannot\Haste\Model\MemberModel;
use HeimrichHannot\Haste\Util\FormSubmission;
use HeimrichHannot\Haste\Util\Url;
use HeimrichHannot\Request\Request;

class MemberLoginRegistrationPlusForm extends \HeimrichHannot\FormHybrid\Form
{
	protected $strTemplate = 'formhybrid_login_registration_plus';

	public function __construct($objModule, $intId = 0)
	{
		$this->strPalette = 'default';
		$this->strMethod  = FORMHYBRID_METHOD_POST;

		parent::__construct($objModule, $intId);
		
	}


	public function modifyDC()
	{
		if (!$this->objModule->disableCaptcha) {
			$this->addEditableField('captcha', $this->dca['fields']['captcha']);
		}
		
		// add password to default palette
		if(in_array('password', deserialize($this->objModule->formHybridEditable)))
		{
			$this->dca['palettes']['default'] = str_replace('login;', 'login,password,password_noConfirm;', $this->dca['palettes']['default']);
		}
		
		// use noConfirm widget when set
		if($this->objModule->bypassPasswordConfirm)
		{
			$this->dca['fields']['password']['inputType'] = 'password_noConfirm';
		}
		
		$this->dca['fields']['email']['eval']['unique'] = false;
		
		
		// HOOK: send insert ID and user data
		if (isset($GLOBALS['TL_HOOKS']['modifyDCLoginRegistrationPlusForm']) && is_array($GLOBALS['TL_HOOKS']['modifyDCLoginRegistrationPlusForm'])) {
			foreach ($GLOBALS['TL_HOOKS']['modifyDCLoginRegistrationPlusForm'] as $callback) {
				$this->import($callback[0]);
				$this->{$callback[0]}->{$callback[1]}($this->dca, $this->objModule);
			}
		}
	}

	protected function modifyVersion($objVersion)
	{
		$objVersion->setUsername($this->objActiveRecord->email);
		$objVersion->setEditUrl('contao/main.php?do=member&act=edit&id=' . $this->objActiveRecord->id . '&rt=' . REQUEST_TOKEN);

		return $objVersion;
	}

	protected function onSubmitCallback(\DataContainer $dc)
	{
		// HOOK: send insert ID and user data
		if (isset($GLOBALS['TL_HOOKS']['preLoginRegistration']) && is_array($GLOBALS['TL_HOOKS']['preLoginRegistration'])) {
		    foreach ($GLOBALS['TL_HOOKS']['preLoginRegistration'] as $callback) {
		        $this->import($callback[0]);
		        $this->{$callback[0]}->{$callback[1]}($dc->activeRecord->id, $dc->activeRecord, $this->objModule);
		    }
		}
	}

	protected function afterSubmitCallback(\DataContainer $dc)
	{
		if (($objTarget = \PageModel::findByPk($this->objModule->jumpTo)) !== null) {
			\Controller::redirect(\Controller::generateFrontendUrl($objTarget->row()));
		}
	}

	public function getEditableFields()
	{
		if ($this->getFields()) {
			return $this->arrEditable;
		}

		return array();
	}

	protected function compile()
	{
	}
	
	protected function processForm()
	{
		$this->onSubmitCallback($this);
		
		$username = Request::getPost('username') ? Request::getPost('username'): Request::getPost('email');
		$this->domainCheck = false;
		

		$this->domainList = $this->getDomainList();

		if (is_array($this->domainList) && !empty($this->domainList)) {
			$this->domainCheck = true;
		}
		
		// Login
		if(!FE_USER_LOGGED_IN)
		{
			if (empty($username) || empty(Request::getPost('password'))) {
				$_SESSION['LOGIN_ERROR'] = $GLOBALS['TL_LANG']['MSC']['emptyField'];
				
				$this->reload();
			}

			$strRedirect = $this->getJumpTo();
			$this->import('FrontendUser', 'User');
			
			$_POST['username'] = Request::getPost('username') ? Request::getPost('username') : Request::getPost('email');
			
			// Auto login is not allowed
			if (isset($_POST['autologin']) && !$this->autologin) {
				unset($_POST['autologin']);
				\Input::setPost('autologin', null);
			}

			if($this->User->login())
			{
				$this->redirect($this->redirectLogin);
			}
			else {
				if ($this->domainCheck || \Validator::isEmail($username))
				{
					if (($username = $this->getValidDomainUsername()) === null)
					{
						$this->reload();
					}
					// overwrite the username
					$username = strtolower($username);
					$_POST['username'] = $username;
					\Input::setPost('username', $username);

					if($this->User->login())
					{
						$this->redirect($strRedirect);
					}

					$this->registerUser($username);
				}
			}
		}

		$this->afterSubmitCallback($this);
	}
	
	protected function registerUser($username)
	{
		if (utf8_strlen(Request::getPost('password')) < \Config::get('minPasswordLength')) {
			$_SESSION['LOGIN_ERROR'] = sprintf(
				$GLOBALS['TL_LANG']['ERR']['passwordLength'],
				\Config::get('minPasswordLength')
			);
			
			return;
		}
		
		$arrData = array
		(
			'username' => $username,
			'password' => \Encryption::hash(Request::getPost('password')),
			'email'    => $username // required for registration email
		);
		
		// clean up previous registrations
		if (($objMember = MemberPlusMemberModel::findInactiveByUsername($username)) !== null) {
			$objMember->delete();
		}
		
		// user with this username already exists
		if (($objMember = MemberPlusMemberModel::findBy('username', $username)) !== null) {
			$_SESSION['LOGIN_ERROR'] = $GLOBALS['TL_LANG']['MSC']['usernameTaken'];
			
			return;
		}
		
		$this->createNewUser($arrData);
	}
	
	/**
	 * Create a new user and redirect
	 *
	 * @param array $arrData
	 */
	protected function createNewUser($arrData)
	{
		
		$token = md5(uniqid(mt_rand(), true));
		
		$arrData['tstamp'] = time();
		$arrData['login'] = $this->reg_allowLogin;
		$arrData['activation'] = $token;
		$arrData['link'] = $this->strAction . '?token=' . $token;
		$arrData['dateAdded'] = $arrData['tstamp'];
		$arrData['member_email'] = $arrData['email'];
		
		// Set default groups
		if (!array_key_exists('groups', $arrData))
		{
			$arrData['groups'] = $this->reg_groups;
		}
		
		// Disable account
		$arrData['disable'] = 1;
		
		// send activation e-mail via notification center
		if($this->formHybridSendConfirmationAsNotification)
		{
			$this->createConfirmationNotification($this->formHybridConfirmationNotification, $arrData);
		}
		
		// Make sure newsletter is an array
		if (isset($arrData['newsletter']) && !is_array($arrData['newsletter']))
		{
			$arrData['newsletter'] = array($arrData['newsletter']);
		}
		
		// Create the user
		$objNewUser = new \MemberModel();
		$objNewUser->setRow($arrData);
		$objNewUser->save();
		
		// Assign home directory
		if ($this->reg_assignDir)
		{
			$objHomeDir = \FilesModel::findByUuid($this->reg_homeDir);
			
			if ($objHomeDir !== null)
			{
				$this->import('Files');
				$strUserDir = standardize($arrData['username']) ?: 'user_' . $objNewUser->id;
				
				// Add the user ID if the directory exists
				while (is_dir(TL_ROOT . '/' . $objHomeDir->path . '/' . $strUserDir))
				{
					$strUserDir .= '_' . $objNewUser->id;
				}
				
				// Create the user folder
				new \Folder($objHomeDir->path . '/' . $strUserDir);
				
				$objUserDir = \FilesModel::findByPath($objHomeDir->path . '/' . $strUserDir);
				
				// Save the folder ID
				$objNewUser->assignDir = 1;
				$objNewUser->homeDir = $objUserDir->uuid;
				$objNewUser->save();
			}
		}
		
		// HOOK: send insert ID and user data
		if (isset($GLOBALS['TL_HOOKS']['createNewUser']) && is_array($GLOBALS['TL_HOOKS']['createNewUser']))
		{
			foreach ($GLOBALS['TL_HOOKS']['createNewUser'] as $callback)
			{
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
		if (!$this->reg_activate_plus && !$this->formHybridSendConfirmationAsNotification)
		{
			$this->sendAdminNotification($objNewUser->id, $arrData);
		}
		
		
		
		// Check whether there is a jumpTo page
		if (($jumpTo = $this->getJumpTo()) != '')
		{
			$this->redirect($jumpTo);
		}
		
		$this->reload();
	}
	
	/**
	 * Send an admin notification e-mail
	 *
	 * @param integer $intId
	 * @param array   $arrData
	 */
	protected function sendAdminNotification($intId, $arrData)
	{
		$objEmail = new \Email();
		
		$objEmail->from = $GLOBALS['TL_ADMIN_EMAIL'];
		$objEmail->fromName = $GLOBALS['TL_ADMIN_NAME'];
		$objEmail->subject = sprintf($GLOBALS['TL_LANG']['MSC']['adminSubject'], \Idna::decode(\Environment::get('host')));
		
		$strData = "\n\n";
		
		// Add user details
		foreach ($arrData as $k=>$v)
		{
			if ($k == 'password' || $k == 'tstamp' || $k == 'activation' || $k == 'dateAdded')
			{
				continue;
			}
			
			$v = deserialize($v);
			
			if ($k == 'dateOfBirth' && strlen($v))
			{
				$v = \Date::parse(\Config::get('dateFormat'), $v);
			}
			
			$strData .= $GLOBALS['TL_LANG']['tl_member'][$k][0] . ': ' . (is_array($v) ? implode(', ', $v) : $v) . "\n";
		}
		
		$objEmail->text = sprintf($GLOBALS['TL_LANG']['MSC']['adminText'], $intId, $strData . "\n") . "\n";
		$objEmail->sendTo($GLOBALS['TL_ADMIN_EMAIL']);
		
		$this->log('A new user (ID ' . $intId . ') has registered on the website', __METHOD__, TL_ACCESS);
	}
	
	
	
	protected function getJumpTo($objMember = null)
	{
		$strRedirect = \Environment::get('request');
		
		if ($this->redirectBack && $_SESSION['LAST_PAGE_VISITED'] != '') {
			$strRedirect = $_SESSION['LAST_PAGE_VISITED'];
		} else {
			if ($this->jumpTo) {
				$strRedirect = Url::generateFrontendUrl($this->jumpTo);
				
				// Overwrite the jumpTo page with an individual group setting
				
				if ($objMember !== null) {
					$arrGroups = deserialize($objMember->groups);
					
					if (!empty($arrGroups) && is_array($arrGroups)) {
						$objGroupPage = \MemberGroupModel::findFirstActiveWithJumpToByIds($arrGroups);
						
						if ($objGroupPage !== null) {
							$strRedirect = $this->generateFrontendUrl($objGroupPage->row());
						}
					}
				}
			}
		}
		
		
		return $strRedirect;
	}
	
	protected function getValidDomainUsername()
	{
		$arrDomainList = $this->getDomainList();
		
		$username = Request::getPost('username')?Request::getPost('username'):Request::getPost('email');
		$domain   = Request::getPost('domain');
		
		if(!empty($arrDomainList))
		{
			if (\Validator::isEmail($username)) {
				$domain = substr($username, strpos($username, '@'));
				
				// remove domain
				$username = str_replace($domain, '', $username);
			}
			
			$domain = $arrDomainList[str_replace('@', '', $domain)];
			
			if ($domain === null) {
				$_SESSION['LOGIN_ERROR'] = $GLOBALS['TL_LANG']['MSC']['invalidDomain'];
				return null;
			}
			
			// combine domain with username
			if ($domain !== null) {
				$username = $username.$domain;
			}
		}
		
		
		return $username;
	}
	
	protected function getDomainList($includeHidden = true)
	{
		$arrDomains = array();
		
		if (!is_array($this->allowedMailDomains) || empty($this->allowedMailDomains)) {
			return $arrDomains;
		}
		
		foreach ($this->allowedMailDomains as $arrDomain) {
			
			if (empty($arrDomain['domain']) || ($arrDomain['hide'] && !$includeHidden)) {
				continue;
			}
			
			$strDomain              = ltrim($arrDomain['domain'], '@');
			$arrDomains[$strDomain] = '@'.$strDomain;
		}
		
		return $arrDomains;
	}
	
	protected function createConfirmationNotification($notificationId, $arrData)
	{
		if (($objMessage = \HeimrichHannot\NotificationCenterPlus\MessageModel::findPublishedById($notificationId)) !== null)
		{
			$arrToken = FormSubmission::tokenizeData($arrData);
			
			if ($this->sendConfirmationNotification($objMessage, $arrSubmissionData, $arrToken))
			{
				$objMessage->send($arrToken, $GLOBALS['TL_LANGUAGE']);
				$_SESSION['LOGIN_INFO'] = sprintf($GLOBALS['TL_LANG']['MSC']['activationEmailSent'], $arrToken['username']);
			}
		}
	}
	
}


