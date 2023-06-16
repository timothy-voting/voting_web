<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExecuteSQL extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:sql';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the dump.sql file';

    public function handle()
    {
        $contents = file_get_contents(base_path().'/dump.sql');
        DB::unprepared($contents);
        echo "sql executed successfully!\n";
    }
}
