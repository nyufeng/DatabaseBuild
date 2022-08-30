<?php
namespace Nyufeng\MakeDatabase;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class DatabaseBuilder{

    public static string $db_conn_type;
    public static string $db_name;
    public static array $tables;

    public static function init(): void
    {
        $db_conn_type = config("database.default");
        $db_name = config("database.connections.{$db_conn_type}.database");

        self::$db_conn_type = $db_conn_type;
        self::$db_name = $db_name;

        $tableNames = DB::select("SHOW TABLES FROM `{$db_name}`");
        foreach ($tableNames as $tableNameClass){
            $tableName = current($tableNameClass);
            self::$tables[$tableName] = [];
            //COLUMNS
            $columns = DB::select("SELECT * FROM `information_schema`.`COLUMNS` WHERE TABLE_SCHEMA='{$db_name}' AND TABLE_NAME='{$tableName}' ORDER BY ORDINAL_POSITION");
            self::$tables[$tableName]["COLUMNS"] = [];
            foreach ($columns as $column){
                self::$tables[$tableName]["COLUMNS"][$column->COLUMN_NAME] = $column;
            }
            //INDEXES
            $indexes = DB::select("SHOW INDEXES FROM `{$tableName}` FROM `{$db_name}`");
            self::$tables[$tableName]["INDEXES"] = $indexes;
        }
    }

    public static function buildSeeder(): void
    {
        foreach (self::$tables as $tableName => $tableInfo){
            $model = DB::table($tableName);
            $data = $model->get();
            $seederStr = "";
            foreach($data as $row){
                $seederStr .= "\t[\r\n";
                foreach ($row as $k => $v){
                    if ($v === null) {
                        $seederStr .= "\t\t\"{$k}\" => null,\r\n";
                    }else {
                        $seederStr .= "\t\t\"{$k}\" => '{$v}',\r\n";
                    }
                }
                $seederStr .= "\t],\r\n";
            }
            $seederBuildName = "DatabaseSeeder" . ucfirst($tableName);
            $seederTmp = file_get_contents(__DIR__ . "\\template\\DatabaseSeeder.tmp");
            $seederBuildStr = str_replace(["{{ class }}", "{{ tableName }}", "{{ seederData }}"], [$seederBuildName, $tableName, $seederStr], $seederTmp);
            File::put(App::databasePath() ."\\seeders\\$seederBuildName.php",$seederBuildStr);
        }
    }
}
