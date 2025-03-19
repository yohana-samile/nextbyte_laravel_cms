<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GenerateCrud extends Command
{
    protected $signature = 'nextbyte:crud {model}';
    protected $description = 'Generate model, migration, controller, route, and request for a new CRUD';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $model = $this->argument('model');
        $modelClass = Str::studly($model); // Convert to StudlyCase (e.g., Post)
        $modelSnake = Str::snake($model); // Convert to snake_case (e.g., post)
        $modelPlural = Str::plural($modelSnake); // Pluralize (e.g., posts)

        // Ask the user to choose between backend or frontend
        $type = $this->choice(
            'Please select the type of CRUD (backend/frontend) default backend',
            ['backend', 'frontend'],
            0 // Default to backend
        );

        $this->handleFrontendOrBackend($type, $modelClass);

       // Generate Model
       Artisan::call('make:model', ['name' => $modelClass]);
       $this->info("Model created: app/Models/{$modelClass}.php");

       // Generate Migration
       Artisan::call('make:migration', ['name' => "create_{$modelPlural}_table"]);
       $this->info("Migration created: create_{$modelPlural}_table");

        // Generate appropriate Controller
        $controllerPath = $type === 'backend' ? "/Backend/{$modelClass}Controller" : "/Frontend/{$modelClass}Controller";
        Artisan::call('make:controller', ['name' => $controllerPath]);
        $this->info("Controller created: app/{$controllerPath}.php");


       // Generate Request in correct directory
       $requestNamespace = "/" . ucfirst($type) . "/";
       $requestPath = str_replace('/', '\\', $requestNamespace . $modelClass . 'Request');

       Artisan::call('make:request', ['name' => $requestPath]);
       $this->info("Request created: app/$requestNamespace$modelClass" . "Request");

       // Generate Repository in correct directory
       $repositoryNamespace = "/" . ucfirst($type) . "/";
       $repositoryPath = str_replace('/', '\\', $repositoryNamespace . $modelClass . 'Repository');

       Artisan::call('make:repository', ['name' => $repositoryPath]);
       $this->info("Repository created: app/$repositoryNamespace$modelClass" . "Repository");

       // Add Route
      $this->addRoute($modelClass, $type);

        $this->info('CRUD generation completed!');
    }

    private function addRoute($modelClass, $type)
    {
        $routeFile = base_path('routes/web.php');
        $routePath = $type === 'backend' ? "Backend\\{$modelClass}Controller" : "Frontend\\{$modelClass}Controller";
        $route = "\nRoute::resource('" . Str::snake(Str::plural($modelClass)) . "', \\App\\Http\\Controllers\\{$routePath}::class);";

        if (!File::exists($routeFile)) {
            $this->error("Routes file not found.");
            return;
        }

        $content = File::get($routeFile);
        if (!Str::contains($content, $route)) {
            File::append($routeFile, $route);
            $this->info("Route added to routes/web.php");
        } else {
            $this->error("Operation Fail, Route already exists in routes/web.php");
        }
    }


    private function handleFrontendOrBackend($type, $modelClass)
    {
        if ($type == 'frontend') {
            $viewPath = resource_path("views/frontend/" . Str::snake($modelClass));
            if (!File::exists($viewPath)) {
                File::makeDirectory(dirname($viewPath), 0777, true, true);
                $this->info("Frontend views directory created: $viewPath");
            }
        } elseif ($type == 'backend') {
            $viewPath = resource_path("views/backend/" . Str::snake($modelClass));
            if (!File::exists($viewPath)) {
                File::makeDirectory(dirname($viewPath), 0777, true, true);
                $this->info("Backend views directory created: $viewPath");
            }
        }
        else {
            $this->error("Invalid type specified. Please use 'backend' or 'frontend'.");
        }
    }
}
