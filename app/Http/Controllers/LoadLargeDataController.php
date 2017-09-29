<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class LoadLargeDataController extends Controller
{
    private $table = 'test_table';
    private $url = '/Users/Desktop/';
    private $file_ext = '.txt';

    public function index()
    {
        echo "start:" . date("h:i:sa");
        //获取所有索引
        $indexes = $this->out_all_index();

        //导出数据到文本
        $this->outfile_data();

        //删除表数据
        DB::table($this->table)->truncate();

        //删除索引和自增
        $this->delete_all_index($indexes);
        
        //导入数据
        $this->infile_data();

        //添加索引
        $this->in_all_index($indexes);

        echo "<br />" . date("h:i:sa");
    }

    /**
     * 获取所有索引
     */
    private function out_all_index()
    {
        $index_results = DB::select("SHOW INDEXES FROM " . $this->table);
        $index_results = array_map("get_object_vars", $index_results);
        $indexs = [];
        foreach ($index_results as $key => $index_result) {
            $indexs[$index_result['Key_name']][] = $index_result;
            unset($index_results[$key]);
        }

        return $indexs;
    }

    /**
     * 导出数据到文本
     */
    private function outfile_data()
    {
        $file = $this->url . $this->table . $this->file_ext;
        if (file_exists($file)) {
            unlink($file);
        }
        $sql = "SELECT * INTO OUTFILE '" . $file . "' FIELDS TERMINATED BY ',' FROM " . $this->table;
        DB::statement($sql);
    }

    /**
     * 删除索引和自增
     */
    private function delete_all_index($indexes)
    {
        //删除自增
        $columns = DB::select("SHOW FULL COLUMNS FROM " . $this->table);
        $columns = array_map("get_object_vars", $columns);
        foreach ($columns as $column) {
            if ($column["Extra"] == "auto_increment") {
                DB::statement("ALTER TABLE " . $this->table . " CHANGE " . $column["Field"] . " " . $column["Field"] . " INT");
                break;
            }
        }

        foreach ($indexes as $key => $index) {
            if ($key == "PRIMARY") {
                DB::statement("ALTER TABLE `" . $this->table . "` DROP PRIMARY KEY");
            } else {
                DB::statement("ALTER TABLE `" . $this->table . "` DROP INDEX " . $key);
            }
        }
    }
    
    /**
     * 导入数据
     */
    private function infile_data()
    {
        //新创建一个链接
        $pdo = $this->connect();

        $file = $this->url . $this->table . $this->file_ext;
        $sql = "LOAD DATA INFILE '" . $file . "' INTO TABLE " . $this->table . " FIELDS TERMINATED BY ','";
        $pdo->prepare($sql)->execute();

        //导入数据后关闭链接
        $pdo = null;

        //删除文件
        unlink($file);
    }

    private function connect()
    {
        try {
            $pdo = new \PDO(
                'mysql:host='. env('DB_HOST') .';dbname='. env('DB_DATABASE') . ';port=' . env('DB_PORT') .';charset=utf8',
                env('DB_USERNAME'),
                env('DB_PASSWORD')
            );
            return $pdo;
        } catch (\PDOException $ex) {
            echo 'database connection failed';
            exit();
        }
    }

    /**
     * 添加索引
     * @param $indexes
     */
    public function in_all_index($indexes)
    {
        foreach ($indexes as $key => $index) {
            if ($key == "PRIMARY") {
                $key_name = "PRIMARY";
            } else {
                $key_name = "INDEX";
            }

            $column = "";
            $index_type = $index[0]['Index_type'];
            foreach ($index as $value) {
                $column .= "`" . $value['Column_name'] . "`,";
            }
            $column = chop($column, ",");
            if ($key == "PRIMARY") {
                DB::statement( "ALTER TABLE `" . $this->table . "` CHANGE COLUMN " . $column . " " . $column . " int(10) NOT NULL AUTO_INCREMENT PRIMARY KEY");
            } else {
                DB::statement("ALTER TABLE `" . $this->table . "`ADD $key_name `$key` USING $index_type($column)");
            }
        }
    }
}
