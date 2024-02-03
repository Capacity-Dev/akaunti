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
        try {
            $this->prepare($sql, array_merge(...$products), false);
        } catch (\Throwable $th) {
            return $th;
        }
    }
    public function getAllProducts()
    {
        $statement = $this->query("SELECT product_name,barcode,price,quantity FROM products ORDER BY product_name");
        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }
    public function search($product)
    {
        $query = "%$product%";
        $statement = $this->prepare("SELECT * FROM products WHERE product_name LIKE :product LIMIT 50", ["product" => $query]);
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
    public function updateQuantity($products)
    {
        $pdo = $this->db->getPDO();
        $statement = $pdo->prepare("UPDATE products SET quantity =quantity+:quantity WHERE id=:id");
        foreach ($products as $product) {
            $statement->bindValue(':quantity', $product['quantity'], \PDO::PARAM_INT);
            $statement->bindValue(':id', $product['id'], \PDO::PARAM_INT);
            $statement->execute();
        }
    }
    public function getMostSelled()
    {
        return $this->query("SELECT product_name,
        SUM(quantity) AS totalQuantity FROM sells GROUP BY product_name ORDER BY totalQuantity DESC LIMIT 10 ")
            ->fetchAll(\PDO::FETCH_ASSOC);
    }
    public function getLasReceipt()
    {
        return $this->query("SELECT * FROM receipts ORDER BY id DESC LIMIT 1")->fetch(\PDO::FETCH_ASSOC);
    }
    public function getOverview()
    {
        $month = date('m');
        $year = date('Y');
        $day = date('d');
        $products = $this->query("SELECT COUNT(id) as totalProducts, SUM(quantity) as totalQuantity, SUM(price*quantity) as stockValue
            FROM products")
            ->fetch(\PDO::FETCH_ASSOC);
        $MonthlySells = $this->query("SELECT SUM(quantity) as totalSell,SUM(price) as products_price
        FROM sells WHERE MONTH(created_at) = $month AND YEAR(created_at) = $year")
            ->fetch(\PDO::FETCH_ASSOC);
        $MonthlyValues = [
            "totalSell" => $MonthlySells['totalSell'],
            'monthValue' => ($MonthlySells['products_price'] * $MonthlySells['totalSell'])
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
        $productExists = $this->prepare("SELECT barcode FROM deleted_products
        WHERE barcode=:barcode ", ['barcode' => $data["barcode"]])->fetch()?true:false;
        if($productExists) {
            return $this->prepare("UPDATE deleted_products SET created_at = NOW() WHERE barcode=:barcode ", ['barcode' => $data["barcode"]]);
        }
        $this->prepare("INSERT INTO deleted_products (product_name,barcode,price,purchase_price) VALUES (:product_name,:barcode,:price,:purchase_price)", $data);
    }
    public function getSellsBy($by)
    {
        
        $month = date('m');
        $year = date('Y');
        $day = date('d');
        if ($by == "d") {
            $products = $this->query("SELECT product_name, SUM(price) as totalPrice, SUM(quantity) as totalQuantity FROM sells WHERE DAY(created_at) = $day AND MONTH(created_at) = $month AND YEAR(created_at) = $year GROUP BY product_name")
            ->fetchAll(\PDO::FETCH_ASSOC);
            
            $total = $this->query("SELECT SUM(price) as total, SUM(quantity) as total_quantity FROM sells WHERE DAY(created_at) = $day AND MONTH(created_at) = $month AND YEAR(created_at) = $year")
            ->fetch(\PDO::FETCH_ASSOC);
            $products = $products ? $products : [];
            return ['products' => $products, 'total' => ($total['total']*$total['total_quantity'])];
        } else if ($by == "m") {
            $products = $this->query("SELECT product_name, SUM(price) as totalPrice, SUM(quantity) as totalQuantity FROM sells WHERE MONTH(created_at) = $month AND YEAR(created_at) = $year GROUP BY product_name")
            ->fetchAll(\PDO::FETCH_ASSOC); 
            
            $total = $this->query("SELECT SUM(price) as total, SUM(quantity) as total_quantity FROM sells WHERE MONTH(created_at) = $month AND YEAR(created_at)")
            ->fetch(\PDO::FETCH_ASSOC);

            $products = $products ? $products : [];
            return ['products' => $products, 'total' => ($total['total']*$total['total_quantity'])];
        } else {
            return [];
        }
    }
    function saveBill($bill, $total)
    {
        //updating products
        $pdo = $this->db->getPDO();
        $statement1 = $pdo->prepare("UPDATE products SET quantity =quantity-:quantity WHERE id=:id");
        foreach ($bill as $product) {
            $statement1->bindValue(':quantity', ($product['quantity']), \PDO::PARAM_INT);
            $statement1->bindValue(':id', $product['id'], \PDO::PARAM_INT);
            $statement1->execute();
        }
        $statement1->closeCursor();
        unset($statement1);
        //add products from sells table
        $statement2 = $pdo->prepare("INSERT INTO sells (product_name,price,quantity) VALUES(:product_name,:price,:quantity)");
        foreach ($bill as $product) {
            $statement2->bindValue(':product_name', $product['product_name'], \PDO::PARAM_STR);
            $statement2->bindValue(':price', $product['price'], \PDO::PARAM_INT);
            $statement2->bindValue(':quantity', $product['quantity'], \PDO::PARAM_INT);
            $statement2->execute();
        }
        $statement2->closeCursor();
        unset($statement2);

        //then save the bill (brut data)
        $billJson = json_encode($bill);
        $this->prepare("INSERT INTO bills (products,total) VALUES(:products,:total)", [
            "products" => $billJson,
            "total" => (int)$total
        ]);
    }
    public function getConfigs()
    {
        return $this->query("SELECT * FROM configurations")->fetchAll(\PDO::FETCH_ASSOC);
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
}
