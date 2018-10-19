<?php
require_once('includes/API.php');

$username = 'API user'; // todo, change per setup.
$password = 'API password'; // todo change per setup.
$url = 'http://magento.dev/'; // change URL to store URL.

$api = new API($username, $password, $url); // Initiate the API request.


// To get Qty.
$sku = 'sample-sku';
$qty = $api->getQty($sku);
echo $qty;


// To update Qty by Sku
$sku = 'sample-sku';
$newQty = $qty + 1;
if($api->updateQty($sku, $newQty)){
	echo 'Successful';
}else{
	echo 'Unsuccessful';
}

// Get products from Magento.
$_products = $api->getProducts();
foreach($_products as $_product){
	// Do whatever you want with each product. All data is in plain array();
}
print_r($_products);


// Add new Product to Magento.
// Refer to for more possible options https://devdocs.magento.com/swagger/index.html#!/catalogProductRepositoryV1/catalogProductRepositoryV1SavePost
$newproduct = array("sku" => "some-sku",
  "name": "Some name",
  "attribute_set_id": 0, // Check ID from admin.
  "price": 5, // change per need.
  "status": 0, // change per need.
  "visibility": 0, // change per need.
  "type_id": "simple", // i.e. simple per need.
  "weight": 0.5
  "extension_attributes" = array(
							"stock_item" = array(
											"qty" => 10 // change per need.
											)
							),
   "custom_attributes" => array(
								array('description', 'some descriptions'),
								array('some_custom_attributes', 'some values'),
								array('dropdownvalue', $api->getDropdownValues('attribute_code','frontend-value')),
								)
  
);

$api->postProduct($newproduct); // for single product.

// For multiple products
$newproducts = array($newproduct, $newproduct, $newproduct, $newproduct); // 2d Array of similar approach which was for new product case.
$api->postProduct($newproducts); // for multiple products.


