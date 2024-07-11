<?php

namespace App\Models;

class ProductsModel extends Model
{


    public function insertProducts($products)
    {
        /* // create the ?,? sequence for a single row
        $values = '?,?,?,?';
        // construct the entire query
        $sql = "INSERT INTO products (product_name,barcode,price,quantity) VALUES " .
            // repeat the (?,?) sequence for each row
            str_repeat("($values),", count($products) - 1) . "($values)";

        $stmt = $this->prepare($sql, array_merge(...$products), false); */
        $pdo = $this->db->getPDO();
        $statement = $pdo->prepare("INSERT INTO products (product_name,barcode,price,purchase_price,quantity) VALUES (:product_name,:barcode,:price,:purchase_price,:quantity)");
        foreach ($products as $product) {
            $statement->bindValue(':quantity', $product['quantity'], \PDO::PARAM_INT);
            $statement->bindValue(':product_name', $product['product_name'], \PDO::PARAM_STR);
            $statement->bindValue(':barcode', $product['barcode'], \PDO::PARAM_STR);
            $statement->bindValue(':price', $product['price'], \PDO::PARAM_INT);
            $statement->bindValue(':purchase_price', $product['purchase_price'], \PDO::PARAM_INT);
            $statement->execute();
        }
    }
    public function importProducts($products, $hasQuantity)
    {
        if ($hasQuantity) {
            $values = '?,?,?,?';
            $sql = "INSERT INTO products (product_name,barcode,price,quantity) VALUES ";
        } else {
            $values = '?,?,?';
            $sql = "INSERT INTO products (product_name,barcode,price) VALUES ";
        }

        $sql .= str_repeat("($values),", count($products) - 1) . "($values)";
        var_dump($sql);
        try {
            $this->prepare($sql, array_merge(...$products), false);
        } catch (\Throwable $th) {
            return $th;
        }
    }
    public function getAllProducts()
    {
        $statement = $this->query("SELECT id,product_name,barcode,price,quantity FROM products ORDER BY product_name");
        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }
    public function getProductsByLocation($locationID)
    {
        $statement = $this->prepare("SELECT p.id,p.product_name,p.barcode,p.price,s.quantity FROM stock s
            INNER JOIN products p ON p.id = s.product_id 
            WHERE s.location_id = :location_id", ["location_id" => $locationID]);
        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }
    public function search($product)
    {
        $query = "%$product%";
        $statement = $this->prepare("SELECT * FROM products WHERE product_name LIKE :product LIMIT 50", ["product" => $query]);
        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }
    public function searchOnLocation($product, $locationID)
    {
        $query = "%$product%";
        $statement = $this->prepare("SELECT p.id,p.product_name,p.barcode,p.price,s.quantity FROM stock s
            INNER JOIN products p ON p.id = s.product_id 
            WHERE s.location_id = :location_id AND p.product_name LIKE :product", ["location_id" => $locationID, "product" => $query]);
        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }
    public function getLast()
    {

        $statement = $this->query("SELECT id,product_name,price,quantity FROM products ORDER BY updated_at DESC LIMIT 10");
        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }
    public function addReceipt($data)
    {
        $this->prepare("INSERT INTO receipts (products) VALUES(:products)", ["products" => $data]);
    }
    public function updateStockQuantity($products, $locationID, $decrement = false)
    {
        $pdo = $this->db->getPDO();
        $quantity_str = $decrement ? "quantity-:quantity" : "quantity+:quantity";
        $statement = $pdo->prepare("UPDATE stock SET quantity =$quantity_str WHERE product_id=:id AND location_id = :location_id");
        foreach ($products as $product) {
            $statement->bindValue(':quantity', $product['quantity'], \PDO::PARAM_INT);
            $statement->bindValue(':id', $product['id'], \PDO::PARAM_INT);
            $statement->bindValue(':location_id', $locationID, \PDO::PARAM_INT);
            $statement->execute();
        }
    }
    public function addProductsInStock($products, $locationID)
    {
        $pdo = $this->db->getPDO();
        $statement = $pdo->prepare("INSERT INTO stock (location_id,product_id,quantity) VALUES(:location_id,:product_id,:quantity)");
        foreach ($products as $product) {
            $statement->bindValue(':location_id', $locationID, \PDO::PARAM_INT);
            $statement->bindValue(':product_id', $product['id'], \PDO::PARAM_INT);
            $statement->bindValue(':quantity', $product['quantity'], \PDO::PARAM_INT);
            $statement->execute();
        }
    }
    public function getMostSelled($shopID)
    {
        return $this->query("SELECT p.product_name,
        SUM(s.quantity) AS totalQuantity FROM sells s INNER JOIN products p ON product_id = id GROUP BY product_id ORDER BY totalQuantity DESC LIMIT 10 ")
            ->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getSellsForLastSixMonths($shopID) {
        $sql = "SELECT
                    DATE_FORMAT(b.created_at, '%Y-%m') AS month,
                    SUM(s.price * s.quantity) AS total_sales
                FROM
                    bills b
                JOIN
                    sells s ON b.bill_id = s.bill_id
                WHERE
                    b.shop_id = :shop_id
                    AND b.created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                GROUP BY
                    month
                ORDER BY
                    month;
                ";
        return $this->prepare($sql,["shop_id" => $shopID])->fetchAll(\PDO::FETCH_ASSOC);
    }
    public function getLasReceipt()
    {
        return $this->query("SELECT * FROM receipts ORDER BY id DESC LIMIT 1")->fetch(\PDO::FETCH_ASSOC);
    }
    public function getOverview($shopID)
    {
        $month = date('m');
        $year = date('Y');
        $day = date('d');
        $products = $this->prepare("SELECT s.location_id, COUNT(p.id) as totalProducts, SUM(s.quantity) as totalQuantity, SUM(p.price*s.quantity) as stockValue
            FROM stock s INNER JOIN products p ON s.product_id = p.id WHERE s.location_id = :location_id",["location_id" => $shopID])
            ->fetch(\PDO::FETCH_ASSOC);
        $MonthlySells = $this->prepare("SELECT SUM(sells.quantity) as totalSell,SUM(price) as products_price
        FROM sells INNER JOIN stock ON sells.product_id = stock.product_id WHERE MONTH(sells.created_at) = $month AND YEAR(sells.created_at) = $year AND location_id = :shop_id",["shop_id" => $shopID])
            ->fetch(\PDO::FETCH_ASSOC);
        $lastSIX = $this->getSellsForLastSixMonths($shopID);
        $MonthlyValues = [
            "totalSell" => $MonthlySells['totalSell'],
            'monthValue' => ($MonthlySells['products_price'] * $MonthlySells['totalSell']),
            "last_month_sells" => $lastSIX
        ];
        /* $daylySells = $this->query("SELECT COUNT(product_name) as daySell,COUNT(price) as daylyValue
        FROM sells WHERE STRFTIME(\"%d\", created_at) = $day AND MONTH(created_at) = $month AND YEAR(created_at) = $year") 
            ->fetch(\PDO::FETCH_ASSOC);*/
        return array_merge($products, $MonthlyValues);
    }
    public function getLastModified()
    {
        return $this->query("SELECT * FROM products LIMIT 200")->fetchAll(\PDO::FETCH_ASSOC);
    }
    public function getProduct($data)
    {
        $key = (array_keys($data))[0];
        return $this->prepare("SELECT * FROM products WHERE $key=:$key ", $data)->fetch(\PDO::FETCH_ASSOC);
    }
    public function updateProduct($product)
    {
        $this->prepare("UPDATE products
        SET product_name=:product_name, price=:price,quantity=:quantity,updated_at=:updated_at
        WHERE id=:id ", $product);
    }
    public function deleteProduct($id)
    {
        $this->prepare("DELETE FROM products
        WHERE id=:id ", ['id' => $id]);
    }
    public function add2Deleted($data)
    {
        $productExists = $this->prepare("SELECT barcode FROM deleted_products WHERE barcode=:barcode ", ['barcode' => $data["barcode"]])->fetch() ? true : false;
        if ($productExists) {
            return $this->prepare("UPDATE deleted_products SET created_at = NOW() WHERE barcode=:barcode ", ['barcode' => $data["barcode"]]);
        }
        $this->prepare("INSERT INTO deleted_products (product_name,barcode,price,purchase_price) VALUES (:product_name,:barcode,:price,:purchase_price)", $data);
    }
    /**
     * @param string $by 'm' for month or 'd' for day
     * @param number[] $date an array that contains the date for filter ['d','m','y']
     */
    public function getSellsBy($from, $to, $shop)
    {
        /* $bills = $this->prepare("SELECT b.bill_id as bill,total, COUNT(s.id) as total_products, b.created_at as created_at FROM bills b JOIN sells s ON b.bill_id = s.bill_id WHERE b.created_at BETWEEN :start_d AND :end_d GROUP BY s.bill_id,b.bill_id", ["start_d" => $from, "end_d" => $to])
            ->fetchAll(\PDO::FETCH_ASSOC); */
        $sql = "
            SELECT 
                b.bill_id, 
                b.user_id, 
                b.discount, 
                b.total, 
                s.product_id, 
                p.product_name, 
                s.price, 
                s.quantity,
                b.created_at
            FROM 
                bills b
            LEFT JOIN 
                sells s ON b.bill_id = s.bill_id
            LEFT JOIN 
                products p ON s.product_id = p.id
            WHERE b.shop_id = :shop_id AND b.created_at BETWEEN :start_d AND :end_d
            ";
        $result = $this->prepare($sql, ["start_d" => $from, "end_d" => $to, "shop_id" => $shop]);
        $bills = [];
        $billProducts = [];
        foreach ($result as $row) {
            $billId = $row["bill_id"];
            if (!isset($billProducts[$billId])) {
                $billProducts[$billId] = [
                    "bill_id" => $billId,
                    "user_id" => $row["user_id"],
                    "discount" => $row["discount"],
                    "created_at" => $row["created_at"],
                    "total" => $row["total"],
                    "products" => []
                ];
            }
            if (!empty($row["product_id"])) {
                $billProducts[$billId]["products"][] = [
                    "product_id" => $row["product_id"],
                    "product_name" => $row["product_name"],
                    "price" => $row["price"],
                    "quantity" => $row["quantity"]
                ];
            }
        }

        foreach ($billProducts as $bill) {
            $bills[] = $bill;
        }
        $total = $this->prepare("SELECT SUM(total) as total FROM bills WHERE shop_id = :shop_id AND created_at BETWEEN :start_d AND :end_d", ["start_d" => $from, "end_d" => $to, "shop_id" => $shop])
            ->fetch(\PDO::FETCH_ASSOC);
        $bills = $bills ? $bills : [];
        return ['bills' => $bills, 'total' => $total['total']];
    }
    public function getBill($id)
    {
        $bill = $this->prepare("SELECT * FROM bills WHERE bill_id = :bill_id", ["bill_id" => $id])->fetch(\PDO::FETCH_ASSOC);
        if (!$bill) return false;
        $products = $this->prepare("SELECT p.id,p.product_name,s.quantity FROM products p INNER JOIN sells s ON s.product_id = p.id WHERE s.bill_id = :bill_id", ["bill_id" => $id])->fetchAll(\PDO::FETCH_ASSOC);
        $bill["products"] = $products;
        return $bill;
    }
    public function saveBill($bill)
    {
        $products = $bill["products"];
        $discount = $bill["discount"];
        $shopID = $bill["shop_id"];
        $total = $bill["total"];
        $bill_id = $bill["bill_id"];

        $pdo = $this->db->getPDO();
        $this->updateStockQuantity($products, $shopID, true);
        //saving the bill
        $bill_data = [
            "bill_id" => $bill_id,
            "shop_id" => $shopID,
            "discount" => $discount,
            "total" => (float)$total
        ];
        if (isset($bill["date"])) $bill_data["created_at"] = $bill["date"];
        if ($bill["user_id"]) $bill_data["user_id"] = $bill["user_id"];
        $bill_keys = join(",", array_keys($bill_data));
        $bill_values = join(",", array_map(function ($value) {
            return ":$value";
        }, array_keys($bill_data)));
        $this->prepare("INSERT INTO bills ($bill_keys) VALUES($bill_values)", $bill_data);

        //then add products from sells table
        $statement2 = $pdo->prepare("INSERT INTO sells (bill_id,product_id,price,quantity" . (isset($bill["date"]) ? ",created_at" : "") . ") VALUES(:bill_id,:product_id,:price,:quantity" . (isset($bill["date"]) ? ",:created_at" : "") . ")");
        foreach ($products as $product_id => $product) {
            $statement2->bindValue(':bill_id', $bill_id, \PDO::PARAM_STR);
            $statement2->bindValue(':product_id', $product_id, \PDO::PARAM_INT);
            $statement2->bindValue(':price', $product['price'], \PDO::PARAM_INT);
            $statement2->bindValue(':quantity', $product['quantity'], \PDO::PARAM_INT);
            isset($bill["date"]) ? $statement2->bindValue(':created_at', $bill['date'], \PDO::PARAM_STR) : null;
            $statement2->execute();
        }
        $statement2->closeCursor();
        unset($statement2);
    }
    public function deleteBill($data)
    {
        $this->query("DELETE FROM bills WHERE bill_id = " . $data["bill_id"]);
        $this->prepare("INSERT INTO deleted_bills (brut_data) VALUES(:brut_data)", ["brut_data" => json_encode($data)]);
    }
    public function getConfigs()
    {
        $data = $this->query("SELECT * FROM configurations")->fetchAll(\PDO::FETCH_ASSOC);
        $config = array();
        foreach ($data as $item) {
            $config[($item['config_key'])] = $item['config_value'];
        }
        return $config;
    }
    public function updateConfigs($keys, $data)
    {
        $pdo = $this->db->getPDO();
        $statement = $pdo->prepare("UPDATE configurations SET config_value =:config_value, updated_at=CURRENT_TIMESTAMP WHERE config_key=:config_key");
        foreach ($keys as $key) {
            $statement->bindValue(':config_value', $data[$key], \PDO::PARAM_STR);
            $statement->bindValue(':config_key', $key, \PDO::PARAM_STR);
            $statement->execute();
        }
    }
    public function getManyByID($IDs)
    {
        $pdo = $this->db->getPDO();
        $placeholders = implode(',', array_fill(0, count($IDs), '?'));
        $sql = "SELECT * FROM products WHERE id IN ($placeholders)";
        $stmt = $pdo->prepare($sql);

        // Exécution de la requête avec les valeurs du tableau
        $stmt->execute($IDs);

        // Récupération des résultats

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    public function fetchLocations($type)
    {
        if (is_null($type)) {
            return $this->query("SELECT * FROM locations")->fetchAll(\PDO::FETCH_ASSOC);
        }
        return $this->prepare("SELECT * FROM locations WHERE location_type = :t", ["t" => $type])->fetchAll(\PDO::FETCH_ASSOC);
    }
    public function getLocation($data)
    {
        $key = array_keys($data)[0];
        return $this->prepare("SELECT * FROM locations WHERE $key = :$key", $data)->fetch(\PDO::FETCH_ASSOC);
    }
    public function newLocation($data)
    {
        $shop_keys = join(",", array_keys($data));
        $shop_values = join(",", array_map(function ($value) {
            return ":$value";
        }, array_keys($data)));
        $this->prepare("INSERT INTO locations ($shop_keys) VALUES($shop_values)", $data);
    }
}
