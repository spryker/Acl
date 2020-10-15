<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Acl\Business;

use Generated\Shared\Transfer\AclEntityAccessRulesSetTransfer;
use Generated\Shared\Transfer\AclEntityAccessRuleTransfer;
use Generated\Shared\Transfer\GroupCriteriaTransfer;
use Generated\Shared\Transfer\GroupTransfer;
use Generated\Shared\Transfer\NavigationItemCollectionTransfer;
use Generated\Shared\Transfer\PropelQueryBuilderRuleSetTransfer;
use Generated\Shared\Transfer\RolesTransfer;
use Generated\Shared\Transfer\RoleTransfer;
use Generated\Shared\Transfer\RuleTransfer;
use Generated\Shared\Transfer\SpyAclEnetityRuleEntityTransfer;
use Generated\Shared\Transfer\UserTransfer;
use Orm\Zed\Acl\Persistence\SpyAclEnetityRuleQuery;
use Orm\Zed\Acl\Persistence\SpyAclEntityRule;
use Orm\Zed\Acl\Persistence\SpyAclEntityRuleQuery;
use Orm\Zed\Acl\Persistence\SpyAclGroupQuery;
use Orm\Zed\Acl\Persistence\SpyAclUserHasGroup;
use Orm\Zed\Acl\Persistence\SpyAclUserHasGroupQuery;
use Orm\Zed\Merchant\Persistence\SpyMerchantQuery;
use Propel\Runtime\ActiveQuery\Join;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveQuery\QueryJoin;
use Propel\Runtime\Map\TableMap;
use Spryker\Shared\Acl\AclConstants;
use Spryker\Zed\Kernel\BundleConfigResolverAwareTrait;
use Spryker\Zed\Kernel\Business\AbstractFacade;
use function _HumbugBox1912330f9177\Amp\Iterator\toArray;

/**
 * @method \Spryker\Zed\Acl\Business\AclBusinessFactory getFactory()
 * @method \Spryker\Zed\Acl\Persistence\AclRepositoryInterface getRepository()
 */
class AclFacade extends AbstractFacade implements AclFacadeInterface
{
    use BundleConfigResolverAwareTrait;

