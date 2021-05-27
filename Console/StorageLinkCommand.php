<?php

namespace Illuminate\Foundation\Console;

use Illuminate\Console\Command;

class StorageLinkCommand extends Command
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'storage:link
                {--relative : Create the symbolic link using relative paths}
                {--force : Recreate already existing symbolic links}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create the symbolic links configured for the application';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $relative = $this->option('relative');
        $force = $this->option('force');

        foreach ($this->links() as $link => $target) {
            if (file_exists($link) && ! $this->removableSymlink($link, $force)) {
                $this->error("The [$link] link already exists.");
                continue;
            }

            if (is_link($link)) {
                $this->laravel->make('files')->delete($link);
            }

            if ($relative) {
                $this->laravel->make('files')->relativeLink($target, $link);
            } else {
                $this->laravel->make('files')->link($target, $link);
            }

            $this->info("The [$link] link has been connected to [$target].");
        }

        $this->info('The links have been created.');
    }

    /**
     * Get the symbolic links that are configured for the application.
     *
     * @return array
     */
    protected function links()
    {
        return $this->laravel['config']['filesystems.links'] ??
               [public_path('storage') => storage_path('app/public')];
    }

    /**
     * Checks that the provided path is a symlink and force option is turned on.
     *
     * @param  string  $link
     * @param  bool  $force
     * @return bool
     */
    protected function removableSymlink(string $link, bool $force): bool
    {
        return is_link($link) && $force;
    }
}
