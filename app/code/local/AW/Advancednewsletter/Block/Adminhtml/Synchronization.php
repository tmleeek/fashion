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
class AW_Advancednewsletter_Block_Adminhtml_Synchronization extends Mage_Adminhtml_Block_Template
{
    const SYNC_TO_MAILCHIMP = 'tochimp';
    const SYNC_FROM_MAILCHIMP = 'fromchimp';

    const SYNC_SUBSCRIBED = 'subscribed';
    const SYNC_UNSUBSCRIBED = 'unsubscribed';
    const SYNC_SUBSCRIBED_AND_UNSUBSCRIBED = 'subscribed_unsubscribed';

    const SYNC_STATUSES = 'sync_statuses';
    const SYNC_LIST = 'sync_list';

    public function _toHtml()
    {
        $request = Mage::app()->getRequest();
        if ($request->getParam('type') == self::SYNC_TO_MAILCHIMP)
			$this->setTemplate("advancednewsletter/sync/tochimp.phtml");
		else if ($request->getParam('type') == self::SYNC_FROM_MAILCHIMP)
			$this->setTemplate("advancednewsletter/sync/fromchimp.phtml");
        return parent::_toHtml();
    }

    public function getSyncToMailchimpUrl()
    {
        return $this->getUrl('*/*/edit', array('type' => self::SYNC_TO_MAILCHIMP));
    }

    public function getSyncFromMailchimpUrl()
    {
        return $this->getUrl('*/*/edit', array('type' => self::SYNC_FROM_MAILCHIMP));
    }

    public function getBackUrl()
    {
        return $this->getUrl('*/*/');
    }

    public function getImportNewsletterTemplatesUrl()
    {
        return $this->getUrl('*/*/newsletterTemplatesImport');
    }

    public function getImportNewsletterSubscribersUrl()
    {
        return $this->getUrl('*/*/newsletterSubscribersImport');
    }

}
