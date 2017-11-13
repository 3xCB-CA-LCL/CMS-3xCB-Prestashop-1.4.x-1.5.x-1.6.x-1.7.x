<?php

class FianetTop3Control extends Top3Control
{

	public function createTop3()
	{
		$top3 = $this->root->appendChild(new FianetTop3());
		$top3->addAttribute('version', FianetTop3::TOP3_VERSION);
		
		return $top3;
	}

}