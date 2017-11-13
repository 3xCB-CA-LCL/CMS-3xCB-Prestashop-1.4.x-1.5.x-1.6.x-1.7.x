<?php

/**
 * Class for the <infocommande> elements
 * 
 *
 */
class Top3OrderDetails extends Top3XMLElement {

  public function __construct() {
    parent::__construct('infocommande');
  }

  /**
   * creates a FianetCarrier object representing element <transport>, adds it to the current element, adds sub-children, then returns it
   * 
   * @param string $name carrier name
   * @param string $type carrier type (1|2|3|4|5)
   * @param type $speed carrier speed (1 means express, 2 means standard)
   * @return FianetCarrier
   */
  public function createCarrier($name, $type, $speed) {
    $carrier = $this->addChild(new Top3Carrier());
    $carrier->createChild('nom', $name);
    $carrier->createChild('type', $type);
    $carrier->createChild('rapidite', $speed);

    return $carrier;
  }

  /**
   * creates a FianetProductList object representing element <list>, adds it to the current element, then returns it
   * 
   * @return FianetProductList
   */
  public function createProductList() {
    $product_list = $this->addChild(new Top3ProductList());
    return $product_list;
  }
  
  
	/**
	 * creates a FianetOrderProducts object representing element <produits>, adds it to the current element, then returns it
	 * 
	 * @return FianetOrderProducts
	 */
	public function createOrderProducts()
	{
		$product_list = $this->addChild(new Top3OrderProducts());
		return $product_list;
	}

}