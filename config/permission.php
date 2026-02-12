<?php

return [

    'models' => [
        'permission' => Spatie\Permission\Models\Permission::class,
        'role' => \App\Models\Role::class,
    ],

    'table_names' => [
        'roles'                 => 'roles',
        'permissions'          => 'permissions',
        'model_has_permissions'=> 'model_has_permissions',
        'model_has_roles'      => 'model_has_roles',
        'role_has_permissions' => 'role_has_permissions',
    ],

    'column_names' => [

        // jangan diubah kecuali benar-benar perlu
        'role_pivot_key' => 'role_id',
        'permission_pivot_key' => 'permission_id',

        'model_morph_key' => 'model_id',

        // inilah pengganti team_id
        'team_foreign_key' => 'school_id',
    ],

    /*
     |-----------------------------------------
     | REGISTER PERMISSION
     |-----------------------------------------
     */
    'register_permission_check_method' => true,
    'register_octane_reset_listener'   => false,

    'events_enabled' => false,

    /*
     |-----------------------------------------
     | 🚀 TEAMS MODE (MULTI SCHOOL)
     |-----------------------------------------
     */

    'teams' => true,

    'team_resolver' => \Spatie\Permission\DefaultTeamResolver::class,

    /*
     |-----------------------------------------
     | SECURITY
     |-----------------------------------------
     */

    'use_passport_client_credentials' => false,

    'display_permission_in_exception' => false,
    'display_role_in_exception'       => false,

    'enable_wildcard_permission' => false,

    /*
     |-----------------------------------------
     | CACHE
     |-----------------------------------------
     */

    'cache' => [
        'expiration_time' => \DateInterval::createFromDateString('24 hours'),
        'key'   => 'spatie.permission.cache',
        'store' => 'default',
    ],
];
