<?php

namespace App\Controllers\Api;

use \App\Controllers\Controller;
use Mpdf\Mpdf;

class ProductsController extends Controller
{
    /**
     * @param \App\Http\Request $req
     * @param \App\Http\Response $res
     */
    public function searchProducts($req, $res)
    {
        $query = $req->get("q");
        $location = (int) $req->get("location");

        if (!is_null($query)) {
            if ($location) {
                if (!$query)
                    $data = $this->model->getProductsByLocation($location);
                else
                    $data = $this->model->searchOnLocation($query, $location);
            } else {
                if (!$query)
                    $data = $this->model->getLast($query);
                else
                    $data = $this->model->search($query);
            }
            $res->renderJSON(($data ? $data : []));
        } else {
            $res->addHeader(http_response_code(400));
            $res->renderJSON(['error' => 'provide query']);
        }
    }
    /**
     * @param \App\Http\Request $req
     * @param \App\Http\Response $res
     */
    public function addReceipt($req, $res)
    {
        $products = $req->post('products');
        if (!is_null($products)) {
            $productsJSON = json_encode($products);
            try {
                $this->model->updateQuantity($products);
                $this->model->addReceipt($productsJSON);
            } catch (\Throwable $th) {
                $res->addHeader(http_response_code(500));
                $res->renderJSON(['error' => $th->getMessage()]);
            }
        } else {
            $res->addHeader(http_response_code(400));
            $res->renderJSON(['error' => 'provide products']);
        }
    }
    /**
     * @param \App\Http\Request $req
     * @param \App\Http\Response $res
     */
    public function verifyReceipt($req, $res)
    {
        $products = $req->post('products');
        if (!is_null($products)) {
            $IDs = array_keys($products);
            $changed = array();
            $data = $this->model->getManyByID($IDs);
            foreach ($data as $product) {
                $newProduct = $products[($product['id'])];
                if ($product['price'] > 0 && $product['price'] != $newProduct) {
                    $changed[($product['id'])] = $product;
                    $changed[($product['id'])]['newPrice'] = $newProduct['price'];
                }
            }
            $res->renderJSON(['products' => $changed]);
        } else {
            $res->addHeader(http_response_code(400));
            $res->renderJSON(['error' => 'provide products']);
        }
    }
    /**
     * @param \App\Http\Request $req
     * @param \App\Http\Response $res
     */
    public function lastReceipt($req, $res)
    {
        $data = $this->model->getLasReceipt();
        if ($data)
            $res->renderJSON($data);
        else
            $res->renderJSON([]);
    }
    /**
     * @param \App\Http\Request $req
     * @param \App\Http\Response $res
     */
    public function mostSelled($req, $res)
    {
        $data = $this->model->getMostSelled();
        $res->renderJSON($data);
    }
    /**
     * @param \App\Http\Request $req
     * @param \App\Http\Response $res
     */
    public function overview($req, $res)
    {
        $shopID = (int) $req->get("shop_id");
        if (!$shopID) {
            http_response_code(404);
            return $res->renderJSON([
                "error" => "Impossible de traiter la demande !"
            ]);
        }
        $data = $this->model->getOverview($shopID);
        $res->renderJSON($data);
    }
    /**
     * @param \App\Http\Request $req
     * @param \App\Http\Response $res
     */
    public function addProducts($req, $res)
    {
        $products = $req->post('products');
        if (!is_null($products)) {
            //$products = array_values($products);
            try {
                $this->model->insertProducts($products);
                $res->addHeader(http_response_code(201));
                $res->renderJSON(['success' => "created"]);
            } catch (\Throwable $th) {
                $res->addHeader(http_response_code(500));
                $res->renderJSON(['error' => $th->getMessage()]);
            }
        } else {
            $res->addHeader(http_response_code(400));
            $res->renderJSON(['error' => 'provide products']);
        }
    }
    /**
     * @param \App\Http\Request $req
     * @param \App\Http\Response $res
     */
    public function getProducts($req, $res)
    {
        $id = $req->get("id");
        $locationID = (int) $req->get("location");
        $barcode = $req->get("barcode");
        if (!is_null($id) || !is_null($barcode)) {
            $id = (int) $id;
            if ($id)
                $data = $this->model->getProduct(['id' => $id]);
            else
                $data = $this->model->getProduct(['barcode' => $barcode]);
        } else {
            if ($locationID) {
                $data = $this->model->getProductsByLocation($locationID);
            } else {

                $data = $this->model->getLastModified();
            }
        }
        $res->renderJSON($data);
    }
    /**
     * @param \App\Http\Request $req
     * @param \App\Http\Response $res
     */
    public function updateProduct($req, $res)
    {
        $product = $req->post("product");
        if (!is_null($product)) {
            $product['updated_at'] = date('Y-m-d H:m:s');
            $this->model->updateProduct($product);
            $res->addHeader(http_response_code(201));
            $res->renderJSON(['message' =>/* 'product modified' */ $product]);
        } else {
            $res->addHeader(http_response_code(400));
            $res->renderJSON(['error' => 'provide data']);
        }
    }
    /**
     * @param \App\Http\Request $req
     * @param \App\Http\Response $res
     */
    public function deleteProducts($req, $res)
    {
        $id = (int) $req->get("id");
        if ($id) {
            $product = $this->model->getProduct(['id' => $id]);
            unset($product['id']);
            unset($product['created_at']);
            unset($product['updated_at']);
            if ($product) {
                if ($product['quantity'] > 0) {
                    $res->addHeader(http_response_code(401));
                    return $res->renderJSON(['error' => 'Impossible d\'effacer un produit ayant une quantité en stock']);
                }
                unset($product['quantity']);
                $product = $this->model->add2Deleted($product);
                $this->model->deleteProduct($id);
                $res->renderJSON(['message' => 'product deleted !']);
            } else {
                $res->addHeader(http_response_code(400));
                $res->renderJSON(['error' => 'product doesn\'t exists']);
            }
        } else {
            $res->addHeader(http_response_code(400));
            $res->renderJSON(['error' => 'invalid ID']);
        }
    }
    /**
     * @param \App\Http\Request $req
     * @param \App\Http\Response $res
     */
    public function getSells($req, $res)
    {
        function isValidDate($date, $format = 'Y-m-d H:i:s')
        {
            $d = \DateTime::createFromFormat($format, $date);
            return $d && $d->format($format) === $date;
        }
        $from = $req->get("from") != null ? $req->get("from") : date('Y-m-d') . " 23:59:00";
        $to = $req->get("to") != null ? $req->get("to") : date('Y-m-d') . " 23:59:00";
        $shopID = (int) $req->get("shop");
        if (isValidDate($from) && isValidDate($to) && $shopID) {
            $data = $this->model->getSellsBy($from, $to, $shopID);
            $res->addHeader(http_response_code(201));
            $res->renderJSON($data);
        } else {
            $res->addHeader(http_response_code(400));
            $res->renderJSON(['error' => 'provide complete data']);
        }
    }
    /**
     * @param \App\Http\Request $req
     * @param \App\Http\Response $res
     */
    public function saveBill($req, $res)
    {
        $products = $req->post("products");
        $total = $req->post("total");
        $discount = $req->post("discount") ? $req->post("discount") : 0;
        $date = $req->post("date");
        $bill_id = time();
        $shopID = (int) $req->post("location");
        if (!is_null($products) && !is_null($total) && $shopID) {
            $shop = $this->model->getLocation(["location_id" => $shopID]);
            if (!$shop || $shop["location_type"] == "store") {
                $res->addHeader(http_response_code(400));
                return $res->renderJSON(['error' => 'provide valid data']);
            }
            $bill = ["products" => $products, "total" => $total, "discount" => $discount, "shop_id" => $shopID, "user_id" => $req->getCInfo("id"), "bill_id" => $bill_id];
            if ($date)
                $bill["date"] = "$date 12:00:00";
            $this->model->saveBill($bill);
            return $this->generateBill($bill);

        } else {
            $res->addHeader(http_response_code(400));
            $res->renderJSON(['error' => 'provide complete data']);
        }
    }
    public function generateBill($bill)
    {
        date_default_timezone_set("Africa/Lubumbashi");
        $configs = $this->model->getConfigs();
        $products = $bill["products"];
        $total = $bill["total"];
        $discount = $bill["discount"];
        $view_path = dirname(dirname(dirname(__DIR__))) . "/views/";
        $template_path = "$view_path/bill.html";
        $template = file_get_contents($template_path);
        $products_items_html = "";
        $taxes_html = "";
        foreach ($products as $product) {
            $p_name = $product["product_name"];
            $p_price = $product["price"];
            $p_quantity = $product["quantity"];
            $p_total = $p_price * $p_quantity;
            $products_items_html .= "
                    <tr>
                        <td style=\"text-align: left;\">$p_name</td>
                        <td>$p_quantity</td>
                        <td>$p_price</td>
                        <td>$p_total</td>
                    </tr>
                ";
        }
        if($discount){
            
            $taxes_html .= "
            <tr >
                        <td colspan=\"2\">Reduction</td>
                        <td colspan=\"2\">$discount</td>
                    </tr>
            ";
        }
        $template = str_replace("{shop_name}", $configs["companyName"], $template);
        $template = str_replace("{shop_infos}", "Adresse : " . $configs["adress"] . "<br>" . "phone : " . $configs["phone"], $template);
        $template = str_replace("{f_number}", $bill["bill_id"] , $template);
        $template = str_replace("{date}", date('d/m/Y H:i:s'), $template);
        $template = str_replace("{products_list}", $products_items_html, $template);
        $template = str_replace("{taxes}", $taxes_html, $template);
        $template = str_replace("{tht}", $total - $discount, $template);
        $template = str_replace("{total}", $total, $template);

        $file = fopen("$view_path/last-bill.html","w");
        fwrite($file, $template);
        fclose($file);

    }

