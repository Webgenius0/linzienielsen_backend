<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class MakeInterface extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:interface {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new interface class';


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
        $namespace = str_replace('/', '\\', dirname($name));  // Get the namespace without the interface name
        $interfaceName = basename($name);  // Get the interface name (e.g., 'SocialLoginInterface')
        $interfaceClass = $this->generateInterfaceClass($name);

        // Define the full path to the interfaces directory
        $directory = app_path("Interfaces/{$namespace}");

        // Ensure the directory exists (create it if it doesn't)
        if (!$this->filesystem->exists($directory)) {
            $this->filesystem->makeDirectory($directory, 0755, true);  // Create any missing directories
        }

        // Define the path for the new interface class file
        $path = $directory . DIRECTORY_SEPARATOR . "{$interfaceName}.php";

        // Check if the interface class already exists
        if ($this->filesystem->exists($path)) {
            $this->error("Interface class {$name} already exists!");
            return;
        }

        // Create the interface class file
        $this->filesystem->put($path, $interfaceClass);

        $this->info("Interface class {$name} created successfully!");
    }

    /**
     * Generate the PHP code for an interface class based on the given name.
     *
     * @param string $name The name of the interface, potentially with a namespace structure.
     *                     Example: 'Auth/SocialLoginInterface/Dss' or 'SocialLoginInterface'.
     *
     * @return string The generated PHP code for the interface class, including the proper namespace
     *                and interface declaration.
     */
    private function generateInterfaceClass($name)
    {
        // Split the input string into parts based on '/'
        $parts = explode('/', $name);

        // Separate the namespace (everything except the last part) and the interface name (the last part)
        $namespaceParts = array_slice($parts, 0, -1); // Get all parts except the last for the namespace
        $interfaceName = end($parts); // Get the last part as the interface name

        // If there are namespace parts, join them with '\' else set it to an empty string
        $namespace = !empty($namespaceParts) ? '\\' . implode('\\', $namespaceParts) : '';

        return "<?php
    
namespace App\\Interfaces{$namespace};

interface {$interfaceName}
{
    // Define the methods that must be implemented by the classes using this interface
}
";
    }
}
