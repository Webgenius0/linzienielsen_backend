<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class MakeRepository extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:repository {name} {--interface}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new repository class';

    protected $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        parent::__construct();
        $this->filesystem = $filesystem;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $namespace = str_replace('/', '\\', dirname($name));  // Get the namespace without the class name
        $className = basename($name);  
        $RepositoryClass = $this->generateRepositoryClass($name);

        // Define the full path to the Repositorys directory
        $directory = app_path("Repositories/{$namespace}");

        // Ensure the directory exists (create it if it doesn't)
        if (!$this->filesystem->exists($directory)) {
            $this->filesystem->makeDirectory($directory, 0755, true);  // Create any missing directories
        }

        // Define the path for the new Repository class file
        $path = $directory . DIRECTORY_SEPARATOR . "{$className}.php";

        // Check if the Repository class already exists
        if ($this->filesystem->exists($path)) {
            $this->error("Repository class {$name} already exists!");
            return;
        }

        // Create the Repository class file
        $this->filesystem->put($path, $RepositoryClass);

        $this->info("Repository class {$name} created successfully!");

        // If the --interface option is passed, create an interface
        if ($this->option('interface')) {
            $InterfaceClass = $this->generateInterfaceClass($name);
            $interfacePath = $directory . DIRECTORY_SEPARATOR . "{$className}Interface.php";

            // Check if the Interface class already exists
            if ($this->filesystem->exists($interfacePath)) {
                $this->error("Interface class {$className}Interface already exists!");
                return;
            }

            // Create the Interface class file
            $this->filesystem->put($interfacePath, $InterfaceClass);

            $this->info("Interface class {$className}Interface created successfully!");
        }
    }

    /**
     * Generate the PHP code for a Repository class based on the given name.
     */
    private function generateRepositoryClass($name)
    {
        $parts = explode('/', $name);
        $namespaceParts = array_slice($parts, 0, -1); // Get all parts except the last for the namespace
        $className = end($parts); // Get the last part as the class name
        $namespace = !empty($namespaceParts) ? '\\' . implode('\\', $namespaceParts) : '';
    
        // Check if the --interface option was passed
        $implements = $this->option('interface') ? " implements {$className}Interface" : '';
    
        return "<?php
        
namespace App\\Repositories{$namespace};

class {$className}{$implements}
{
    // Your Repository logic goes here
}
";
    }
    
    /**
     * Generate the PHP code for an Interface class based on the given name.
     */
    private function generateInterfaceClass($name)
    {
        $parts = explode('/', $name);
        $namespaceParts = array_slice($parts, 0, -1); // Get all parts except the last for the namespace
        $className = end($parts); // Get the last part as the class name
        $namespace = !empty($namespaceParts) ? '\\' . implode('\\', $namespaceParts) : '';

        return "<?php
    
namespace App\\Repositories{$namespace};

interface {$className}Interface
{
    // Define the methods your repository should implement
}
";
    }
}
