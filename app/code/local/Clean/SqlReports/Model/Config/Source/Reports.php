<?php

/**
 * @author Lee Saferite <lee.saferite@aoe.com>
 * @since  2014-08-26
 */
class Clean_SqlReports_Model_Config_Source_Reports
{
    public function toOptionArray($multiselect = false)
    {
        $data = Mage::getSingleton('cleansql/report')->getCollection()->toOptionArray();

        if (!$multiselect) {
            array_unshift($data, array('value' => '', 'label' => Mage::helper('cleansql')->__('-- Please Select --')));
        }

        return $data;
    }
}
