<?php

namespace Nyufeng\MakeDatabase\Console\Commands;

use Illuminate\Console\Command;
use Nyufeng\MakeDatabase\DatabaseBuilder;

class BuildSeeder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database:build-seeder';

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
    public function handle()
    {
        DatabaseBuilder::init();
        DatabaseBuilder::buildSeeder();
    }
}
