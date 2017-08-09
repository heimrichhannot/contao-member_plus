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


use HeimrichHannot\Request\Request;

class Hooks extends \System
{
	public function generateBreadcrumbHook($arrItems, $objModule)
	{
		// logic for this is done in HeimrichHannot\Memberplus\Hooks::getPageIdFromUrlHook
		if(\Input::get('article_reader'))
		{
			foreach($arrItems as $i => $arrItem)
			{
				// skip pages
				if(isset($arrItem['data']['type'])) continue;

				global $objPage;

				// set title & link to pageTitle = member comined title
				$arrItems[$i]['title'] = $objPage->pageTitle;
				$arrItems[$i]['link'] = $objPage->pageTitle;
			}
		}

		return $arrItems;
	}

	public function getPageIdFromUrlHook($arrFragments)
	{
		// source = article_reader -> unset unused GET parameters and store them in custom Get Variable, for ModuleMemberReader
		if($arrFragments[1] == 'auto_item' && $arrFragments[2] == 'articles')
		{
			// article alias = 3rd & member alias = 4th item
			if(isset($arrFragments[3]) && isset($arrFragments[4]))
			{
				unset($arrFragments[1]); // unset auto_item

				\Input::setGet('article_reader', $arrFragments[4]);
				unset($arrFragments[4]); // unset member alias
			}
		}

		// restore array index order
		return array_values($arrFragments);
	}


	public function activateAccountHook($objMember, $objModule)
	{
		if(!$objModule->reg_activate_login) return;

		global $objPage;

		$objRootPage = \PageModel::findByPk($objPage->rootId);

		if ($objRootPage === null) return;

		$time = time();

		// Generate the cookie hash
		$strHash = sha1(session_id() . (!\Config::get('disableIpCheck') ? \Environment::get('ip') : '') . 'FE_USER_AUTH');

		// Clean up old sessions
		\Database::getInstance()->prepare("DELETE FROM tl_session WHERE tstamp<? OR hash=?")
			->execute(($time - \Config::get('sessionTimeout')), $strHash);

		// Save the session in the database
		\Database::getInstance()->prepare("INSERT INTO tl_session (pid, tstamp, name, sessionID, ip, hash) VALUES (?, ?, ?, ?, ?, ?)")
			->execute($objMember->id, $time, 'FE_USER_AUTH', session_id(), \Environment::get('ip'), $strHash);

		// Set the authentication cookie
		$this->setCookie('FE_USER_AUTH', $strHash, ($time + \Config::get('sessionTimeout')), $GLOBALS['TL_CONFIG']['websitePath']);

		// Save the login status
		$_SESSION['TL_USER_LOGGED_IN'] = true;

		$this->log('User "' . $objMember->username . '" was logged in automatically', get_class($objModule) . ' activateAccount()', TL_ACCESS);
	}

}