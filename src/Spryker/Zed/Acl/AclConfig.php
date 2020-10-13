<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Acl;


use Orm\Zed\Customer\Persistence\Map\SpyCustomerAddressTableMap;
use Orm\Zed\Customer\Persistence\Map\SpyCustomerTableMap;
use Orm\Zed\Merchant\Persistence\Map\SpyMerchantTableMap;
use Orm\Zed\MerchantProduct\Persistence\Map\SpyMerchantProductAbstractTableMap;
use Orm\Zed\MerchantSalesOrder\Persistence\Map\SpyMerchantSalesOrderTableMap;
use Orm\Zed\MerchantSalesOrder\Persistence\SpyMerchantSalesOrder;
use Orm\Zed\Product\Persistence\Base\SpyProduct;
use Orm\Zed\ProductOffer\Persistence\Map\SpyProductOfferTableMap;
use Orm\Zed\ProductOffer\Persistence\SpyProductOffer;
use Orm\Zed\Sales\Persistence\Map\SpySalesShipmentTableMap;
use Orm\Zed\Sales\Persistence\SpySalesShipment;
use Spryker\Shared\Acl\AclConstants;
use Spryker\Shared\Config\Config;
use Spryker\Zed\Kernel\AbstractBundleConfig;

class AclConfig extends AbstractBundleConfig
{
    /**
     * @var array
     */
    protected $rules = [];

    /**
     * @api
     *
     * @return array
     */
    public function getRules()
    {
        $default = Config::get(AclConstants::ACL_DEFAULT_RULES);

        return array_merge($default, $this->rules);
    }

    /**
     * @api
     *
     * @param string $bundle
     * @param string $controller
     * @param string $action
     * @param string $type
     *
     * @return void
     */
    public function setRules($bundle, $controller, $action, $type)
    {
        $this->rules[] = [
            'bundle' => $bundle,
            'controller' => $controller,
            'action' => $action,
            'type' => $type,
        ];
    }

    /**
     * @api
     *
     * @return array
     */
    public function getCredentials()
    {
        return Config::get(AclConstants::ACL_DEFAULT_CREDENTIALS);
    }

    /**
     * @api
     *
     * @return string
     */
    public function getAccessDeniedUri()
    {
        return '/acl/index/denied';
    }

    /**
     * @api
     *
     * @return array
     */
    public function getInstallerRules()
    {
        return [
            [
                'bundle' => AclConstants::VALIDATOR_WILDCARD,
                'controller' => AclConstants::VALIDATOR_WILDCARD,
                'action' => AclConstants::VALIDATOR_WILDCARD,
                'type' => AclConstants::ALLOW,
                'role' => AclConstants::ROOT_ROLE,
                //this is related to the installer_data only and will not interact with existing data if any
            ],
        ];
    }

    /**
     * @api
     *
     * @return array
     */
    public function getInstallerRoles()
    {
        return [
            [
                'name' => AclConstants::ROOT_ROLE,
                'group' => AclConstants::ROOT_GROUP,
                //this is related to the installer_data only and will not interact with existing data if any
            ],
        ];
    }

    /**
     * @api
     *
     * @return array
     */
    public function getInstallerGroups()
    {
        return [
            [
                'name' => AclConstants::ROOT_GROUP,
            ],
        ];
    }

    /**
     * @api
     *
     * @return array
     */
    public function getInstallerUsers()
    {
        return [
            'admin@spryker.com' => [
                'group' => AclConstants::ROOT_GROUP,
            ],
            //this is related to existent username and will be searched into the database
        ];
    }

    /**
     * @api
     *
     * @return array
     */
    public function getUserRuleWhitelist()
    {
        if (Config::hasValue(AclConstants::ACL_USER_RULE_WHITELIST)) {
            return Config::get(AclConstants::ACL_USER_RULE_WHITELIST);
        }

        return [];
    }

    public function getProtectedEntities(): array
    {
        return [
            [
                'name' => SpyMerchantTableMap::OM_CLASS,
            ],
            [
                'name' => SpyMerchantProductAbstractTableMap::OM_CLASS,
                'parents' => [
                    [
                        'name' => SpyMerchantTableMap::OM_CLASS,
                        'connection' => [
                            'referenced_column' => SpyMerchantTableMap::COL_ID_MERCHANT,
                            'reference' => SpyMerchantProductAbstractTableMap::COL_FK_MERCHANT
                        ]
                    ]
                ]
            ],
            [
                'name' => SpySalesShipmentTableMap::OM_CLASS,
                'parents' => [
                    [
                        'name' => SpyMerchantTableMap::OM_CLASS,
                        'connection' => [
                            'referenced_column' => SpyMerchantTableMap::COL_MERCHANT_REFERENCE,
                            'reference' => SpySalesShipmentTableMap::COL_MERCHANT_REFERENCE
                        ]
                    ]
                ]
            ],
//            [
//                'name' => SpyProductOfferTableMap::OM_CLASS,
//                'parents' => [
//                    [
//                        'name' => SpyMerchantTableMap::OM_CLASS,
//                        'connection' => [
//                            'referenced_column' => SpyMerchantTableMap::COL_ID_MERCHANT,
//                            'reference' => SpyProductOfferTableMap::COL_FK_MERCHANT
//                        ]
//                    ]
//                ]
//            ]
        ];
    }
}
