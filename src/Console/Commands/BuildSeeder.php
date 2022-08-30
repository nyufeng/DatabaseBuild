<?php

namespace Nyufeng\MakeDatabase\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Nyufeng\MakeDatabase\DatabaseBuilder;

class BuildSeeder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database:build-seeder
                            {--max-rows=100 : 表允许最大的行数，超过此值跳过生成. 0为不限制}
                            {--tables= : 指定生成数据表表列表 example users,sites,logs}
                            ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '根据数据库生成 seeder 数据';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): void
    {
        $maxRows = $this->option("max-rows");
        $tablesOption = $this->option("tables");
        DatabaseBuilder::init($tablesOption);
        $tables = DatabaseBuilder::$tables;

        foreach ($tables as $tableName => $tableInfo){
            ;
            if ($maxRows != 0 && ($count = DB::table($tableName)->count()) > $maxRows) {
                $this->warn("Skip: {$tableName}");
                continue;
            }
            DatabaseBuilder::buildSeeder($tableName);
            $this->info("Build: {$tableName}");
        }
    }
}
