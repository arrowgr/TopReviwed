<?php
namespace silici0\TopReviwed\Block\Widget;

class TopReviwed extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{

    protected $_days = 270;

    protected $_template = 'widget/top_reviwed.phtml';

    protected $_productRepository;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Review\Model\ResourceModel\Review\Product\CollectionFactory $_reviewsColFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        $this->_reviewsColFactory = $_reviewsColFactory;
        $this->_productRepository = $productRepository;
        parent::__construct($context);
    }

    public function getCollection()
    {
        $collection = $this->_reviewsColFactory->create()->addStoreFilter(
                $this->_storeManager->getStore()->getId()
            )->addStatusFilter(
                \Magento\Review\Model\Review::STATUS_APPROVED
            )->setDateOrder();

        $arr = array();
        foreach ($collection as $_review) {
            $days = $this->returnDays($_review);
            if ($days <= $this->_days) {
                if (!isset($arr[$_review->getSku()])) {
                    $arr[$_review->getSku()]['cont'] = 1;
                } else {
                    $arr[$_review->getSku()]['cont']++;
                }
                $arr[$_review->getSku()]['productUrl'] = $_review->getProductUrl();
                $arr[$_review->getSku()]['productName'] = $_review->getName();
                $_product = $this->loadProduct($_review->getId());
                $arr[$_review->getSku()]['productImg'] = $this->getProductImageUrl($_product);
                $arr[$_review->getSku()]['productPrice'] = $_product->getPrice();
                $arr[$_review->getSku()]['productSpecialPrice'] = $arr[$_review->getSku()]['productPrice']/12;

            }
        }
        arsort($arr);
            
        return $arr;
    }


    protected function getProductImageUrl($product)
    {
        $imageBlock =  $this->getLayout()->createBlock('Magento\Catalog\Block\Product\ListProduct');
        $productImage = $imageBlock->getImage($product, 'category_page_grid');
        return $productImage->toHtml();
    }

    protected function getMediaBaseUrl()
    {
        return $this->_storeManager
                    ->getStore()
                    ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    protected function loadProduct($id)
    {
        return $this->_productRepository->getById($id);
    }

    protected function returnDays($_review) 
    {
        $today = date('d-m-Y',time());
        $exp = date('d-m-Y',strtotime($_review->getCreatedAt())); //query result form database
        $expDate =  date_create($exp);
        $todayDate = date_create($today);
        $diff =  date_diff($todayDate, $expDate);
        return $diff->format("%a");
    }
}
?>