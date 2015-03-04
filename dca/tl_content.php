<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2015 Heimrich & Hannot GmbH
 * @package member_plus
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

$dc = &$GLOBALS['TL_DCA']['tl_content'];

/**
 * Palettes
 */

// selector
array_insert($dc['palettes']['__selector__'], 0, array('mlSource')); // bug? mustn't be inserted after type selector

// memberlist
$dc['palettes']['memberlist'] = '{type_legend},type,headline;{ml_config_legend},mlGroups,mlSort,mlSource,mlTemplate,mlLoadContent,size;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space;{invisible_legend:hide},invisible,start,stop';

/**
 * Subpalettes
 */

$dc['subpalettes']['mlSource_internal']       = 'mlJumpTo';
$dc['subpalettes']['mlSource_article']        = 'mlArticleId';
$dc['subpalettes']['mlSource_article_reader'] = 'mlArticleId';
$dc['subpalettes']['mlSource_external']       = 'mlUrl,mlTarget';

$arrFields = array
(
	'mlGroups'    => array
	(
		'label'      => &$GLOBALS['TL_LANG']['tl_content']['mlGroups'],
		'exclude'    => true,
		'inputType'  => 'checkboxWizard',
		'foreignKey' => 'tl_member_group.name',
		'eval'       => array('mandatory' => true, 'multiple' => true, 'submitOnChange' => true),
		'sql'        => "blob NULL",
		'relation'   => array('type' => 'hasMany', 'load' => 'lazy')
	),
	'mlSort'      => array
	(
		'label'            => &$GLOBALS['TL_LANG']['tl_content']['mlSort'],
		'exclude'          => true,
		'inputType'        => 'checkboxWizard',
		'options_callback' => array('tl_content_member_plus', 'getMembers'),
		'eval'             => array('mandatory' => true, 'multiple' => true),
		'sql'              => "blob NULL",
	),
	'mlSource'    => array
	(
		'label'            => &$GLOBALS['TL_LANG']['tl_content']['mlSource'],
		'default'          => 'default',
		'exclude'          => true,
		'filter'           => true,
		'inputType'        => 'radio',
		'options_callback' => array('tl_content_member_plus', 'getSourceOptions'),
		'reference'        => &$GLOBALS['TL_LANG']['tl_content'],
		'eval'             => array('submitOnChange' => true, 'helpwizard' => true),
		'sql'              => "varchar(32) NOT NULL default ''"
	),
	'mlTemplate'  => array
	(
		'label'            => &$GLOBALS['TL_LANG']['tl_content']['mlTemplate'],
		'default'          => 'memberlist_default',
		'exclude'          => true,
		'inputType'        => 'select',
		'options_callback' => array('tl_content_member_plus', 'getMemberlistTemplates'),
		'eval'             => array('tl_class' => 'w50'),
		'sql'              => "varchar(64) NOT NULL default ''"
	),
	'mlJumpTo'    => array
	(
		'label'      => &$GLOBALS['TL_LANG']['tl_content']['mlJumpTo'],
		'exclude'    => true,
		'inputType'  => 'pageTree',
		'foreignKey' => 'tl_page.title',
		'eval'       => array('fieldType' => 'radio'),
		'sql'        => "int(10) unsigned NOT NULL default '0'",
		'relation'   => array('type' => 'hasOne', 'load' => 'eager')
	),
	'mlArticleId' => array
	(
		'label'            => &$GLOBALS['TL_LANG']['tl_content']['mlArticleId'],
		'exclude'          => true,
		'inputType'        => 'select',
		'options_callback' => array('tl_content_member_plus', 'getArticleAlias'),
		'eval'             => array('chosen' => true, 'mandatory' => true),
		'sql'              => "int(10) unsigned NOT NULL default '0'"
	),
	'mlUrl'       => array
	(
		'label'     => &$GLOBALS['TL_LANG']['MSC']['mlUrl'],
		'exclude'   => true,
		'search'    => true,
		'inputType' => 'text',
		'eval'      => array('mandatory' => true, 'decodeEntities' => true, 'maxlength' => 255, 'tl_class' => 'w50'),
		'sql'       => "varchar(255) NOT NULL default ''"
	),
	'mlTarget'    => array
	(
		'label'     => &$GLOBALS['TL_LANG']['MSC']['mlTarget'],
		'exclude'   => true,
		'inputType' => 'checkbox',
		'eval'      => array('tl_class' => 'w50 m12'),
		'sql'       => "char(1) NOT NULL default ''"
	),
	'mlLoadContent' => array
	(
		'label'     => &$GLOBALS['TL_LANG']['tl_content']['mlLoadContent'],
		'exclude'   => true,
		'inputType' => 'checkbox',
		'eval'      => array('tl_class' => 'w50 m12'),
		'sql'       => "char(1) NOT NULL default ''"
	)
);

