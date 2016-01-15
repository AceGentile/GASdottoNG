<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use DB;
use Theme;
use CsvReader;

use App\Supplier;
use App\Product;
use App\Category;
use App\Measure;

class ImportController extends Controller
{
        private function guessCsvFileSeparator($path)
	{
		$contents = fopen($path, 'r');
		if ($contents === null)
			return null;

		$separators = [',', ';', "\t"];
		$target_separator = null;

		while(!feof($contents) && $target_separator == null) {
			$char = fgetc($contents);
			foreach ($separators as $del) {
				if ($char == $del) {
					$target_separator = $del;
					break;
				}
			}
		}

		fclose($contents);
		return $target_separator;
	}

	public function postCsv(Request $request)
	{
		$supplier_id = $request->input('supplier_id');
		$s = Supplier::findOrFail($supplier_id);
		if ($s->userCan('supplier.modify') == false)
			return $this->errorResponse('Non autorizzato');

		$step = $request->input('step', 'guess');

                /*
                        TODO: indovinare se il file ha una riga di intestazione
                */

		switch ($step) {
			case 'guess':
				try {
					$f = $request->file('file', null);
					if ($f == null || $f->isValid() == false)
						return $this->errorResponse('File non caricato correttamente, possibili problemi con la dimensione');

					$filepath = $s->filesPath();
                                        if ($filepath == null)
                                                return $this->errorResponse('Impossibile salvare il file, possibili problemi di permessi');

					$filename = $f->getClientOriginalName();
					$f->move($filepath, $filename);
					$path = $filepath . '/' . $filename;

					$target_separator = $this->guessCsvFileSeparator($path);
					if ($target_separator == null)
						return $this->errorResponse('Impossibile interpretare il file');

					$reader = CsvReader::open($path, $target_separator);
					$sample_line = $reader->readLine();

                                        return Theme::view('import.csvsortcolumns', ['supplier' => $s, 'path' => $path, 'columns' => $sample_line]);
				}
				catch(\Exception $e) {
					return $this->errorResponse('Errore nel salvataggio del file');
				}

				break;

			case 'run':
                                DB::beginTransaction();

				$path = $request->input('path');
				$columns = $request->input('column');

				$target_separator = $this->guessCsvFileSeparator($path);
				if ($target_separator == null)
					return $this->errorResponse('Impossibile interpretare il file');

                                $products = [];
                                $errors = [];

				$reader = CsvReader::open($path, $target_separator);
				while (($line = $reader->readLine()) !== false) {
                                        try {
                                                $p = new Product();
        					$p->supplier_id = $s->id;
                                                $p->active = true;

        					foreach($columns as $index => $field) {
                                                        if ($field == 'none') {
                                                                continue;
                                                        }
        						else if ($field == 'category') {
        							$name = $line[$index];
        							$category = Category::where('name', '=', $name)->first();
        							if ($category == null) {
        								$category = new Category();
        								$category->name = $name;
        								$category->save();
        							}

                                                                $field = 'category_id';
        							$value = $category->id;
        						}
        						else if ($field == 'measure') {
        							$name = $line[$index];
        							$measure = Measure::where('name', '=', $name)->first();
        							if ($measure == null) {
        								$measure = new Measure();
        								$measure->name = $name;
        								$measure->save();
        							}

                                                                $field = 'measure_id';
        							$value = $measure->id;
        						}
                                                        else if ($field == 'price' || $field == 'transport') {
        							$value = str_replace (',', '.', $line[$index]);
        						}
        						else {
        							$value = $line[$index];
        						}

                                                        if ($field == 'name') {
                                                                $existing_p = $s->products()->where('name', '=', $value)->orderBy('id', 'desc')->first();
                                                                if ($existing_p != null) {
                                                                        $p->id = $existing_p->nextId();
                                                                        $p->previous_id = $existing_p->id;
                                                                }
                                                        }

        						$p->$field = $value;
        					}

        					$p->save();
                                                $products[] = $p;
                                        }
                                        catch(\Exception $e) {
                                                $errors[] = join($target_separator, $line) . '<br/>' . $e->getMessage();
                                        }
				}

                                DB::commit();

				return Theme::view('import.csvfinal', ['supplier' => $s, 'products' => $products, 'errors' => $errors]);
				break;
		}

		return $this->errorResponse('Comando non valido');
	}
}
