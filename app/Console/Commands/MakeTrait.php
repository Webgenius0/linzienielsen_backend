<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class MakeTrait extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:trait {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new trait';

    protected $filesystem;

    /**
     * Create a new command instance.
     *
     * @param Filesystem $filesystem
     */
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
        $className = basename($name);  // Get the class name (e.g., 'SocialLoginTrait')
        $TraitClass = $this->generateTrait($name);

        // Define the full path to the traits directory
        $directory = app_path("Traits/{$namespace}");

        // Ensure the directory exists (create it if it doesn't)
        if (!$this->filesystem->exists($directory)) {
            $this->filesystem->makeDirectory($directory, 0755, true);  // Create any missing directories
        }

        // Define the path for the new Trait class file
        $path = $directory . DIRECTORY_SEPARATOR . "{$className}.php";

        // Check if the Trait class already exists
        if ($this->filesystem->exists($path)) {
            $this->error("Trait class {$name} already exists!");
            return;
        }

        // Create the Trait class file
        $this->filesystem->put($path, $TraitClass);

        $this->info("Trait class {$name} created successfully!");
    }




    /**
     * Generate the PHP code for a Trait class based on the given name.
     *
     * This method splits the given Trait name by the '/' delimiter to separate the namespace
     * and class name. It then generates the appropriate PHP code for the Trait class, ensuring
     * that the namespace and class name are correctly formatted. If a directory structure is
     * specified, it creates the corresponding namespace. Otherwise, it defaults to the root 
     * namespace.
     *
     * @param string $name The name of the Trait, potentially with a namespace structure.
     *                     Example: 'Auth/SocialLoginTrait/Dss' or 'SocialLoginTrait'.
     *
     * @return string The generated PHP code for the Trait class, including the proper namespace
     *                and class declaration.
     */
    private function generateTrait($name)
    {
        // Split the input string into parts based on '/'
        $parts = explode('/', $name);

        // Separate the namespace (everything except the last part) and the class name (the last part)
        $namespaceParts = array_slice($parts, 0, -1); // Get all parts except the last for the namespace
        $className = end($parts); // Get the last part as the class name

        // If there are namespace parts, join them with '\' else set it to an empty string
        $namespace = !empty($namespaceParts) ? '\\' . implode('\\', $namespaceParts) : '';

        return "<?php
    
namespace App\\Traits{$namespace};

trait {$className}
{
   // Your trait methods go here
}
";
    }
}