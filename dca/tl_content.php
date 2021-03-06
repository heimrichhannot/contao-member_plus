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

$dc = &$GLOBALS['TL_DCA']['tl_content'];

/**
 * Palettes
 */

// selector
array_insert(
	$dc['palettes']['__selector__'],
	0,
	['mlSource', 'mlAddCustomDummyImages', 'mlSkipFields']
); // bug? mustn't be inserted after type selector


// memberlist
$dc['palettes']['memberlist'] =
	'{type_legend},type,headline;{ml_config_legend},mlGroups,mlSort,mlSource,mlTemplate,mlLoadContent,size,mlDisableImages,mlDisableDummyImages,mlAddCustomDummyImages,mlSkipFields;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space;{invisible_legend:hide},invisible,start,stop';

/**
 * Subpalettes
 */

$dc['subpalettes']['mlSource_internal']       = 'mlJumpTo';
$dc['subpalettes']['mlSource_article']        = 'mlArticleId';
$dc['subpalettes']['mlSource_article_reader'] = 'mlArticleId';
$dc['subpalettes']['mlSource_external']       = 'mlUrl,mlTarget';
$dc['subpalettes']['mlAddCustomDummyImages']  = 'mlDummyImageMale,mlDummyImageFemale';
$dc['subpalettes']['mlSkipFields']            = 'mlFields';

