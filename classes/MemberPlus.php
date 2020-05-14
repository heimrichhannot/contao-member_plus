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

class MemberPlus extends \Frontend
{
	/**
	 * Model
	 *
	 * @var Model
	 */
	protected $objModel;
	
	/**
	 * Current record
	 *
	 * @var array
	 */
	protected $arrData = [];
	
	/**
	 * URL cache array
	 *
	 * @var array
	 */
	private static $arrUrlCache = [];
	
	protected $strDummyMaleImageSRC   = '/system/modules/member_plus/assets/img/dummy_male.png';
	protected $strDummyFemaleImageSRC = '/system/modules/member_plus/assets/img/dummy_female.png';
	
	public function __construct($objModel)
	{
		if ($objModel instanceof \Model) {
			$this->objModel = $objModel;
		} elseif ($objModel instanceof \Model\Collection) {
			$this->objModel = $objModel->current();
		}
		
		parent::__construct();
		
		$this->arrData = $objModel->row();
	}
	
	public function parseMember($objMember)
	{
		global $objPage;
		
        $this->mlTemplate = $this->mlTemplate ?: 'memberlist_default';

		$objT = new \FrontendTemplate($this->mlTemplate);
		$objT->setData($objMember->row());
		
		$arrSkipFields = deserialize($this->mlFields, true);
		
		if ($this->mlSkipFields) {
			$this->dropFieldsFromTemplate($objT, $arrSkipFields);
		}
		
		$strUrl = $this->generateMemberUrl($objMember);
		
		$objT->hasContent = false;
		$objElement       = \ContentModel::findPublishedByPidAndTable($objMember->id, 'tl_member');
		
		if ($objElement !== null) {
			$objT->hasContent = true;
			
			if ($this->mlLoadContent) {
				while ($objElement->next()) {
					$objT->text .= $this->getContentElement($objElement->current());
				}
			}
		}
		
		$objT->addImage = false;
		
		if (!$this->mlDisableImages) {
			$this->addMemberImageToTemplate($objT, $objMember);
		}
		
		$objT->titleCombined = $this->getCombinedTitle($objMember, $arrSkipFields);
		
		$arrLocation = array_filter([$objMember->postal, $objMember->city]);
		
		$objT->locationCombined = empty($arrLocation) ? '' : implode(' ', $arrLocation);
		
		$objT->websiteLink  = $objMember->website;
		$objT->websiteTitle = $GLOBALS['TL_LANG']['MSC']['memberlist']['websiteTitle'];
		
		// Add http:// to the website
		if (($objMember->website != '') && !preg_match('@^(https?://|ftp://|mailto:|#)@i', $objMember->website)) {
			$objT->websiteLink = 'http://' . $objMember->website;
		}
		
		if ($this->mlSource == 'external') {
			// Encode e-mail addresses
			if (substr($this->mlUrl, 0, 7) == 'mailto:') {
				$strUrl = \StringUtil::encodeEmail($this->mlUrl);
			} // Ampersand URIs
			else {
				$strUrl = ampersand($this->mlUrl);
			}
		}
		
		$objT->link       = $strUrl;
		$objT->linkTarget =
			($this->mlTarget ? (($objPage->outputFormat == 'xhtml') ? ' onclick="return !window.open(this.href)"' : ' target="_blank"') : '');
		$objT->linkTitle  = specialchars(sprintf($GLOBALS['TL_LANG']['MSC']['openMember'], $objT->titleCombined));
		
		return $objT->parse();
	}
	
	protected function dropFieldsFromTemplate($objT, array $arrFields = [])
	{
		if (empty($arrFields)) {
			return $objT;
		}
		
		foreach ($arrFields as $strName) {
			$objT->{$strName} = null;
		}
	}
	
	protected function addMemberImageToTemplate($objTemplate, $objMember)
	{
		// Add an image
		if ($objMember->addImage && $objMember->singleSRC != '') {
			$objModel = \FilesModel::findByUuid($objMember->singleSRC);
			
			if ($objModel === null) {
				if (!\Validator::isUuid($objMember->singleSRC)) {
					$objMember->text = '<p class="error">' . $GLOBALS['TL_LANG']['ERR']['version2format'] . '</p>';
				}
			} elseif (is_file(TL_ROOT . '/' . $objModel->path)) {
				// Do not override the field now that we have a model registry (see #6303)
				$arrMember = $objMember->row();
				
				if ($this->objModel instanceof \ModuleModel) {
					$this->size = $this->mlImgSize != '' ? $this->mlImgSize : $this->imgSize; // tl_module = imgSize, tl_content = size
				}
				
				// Override the default image size
				if ($this->size != '') {
					$size = deserialize($this->size);
					
					if ($size[0] > 0 || $size[1] > 0 || is_numeric($size[2])) {
						$arrMember['size'] = $this->size;
					}
				}
				
				$arrMember['singleSRC'] = $objModel->path;
				\Controller::addImageToTemplate($objTemplate, $arrMember);
			}
		} else {
			if (!$this->mlDisableDummyImages) {
				$strDummyImage = null;
				
				switch ($objMember->gender) {
					case 'female':
						$strDummyImage = $this->strDummyFemaleImageSRC;
						break;
					case 'male' :
						$strDummyImage = $this->strDummyMaleImageSRC;
						break;
				}
				
				if ($this->mlAddCustomDummyImages) {
					switch ($objMember->gender) {
						case 'female':
							$strDummyImage = $this->mlDummyImageFemale;
							break;
						case 'male' :
							$strDummyImage = $this->mlDummyImageMale;
							break;
					}
					
					$objModel = \FilesModel::findByUuid($strDummyImage);
					
					if ($objModel === null) {
						if (!\Validator::isUuid($strDummyImage)) {
							$objMember->text = '<p class="error">' . $GLOBALS['TL_LANG']['ERR']['version2format'] . '</p>';
						}
					} else {
						$strDummyImage = $objModel->path;
					}
				}
				
				if (is_file(TL_ROOT . '/' . $strDummyImage)) {
					// Do not override the field now that we have a model registry (see #6303)
					$arrMember = $objMember->row();
					
					if ($this->objModel instanceof \ModuleModel) {
						$this->size = $this->mlImgSize != '' ? $this->mlImgSize : $this->imgSize; // tl_module = imgSize, tl_content = size
					}
					
					// Override the default image size
					if ($this->size != '') {
						$size = deserialize($this->size);
						
						if ($size[0] > 0 || $size[1] > 0 || is_numeric($size[2])) {
							$arrMember['size'] = $this->size;
						}
					}
					
					$arrMember['singleSRC'] = $strDummyImage;
					\Controller::addImageToTemplate($objTemplate, $arrMember);
				}
			}
		}
	}
	
