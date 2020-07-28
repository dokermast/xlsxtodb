<?php

namespace App\Http\Controllers;

use App\Category;
use App\Product;
use Illuminate\Support\Facades\DB;
use App\Manufacturer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Rules\MaxFileSize;


class UploadController extends Controller
{
    public function getForm()
    {
        return view('form');
    }

    public function upload(Request $request)
    {
        if ($request->isMethod('post')) {
            /* validation */
            // CUSTOM RULE
            $request->validate([
                'file' => ['required', new MaxFileSize()],
            ]);

            $messages = [
                'file.mimes' => 'File exstension should be .xlsx',
            ];

            $validator = Validator::make($request->all(), [
                'file' => 'mimes:xlsx',
            ], $messages);

            if ($validator->fails()) {

                return redirect()
                    ->route('form')
                    ->withErrors($validator)
                    ->withInput($request->all);
            }

            $fix = $request->input('fix');

            /* get data array from file */
            $file = $request->file('file');

            $filename = $file->getPathname();

            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $spreadsheet = $reader->load($filename);

            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            /* fix array if damaged */
            if ($fix){
                $fixed_rows = [];
                foreach($rows as $row){
                    if (!$row[10]){
                        $fixed_rows[] = $row;
                    } else {
                        array_shift($row);
                        $row[] = null;
                        $fixed_rows[] = $row;
                    }
                }
                $rows = $fixed_rows;
            }

            /* remove head */
            array_shift($rows);

            $categories = [];
            $categories_array = [];
            $model_codes = [];
            $errors_count = null;
            $repeated_code_count = null;
            $manufacturer = [];
            $manufacturer_array = [];
            $product_names = [];
            $product_name_error_count = null;

            $i = 1;
            $j = 1;
            $p = 1;

            foreach ($rows as $el){

                /* searching repeated model_code */
                if ( $el[5] && trim($el[5]) &&  !in_array(trim($el[5]), $model_codes)) {
                    $model_codes[] = trim($el[5]);

                    /* searching product name errors */
                    if ($el[4] && !in_array(mb_strtolower($el[4]), $product_names)){
                        $product_names[] = mb_strtolower($el[4]);

                        /* set CATEGORIES array */
                        if ($el[0]) {

                            if (!in_array($el[0], $categories)) {

                                $categories[] = $el[0];
                                $categories_array[] = [
                                    'id' => $i,
                                    "parent_id" => 0,
                                    "name" => $el[0]
                                ];
                                $i++;

                                if (!in_array($el[0]."/".$el[1], $categories)) {

                                    $categories[] = $el[0]."/".$el[1];
                                    $categories_array[] = [
                                        "id" => $i,
                                        "parent_id" => $this->getParentId($el[0], $categories_array),
                                        "name" => $el[0] . "/" . $el[1]
                                    ];
                                    $i++;

                                    if (!in_array($el[0]."/".$el[1]."/".$el[2], $categories)) {

                                        $categories[] = $el[0]."/".$el[1]."/".$el[2];
                                        $categories_array[] = [
                                            "id" => $i,
                                            "parent_id" =>  $this->getParentId($el[0]."/".$el[1], $categories_array),
                                            "name" => $el[0] . "/" . $el[1] . "/" .$el[2]
                                        ];
                                        $i++;
                                    }
                                }

                            } else {

                                if (!in_array($el[0] . "/" . $el[1], $categories)) {

                                    $categories[] = $el[0]."/".$el[1];
                                    $categories_array[] = [
                                        "id" => $i,
                                        "parent_id" => $this->getParentId($el[0], $categories_array),
                                        "name" => $el[0] . "/" . $el[1]
                                    ];
                                    $i++;

                                } else {

                                    if (!in_array($el[0] . "/" . $el[1] . "/" . $el[2], $categories)) {

                                        $categories[] = $el[0] . "/" . $el[1] . "/" . $el[2];
                                        $categories_array[] = [
                                            "id" => $i,
                                            "parent_id" => $this->getParentId($el[0]."/".$el[1], $categories_array),
                                            "name" => $el[0] . "/" . $el[1] . "/" . $el[2]
                                        ];
                                        $i++;
                                    }
                                }
                            }

                            $availability = ($el[9]) ? true : false;
                            $product = [
                                "id" => $p,
                                "category" => $el[0]."/".$el[1]."/".$el[2],
                                "category_id" => null,
                                "name" => $el[4],
                                "description" => $el[6],
                                "manufacturer" => $el[3],
                                "manufacturer_id" => null,
                                "model_code" => $el[5],
                                "price" => $el[7],
                                "varanty" => $el[8],
                                "availability" => $availability,
                            ];
                            $p++;
                            $products_array[] = $product;

                        } else {

                            if ($el[1]) {

                                if (!in_array($el[1], $categories)) {

                                    $categories[] = $el[1];
                                    $categories_array[] = [
                                        "id" => $i,
                                        "parent_id" => 0,
                                        "name" => $el[1]
                                    ];
                                    $i++;

                                    if (!in_array($el[1]."/".$el[2], $categories)) {

                                        $categories[] = $el[1]."/".$el[2];
                                        $categories_array[] = [
                                            "id" => $i,
                                            "parent_id" => $this->getParentId($el[1], $categories_array),
                                            "name" => $el[1] . "/" . $el[2]
                                        ];
                                        $i++;
                                    }

                                } else {

                                    if (!in_array($el[1] . "/" . $el[2], $categories)) {

                                        $categories[] = $el[1] . "/" . $el[2];
                                        $categories_array[] = [
                                            "id" => $i,
                                            "parent_id" => $this->getParentId($el[1], $categories_array),
                                            "name" => $el[1] . "/" . $el[2]
                                        ];
                                        $i++;
                                    }
                                }

                                $availability = ($el[9]) ? true : false;
                                $product = [
                                    "id" => $p,
                                    "category" => $el[1]."/".$el[2],
                                    "category_id" => null,
                                    "name" => $el[4],
                                    "description" => $el[6],
                                    "manufacturer" => $el[3],
                                    "manufacturer_id" => null,
                                    "model_code" => $el[5],
                                    "price" => $el[7],
                                    "varanty" => $el[8],
                                    "availability" => $availability,
                                ];
                                $p++;
                                $products_array[] = $product;
                            }
                        }

                    } else {

                        $product_name_error_count++;
                    }

                    /* set MANUFACTURERS array */
                    if ($el[3] && !in_array($el[3], $manufacturer)) {
                        $manufacturer[] = $el[3];
                        $manufacturer_array[] = [
                            'id' => $j,
                            'name' => $el[3]
                        ];
                        $j++;
                    }

                } else {

                    $repeated_code_count++;
                }
            }

            /*  set PRODUCTS array */
            $products_array_full = [];
            foreach($products_array as $product){

                $product['category_id'] = $this->getParentId($product['category'], $categories_array);
                $product['manufacturer_id'] = $this->getParentId($product['manufacturer_id'], $categories_array);
                $products_array_full[] = $product;
            }

            try {

                /* SAVE MANUFACTURERS */
                $manufacturers = [];
                foreach($manufacturer_array as $el){
                    $manufacturers[] = [
                       'id' => $el['id'],
                        'name' => $el['name']
                    ];
                }
                $manufacturer_saved_count = count($manufacturer_array);
                DB::table('manufacturers')->insert($manufacturer_array);

                /* SAVE CATEGORIES */
                $categories = [];
                foreach($categories_array as $el){
                    $categories[] = [
                        'id' => $el['id'],
                        'parent_id' => $el['parent_id'],
                        'name' => $el['name']
                    ];
                }
                $categories_saves_count = count($categories_array);
                DB::table('categories')->insert($categories);


                /* SAVE PRODUCTS */
                $products = [];
                foreach($products_array_full as $el){
                    if ($i < 10){
                        $products[] = [
                            'id' => $el['id'],
                            'name' => htmlspecialchars_decode($el['name']),
                            'category_id' => $el['category_id'],
                            'description' => htmlspecialchars_decode($el['description']),
                            'manufacturer_id' => $el['manufacturer_id'],
                            'model_code' => $el['model_code'],
                            'price' => $el['price'],
                            'varanty' => $el['varanty'],
                            'availability' => $el['availability']
                        ];
                    } else {

                        break;
                    }
                }
                $product_saved_count = count($products_array);
                DB::table('products')->insert($products);

            } catch (\Exception $exception) {

                return back()->withError($exception->getMessage())->withInput();
            }

            return redirect('/')->with('status', 'File was upload to DB. Were uploaded '.$manufacturer_saved_count.' rows to the Manefacturers Table, '.$categories_saves_count.' rows to the Categories table
                and '.$product_saved_count.'  rows to the Products Table.  '.$repeated_code_count.' rows from exel file were missed because repeated or missed the model_code.
                '.$product_name_error_count.' were missed because have product name errors');
        }

        return redirect('/')->withErrors(['error' => 'Wrong method']);
    }


    public function getParentId($needle, $categories_array)
    {
        $key = array_search($needle, array_column($categories_array, 'name'));

        return $categories_array[$key]["id"];
    }


    public function trunkate()
    {
        DB::delete('delete from products');
        DB::delete('delete from categories');
        DB::delete('delete from manufacturers');

        return redirect('/')->with('status', 'The DB was trunkated');
    }
}
