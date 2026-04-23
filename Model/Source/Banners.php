<?php

namespace Revolut\Payment\Model\Source;

use Magento\Framework\Option\ArrayInterface;

class Banners implements ArrayInterface
{
    /**
     * To OptionArray
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'icon',
                'label' => __('Small icon')
            ],[
                'value' => 'link',
                'label' => __('Learn more')
            ],[
                'value' => 'cashback',
                'label' => __('Get Cashback')
            ],[
                'value' => 'disabled',
                'label' => __('Disabled')
            ]
        ];
    }
}
