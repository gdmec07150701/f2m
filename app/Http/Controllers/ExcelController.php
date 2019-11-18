<?php

namespace App\Http\Controllers;

use App\Http\Requests;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

use Excel;
use Schema;
use DB;

class ExcelController extends Controller

{

    public function export()
    {
        $cellData = [
            ['id','name','number'],
            ['10001','AAAAA','99'],
            ['10002','BBBBB','92'],
            ['10003','CCCCC','95'],
            ['10004','DDDDD','89'],
            ['10005','EEEEE','96'],
        ];

        Excel::create('users',function($excel) use ($cellData){

            $excel->sheet('score', function($sheet) use ($cellData){

                $sheet->rows($cellData);

            });

        })->store('xls')->export('xls');

    }

    public function import(){
        ini_set("memory_limit", "1024M");
        set_time_limit(300);
        $filePath = 'storage/exports/daba_wx_message.xlsx';

        Excel::load($filePath, function($reader) {
            $data = $reader->all()->toArray();
            $allDatas = array_chunk($data, 500);   //切割500个一组

            //获取表头字段
            foreach ($allDatas[0][0] as $k1 => $v1){
                $th[] = $k1;
            }

            if(in_array('id', $th)){
                echo 'id为主键字段不允许为表头字段名';
            }

            $tableName = 'daba_wx_message';
            try{
                Schema::create($tableName, function ($table) use ($th) {
                    $table->increments('id');
                    foreach ($th as $k => $v){
                        $table->string($v)->default('')->nullable();
                    }
                });
            }catch (\Exception $e){
                echo '<div style="text-align: center">';
                dd('已存在数据表');
                echo '</div>';
            }

            foreach ($allDatas as $k1 => $v1){
                DB::table($tableName)->insert($v1);
            }

        });
        exit;

    }

}