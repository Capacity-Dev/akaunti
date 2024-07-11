<?php
use \Mpdf\Mpdf;

require_once('vendor/autoload.php');
// tests data
$products = array(
    ["p_name" => "TEST-1 wurejh sdheurjenf sdjs","p_price" => 12000, "p_quantity" => 60],
    ["p_name" => "TEST-2","p_price" => 12000, "p_quantity" => 60],
    ["p_name"=> "TEST-3","p_price"=> 12000,"p_quantity" => 126],
);

$mpdf = new Mpdf();

$html_template = file_get_contents('views/bill.html');
$products_items_html = "";

foreach ($products as $product) {
    $p_name = $product["p_name"];
    $p_price = $product["p_price"];
    $p_quantity = $product["p_quantity"];
    $p_total = $p_price * $p_quantity;
    $products_items_html .= "
        <tr>
            <td style=\"text-align: left;border-left: none;\">$p_name</td>
            <td>$p_quantity</td>
            <td>$p_price</td>
            <td>$p_total</td>
        </tr>
    ";
}
$html_template = str_replace("{products_list}", $products_items_html, $html_template);

$mpdf->WriteHTML($html_template);
$mpdf->OutputFile("test.pdf");