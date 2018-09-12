<?php

class Vindi_Subscription_Model_Config_Installments
{
    public function toOptionArray()
    {
        $list = [];

        foreach (range(1, 12) as $i) {
            $list[] = [
                'value' => $i,
                'label' => "{$i}x",
            ];
        }

        return $list;
    }
}