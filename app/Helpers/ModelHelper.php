<?php

namespace App\Helpers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class ModelHelper
{
    public static function generateModel($modelClass, $migrationName)
    {
        $modelPath = app_path("Models/{$modelClass}.php");

        // Generate the model using Artisan if it doesn't exist
        if (!File::exists($modelPath)) {
            Artisan::call('make:model', ['name' => "{$modelClass}"]);
            echo "Model created: app/{$modelClass}.php\n";
        }

        // Define the content for the model
        $modelContent = <<<EOT
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class $modelClass extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected \$table = '$migrationName';
    // protected \$primaryKey = 'id';
    // public \$timestamps = false;
    protected \$guarded = ['id'];
    // protected \$fillable = [];
    // protected \$hidden = [];

    protected function casts(): array
    {
        // return [
        //     'email_password' => 'hashed',
        // ];
    }

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
}
EOT;

        // Write the model content to the file (overwrite if necessary)
        File::put($modelPath, $modelContent);
        echo "Model structure updated: app/{$modelClass}.php\n";
    }
}
