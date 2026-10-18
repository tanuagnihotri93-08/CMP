<?php

/*
 * Legacy migration compatibility only. Application authorization uses the
 * custom roles, permissions, and permission_role tables.
 * Keep these table names available while the historical Spatie-table
 * migration remains in the migration history; do not use this as RBAC config.
 */

return [
    'teams' => false,

    'table_names' => [
        'roles' => 'spatie_roles',
        'permissions' => 'spatie_permissions',
        'model_has_permissions' => 'spatie_model_has_permissions',
        'model_has_roles' => 'spatie_model_has_roles',
        'role_has_permissions' => 'spatie_role_has_permissions',
    ],

    'column_names' => [
        'role_pivot_key' => null,
        'permission_pivot_key' => null,
        'model_morph_key' => 'model_id',
        'team_foreign_key' => 'team_id',
    ],

    'cache' => [
        'store' => 'default',
        'key' => 'spatie.permission.cache',
    ],
];