    protected $aliasIndex = 0;
    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @return void
     */
    public function install()
    {
        $this->getFactory()->createInstallerModel()->install();
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param string $groupName
     * @param \Generated\Shared\Transfer\RolesTransfer $rolesTransfer
     *
     * @return \Generated\Shared\Transfer\GroupTransfer
     */
    public function addGroup($groupName, RolesTransfer $rolesTransfer)
    {
        $groupTransfer = $this->getFactory()
            ->createGroupModel()
            ->addGroup($groupName);

        if (!empty($rolesTransfer)) {
            $this->addRolesToGroup($groupTransfer, $rolesTransfer);
        }

        return $groupTransfer;
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\GroupTransfer $transfer
     * @param \Generated\Shared\Transfer\RolesTransfer $rolesTransfer
     *
     * @return \Generated\Shared\Transfer\GroupTransfer
     */
    public function updateGroup(GroupTransfer $transfer, RolesTransfer $rolesTransfer)
    {
        $groupTransfer = $this->getFactory()
            ->createGroupModel()
            ->updateGroup($transfer);

        if (!empty($rolesTransfer)) {
            $this->addRolesToGroup($groupTransfer, $rolesTransfer);
        }

        return $groupTransfer;
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param int $id
     *
     * @return \Generated\Shared\Transfer\GroupTransfer
     */
    public function getGroup($id)
    {
        return $this->getFactory()
            ->createGroupModel()
            ->getGroupById($id);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param string $name
     *
     * @return \Generated\Shared\Transfer\GroupTransfer
     */
    public function getGroupByName($name)
    {
        return $this->getFactory()
            ->createGroupModel()
            ->getByName($name);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\GroupCriteriaTransfer $groupCriteriaTransfer
     *
     * @return \Generated\Shared\Transfer\GroupTransfer|null
     */
    public function findGroup(GroupCriteriaTransfer $groupCriteriaTransfer): ?GroupTransfer
    {
        return $this->getRepository()->findGroup($groupCriteriaTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @return \Generated\Shared\Transfer\GroupsTransfer
     */
    public function getAllGroups()
    {
        return $this->getFactory()
            ->createGroupModel()
            ->getAllGroups();
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @return bool
     */
    public function hasCurrentUser()
    {
        return $this->getFactory()->getUserFacade()->hasCurrentUser();
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @return \Generated\Shared\Transfer\UserTransfer
     */
    public function getCurrentUser()
    {
        return $this->getFactory()->getUserFacade()->getCurrentUser();
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param string $name
     *
     * @return bool
     */
    public function existsRoleByName($name)
    {
        return $this->getFactory()
            ->createRoleModel()
            ->hasRoleName($name);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param int $id
     *
     * @return \Generated\Shared\Transfer\RoleTransfer
     */
    public function getRoleById($id)
    {
        return $this->getFactory()
            ->createRoleModel()
            ->getRoleById($id);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param int $id
     *
     * @return \Generated\Shared\Transfer\RoleTransfer|null
     */
    public function findRoleById(int $id): ?RoleTransfer
    {
        return $this->getFactory()
            ->createRoleModel()
            ->findRoleById($id);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param string $name
     *
     * @return \Generated\Shared\Transfer\RoleTransfer
     */
    public function getRoleByName($name)
    {
        return $this->getFactory()
            ->createRoleModel()
            ->getByName($name);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param string $name
     *
     * @return \Generated\Shared\Transfer\RoleTransfer
     */
    public function addRole($name)
    {
        return $this->getFactory()->createRoleModel()->addRole($name);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\RoleTransfer $roleTransfer
     *
     * @return \Generated\Shared\Transfer\RoleTransfer
     */
    public function updateRole(RoleTransfer $roleTransfer)
    {
        return $this->getFactory()->createRoleModel()->save($roleTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param int $id
     *
     * @return \Generated\Shared\Transfer\RuleTransfer
     */
    public function getRule($id)
    {
        return $this->getFactory()
            ->createRuleModel()
            ->getRuleById($id);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param int $idUser
     * @param int $idGroup
     *
     * @return int
     */
    public function addUserToGroup($idUser, $idGroup)
    {
        return $this->getFactory()
            ->createGroupModel()
            ->addUser($idGroup, $idUser);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param int $idUser
     * @param int $idGroup
     *
     * @return bool
     */
    public function userHasGroupId($idUser, $idGroup)
    {
        return $this->getFactory()
            ->createGroupModel()
            ->hasUser($idGroup, $idUser);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasGroupByName($name)
    {
        return $this->getFactory()
            ->createGroupModel()
            ->hasGroupName($name);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param int $idUser
     *
     * @return \Generated\Shared\Transfer\GroupsTransfer
     */
    public function getUserGroups($idUser)
    {
        return $this->getFactory()->createGroupModel()->getUserGroups($idUser);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param int $idUser
     * @param int $idGroup
     *
     * @return void
     */
    public function removeUserFromGroup($idUser, $idGroup)
    {
        $this->getFactory()
            ->createGroupModel()
            ->removeUser($idGroup, $idUser);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\RuleTransfer $ruleTransfer
     *
     * @return \Generated\Shared\Transfer\RuleTransfer
     */
    public function addRule(RuleTransfer $ruleTransfer)
    {
        return $this->getFactory()
            ->createRuleModel()
            ->addRule($ruleTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param int $idGroup
     *
     * @return \Generated\Shared\Transfer\RolesTransfer
     */
    public function getGroupRoles($idGroup)
    {
        return $this->getFactory()
            ->createRoleModel()
            ->getGroupRoles($idGroup);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param int $idGroup
     *
     * @return \Generated\Shared\Transfer\RulesTransfer
     */
    public function getGroupRules($idGroup)
    {
        return $this->getFactory()
            ->createRuleModel()
            ->getRulesForGroupId($idGroup);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param int $idRole
     *
     * @return \Generated\Shared\Transfer\RulesTransfer
     */
    public function getRoleRules($idRole)
    {
        return $this->getFactory()
            ->createRuleModel()
            ->getRoleRules($idRole);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param int $idAclRole
     * @param string $bundle
     * @param string $controller
     * @param string $action
     * @param string $type
     *
     * @return bool
     */
    public function existsRoleRule($idAclRole, $bundle, $controller, $action, $type)
    {
        return $this->getFactory()
            ->createRuleModel()
            ->existsRoleRule($idAclRole, $bundle, $controller, $action, $type);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param int $idUser
     *
     * @return \Generated\Shared\Transfer\RolesTransfer
     */
    public function getUserRoles($idUser)
    {
        return $this->getFactory()
            ->createRoleModel()
            ->getUserRoles($idUser);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param int $idGroup
     *
     * @return bool
     */
    public function removeGroup($idGroup)
    {
        return $this->getFactory()
            ->createGroupModel()
            ->removeGroupById($idGroup);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param int $idRole
     *
     * @return bool
     */
    public function removeRole($idRole)
    {
        return $this->getFactory()
            ->createRoleModel()
            ->removeRoleById($idRole);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param int $idRule
     *
     * @return bool
     */
    public function removeRule($idRule)
    {
        return $this->getFactory()
            ->createRuleModel()
            ->removeRuleById($idRule);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param int $idRole
     * @param int $idGroup
     *
     * @return int
     */
    public function addRoleToGroup($idRole, $idGroup)
    {
        return $this->getFactory()
            ->createGroupModel()
            ->addRoleToGroup($idRole, $idGroup);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\GroupTransfer $groupTransfer
     * @param \Generated\Shared\Transfer\RolesTransfer $rolesTransfer
     *
     * @return void
     */
    public function addRolesToGroup(GroupTransfer $groupTransfer, RolesTransfer $rolesTransfer)
    {
        $groupModel = $this->getFactory()->createGroupModel();
        $groupModel->removeRolesFromGroup($groupTransfer->getIdAclGroup());

        foreach ($rolesTransfer->getRoles() as $roleTransfer) {
            if ($roleTransfer->getIdAclRole() > 0) {
                $groupModel->addRoleToGroup($roleTransfer->getIdAclRole(), $groupTransfer->getIdAclGroup());
            }
        }
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\UserTransfer $user
     * @param string $bundle
     * @param string $controller
     * @param string $action
     *
     * @return bool
     */
    public function checkAccess(UserTransfer $user, $bundle, $controller, $action)
    {
        return $this->getFactory()
            ->createRuleModel()
            ->isAllowed($user, $bundle, $controller, $action);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\NavigationItemCollectionTransfer $navigationItemCollectionTransfer
     *
     * @return \Generated\Shared\Transfer\NavigationItemCollectionTransfer
     */
    public function filterNavigationItemCollectionByAccessibility(
        NavigationItemCollectionTransfer $navigationItemCollectionTransfer
    ): NavigationItemCollectionTransfer {
        return $this->getFactory()
            ->createNavigationItemFilter()
            ->filterNavigationItemCollectionByAccessibility($navigationItemCollectionTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param string $bundle
     * @param string $controller
     * @param string $action
     *
     * @return bool
     */
    public function isIgnorable($bundle, $controller, $action)
    {
        return $this->getFactory()
            ->createRuleModel()
            ->isIgnorable($bundle, $controller, $action);
    }


    public function isProtectedEntity(string $entityName)
    {
        foreach ($this->getConfig()->getProtectedEntities() as $protectedEntity) {
            if ($protectedEntity['name'] === $entityName) {
                return true;
            }
        }

        return false;
    }

    public function hasEntityAccess($idUser, string $entityName, int $permission = null)
    {
        $roles = $this->getCurrentUserRolesIds($idUser);
        xdebug_break();
        $query = SpyAclEntityRuleQuery::create()
            ->filterByFkAclRole_In($roles)
            ->filterByEntity($entityName);

        foreach ($query->find() as $item) {
            if ($permission & $item->getPermissionMask()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $idUser
     * @param string $entityName
     * @return SpyAclEntityRule[]
     * @throws \Propel\Runtime\Exception\PropelException
     * @throws \Spryker\Zed\Propel\Business\Exception\AmbiguousComparisonException
     */
    public function findEntityAccessRule($idUser, string $entityName, int $permission = null):  array
    {
        $roles = $this->getCurrentUserRolesIds($idUser);

        $rules = SpyAclEntityRuleQuery::create()
            ->filterByFkAclRole_In($roles)
            ->filterByEntity($entityName)
            ->find()
            ->getData();

        if ($permission !== null) {
            $rules = array_filter($rules, function (SpyAclEntityRule $aclEntityRule) use ($permission) {
                return $permission & $aclEntityRule->getPermissionMask();
            });
        }

        return $rules;
    }

    /**
     * @param $idUser
     * @param string $entityName
     * @return \Generated\Shared\Transfer\AclEntityAccessRulesSetTransfer
     * @throws \Propel\Runtime\Exception\PropelException
     * @throws \Spryker\Zed\Propel\Business\Exception\AmbiguousComparisonException
     */
    public function findEntityAccessRuleForRole(int $idRole, string $entityName):  ?SpyAclEntityRule
    {
//        $roles = $this->getCurrentUserRolesIds($idUser);

        return SpyAclEntityRuleQuery::create()
            ->filterByFkAclRole($idRole)
            ->filterByEntity($entityName)
            ->findOne();

    }

    /**
     * @param $idUser
     * @param string $entityName
     * @return string[]
     * @throws \Propel\Runtime\Exception\PropelException
     * @throws \Spryker\Zed\Propel\Business\Exception\AmbiguousComparisonException
     */
    public function findEntityParents(string $entityName): array
    {
        foreach ($this->getConfig()->getProtectedEntities() as $protectedEntity) {
            if ($protectedEntity['name'] === $entityName) {
                return $protectedEntity['parents'] ?? [];
            }
        }

        return [];
    }

    /**
     * @param $idUser
     * @return array
     */
    public function getCurrentUserRolesIds(): array
    {
        $videoKingGroup = 3;
        $sprykerGroup = 4;
        $catalogViewerGroup = 5;
        $catalogManagerGroup = 6;

        return [
//            $videoKingGroup = 3,
            $sprykerGroup = 4,
//            $catalogViewerGroup = 5,
//            $catalogManagerGroup = 6,
        ];
    }

    public function applyAclToQuery(ModelCriteria $query, TableMap $entityToFilterTableMap, int $roleId, int $permission)
    {
        $phpName = ($entityToFilterTableMap)::OM_CLASS;
        if ($this->isProtectedEntity($phpName)) {
            $this->addAclFilterOnEntity($query, $entityToFilterTableMap, $entityToFilterTableMap->getName(),  $roleId, $permission);
        }
        $this->addAclWhereForJoinedTables($query);

    }


    private function addAclFilterOnEntity(ModelCriteria $query, TableMap $entityToFilterTableMap, string $entityToFilterAlias,  int $roleId, int $permission)
    {
        $entityPhpName = $entityToFilterTableMap::OM_CLASS;
        $aclRules = $this->findEntityAccessRule(1, $entityPhpName, $permission);
        $workingRoleIds = [];
        foreach ($aclRules as $rule) {
            $workingRoleIds[] = $rule->getFkAclRole();
        }

        if (!$workingRoleIds) {
            $query->where('TRUE = FALSE');
            return;
        }

        foreach ($aclRules as $rule) {
            if ($rule->getScope() === \Orm\Zed\Acl\Persistence\Map\SpyAclEntityRuleTableMap::COL_SCOPE_FULL) {
                return;
            }
        }

        $segmentIds = [];
        foreach ($aclRules as $rule) {
            if ($rule->getScope() === \Orm\Zed\Acl\Persistence\Map\SpyAclEntityRuleTableMap::COL_SCOPE_SEGMENT) {
                $segmentIds[] = $rule->getFkAclEntitySegment();
            }
        }
        if ($segmentIds) {
            $this->addJoinToSegmentTable($query, $segmentIds, $entityToFilterTableMap, $entityToFilterAlias);
        }

        $inheritedRoleIds = [];
        foreach ($aclRules as $rule) {
            if ($rule->getScope() === \Orm\Zed\Acl\Persistence\Map\SpyAclEntityRuleTableMap::COL_SCOPE_INHERITED) {
                $inheritedRoleIds[] = $rule->getFkAclRole();
            }
        }
        if ($inheritedRoleIds) {
            $parents = $this->findEntityParents($entityPhpName);

            foreach ($parents as $parent) {
                $parentEntityName = $parent['name'];
                if (isset($parent['connection'])) {
                    $this->joinAclIndirectRelation($query, $parent, $entityToFilterTableMap, $entityToFilterAlias);
                    continue;
                }
            }
        }
    }
    /**
     * @param \Propel\Runtime\Map\TableMap $tableMap
     *
     * @return void
     */
    protected function addJoinToSegmentTable(ModelCriteria $query, array $segmentIds,  \Propel\Runtime\Map\TableMap $leftTable, string $leftTableAlias): void
    {
        $entitySegment = $leftTable->getRelation($this->getAclSegmentEntityName($leftTable->getPhpName()));

        $primaryKeys = $entitySegment->getLeftTable()->getPrimaryKeys();

        $aliasRight = $this->generateUniqueAlias();
        $join = new \Propel\Runtime\ActiveQuery\Join(
            sprintf('%s.%s', $leftTableAlias, array_shift($primaryKeys)->getName()),
            sprintf('%s.fk_%s', $entitySegment->getRightTable()->getName(), $entitySegment->getLeftTable()->getName()),
            ModelCriteria::INNER_JOIN
        );

        $join->setRightTableAlias($aliasRight);
        $join->setRightTableName($entitySegment->getRightTable()->getName());

        $query->addJoinObject($join);

        $query->where(sprintf('%s IN (%s)', $aliasRight . '.fk_spy_acl_entity_segment', implode(',', $segmentIds)));
    }


    /**
     * @param string $modelName
     *
     * @return string
     */
    protected function getAclSegmentEntityName(string $modelName): string
    {
        return sprintf('%sZedAcl', $modelName);
    }

    /**
     * @param string $aclTablePath
     * @param \Propel\Runtime\Map\TableMap $tableMap
     *
     * @return void
     */
    public function joinAclIndirectRelation(ModelCriteria $mainQuery, array $parentDefinition, \Propel\Runtime\Map\TableMap $tableMap, string $leftTableAlias): void
    {
        $mainQuery->addAlias($leftTableAlias, $mainQuery->getTableMap()->getName());
        $reference = $tableMap::translateFieldName($parentDefinition['connection']['reference'], $tableMap::TYPE_COLNAME, $tableMap::TYPE_FIELDNAME);
        $left = sprintf('%s.%s', $leftTableAlias, $reference);

        $rightTableMapNamespace =  ($parentDefinition['name'])::TABLE_MAP;
        /**
         * @var $rightTableMap \Propel\Runtime\Map\TableMap
         */
        $rightTableMap = new $rightTableMapNamespace();
        $rightTableMap->setDatabaseMap($mainQuery->getTableMap()->getDatabaseMap());
        $rightTableMap->buildRelations();
        $rightTableName = $rightTableMap->getName();

        $rightTableAlias = $this->generateUniqueAlias();
        $mainQuery->addAlias($rightTableAlias, $rightTableName);

        $referenced =  $rightTableMap::translateFieldName($parentDefinition['connection']['referenced_column'], $rightTableMap::TYPE_COLNAME, $rightTableMap::TYPE_FIELDNAME);
        $right = sprintf('%s.%s', $rightTableAlias, $referenced);

        $mainQuery->addJoin($left, $right, ModelCriteria::INNER_JOIN);
    }

    /**
     * @return string
     */
    protected function generateUniqueAlias(): string
    {
        $length = 64;
        $keyspace = 'abcdefghijklmnopqrstuvwxyz';


        if ($length < 1) {
            throw new \RangeException("Length must be a positive integer");
        }
        $pieces = [];
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $pieces []= $keyspace[random_int(0, $max)];
        }
        return implode('', $pieces);
    }

    /**
     * @return void
     */
    protected function addAclWhereForJoinedTables(ModelCriteria $query): void
    {
        foreach ($query->getJoins() as $joinName => $join) {
            if ($join instanceof \Propel\Runtime\ActiveQuery\QueryJoin) {
                continue;
            }
            if (isset($query->handledJoins[spl_object_hash($join)])) {
                continue;
            }

            $tableMap = $query
                ->getTableMap()
                ->getDatabaseMap()
                ->getTable($join->getRightTableName());

            $query->handledJoins[spl_object_hash($join)] = true;

            if (!$this->isProtectedEntity($tableMap::OM_CLASS)) {
                continue;
            }

            // permission should be dynamic
            $this->addAclFilterOnEntity($query, $tableMap, $joinName, 1, AclConstants::ENTNTY_PERMISSION_MASK_READ);
        }
    }

}