$arrFields = [
	'mlGroups'               => [
		'label'      => &$GLOBALS['TL_LANG']['tl_content']['mlGroups'],
		'exclude'    => true,
		'inputType'  => 'checkboxWizard',
		'foreignKey' => 'tl_member_group.name',
		'eval'       => ['mandatory' => true, 'multiple' => true, 'submitOnChange' => true],
		'sql'        => "blob NULL",
		'relation'   => ['type' => 'hasMany', 'load' => 'lazy']
	],
	'mlSort'                 => [
		'label'            => &$GLOBALS['TL_LANG']['tl_content']['mlSort'],
		'exclude'          => true,
		'inputType'        => 'checkboxWizard',
		'options_callback' => ['tl_content_member_plus', 'getMembers'],
		'eval'             => ['multiple' => true],
		'sql'              => "blob NULL",
	],
	'mlSource'               => [
		'label'            => &$GLOBALS['TL_LANG']['tl_content']['mlSource'],
		'default'          => 'default',
		'exclude'          => true,
		'filter'           => true,
		'inputType'        => 'radio',
		'options_callback' => ['tl_content_member_plus', 'getSourceOptions'],
		'reference'        => &$GLOBALS['TL_LANG']['tl_content']['memberPlusReference'],
		'eval'             => ['submitOnChange' => true, 'helpwizard' => true],
		'sql'              => "varchar(32) NOT NULL default ''"
	],
	'mlTemplate'             => [
		'label'            => &$GLOBALS['TL_LANG']['tl_content']['mlTemplate'],
		'default'          => 'memberlist_default',
		'exclude'          => true,
		'inputType'        => 'select',
		'options_callback' => ['tl_content_member_plus', 'getMemberlistTemplates'],
		'eval'             => ['tl_class' => 'w50'],
		'sql'              => "varchar(64) NOT NULL default ''"
	],
	'mlJumpTo'               => [
		'label'      => &$GLOBALS['TL_LANG']['tl_content']['mlJumpTo'],
		'exclude'    => true,
		'inputType'  => 'pageTree',
		'foreignKey' => 'tl_page.title',
		'eval'       => ['fieldType' => 'radio'],
		'sql'        => "int(10) unsigned NOT NULL default '0'",
		'relation'   => ['type' => 'hasOne', 'load' => 'eager']
	],
	'mlArticleId'            => [
		'label'            => &$GLOBALS['TL_LANG']['tl_content']['mlArticleId'],
		'exclude'          => true,
		'inputType'        => 'select',
		'options_callback' => ['tl_content_member_plus', 'getArticleAlias'],
		'eval'             => ['chosen' => true, 'mandatory' => true],
		'sql'              => "int(10) unsigned NOT NULL default '0'"
	],
	'mlUrl'                  => [
		'label'     => &$GLOBALS['TL_LANG']['MSC']['mlUrl'],
		'exclude'   => true,
		'search'    => true,
		'inputType' => 'text',
		'eval'      => ['mandatory' => true, 'decodeEntities' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
		'sql'       => "varchar(255) NOT NULL default ''"
	],
	'mlTarget'               => [
		'label'     => &$GLOBALS['TL_LANG']['MSC']['mlTarget'],
		'exclude'   => true,
		'inputType' => 'checkbox',
		'eval'      => ['tl_class' => 'w50 m12'],
		'sql'       => "char(1) NOT NULL default ''"
	],
	'mlLoadContent'          => [
		'label'     => &$GLOBALS['TL_LANG']['tl_content']['mlLoadContent'],
		'exclude'   => true,
		'inputType' => 'checkbox',
		'eval'      => ['tl_class' => 'w50 m12'],
		'sql'       => "char(1) NOT NULL default ''"
	],
	'mlDisableImages'        => [
		'label'     => &$GLOBALS['TL_LANG']['tl_content']['mlDisableImages'],
		'exclude'   => true,
		'inputType' => 'checkbox',
		'eval'      => ['tl_class' => 'w50 m12'],
		'sql'       => "char(1) NOT NULL default ''"
	],
	'mlDisableDummyImages'   => [
		'label'     => &$GLOBALS['TL_LANG']['tl_content']['mlDisableDummyImages'],
		'exclude'   => true,
		'inputType' => 'checkbox',
		'eval'      => ['tl_class' => 'w50 m12'],
		'sql'       => "char(1) NOT NULL default ''"
	],
	'mlAddCustomDummyImages' => [
		'label'     => &$GLOBALS['TL_LANG']['tl_content']['mlAddCustomDummyImages'],
		'exclude'   => true,
		'inputType' => 'checkbox',
		'eval'      => ['tl_class' => 'w50 m12', 'submitOnChange' => true],
		'sql'       => "char(1) NOT NULL default ''"
	],
	'mlDummyImageMale'       => [
		'label'     => &$GLOBALS['TL_LANG']['tl_content']['mlDummyImageMale'],
		'exclude'   => true,
		'inputType' => 'fileTree',
		'eval'      => ['filesOnly' => true, 'fieldType' => 'radio', 'tl_class' => 'clr w50'],
		'sql'       => "binary(16) NULL"
	],
	'mlDummyImageFemale'     => [
		'label'     => &$GLOBALS['TL_LANG']['tl_content']['mlDummyImageFemale'],
		'exclude'   => true,
		'inputType' => 'fileTree',
		'eval'      => ['filesOnly' => true, 'fieldType' => 'radio', 'tl_class' => 'w50'],
		'sql'       => "binary(16) NULL"
	],
	'mlSkipFields'           => [
		'label'     => &$GLOBALS['TL_LANG']['tl_content']['mlSkipFields'],
		'exclude'   => true,
		'inputType' => 'checkbox',
		'eval'      => ['tl_class' => 'w50', 'submitOnChange' => true],
		'sql'       => "char(1) NOT NULL default ''"
	],
	'mlFields'               => [
		'label'            => &$GLOBALS['TL_LANG']['tl_content']['mlFields'],
		'exclude'          => true,
		'inputType'        => 'checkbox',
		'options_callback' => ['tl_content_member_plus', 'getViewableMemberFields'],
		'eval'             => ['multiple' => true, 'tl_class' => 'clr',],
		'sql'              => "blob NULL",
	],
];

$dc['fields'] = array_merge($dc['fields'], $arrFields);

/**
 * Dynamically add the parent table
 */
if (Input::get('do') == 'member') {
	$GLOBALS['TL_DCA']['tl_content']['config']['ptable']                = 'tl_member';
	$GLOBALS['TL_DCA']['tl_content']['list']['sorting']['headerFields'] = ['firstname', 'lastname', 'username', 'email'];
}


class tl_content_member_plus extends \Backend
{
	/**
	 * Import the back end user object
	 */
	public function __construct()
	{
		parent::__construct();
		$this->import('BackendUser', 'User');
	}
	
	/**
	 * Get all articles and return them as array
	 *
	 * @param \DataContainer
	 *
	 * @return array
	 */
	public function getArticleAlias(DataContainer $dc)
	{
		$arrPids  = [];
		$arrAlias = [];
		
		if (!$this->User->isAdmin) {
			foreach ($this->User->pagemounts as $id) {
				$arrPids[] = $id;
				$arrPids   = array_merge($arrPids, $this->Database->getChildRecords($id, 'tl_page'));
			}
			
			if (empty($arrPids)) {
				return $arrAlias;
			}
			
			$objAlias = $this->Database->prepare(
				"SELECT a.id, a.title, a.inColumn, p.title AS parent FROM tl_article a LEFT JOIN tl_page p ON p.id=a.pid WHERE a.pid IN(" . implode(
					',',
					array_map('intval', array_unique($arrPids))
				) . ") ORDER BY parent, a.sorting"
			)
				->execute($dc->id);
		} else {
			$objAlias = $this->Database->prepare(
				"SELECT a.id, a.title, a.inColumn, p.title AS parent FROM tl_article a LEFT JOIN tl_page p ON p.id=a.pid ORDER BY parent, a.sorting"
			)
				->execute($dc->id);
		}
		
		if ($objAlias->numRows) {
			System::loadLanguageFile('tl_article');
			
			while ($objAlias->next()) {
				$arrAlias[$objAlias->parent][$objAlias->id] =
					$objAlias->title . ' (' . ($GLOBALS['TL_LANG']['tl_article'][$objAlias->inColumn] ?: $objAlias->inColumn) . ', ID '
					. $objAlias->id . ')';
			}
		}
		
		return $arrAlias;
	}
	
	/**
	 * Add the source options depending on the allowed fields (see #5498)
	 *
	 * @param \DataContainer
	 *
	 * @return array
	 */
	public function getSourceOptions(DataContainer $dc)
	{
		if ($this->User->isAdmin) {
			return ['default', 'internal', 'article_reader', 'article', 'external'];
		}
		
		$arrOptions = ['default'];
		
		// Add the "internal" option
		if ($this->User->hasAccess('tl_content::mlJumpTo', 'alexf')) {
			$arrOptions[] = 'internal';
		}
		
		// Add the "article" option
		if ($this->User->hasAccess('tl_content::mlArticleId', 'alexf')) {
			$arrOptions[] = 'article';
			$arrOptions[] = 'article_reader';
		}
		
		// Add the "external" option
		if ($this->User->hasAccess('tl_content::mlUrl', 'alexf') && $this->User->hasAccess('tl_content::mlTarget', 'alexf')) {
			$arrOptions[] = 'external';
		}
		
		// Add the option currently set
		if ($dc->activeRecord && $dc->activeRecord->mlSource != '') {
			$arrOptions[] = $dc->activeRecord->mlSource;
			$arrOptions   = array_unique($arrOptions);
		}
		
		return $arrOptions;
	}
	
	/**
	 * get array of members by group
	 *
	 * @param DataContainer $dc
	 *
	 * @return array
	 */
	public function getMembers(DataContainer $dc)
	{
		$arrOptions = [];
		
		$arrGroups = deserialize($dc->activeRecord->mlGroups);
		
		if (!is_array($arrGroups) || empty($arrGroups)) {
			return $arrOptions;
		}
		
		$objMembers = \HeimrichHannot\MemberPlus\MemberPlusMemberModel::findActiveByGroups($arrGroups);
		
		if ($objMembers === null) {
			return $arrOptions;
		}
		
		while ($objMembers->next()) {
			$arrTitle = [$objMembers->academicTitle, $objMembers->firstname, $objMembers->lastname];
			
			if (empty($arrTitle)) {
				continue;
			}
			
			$arrOptions[$objMembers->id] = implode(' ', $arrTitle);
		}
		
		return $arrOptions;
	}
	
	/**
	 * Return all news templates as array
	 *
	 * @return array
	 */
	public function getMemberlistTemplates(DataContainer $dc)
	{
		return $this->getTemplateGroup('memberlist_');
	}
	
	
	/**
	 * Return all feViewable fields as array
	 *
	 * @param DataContainer $dc
	 *
	 * @return array
	 */
	public function getViewableMemberFields(DataContainer $dc)
	{
		\Controller::loadDataContainer('tl_member');
		\Controller::loadLanguageFile('tl_member');
		
		$arrOptions = [];
		
		$arrFields = $GLOBALS['TL_DCA']['tl_member']['fields'];
		
		if (!is_array($arrFields) || empty($arrFields)) {
			return $arrOptions;
		}
		
		foreach ($arrFields as $strName => $arrData) {
			if (!isset($arrData['inputType'])) {
				continue;
			}
			
			if (!$arrData['eval']['feViewable']) {
				continue;
			}
			
			$arrOptions[$strName] = $arrData['label'][0];
		}
		
		return $arrOptions;
	}
}