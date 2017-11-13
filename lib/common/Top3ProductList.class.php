<?php

/**
 * Class for the <list> elements
 * 
 *
 */
class Top3ProductList extends Top3XMLElement {

  public function __construct() {
    parent::__construct("list");
  }

  /**
   * adds a product into the list and increases the attribute nbproduit of the current object
   * 
   * @param Top3XMLElement $produit
   * @param array $attrs
   * @return Top3XMLElement
   */
  public function createProduct($label, $ref, $type, $price, $nb) {
    $product = $this->createChild('produit', $label, array('ref' => $ref, 'type' => $type, 'prixunit' => $price, 'nb' => $nb));
    $nbproducts = (int)$this->getAttribute('nbproduit');
    $this->setAttribute('nbproduit', $nbproducts + $nb);

    return $product;
  }

}