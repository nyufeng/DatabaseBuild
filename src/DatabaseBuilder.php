<?php
namespace Nyufeng\MakeDatabase;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DatabaseBuilder{

    public static string $db_conn_type;
    public static string $db_name;
    public static array $tables;

    public static function init(?string $tables): void
    {
        $db_conn_type = config("database.default");
        $db_name = config("database.connections.{$db_conn_type}.database");
        self::$db_conn_type = $db_conn_type;
        self::$db_name = $db_name;
        if($tables == null){
            $tableNames = DB::select("SHOW TABLES FROM `{$db_name}`");
        }else{
            $tableNames = explode(",", $tables);
        }
        foreach ($tableNames as $tableNameClass){
            $tableName = is_string($tableNameClass)?$tableNameClass:current($tableNameClass);
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

    public static function buildSeeder(string $tableName): void
    {
        $model = DB::table($tableName);
        $data = $model->get();
        $seederStr = "";
        foreach($data as $row){
            $seederStr .= "\t[\r\n";
            foreach ($row as $k => $v){
                if ($v === null) {
                    $seederStr .= "\t\t\"{$k}\" => null,\r\n";
                }else {
                    $value = str_replace("'","\\'", $v);
                    $seederStr .= "\t\t\"{$k}\" => '{$value}',\r\n";
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
