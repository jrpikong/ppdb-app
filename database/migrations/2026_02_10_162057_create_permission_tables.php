<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $teams = config('permission.teams');
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');

        $pivotRole = $columnNames['role_pivot_key'] ?? 'role_id';
        $pivotPermission = $columnNames['permission_pivot_key'] ?? 'permission_id';
        $teamKey = $columnNames['team_foreign_key'] ?? 'school_id';

        throw_if(empty($tableNames), Exception::class, 'permission.php not loaded.');
        throw_if($teams && empty($teamKey), Exception::class, 'team_foreign_key missing.');

        /*
        |----------------------------------
        | PERMISSIONS
        |----------------------------------
        */
        Schema::create($tableNames['permissions'], function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();

            $table->unique(['name', 'guard_name']);
        });

        /*
        |----------------------------------
        | ROLES (scoped by school)
        |----------------------------------
        */
        Schema::create($tableNames['roles'], function (Blueprint $table) use ($teams, $teamKey) {
            $table->bigIncrements('id');

            if ($teams) {
                $table->unsignedBigInteger($teamKey)->default(0)->index();
            }

            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();

            if ($teams) {
                $table->unique([$teamKey, 'name', 'guard_name']);
            } else {
                $table->unique(['name', 'guard_name']);
            }
        });

        /*
        |----------------------------------
        | MODEL HAS PERMISSIONS
        |----------------------------------
        */
        Schema::create($tableNames['model_has_permissions'], function (Blueprint $table) use (
            $tableNames, $pivotPermission, $columnNames, $teams, $teamKey
        ) {
            $table->unsignedBigInteger($pivotPermission);

            $table->string('model_type');
            $table->unsignedBigInteger($columnNames['model_morph_key']);
            $table->index([$columnNames['model_morph_key'], 'model_type']);

            $table->foreign($pivotPermission)
                ->references('id')
                ->on($tableNames['permissions'])
                ->cascadeOnDelete();

            if ($teams) {
                $table->unsignedBigInteger($teamKey)->default(0)->index();


                $table->primary([
                    $teamKey,
                    $pivotPermission,
                    $columnNames['model_morph_key'],
                    'model_type'
                ]);
            } else {
                $table->primary([
                    $pivotPermission,
                    $columnNames['model_morph_key'],
                    'model_type'
                ]);
            }
        });

        /*
        |----------------------------------
        | MODEL HAS ROLES
        |----------------------------------
        */
        Schema::create($tableNames['model_has_roles'], function (Blueprint $table) use (
            $tableNames, $pivotRole, $columnNames, $teams, $teamKey
        ) {
            $table->unsignedBigInteger($pivotRole);

            $table->string('model_type');
            $table->unsignedBigInteger($columnNames['model_morph_key']);
            $table->index([$columnNames['model_morph_key'], 'model_type']);

            $table->foreign($pivotRole)
                ->references('id')
                ->on($tableNames['roles'])
                ->cascadeOnDelete();

            if ($teams) {
                $table->unsignedBigInteger($teamKey)->default(0)->index();

                $table->primary([
                    $teamKey,
                    $pivotRole,
                    $columnNames['model_morph_key'],
                    'model_type'
                ]);
            } else {
                $table->primary([
                    $pivotRole,
                    $columnNames['model_morph_key'],
                    'model_type'
                ]);
            }
        });

        /*
        |----------------------------------
        | ROLE HAS PERMISSIONS
        |----------------------------------
        */
        Schema::create($tableNames['role_has_permissions'], function (Blueprint $table) use (
            $tableNames, $pivotRole, $pivotPermission
        ) {
            $table->unsignedBigInteger($pivotPermission);
            $table->unsignedBigInteger($pivotRole);

            $table->foreign($pivotPermission)
                ->references('id')
                ->on($tableNames['permissions'])
                ->cascadeOnDelete();

            $table->foreign($pivotRole)
                ->references('id')
                ->on($tableNames['roles'])
                ->cascadeOnDelete();

            $table->primary([$pivotPermission, $pivotRole]);
        });

        app('cache')
            ->store(config('permission.cache.store') !== 'default'
                ? config('permission.cache.store')
                : null
            )
            ->forget(config('permission.cache.key'));
    }

    public function down(): void
    {
        $tableNames = config('permission.table_names');

        Schema::dropIfExists($tableNames['role_has_permissions']);
        Schema::dropIfExists($tableNames['model_has_roles']);
        Schema::dropIfExists($tableNames['model_has_permissions']);
        Schema::dropIfExists($tableNames['roles']);
        Schema::dropIfExists($tableNames['permissions']);
    }
};
