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


class ContentMemberlist extends \ContentElement
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'ce_memberlist';


	protected $objMembers;

	/**
	 * URL cache array
	 * @var array
	 */
	private static $arrUrlCache = array();

	protected $Controller;

	/**
	 * Return if the highlighter plugin is not loaded
	 * @return string
	 */
	public function generate()
	{
		$this->mlSort = deserialize($this->mlSort);

		return parent::generate();
	}


	protected function compile()
	{
		$this->Controller = new MemberPlus($this->objModel);

		$this->objMembers = MemberPlusMemberModel::findActiveByIds($this->mlSort);

		if($this->objMembers === null)
		{
			$this->Template->empty = $GLOBALS['TL_LANG']['MSC']['emptyMemberlist'];
            return;
		}

		$arrMembers = array();

		while($this->objMembers->next())
		{
			$arrMembers[$this->objMembers->id] = $this->Controller->parseMember($this->objMembers->current());
		}

		$this->Template->members = $arrMembers;
	}

	protected function parseMember($objMember)
	{
		global $objPage;

		$objT = new \FrontendTemplate('memberlist_default');
		$objT->setData($objMember->row());

		$strUrl = $this->generateMemberUrl($objMember);

		$objT->addImage = false;

		// Add an image
		if ($objMember->addImage && $objMember->singleSRC != '')
		{
			$objModel = \FilesModel::findByUuid($objMember->singleSRC);

			if ($objModel === null)
			{
				if (!\Validator::isUuid($objMember->singleSRC))
				{
					$objMember->text = '<p class="error">'.$GLOBALS['TL_LANG']['ERR']['version2format'].'</p>';
				}
			}
			elseif (is_file(TL_ROOT . '/' . $objModel->path))
			{
				// Do not override the field now that we have a model registry (see #6303)
				$arrMember = $objMember->row();

				// Override the default image size
				if ($this->size != '')
				{
					$size = deserialize($this->size);

					if ($size[0] > 0 || $size[1] > 0 || is_numeric($size[2]))
					{
						$arrMember['size'] = $this->size;
					}
				}

				$arrMember['singleSRC'] = $objModel->path;
				\Controller::addImageToTemplate($objT, $arrMember);
			}
		}

		$arrTitle = array($objMember->academicTitle, $objMember->firstname, $objMember->lastname);
		$objT->titleCombined = empty($arrTitle) ? '' : implode(' ', $arrTitle);

		$arrLocation = array($objMember->postal, $objMember->city);
		$objT->locationCombined = empty($arrLocation) ? '' : implode(' ', $arrLocation);

		$objT->websiteLink = $objMember->website;
		$objT->websiteTitle = $GLOBALS['TL_LANG']['MSC']['memberlist']['websiteTitle'];

		// Add http:// to the website
		if (($objMember->website != '') && !preg_match('@^(https?://|ftp://|mailto:|#)@i', $objMember->website))
		{
			$objT->websiteLink = 'http://' . $objMember->website;
		}

		if($this->mlSource == 'external')
		{
			// Encode e-mail addresses
			if (substr($this->mlUrl, 0, 7) == 'mailto:')
			{
				$strUrl = \StringUtil::encodeEmail($this->mlUrl);
			}

			// Ampersand URIs
			else
			{
				$strUrl = ampersand($this->mlUrl);
			}
		}

		$objT->link = $strUrl;
		$objT->linkTarget = ($this->mlTarget ? (($objPage->outputFormat == 'xhtml') ? ' onclick="return !window.open(this.href)"' : ' target="_blank"') : '');
		$objT->linkTitle = specialchars(sprintf($GLOBALS['TL_LANG']['MSC']['openMember'], $objT->titleCombined));

		return $objT->parse();
	}


	/**
	 * Generate a URL and return it as string
	 * @param object
	 * @param boolean
	 * @return string
	 */
	protected function generateMemberUrl($objItem)
	{
		$strCacheKey = 'id_' . $objItem->id;

		// Load the URL from cache
		if (isset(self::$arrUrlCache[$strCacheKey]))
		{
			return self::$arrUrlCache[$strCacheKey];
		}

		// Initialize the cache
		self::$arrUrlCache[$strCacheKey] = null;

		switch ($this->mlSource)
		{
			// Link to an external page
			case 'external':
				if (substr($objItem->url, 0, 7) == 'mailto:')
				{
					self::$arrUrlCache[$strCacheKey] = \StringUtil::encodeEmail($objItem->mlUrl);
				}
				else
				{
					self::$arrUrlCache[$strCacheKey] = ampersand($objItem->mlUrl);
				}
				break;

			// Link to an internal page
			case 'internal':
				if (($objTarget = \PageModel::findByPk($this->mlJumpTo)) !== null)
				{
					self::$arrUrlCache[$strCacheKey] = ampersand($this->generateFrontendUrl($objTarget->row()));
				}
				break;
			// Link to an article with the Reader Module
			case 'article_reader':
				if (($objArticle = \ArticleModel::findByPk($this->mlArticleId, array('eager'=>true))) !== null && ($objPid = $objArticle->getRelated('pid')) !== null)
				{
//					$GLOBALS['TL_AUTO_ITEM'][] = ((\Config::get('disableAlias') && $objArticle->alias == '') ? : $objArticle->alias);
					self::$arrUrlCache[$strCacheKey] = ampersand($this->generateFrontendUrl($objPid->row(), '/articles/' . ((!\Config::get('disableAlias') && $objArticle->alias != '') ? $objArticle->alias : $objArticle->id)) . ((\Config::get('useAutoItem') && !\Config::get('disableAlias')) ?  '/' : '/items/') . ((!\Config::get('disableAlias') && $objItem->alias != '') ? $objItem->alias : $objItem->id));
				}
			break;
			// Link to an article
			case 'article':
				if (($objArticle = \ArticleModel::findByPk($this->mlArticleId, array('eager'=>true))) !== null && ($objPid = $objArticle->getRelated('pid')) !== null)
				{
					self::$arrUrlCache[$strCacheKey] = ampersand($this->generateFrontendUrl($objPid->row(), '/articles/' . ((!\Config::get('disableAlias') && $objArticle->alias != '') ? $objArticle->alias : $objArticle->id)));
				}
			break;
		}

		return self::$arrUrlCache[$strCacheKey];
	}

}