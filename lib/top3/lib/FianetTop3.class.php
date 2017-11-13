<?php

/**
 * Class for the <wallet> element
 *
 *
 */
class FianetTop3 extends Top3XMLElement {
  const TOP3_VERSION = "1.0";
  const CRYPT_VERSION = "3.0";

  public function __construct() {
    parent::__construct('top3');
  }

  public function addDateLivr($datelivr) {
    return $this->createChild('datelivr', $datelivr);
  }

  public function addCrypt($crypt) {
    return $this->createChild('crypt', $crypt, array('version'=>self::CRYPT_VERSION));
  }

}