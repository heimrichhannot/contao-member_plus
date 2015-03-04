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

class MemberPlusMemberModel extends \MemberModel
{

	/**
	 * Find active member item by ID or alias
	 *
	 * @param mixed $varId      The numeric ID or alias name
	 * @param array $arrPids    An array of parent IDs
	 * @param array $arrOptions An optional options array
	 *
	 * @return \Model|null The MemberModel or null if there are no news
	 */
	public static function findActiveByIdOrAlias($varId, array $arrOptions=array())
	{
		$t = static::$strTable;
		$arrColumns = array("($t.id=? OR $t.alias=?)");

		if (!BE_USER_LOGGED_IN)
		{
			$time = time();
			$arrColumns[] = "($t.start='' OR $t.start<$time) AND ($t.stop='' OR $t.stop>$time) AND $t.disable=''";
		}

		return static::findBy($arrColumns, array((is_numeric($varId) ? $varId : 0), $varId), $arrOptions);;
	}


	/**
	 * Find active member items by their group ID and ID or alias
	 *
	 * @param mixed $varId      The numeric ID or alias name
	 * @param array $arrPids    An array of parent IDs
	 * @param array $arrOptions An optional options array
	 *
	 * @return \Model|null The MemberModel or null if there are no news
	 */
	public static function findActiveByParentAndIdOrAlias($varId, $arrPids, array $arrOptions=array())
	{
		if (!is_array($arrPids) || empty($arrPids))
		{
			return null;
		}

		$t = static::$strTable;
		$arrColumns = array("($t.id=? OR $t.alias=?)");

		if (!BE_USER_LOGGED_IN)
		{
			$time = time();
			$arrColumns[] = "($t.start='' OR $t.start<$time) AND ($t.stop='' OR $t.stop>$time) AND $t.disable=''";
		}

		$objMember = static::findBy($arrColumns, array((is_numeric($varId) ? $varId : 0), $varId), $arrOptions);

		if($objMember === null) return null;

		if(!static::isInGroups($arrPids, $objMember->current())) return null;

		return $objMember;
	}

	/**
	 * Find an all active members by their id
	 *
	 * @param string $arrIds  The member ids as array
	 * @param array  $arrOptions  An optional options array
	 *
	 * @return \Model|null The model or null if there is no member
	 */
	public static function findActiveByIds($arrIds, array $arrOptions=array())
	{
		if (empty($arrIds) || !is_array($arrIds))
		{
			return null;
		}

		$time = time();
		$t = static::$strTable;
		$pk = static::$strPk;

		$arrColumns = array("($t.start='' OR $t.start<$time) AND ($t.stop='' OR $t.stop>$time) AND $t.disable='' AND $t.id IN(" . implode(',', array_map('intval', $arrIds)) . ")");

		if (!isset($arrOptions['order']))
		{
			$arrOptions['order']  = "FIELD ($t.$pk, " . implode(',', array_map('intval', $arrIds)) . ")";
		}

		return static::findBy($arrColumns, null, $arrOptions);
	}


	/**
	 * Find all active members by member groups
	 *
	 * @param string $arrGroups The member group ids
	 * @param array  $arrOptions  An optional options array
	 *
	 * @return \Model|null The model or null if there is no member
	 */
	public static function findActiveByGroups($arrGroups, array $arrOptions=array())
	{
		$time = time();
		$t = static::$strTable;

		if (!is_array($arrGroups) || empty($arrGroups))
		{
			return null;
		}

		$arrColumns = array("($t.start='' OR $t.start<$time) AND ($t.stop='' OR $t.stop>$time) AND $t.disable=''");

		$objMembers =  static::findBy($arrColumns, null, $arrOptions);

		if($objMembers === null) return null;

		$arrMembers = array();

		while($objMembers->next())
		{
			if(!static::isInGroups($arrGroups, $objMembers->current())) continue;

			$arrMembers[] = $objMembers->current();
		}

		return new \Model\Collection($arrMembers, $t);
	}


	public static function isInGroups($arrGroups, $objMember)
	{
		$arrGroups = deserialize($arrGroups, true);
		$arrMemberGroups = deserialize($objMember->groups, true);

		if(empty($arrMemberGroups)) return false;

		$arrCompareGroups = array_intersect($arrMemberGroups, $arrGroups);

		if(empty($arrCompareGroups)) return false;

		return true;
	}
}