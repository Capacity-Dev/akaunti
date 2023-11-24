<?php

namespace App\Controllers\Api;

use \App\Controllers\Controller;

class ProductsController extends Controller
{
    /**
     * @param \App\Http\Request $req
     * @param \App\Http\Response $res
     */
    public function searchProducts($req, $res)
    {
        $query = $req->get("q");
        if (!is_null($query)) {
            if (!$query) $data = $this->model->getLast($query);
            else $data = $this->model->search($query);
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
            foreach($data as $product){
                $newProduct = $products[($product['id'])];
                if($product['price']>0 && $product['price'] != $newProduct){
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
        if ($data) $res->renderJSON($data);
        else $res->renderJSON([]);
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
        $data = $this->model->getOverview();
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
            $products = array_values($products);
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
        $barcode = $req->get("barcode");
        if (!is_null($id) || !is_null($id)) {
            $id = (int)$id;
            if ($id) $data = $this->model->getProduct(['id' => $id]);
            else $data = $this->model->getProduct(['barcode' => $barcode]);
        } else {
            $data = $this->model->getLastModified();
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
                    $res->renderJSON(['error' => 'Impossible d\'effacer un produit ayant une quantité en stock']);
                    return;
                }
                unset($product['quantity']);
                $product = $this->model->add2Deleted($product);
                $this->model->deleteProduct($id);
                $res->renderJSON(['message' => 'product deleted !']);
            } else {
                $res->addHeader(http_response_code(400));
                $res->renderJSON(['error' => 'product doesn\'n exists']);
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
        $by = $req->get("by");
        if (!is_null($by)) {
            $data = $this->model->getSellsBy($by);
            $res->addHeader(http_response_code(201));
            $res->renderJSON($data);
        } else {
            $res->addHeader(http_response_code(400));
            $res->renderJSON(['error' => 'provide data']);
        }
    }
    /**
     * @param \App\Http\Request $req
     * @param \App\Http\Response $res
     */
    public function saveBill($req, $res)
    {
        $bill = $req->post("bill");
        $total = $req->post("total");
        if (!is_null($bill) && !is_null($total)) {
            $this->model->saveBill($bill, $total);
        } else {
            $res->addHeader(http_response_code(400));
            $res->renderJSON(['error' => 'provide complete data']);
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
            if (count(end($data))) array_pop($data); // delete last blank line 
            $hasQuantity = false;
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $barcode = $value[1];
                    if (!isset($barcodes[$barcode])) {
                        $barcodes[$barcode] = $key; //saving the barcode for the next verification

                    } else {
                        //Verify if there is conflicts on barcodes
                        $firstKey = $barcodes[$barcode];
                        $firstProductName = $data[$firstKey][0];
                        $secondProductName = $value[0];
                        $res->setHeader(http_response_code(400));
                        $res->renderJSON(['error' => "Les produits $firstProductName et $secondProductName ont le meme code-barre"]);
                        return;
                    }
                    if (count($value) < 3) {
                        $res->setHeader(http_response_code(400));
                        $line = $key + 1;
                        $res->renderJSON(['error' => "La ligne $line n'est pas complete"]);
                        return;
                    } else {
                        $data[$key][0] = trim($data[$key][0]);
                        $data[$key][2] = floatval($data[$key][2]);
                        if (isset($value[3])) {
                            $hasQuantity = true;
                            $data[$key][3] = (int) $value[3];
                        }
                    }
                } else {
                    unset($data[$key]);
                }
            }
            try {
                $this->model->importProducts($data, $hasQuantity);
                $res->renderJSON(['success' => 'data saved']);
            } catch (\Throwable $th) {
                $res->setHeader(http_response_code(500));
                throw $th;
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
            $file = fopen('php://output', 'w');
            fputcsv($file, ['produit', 'barre-code', 'prix', 'quantite'], ';');
            foreach ($data as $row) {
                fputcsv($file, $row, ';');
            }
            fclose($file);
            exit();
        }
    }
    /**
     * @param \App\Http\Request $req
     * @param \App\Http\Response $res
     */
    public function getConfigs($req, $res)
    {
        $data = $this->model->getConfigs();
        $config = array();
        foreach ($data as $item) {
            $config[($item['config_key'])] = $item['config_value'];
        }
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
                $keys = ['taux', 'tva','currency'];
            } else {
                http_response_code(400);
                $res->renderJSON(['error' => 'data is incorrect',$data ]);
                return;
            }
            foreach ($keys as $key) {
                if (!isset($data[$key])) {
                    http_response_code(400);
                    $res->renderJSON(['error' => 'data is incorrect','data'=>$key]);
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
}
