<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 * 
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Advancednewsletter
 * @copyright  Copyright (c) 2010-2011 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */
class AW_Advancednewsletter_Model_Source_Formpositions
{
    const IN_BLOCK = 'in_block';
    const AJAX_LAYER = 'ajax_layer';
    
    public function toOptionArray()
    {
        return array(
            array('value'=> self::IN_BLOCK, 'label'=>Mage::helper('advancednewsletter')->__('In block')),
            array('value'=> self::AJAX_LAYER, 'label'=>Mage::helper('advancednewsletter')->__('AJAX layer')),
        );
    }
}