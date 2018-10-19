<?php
/**
 *
 *	@author: 	Farhan Islam <farhan@orvisoft.com>
 *	@dated:		1st August,2018
 *	Notes:		Refer to use case found in the file.
 **/

class API{
    private $user, $pass, $url, $headers, $timeout, $dropDownScope;
	/**
	 *	@Param
	 *	$user = USERNAME of Magento ADMIN, with rights to execute restful API.
	 *	$pass = PASSWORD of Magento ADMIN, with rights to execute restful API.
	 *	$url  =	URL of store.
	 *	$timeout = 30, optional. this is for script timeout for read/ post from Magento.
	 **/
    function __construct($user,$pass,$url, $timeout = 30){
        $this->user = $user;
        $this->pass = $pass;
        $this->url = $url;
        $this->timeout = $timeout;
        self::auth();
    }
	
	/**
	 *	Authenticates the request, for proceeding with other part of API.
	 **/
    function auth(){
        $adminUrl = $this->url .'rest/V1/integration/admin/token/';
        $ch = curl_init();
        $data = array("username" => $this->user, "password" => $this->pass);

        $data_string = json_encode($data);
        $ch = curl_init($adminUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string))
        );
        $token = curl_exec($ch);
        $token=  json_decode($token);

        //Use above token into header
        $this->headers = array("Authorization: Bearer $token","Content-Type: application/json");
    }
	
	/**
	 *	Get QTY of product by SKU.
	 **/
    function getQty($sku){
        $requestUrl=$this->url .'rest/V1/products/'.urlencode($sku);
            
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, $requestUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        $response = json_decode($response, true);
        if(isset($response['extension_attributes'])){
            return $response['extension_attributes']['stock_item']['qty']; 
        }
        return false;
    }
	
	/**
	 *	Update Qty by Sku.
	 **/
    function updateQty($sku, $stock){
        $headers = $this->headers;
        $requestUrl= $this->url .'rest/V1/products/' . urlencode($sku) . '/stockItems/1';
        $sampleProductData = array(
            "qty" => $stock
        );
        
        $productData = json_encode(array('stockItem' => $sampleProductData));

        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, $requestUrl);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $productData);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        $response = json_decode($response, true);
        if((int) $response > 0){
            return true;
        }
        return false;
    }
	
	/**
	 *	Get all products, from and to. With pagination limit.
	 *	@Param
	 *	$from = 1 // Or starting point of data. (optional)
	 *	$to = 500 // or ending point of data limit. (optional)
	 **/
	function getProducts($from=1, $to=500){
		$requestUrl=$this->url .'rest/V1/products/?searchCriteria[currentPage]='.$from.'&searchCriteria[pageSize]='.$to;
		$ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, $requestUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        $response = json_decode($response, true);
        return $response;
	}
	
	/**
	 *	Post product/ Add new Product to Magento.
	 **/
	function postProduct($productData){
		$headers = $this->headers;
		$requestUrl=$this->url .'rest/V1/products';
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL, $requestUrl);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $productData);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		curl_close($ch);
		$response = json_decode($response, true);
		return $response; 
	}
	
	/** This one is for trial and as an alternative option. **/
	function putProduct($productData, $sku){
		$productData =  json_encode($productData); //exit;  
		$headers = $this->headers;
		
		$requestUrl=$this->url .'rest/V1/products/' . urlencode($sku);
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL, $requestUrl);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $productData);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		curl_close($ch);
		$response = json_decode($response, true);
		return $response; 
	}
	/** End of not tested one **/
	
	/**
	 *	Gets the values by attributes from Magento2.
	 *	@Param
	 *	$attribute_code = attribute code which needs to be tested.
	 **/
	function getAttributesByCode($attribute_code){
		$requestUrl=$this->url .'rest/V1/products/attributes/'.urlencode($attribute_code);
		$ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, $requestUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        $response = json_decode($response, true);
        return $response;
	}
	
	/**
	 *	Gets the dropdown values of the 
	 *	@Param
	 *	$attribute_code = attribute of product in Magento 2
	 *	$search = string you want to look into database.
	 *	$alternative = if not found, what you want to return?
	 **/
	function getDropdownValues($attribute_code, $search, $alternative = 0){
		$search = trim($search);
		if(isset($this->dropDownScope[$attribute_code][$search])){ // this adds possibility to return results from cache.
			return $this->dropDownScope[$attribute_code][$search];
		}
		$attribute = $this->getAttributesByCode($attribute_code);
		if(is_array($attribute) && isset($attribute['options']) && count($attribute['options'])){
			foreach($attribute['options'] as $_attribute){
				$label = trim($_attribute['label']);
				$this->dropDownScope[$attribute_code][$label] = $_attribute['value'];
			}
		}
		if(isset($this->dropDownScope[$attribute_code][$search])){ // this serves results from cache.
			return $this->dropDownScope[$attribute_code][$search];
		}
		return $alternative;
	}
}