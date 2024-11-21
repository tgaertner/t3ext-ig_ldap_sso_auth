<?php
declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace Causal\IgLdapSsoAuth\Library;

/**
 * Class tx_igldapssoauth_typo3_group for the 'ig_ldap_sso_auth' extension.
 *
 * @author     Michael Gagnon <mgagnon@infoglobe.ca>
 * @package    TYPO3
 * @subpackage ig_ldap_sso_auth
 */
class LdapGroup
{
    /**
     * Returns LDAP group records based on a list of DNs provided as $membership,
     * taking group's baseDN and filter into consideration.
     *
     * @param string $baseDn
     * @param string $filter
     * @param array $membership
     * @param array $attributes
     * @param bool $extendedCheck true if groups should be actively checked against LDAP server, false to check against baseDN solely
     * @param Ldap|null $ldapInstance
     * @return array
     */
    public static function selectFromMembership(
        string $baseDn,
        string $filter,
        array $membership = [],
        array $attributes = [],
        bool $extendedCheck = true,
        ?Ldap $ldapInstance = null
    )
    {
        $ldapGroups['count'] = 0;

        if (empty($membership) || empty($filter) || $ldapInstance === null) {
            return $ldapGroups;
        }

        unset($membership['count']);

        foreach ($membership as $groupDn) {
            if (!empty($baseDn) && strcasecmp(substr($groupDn, -strlen($baseDn)), $baseDn) !== 0) {
                // Group $groupDn does not match the required baseDn for LDAP groups
                continue;
            }
            if ($extendedCheck) {
                $ldapGroup = $ldapInstance->search($groupDn, $filter, $attributes);
            } else {
                $parts = explode(',', $groupDn);
                list($firstAttribute, $value) = explode('=', $parts[0]);
                $firstAttribute = strtolower($firstAttribute);
                $ldapGroup = [
                    0 => [
                        0 => $firstAttribute,
                        $firstAttribute => [
                            0 => $value,
                            'count' => 1,
                        ],
                        'dn' => $groupDn,
                        'count' => 1,
                    ],
                    'count' => 1,
                ];
            }
            if (!isset($ldapGroup['count']) || $ldapGroup['count'] == 0) {
                continue;
            }
            $ldapGroups['count']++;
            $ldapGroups[] = $ldapGroup[0];
        }

        return $ldapGroups;
    }

    /**
     * Returns groups associated to a given user (identified either by his DN or his uid attribute).
     *
     * @param string $baseDn
     * @param string $filter
     * @param string $userDn
     * @param string $userUid
     * @param array $attributes
     * @param Ldap|null $ldapInstance
     * @return array
     */
    public static function selectFromUser(
        string $baseDn,
        string $filter = '',
        string $userDn = '',
        string $userUid = '',
        array $attributes = [],
        ?Ldap $ldapInstance = null
    )
    {
        if ($ldapInstance === null) {
            return [];
        }

        $filter = str_replace('{USERDN}', $ldapInstance->escapeDnForFilter($userDn), $filter);
        $filter = str_replace('{USERUID}', $ldapInstance->escapeDnForFilter($userUid), $filter);

        $groups = $ldapInstance->search($baseDn, $filter, $attributes);
        return $groups;
    }

    /**
     * Returns the membership information for a given user.
     *
     * @param array $ldapUser
     * @param array $mapping
     * @return array|bool
     */
    public static function getMembership(array $ldapUser = [], array $mapping = [])
    {
        if (
            isset($mapping['usergroup'])
            && preg_match("`<([^$]*)>`", $mapping['usergroup'], $attribute)
            && array_key_exists(strtolower($attribute[1]), $ldapUser)
        ) {
            return $ldapUser[strtolower($attribute[1])];
        }

        return false;
    }
}
