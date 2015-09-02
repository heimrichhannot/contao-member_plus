<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2015 Heimrich & Hannot GmbH
 * @package member_plus
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\MemberPlus;


class ModuleLoginRegistration extends \ModuleRegistration
{

    protected $strTemplate = 'mod_loginregistration';

    public function generate()
    {
        if (TL_MODE == 'BE') {
            $objTemplate           = new \BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### '.utf8_strtoupper($GLOBALS['TL_LANG']['FMD']['loginregistration'][0]).' ###';
            $objTemplate->title    = $this->headline;
            $objTemplate->id       = $this->id;
            $objTemplate->link     = $this->name;
            $objTemplate->href     = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id='.$this->id;

            return $objTemplate->parse();
        }

        // required by ModuleRegistration::generate();
        $this->editable = array('username', 'password');

        $this->allowedMailDomains = deserialize($this->allowedMailDomains, true);

        $this->domainCheck = false;

        $this->domainList = $this->getDomainList();

        if (is_array($this->domainList) && !empty($this->domainList)) {
            $this->domainCheck = true;
        }

        // Set the last page visited
        if ($this->redirectBack) {
            $_SESSION['LAST_PAGE_VISITED'] = $this->getReferer();
        }

        // Redirect to the jumpTo page if user is logged in and permanentRedirect is enables
        if (FE_USER_LOGGED_IN && $this->redirectPermanent) {
            $this->redirect($this->getJumpTo());
        }

        // Login
        if (\Input::post('FORM_SUBMIT') == 'tl_login') {
            // Check whether username and password are set
            if (empty($_POST['username']) || empty($_POST['password'])) {
                $_SESSION['LOGIN_ERROR'] = $GLOBALS['TL_LANG']['MSC']['emptyField'];
                $this->reload();
            }

			$strRedirect = $this->getJumpTo();
			$this->import('FrontendUser', 'User');

			// Auto login is not allowed
			if (isset($_POST['autologin']) && !$this->autologin) {
				unset($_POST['autologin']);
				\Input::setPost('autologin', null);
			}

            // Login existing user, or try to get username-domain-combination or register
			if($this->User->login())
			{
				$this->redirect($strRedirect);
			}
			else
			{
				$username = $_POST['username'];

				if ($this->domainCheck || \Validator::isEmail($username))
				{
					if (($username = $this->getValidDomainUsername()) === null)
					{
						$this->reload();
					}
                    // overwrite the username
                    $_POST['username'] = $username;
                    \Input::setPost('username', $username);

                    if($this->User->login())
                    {
                        $this->redirect($strRedirect);
                    }

					$this->registerUser($username);
				}
			}

            $this->reload();
        }

        // Logout and redirect to the website root if the current page is protected
        if (\Input::post('FORM_SUBMIT') == 'tl_logout') {
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

        // Activate account
        if (\Input::get('token') != '') {
            $this->activateAcount();
        }

//		$this->strTemplate = ($this->cols > 1) ? 'mod_login_2cl' : 'mod_login_1cl';

        $this->Template = new \FrontendTemplate($this->strTemplate);
        $this->Template->setData($this->arrData);

        if ($this->domainCheck) {
            $this->Template->domainList = $this->getDomainList(false);
        }


        $blnHasError = false;

        if (!empty($_SESSION['TL_ERROR'])) {
            $blnHasError             = true;
            $_SESSION['LOGIN_ERROR'] = $_SESSION['TL_ERROR'][0];
            $_SESSION['TL_ERROR']    = array();
        }

        if (isset($_SESSION['LOGIN_ERROR'])) {
            $blnHasError                 = true;
            $this->Template->message     = $_SESSION['LOGIN_ERROR'];
            $this->Template->messageType = 'danger';
            unset($_SESSION['LOGIN_ERROR']);
        }

        if (isset($_SESSION['LOGIN_INFO'])) {
            $blnHasError                 = true;
            $this->Template->message     = $_SESSION['LOGIN_INFO'];
            $this->Template->messageType = 'info';
            unset($_SESSION['LOGIN_INFO']);
        }

        if (isset($_SESSION['LOGIN_SUCCESS'])) {
            $blnHasError                 = true;
            $this->Template->message     = $_SESSION['LOGIN_SUCCESS'];
            $this->Template->messageType = 'success';
            unset($_SESSION['LOGIN_SUCCESS']);
        }

        $this->Template->hasError  = $blnHasError;
        $this->Template->username  = $GLOBALS['TL_LANG']['MSC']['username'];
        $this->Template->password  = $GLOBALS['TL_LANG']['MSC']['password'][0];
        $this->Template->action    = ampersand(\Environment::get('indexFreeRequest'));
        $this->Template->slabel    = specialchars($GLOBALS['TL_LANG']['MSC']['login']);
        $this->Template->value     = specialchars(\Input::post('username'));
        $this->Template->autologin = ($this->autologin && \Config::get('autologin') > 0);
        $this->Template->autoLabel = $GLOBALS['TL_LANG']['MSC']['autologin'];
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

    protected function getValidDomainUsername()
    {
        $arrDomainList = $this->getDomainList();

        $username = $_POST['username'];
        $domain   = $_POST['domain'];


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

        return $username;
    }

    protected function registerUser($username)
    {
        if (utf8_strlen(\Input::post('password')) < \Config::get('minPasswordLength')) {
            $_SESSION['LOGIN_ERROR'] = sprintf(
                $GLOBALS['TL_LANG']['ERR']['passwordLength'],
                \Config::get('minPasswordLength')
            );

            return;
        }

        $arrData = array
        (
            'username' => $username,
            'password' => \Encryption::hash(\Input::post('password')),
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

        $_SESSION['LOGIN_INFO'] = sprintf($GLOBALS['TL_LANG']['MSC']['activationEmailSent'], $username);

        $this->createNewUser($arrData);
    }

    /**
     * Activate an account
     */
    protected function activateAcount()
    {
        // Check the token
        $objMember = \MemberModel::findByActivation(\Input::get('token'));

        if ($objMember === null) {
            $_SESSION['LOGIN_ERROR'] = $GLOBALS['TL_LANG']['MSC']['accountError'];

            return;
        }

        // Update the account
        $objMember->disable    = '';
        $objMember->activation = '';
        $objMember->save();

        // Log activity
        $this->log(
            'User account ID '.$objMember->id.' ('.$objMember->email.') has been activated',
            __METHOD__,
            TL_ACCESS
        );

        // Confirm activation
        $_SESSION['LOGIN_SUCCESS'] = $GLOBALS['TL_LANG']['MSC']['accountActivated'];

        // HOOK: post activation callback
        if (isset($GLOBALS['TL_HOOKS']['activateAccount']) && is_array($GLOBALS['TL_HOOKS']['activateAccount'])) {
            foreach ($GLOBALS['TL_HOOKS']['activateAccount'] as $callback) {
                $this->import($callback[0]);
                $this->$callback[0]->$callback[1]($objMember, $this);
            }
        }

        // Redirect to the jumpTo page
        if (($objTarget = $this->objModel->getRelated('reg_jumpTo')) !== null) {
            $this->redirect($this->generateFrontendUrl($objTarget->row()));
        }
    }


    protected function getJumpTo($objMember = null)
    {
        $strRedirect = \Environment::get('request');

        if ($this->redirectBack && $_SESSION['LAST_PAGE_VISITED'] != '') {
            $strRedirect = $_SESSION['LAST_PAGE_VISITED'];
        } else {
            if ($this->jumpTo && ($objTarget = $this->objModel->getRelated('jumpTo')) !== null) {
                $strRedirect = $this->generateFrontendUrl($objTarget->row());

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
}