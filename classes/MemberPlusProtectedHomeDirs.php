<?php

namespace HeimrichHannot\MemberPlus;

use HeimrichHannot\Haste\Util\Files;

class MemberPlusProtectedHomeDirs
{
    public static function getProtectedMemberUploadFolder(MemberPlusMemberModel $objMember=null)
    {
        if ($objMember === null)
        {
            $objMember = \FrontendUser::getInstance();
        }

        if ($objMember)
        {
            // check if a protected folder exists -> else create one
            $objFolder = new \Folder('files/members_protected');
            static::addHomeDir($objMember, 'protectedHomeDir', 'assignProtectedDir', $objFolder);

            $strDir = Files::getPathFromUuid($objMember->protectedHomeDir);

            return $strDir;
        }

        return '';
    }

    public static function addHomeDir($objMember, $strProperty, $strBooleanProperty, $objFolder)
    {
        if (!$objMember->{$strBooleanProperty})
        {
            $objMember->{$strBooleanProperty} = true;
            $objMember->save();
        }

        if (!\Validator::isUuid($objMember->{$strProperty}))
        {
            $strPath = $objFolder->path . '/' . $objMember->id;

            $objHomeDir = new \Folder($strPath);

            $objMember->{$strBooleanProperty} = true;
            $objMember->{$strProperty}        = $objHomeDir->getModel()->uuid;

            $objMember->save();
        }
        else
        {
            $strPath = Files::getPathFromUuid($objMember->{$strProperty});
            $strId   = str_replace($objFolder->path . '/', '', $strPath);

            // create if not existing
            new \Folder($strPath);

            if ($strId != $objMember->id)
            {
                $strPath = $objFolder->path . '/' . $objMember->id;

                $objHomeDir = new \Folder($strPath);

                $objMember->{$strBooleanProperty} = true;
                $objMember->{$strProperty}        = $objHomeDir->getModel()->uuid;

                $objMember->save();
            }
        }
    }
}