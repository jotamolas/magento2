<?php

namespace Jotadevs\BotonArrepentimiento\Block\Carga;

use Jotadevs\BotonArrepentimiento\Model\CasoFactory;
use Magento\Directory\Block\Data;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\View\Element\Template;

class Index extends Template
{
    /**
     * @var DateTime
     */
    protected $dataTime;
    /**
     * @var CasoFactory
     */
    protected $caso;
    /**
     * @var Data
     */
    protected $directoryBlock;

    /**
     * @var RegionCollectionFactory
     */
    protected $regionCollectionFactory;

    public function __construct(
        Template\Context $context,
        DateTime $dateTime,
        CasoFactory $caso,
        Data $directoryBlock,
        RegionCollectionFactory $regionCollectionFactory,
        array $data = []
    ) {
        $this->dataTime = $dateTime;
        $this->caso = $caso;
        $this->directoryBlock = $directoryBlock;
        $this->regionCollectionFactory = $regionCollectionFactory;
        parent::__construct($context, $data);
    }
    /** TODO getProvincias */
    public function getProvincias()
    {
        $html = "";
        $regions = $this->regionCollectionFactory->create()
                    ->addFieldToFilter('country_id', ['eq' => 'AR']);
        if (count($regions)>0) {
            $html .= '<option selected="selected" value=""></option>';
            foreach ($regions as $state) {
                $html .= '<option value="' . $state->getName() . '">' . $state->getName() . '</option>';
            }
        }
        return $html;
    }
}
