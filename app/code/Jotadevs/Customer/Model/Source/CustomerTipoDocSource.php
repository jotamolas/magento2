<?php

namespace Jotadevs\Customer\Model\Source;

class CustomerTipoDocSource extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    public function getAllOptions()
    {
        return[
          ['value' => 'DNI', 'label' => __('DNI')],
          ['value' => 'LE', 'label' => __('LE')],
          ['value' => 'LC', 'label' => __('LC')]
        ];
    }
}
