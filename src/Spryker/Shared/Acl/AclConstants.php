<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Shared\Acl;

/**
 * Declares global environment configuration keys. Do not use it for other class constants.
 */
interface AclConstants
{
    public const ACL_DEFAULT_RULES = 'ACL_DEFAULT_RULES';
    public const ACL_DEFAULT_CREDENTIALS = 'ACL_DEFAULT_CREDENTIALS';
    public const ACL_USER_RULE_WHITELIST = 'ACL_USER_RULE_WHITELIST';

    public const VALIDATOR_WILDCARD = '*';

    public const ACL_SESSION_KEY = 'acl';
    public const ACL_CREDENTIALS_KEY = 'credentials';
    public const ACL_DEFAULT_KEY = 'default';
    public const ACL_DEFAULT_RULES_KEY = 'rules';
    public const ROOT_GROUP = 'root_group';
    public const ROOT_ROLE = 'root_role';
    public const ALLOW = 'allow';


    const ENTNTY_PERMISSION_MASK_READ = 0b1;
    const ENTNTY_PERMISSION_MASK_CREATE = 0b10;
    const ENTNTY_PERMISSION_MASK_UPDATE = 0b100;
    const ENTNTY_PERMISSION_MASK_DELETE = 0b01000;
}
