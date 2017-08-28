<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2015 Heimrich & Hannot GmbH
 *
 * @package member_plus
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\MemberPlus;


class ModuleMemberReader extends \Module
{
	
	protected $strTemplate = 'mod_memberreader';
	
	protected $Controller;
	
	public function generate()
	{
		if (TL_MODE == 'BE') {
			$objTemplate           = new \BackendTemplate('be_wildcard');
			$objTemplate->wildcard = '### ' . utf8_strtoupper($GLOBALS['TL_LANG']['FMD']['memberreader'][0]) . ' ###';
			$objTemplate->title    = $this->headline;
			$objTemplate->id       = $this->id;
			$objTemplate->link     = $this->name;
			$objTemplate->href     = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;
			
			return $objTemplate->parse();
		}
		
		// logic for this is done in HeimrichHannot\Memberplus\Hooks::getPageIdFromUrlHook
		if (\Input::get('article_reader')) {
			\Input::setGet('items', \Input::get('article_reader'));
		} else {
			if (!isset($_GET['items']) && \Config::get('useAutoItem') && (isset($_GET['auto_item']))) {
				\Input::setGet('items', \Input::get('auto_item'));
			}
		}
		
		if (!\Input::get('items')) {
			return '';
		}
		
		$this->mlGroups = deserialize($this->mlGroups);
		
		if (!is_array($this->mlGroups) || empty($this->mlGroups)) {
			return '';
		}
		
		return parent::generate();
	}
	
	protected function compile()
	{
		global $objPage;
		
		$this->Controller = new MemberPlus($this->objModel);
		
		$this->Template->members = '';
		
		// Get the member item
		$objMember = MemberPlusMemberModel::findActiveByParentAndIdOrAlias(\Input::get('items'), $this->mlGroups);
		
		if ($objMember === null) {
			// Do not index or cache the page
			$objPage->noSearch = 1;
			$objPage->cache    = 0;
			
			// Send a 404 header
			header('HTTP/1.1 404 Not Found');
			$this->Template->members = '<p class="error">' . sprintf($GLOBALS['TL_LANG']['MSC']['invalidPage'], \Input::get('items')) . '</p>';
			
			return;
		}
		
		
		$strMember               = $this->Controller->parseMember($objMember);
		$this->Template->members = $strMember;
		
		$strCombinedTitle = $this->Controller->getCombinedTitle($objMember);
		
		// Overwrite the page title (see #2853 and #4955)
		if ($strCombinedTitle != '') {
			$objPage->pageTitle = strip_tags(strip_insert_tags($strCombinedTitle));
		}
		
		// Overwrite the page description
//		if ($objArticle->teaser != '')
//		{
//			$objPage->description = $this->prepareMetaDescription($objArticle->teaser);
//		}
		
		
	}
}