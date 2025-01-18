<?php

use App\Console\Commands\MakeInterface;
use App\Console\Commands\MakeRepository;
use App\Console\Commands\MakeService;
use App\Console\Commands\MakeTrait;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();


// Register the custom Artisan command for creating a service
Artisan::command('make:service {name}', function($name) {
    // Call the MakeService command and pass the 'name' argument
    $this->call(MakeService::class, ['name' => $name]);
});

// Register the custom Artisan command for creating a repository
Artisan::command('make:repository {name} {--interface}', function($name) {
    // Call the MakeRepository command with the 'name' argument
    // Pass the --interface option if provided (this is checked using $this->option('interface'))
    $this->call(MakeRepository::class, [
        'name' => $name,
        '--interface' => $this->option('interface'),  // Pass the --interface option if it's present
    ]);
});

// Register the custom Artisan command for creating an interface
Artisan::command('make:interface {name}', function($name) {
    // Call the MakeInterface command and pass the 'name' argument
    $this->call(MakeInterface::class, ['name' => $name]);
});

// Register the custom Artisan command for creating a trait
Artisan::command('make:trait {name}', function($name) {
    // Call the MakeTrait command and pass the 'name' argument
    $this->call(MakeTrait::class, ['name' => $name]);
});