	public static function getCombinedTitle($objMember, $arrSkipFields = [])
	{
		$arrTitle = ['academicTitle' => $objMember->academicTitle, 'firstname' => $objMember->firstname, 'nobilityTitle' => $objMember->nobilityTitle,'lastname' => $objMember->lastname];
		
		if (is_array($arrSkipFields) && !empty($arrSkipFields)) {
			foreach ($arrSkipFields as $strName) {
				unset($arrTitle[$strName]);
			}
		}
		
		return empty($arrTitle) ? '' : trim(implode(' ', $arrTitle));
	}
	
	/**
	 * Generate a URL and return it as string
	 *
	 * @param object
	 * @param boolean
	 *
	 * @return string
	 */
	protected function generateMemberUrl($objItem)
	{
		$strCacheKey = 'id_' . $objItem->id;
		
		// Load the URL from cache
		if (isset(self::$arrUrlCache[$strCacheKey])) {
			return self::$arrUrlCache[$strCacheKey];
		}
		
		// Initialize the cache
		self::$arrUrlCache[$strCacheKey] = null;
		
		switch ($this->mlSource) {
			// Link to an external page
			case 'external':
				if (substr($objItem->url, 0, 7) == 'mailto:') {
					self::$arrUrlCache[$strCacheKey] = \StringUtil::encodeEmail($objItem->mlUrl);
				} else {
					self::$arrUrlCache[$strCacheKey] = ampersand($objItem->mlUrl);
				}
				break;
			
			// Link to an internal page
			case 'internal':
				if (($objTarget = \PageModel::findByPk($this->mlJumpTo)) !== null) {
					self::$arrUrlCache[$strCacheKey] = ampersand($this->generateFrontendUrl($objTarget->row()));
				}
				break;
			// Link to an article with the Reader Module
			case 'article_reader':
				if (($objArticle = \ArticleModel::findByPk($this->mlArticleId, ['eager' => true])) !== null
					&& ($objPid = $objArticle->getRelated('pid')) !== null
				) {
//					$GLOBALS['TL_AUTO_ITEM'][] = ((\Config::get('disableAlias') && $objArticle->alias == '') ? : $objArticle->alias);
					self::$arrUrlCache[$strCacheKey] = ampersand(
						$this->generateFrontendUrl(
							$objPid->row(),
							'/articles/' . ((!\Config::get('disableAlias')
											 && $objArticle->alias != '') ? $objArticle->alias : $objArticle->id)
						) . ((\Config::get('useAutoItem') && !\Config::get('disableAlias')) ? '/' : '/items/') . ((!\Config::get('disableAlias')
																												   && $objItem->alias
																													  != '') ? $objItem->alias : $objItem->id)
					);
				}
				break;
			// Link to an article
			case 'article':
				if (($objArticle = \ArticleModel::findByPk($this->mlArticleId, ['eager' => true])) !== null
					&& ($objPid = $objArticle->getRelated('pid')) !== null
				) {
					self::$arrUrlCache[$strCacheKey] = ampersand(
						$this->generateFrontendUrl(
							$objPid->row(),
							'/articles/' . ((!\Config::get('disableAlias')
											 && $objArticle->alias != '') ? $objArticle->alias : $objArticle->id)
						)
					);
				}
				break;
		}
		
		return self::$arrUrlCache[$strCacheKey];
	}
	
	
	/**
	 * Set an object property
	 *
	 * @param string
	 * @param mixed
	 */
	public function __set($strKey, $varValue)
	{
		$this->arrData[$strKey] = $varValue;
	}
	
	
	/**
	 * Return an object property
	 *
	 * @param string
	 *
	 * @return mixed
	 */
	public function __get($strKey)
	{
		if (isset($this->arrData[$strKey])) {
			return $this->arrData[$strKey];
		}
		
		return parent::__get($strKey);
	}
	
	
	/**
	 * Check whether a property is set
	 *
	 * @param string
	 *
	 * @return boolean
	 */
	public function __isset($strKey)
	{
		return isset($this->arrData[$strKey]);
	}
	
	
	/**
	 * Return the model
	 *
	 * @return \Model
	 */
	public function getModel()
	{
		return $this->objModel;
	}
}