    public function getLastBill($req,$res) {
        $view_path = dirname(dirname(dirname(__DIR__))) . "/views/";
        $last_bill_path = "$view_path/last-bill.html";
        $last_bill = file_get_contents($last_bill_path);
        $mpdf = new Mpdf();
        $mpdf->WriteHTML($last_bill);
        $mpdf->Output();
        exit();
    }
    /**
     * @param \App\Http\Request $req
     * @param \App\Http\Response $res
     */
    public function deleteBill($req, $res)
    {
        $id = (int) $req->post("bill_id");
        $bill = $id ? $this->model->getBill($id) : false;
        if ($bill) {
            $this->model->updateStockQuantity($bill["products"], $bill["shop_id"]);
            $this->model->deleteBill($bill);
        } else {
            http_response_code(404);
            $res->renderJSON([
                "error" => "Illegal Action 😝"
            ]);
        }

    }
    /**
     * @param \App\Http\Request $req
     * @param \App\Http\Response $res
     */
    public function import($req, $res)
    {
        $data = $req->post('data');
        if ($data) {
            $data = json_decode($data, true);
            $barcodes = array(); // will be used to verify conflict into barcodes
            array_shift($data);
            if (count(end($data)))
                array_pop($data); // delete last blank line 
            $hasQuantity = false;
            $errors = [];
            $dontImport = [];
            $i = 2;
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $barcode = $value[1];
                    if (!$barcode || strlen($barcode) < 2) {
                        // generate a barcode if not exists
                        $barcode = (time() * 999) + $i;
                        $value[1] = $barcode;
                    }
                    if (!isset($barcodes[$barcode])) {
                        $barcodes[$barcode] = $key; //saving the barcode for the next verification

                    } else {
                        //Verify if there is conflicts on barcodes
                        $firstKey = $barcodes[$barcode];
                        $prevLine = $firstKey + 1;
                        $firstProductName = $data[$firstKey][0];
                        $secondProductName = $value[0];
                        $errors[] = [($key + 1), "Les produits $firstProductName (ligne $prevLine ) et $secondProductName ont le meme code-barre seule $firstProductName est enregistré "];
                        $dontImport[] = $key;
                        continue;
                    }
                    if (count($value) < 3) {
                        $line = $key + 1;
                        $errors[] = [$line, "La ligne $line n'est pas complete"];
                        $dontImport[] = $key;
                        unset($barcodes[$barcode]);
                    } else {
                        $value[0] = trim($value[0]);
                        $value[2] = floatval($value[2]);
                        if (isset($value[3])) {
                            $hasQuantity = true;
                            $value[3] = (int) $value[3];
                        }
                    }
                } else {
                    $line = $key + 1;
                    $errors[] = [$line, "La ligne $line est vide"];
                    $dontImport[] = $key;
                }
                $i++;
                $data[$key] = $value;
            }
            // clean data
            foreach ($dontImport as $key) {
                unset($data[$key]);
            }
            // send bad request to user if all data contained errors
            if (empty($data)) {
                http_response_code(400);
                return $res->renderJSON([
                    'error' => 'verifiez vos données',
                    'not_imported' => $errors
                ]);
            }
            try {
                //return var_dump($data);
                $this->model->importProducts($data, $hasQuantity);
                $responseData = ['success' => 'data saved'];
                count($errors) ? $responseData['not_imported'] = $errors : false;
                $res->renderJSON($responseData);
            } catch (\Throwable $th) {
                $res->setHeader(http_response_code(500));
                $res->renderJSON(['message' => $th->getMessage()]);
            }
        }
    }
    /**
     * @param \App\Http\Request $req
     * @param \App\Http\Response $res
     */
    public function export($req, $res)
    {
        $data = $this->model->getAllProducts();
        if ($data) {
            $res->addHeader("Content-type: text/csv");
            $res->addHeader("Content-Disposition: attachment; filename=exports-product-list.csv");
            $res->addHeader("Pragma: no-cache");
            $res->addHeader("Expires: 0");
            $file = fopen('php://memory', 'w+');
            fputcsv($file, ['produit', 'barre-code', 'prix', 'quantite'], ';');
            foreach ($data as $row) {
                fputcsv($file, $row, ';');
            }
            rewind($file);
            return $res->setContent(stream_get_contents($file));
        }
    }
    /**
     * @param \App\Http\Request $req
     * @param \App\Http\Response $res
     */
    public function getConfigs($req, $res)
    {
        $config = $this->model->getConfigs();
        $res->renderJSON($config);
    }
    /**
     * @param \App\Http\Request $req
     * @param \App\Http\Response $res
     */
    public function changeConfigs($req, $res)
    {
        $type = $req->post('type');
        $data = $req->post('data');
        if ($type && $data) {
            if ($type == 'bill') {
                $keys = ['companyName', 'adress', 'phone', 'goodbye'];
            } else if ($type == 'globals') {
                $keys = ['taux', 'tva', 'currency', 'critical', 'discount_threshold', 'discount'];
            } else {
                http_response_code(400);
                $res->renderJSON(['error' => 'data is incorrect', $data]);
                return;
            }
            foreach ($keys as $key) {
                if (!isset($data[$key])) {
                    http_response_code(400);
                    $res->renderJSON(['error' => 'data is incorrect', 'data' => $key]);
                    return;
                } else {
                    $this->model->updateConfigs($keys, $data);
                }
            }
        } else {
            http_response_code(400);
            $res->renderJSON(['error' => 'no data sent !']);
        }
    }

    /**
     * @param \App\Http\Request $req
     * @param \App\Http\Response $res
     */
    public function getLocations($req, $res)
    {
        $type = $req->get("type");
        $locations = $this->model->fetchLocations($type);
        $res->renderJSON($locations);
    }
    /**
     * @param \App\Http\Request $req
     * @param \App\Http\Response $res
     */
    public function getLocation($req, $res)
    {
        $id = (int) $req->get("id");
        $name = $req->get("name");
        if ($id) {
            $data = ["location_id" => $id];
        } else if (!is_null($name)) {
            $data = ["location_name" => $name];
        } else {
            return $res->renderJSON([]);
        }
        $location = $this->model->getLocation($data);
        if (!$location) {
            http_response_code(404);
            return $res->renderJSON([
                'error' => 'L\'item recherché est introuvable !'
            ]);
        }
        $res->renderJSON($location);
    }
    /**
     * @param \App\Http\Request $req
     * @param \App\Http\Response $res
     */
    public function addlocation($req, $res)
    {
        $location_name = $req->post("location_name");
        $location_type = $req->post("type");
        $location_desc = $req->post("location_desc");
        if (is_null($location_name) || !in_array($location_type, ["shop", "store"])) {
            http_response_code(400);
            return $res->renderJSON([
                'error' => 'Le nom du magasin est obligatoire !'
            ]);
        }
        $location = $this->model->getLocation(["location_name" => $location_name]);
        if ($location) {
            http_response_code(400);
            return $res->renderJSON([
                'error' => 'Ce magasin existe déjà !'
            ]);
        }
        $data = [
            "location_name" => $location_name,
            "location_type" => $location_type,

        ];
        if (!is_null($location_desc))
            $data["description"] = $location_desc;
        $this->model->newlocation($data);
        http_response_code(201);
        $res->renderJSON(['message' => 'location created !']);
    }
    /**
     * @param \App\Http\Request $req
     * @param \App\Http\Response $res
     */
    public function updateStock($req, $res)
    {
        // Faute de temps j'omets la verification coté serveur
        // Il faut le faire
        $products = $req->post("products");
        $locationID = (int) $req->post("location");
        $storeID = (int) $req->post("store");
        if (!is_array($products) || !$locationID) {
            http_response_code(400);
            return $res->renderJSON(["error" => "provide all required data !"]);
        }
        $location = $this->model->getLocation(["location_id" => $locationID]);
        if (!$location || ($location["location_type"] == "shop" && !$storeID)) {
            http_response_code(400);
            return $res->renderJSON(["error" => "Impossible de traiter la demande"]);
        }
        $available_products = $this->model->getProductsByLocation($locationID);
        if (!$available_products) {
            $this->model->addProductsInStock($products, $locationID);
            http_response_code(201);
            return $res->renderJSON(["message" => "Stock mis à jour avec succès !"]);
        } else {
            $toUpdate = [];
            $toAdd = $products;
            foreach ($available_products as $product) {
                $key = (int) $product["id"];
                if (in_array($key, array_keys($products))) {
                    unset($toAdd[$key]);
                    $toUpdate[$key] = $products[$key];
                }
            }
            if ($location["location_type"] == "shop")
                $this->model->updateStockQuantity($products, $storeID, true);
            if (!empty($toUpdate))
                $this->model->updateStockQuantity($toUpdate, $locationID);
            if (!empty($toAdd))
                $this->model->addProductsInStock($toAdd, $locationID);
            return $res->renderJSON(["message" => "le Stock a été mis à jour avec succès !"]);
        }

    }

    /**
     * @param \App\Http\Request $req
     * @param \App\Http\Response $res
     */
    public function fillStock($req,$res)
    {
        $locationID = (int) $req->get("location");

        if (!$locationID ){
            http_response_code(404);
            return $res->renderJSON(["error"=> "Page not found"]);
        }
        $stock = $this->model->getProductsByLocation($locationID);

        if($stock){
            http_response_code(400);
            return $res->renderJSON(["error"=> "Impossible d'executer cette action car le depot ou le magasin contient déjà des produits !"]);
        }

        $products = $this->model->getAllProducts();
        $this->model->addProductsInStock($products, $locationID);
        return $res->renderJSON(["message"=> "Stock mise à jour"]);
    }

}
