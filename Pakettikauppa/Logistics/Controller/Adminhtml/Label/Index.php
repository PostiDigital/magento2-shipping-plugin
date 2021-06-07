<?php


namespace Pakettikauppa\Logistics\Controller\Adminhtml\Label;

use Pakettikauppa\Logistics\Helper\Api;

class Index extends \Magento\Backend\App\Action
{
    protected $api;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        API $api
    ) {
        $this->api = $api;
        parent::__construct($context);
    }

    /**
     * Load the page defined in view/adminhtml/layout/exampleadminnewpage_helloworld_index.xml
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $trackingCode = $this->getRequest()->getParam('tracking_code');

        $label = $this->api->getLabel($trackingCode);

        header('Content-type:application/pdf');
        header("Content-Disposition:attachment;filename={$trackingCode}.pdf");

        echo base64_decode($label->{"response.file"});

        exit;

    }
}
