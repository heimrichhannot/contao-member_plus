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

}