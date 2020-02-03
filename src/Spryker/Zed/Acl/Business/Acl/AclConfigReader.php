<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Acl\Business\Acl;

use Generated\Shared\Transfer\GroupTransfer;
use Generated\Shared\Transfer\RoleTransfer;
use Generated\Shared\Transfer\RuleTransfer;
use Generated\Shared\Transfer\UserTransfer;
use Spryker\Zed\Acl\AclConfig;

class AclConfigReader implements AclConfigReaderInterface
{
    protected const GROUP_INDEX = 'group';
    protected const ROLE_INDEX = 'role';

    /**
     * @var \Spryker\Zed\Acl\AclConfig
     */
    protected $aclConfig;

    /**
     * @param \Spryker\Zed\Acl\AclConfig $aclConfig
     */
    public function __construct(AclConfig $aclConfig)
    {
        $this->aclConfig = $aclConfig;
    }

    /**
     * @return \Generated\Shared\Transfer\RoleTransfer[]
     */
    public function getRoles(): array
    {
        $roleTransfers = [];
        foreach ($this->aclConfig->getInstallerRoles() as $roleData) {
            $groupTransfer = (new GroupTransfer())->setName($roleData[static::GROUP_INDEX]);
            $roleTransfers[$roleData[RoleTransfer::NAME]] = (new RoleTransfer())
                ->setName($roleData[RoleTransfer::NAME])
                ->setAclGroup($groupTransfer);
        }
        foreach ($this->aclConfig->getInstallerRules() as $ruleData) {
            if (!isset($roleTransfers[$ruleData[static::ROLE_INDEX]])) {
                continue;
            }
            $roleTransfer = $roleTransfers[$ruleData[static::ROLE_INDEX]];

            $ruleTransfer = (new RuleTransfer())
                ->setType($ruleData[RuleTransfer::TYPE])
                ->setAction($ruleData[RuleTransfer::ACTION])
                ->setBundle($ruleData[RuleTransfer::BUNDLE])
                ->setController($ruleData[RuleTransfer::CONTROLLER]);
            $roleTransfer->addAclRule($ruleTransfer);
        }

        return array_values($roleTransfers);
    }

    /**
     * @return \Generated\Shared\Transfer\GroupTransfer[]
     */
    public function getGroups(): array
    {
        $groupTransfers = [];
        foreach ($this->aclConfig->getInstallerGroups() as $groupData) {
            $groupTransfers[] = (new GroupTransfer())->setName($groupData[GroupTransfer::NAME]);
        }

        return $groupTransfers;
    }

    /**
     * @return \Generated\Shared\Transfer\UserTransfer[]
     */
    public function getUserGroupRelations(): array
    {
        $userTransfers = [];
        foreach ($this->aclConfig->getInstallerUsers() as $username => $userData) {
            $groupTransfer = (new GroupTransfer())->setName($userData[static::GROUP_INDEX]);
            $userTransfers[] = (new UserTransfer())
                ->setUsername($username)
                ->addAclGroup($groupTransfer);
        }

        return $userTransfers;
    }
}
