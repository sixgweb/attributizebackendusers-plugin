<?php

namespace Sixgweb\Attributize\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use Backend\Models\User;

class AddFieldValuesToBackendUsersTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('backend_users')) {
            return;
        }

        if (Schema::hasColumns('backend_users', ['field_values'])) {
            return;
        }

        Schema::table('backend_users', function ($table) {
            $table->json('field_values')->nullable();
        });
    }

    public function down()
    {
        if (!Schema::hasTable('backend_users')) {
            return;
        }

        Schema::table('backend_users', function ($table) {
            $user = new User;
            foreach ($user->getFieldableFieldsWithoutGlobalScopes() as $field) {
                $field->deleteVirtualColumn();
            }
            if (Schema::hasColumn($table->getTable(), 'field_values')) {
                $table->dropColumn(['field_values']);
            }
        });
    }
}