$dc['fields'] = array_merge($dc['fields'], $arrFields);

/**
 * Dynamically add the parent table
 */
if (Input::get('do') == 'member') {
	$GLOBALS['TL_DCA']['tl_content']['config']['ptable']                = 'tl_member';
	$GLOBALS['TL_DCA']['tl_content']['list']['sorting']['headerFields'] = array('firstname', 'lastname', 'username', 'email');
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
	 * @param \DataContainer
	 * @return array
	 */
	public function getArticleAlias(DataContainer $dc)
	{
		$arrPids  = array();
		$arrAlias = array();

		if (!$this->User->isAdmin) {
			foreach ($this->User->pagemounts as $id) {
				$arrPids[] = $id;
				$arrPids   = array_merge($arrPids, $this->Database->getChildRecords($id, 'tl_page'));
			}

			if (empty($arrPids)) {
				return $arrAlias;
			}

			$objAlias = $this->Database->prepare("SELECT a.id, a.title, a.inColumn, p.title AS parent FROM tl_article a LEFT JOIN tl_page p ON p.id=a.pid WHERE a.pid IN(" . implode(',', array_map('intval', array_unique($arrPids))) . ") ORDER BY parent, a.sorting")
				->execute($dc->id);
		} else {
			$objAlias = $this->Database->prepare("SELECT a.id, a.title, a.inColumn, p.title AS parent FROM tl_article a LEFT JOIN tl_page p ON p.id=a.pid ORDER BY parent, a.sorting")
				->execute($dc->id);
		}

		if ($objAlias->numRows) {
			System::loadLanguageFile('tl_article');

			while ($objAlias->next()) {
				$arrAlias[$objAlias->parent][$objAlias->id] = $objAlias->title . ' (' . ($GLOBALS['TL_LANG']['tl_article'][$objAlias->inColumn] ?: $objAlias->inColumn) . ', ID ' . $objAlias->id . ')';
			}
		}

		return $arrAlias;
	}

	/**
	 * Add the source options depending on the allowed fields (see #5498)
	 * @param \DataContainer
	 * @return array
	 */
	public function getSourceOptions(DataContainer $dc)
	{
		if ($this->User->isAdmin) {
			return array('default', 'internal', 'article_reader', 'article', 'external');
		}

		$arrOptions = array('default');

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
	 * @param DataContainer $dc
	 * @return array
	 */
	public function getMembers(\DataContainer $dc)
	{
		$arrOptions = array();

		$arrGroups = deserialize($dc->activeRecord->mlGroups);

		if (!is_array($arrGroups) || empty($arrGroups)) return $arrOptions;

		$objMembers = \HeimrichHannot\MemberPlus\MemberPlusMemberModel::findActiveByGroups($arrGroups);

		if ($objMembers === null) return $arrOptions;

		while ($objMembers->next()) {
			$arrTitle = array($objMembers->academicTitle, $objMembers->firstname, $objMembers->lastname);

			if (empty($arrTitle)) continue;

			$arrOptions[$objMembers->id] = implode(' ', $arrTitle);
		}

		return $arrOptions;
	}

	/**
	 * Return all news templates as array
	 * @return array
	 */
	public function getMemberlistTemplates()
	{
		return $this->getTemplateGroup('memberlist_');
	}
}