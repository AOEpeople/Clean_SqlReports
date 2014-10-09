<?php

/**
 * @author Lee Saferite <lee.saferite@aoe.com>
 * @since  2014-09-14
 */
class Clean_SqlReports_Model_Api extends Mage_Catalog_Model_Api_Resource
{
    public function listReports()
    {
        /** @var Clean_SqlReports_Model_Mysql4_Report_Collection $reports */
        $reports = Mage::getSingleton('cleansql/report')->getCollection();

        $reports->removeAllFieldsFromSelect();
        $reports->removeFieldFromSelect($reports->getResource()->getIdFieldName());

        $reports->addFieldToSelect($reports->getResource()->getIdFieldName(), 'id');
        $reports->addFieldToSelect('title');

        return $reports->getData();
    }

    public function runReport($reportId, $startDate = null, $stopDate = null)
    {
        /* @var Clean_SqlReports_Model_Report $report */
        $report = Mage::getModel('cleansql/report')->load($reportId);
        if (!$report->getId()) {
            $this->_fault('report_not_found');
        }

        if ($report->hasReportingStartDate() && !$startDate) {
            $this->_fault('report_start_date_missing');
        }

        if ($report->hasReportingStopDate() && !$stopDate) {
            $this->_fault('report_stop_date_missing');
        }

        $result = $report->run(array('start_date' => $startDate, 'end_date' => $stopDate));

        return $this->downloadResult($reportId, $result->getId());
    }

    public function listResults($reportId)
    {
        /** @var Clean_SqlReports_Model_Mysql4_Result_Collection $results */
        $results = Mage::getSingleton('cleansql/result')->getCollection();

        $results->addFieldToFilter('report_id', intval($reportId));

        $results->removeAllFieldsFromSelect();
        $results->addFieldToSelect('created_at');
        $results->addFieldToSelect('start_date');
        $results->addFieldToSelect('end_date');

        return $results->getData();
    }

    public function downloadResult($reportId, $resultId = null)
    {
        /* @var Clean_SqlReports_Model_Report $report */
        $report = Mage::getModel('cleansql/report')->load($reportId);
        if (!$report->getId()) {
            $this->_fault('report_not_found');
        }

        $resultId = intval($resultId);

        if($resultId) {
            /* @var Clean_SqlReports_Model_Result $result */
            $result = Mage::getModel('cleansql/result')->load($resultId);
        } else {
            /** @var Clean_SqlReports_Model_Mysql4_Result_Collection $results */
            $results = Mage::getSingleton('cleansql/result')->getCollection();
            $results->addFieldToFilter('report_id', intval($reportId));
            $results->addOrder('created_at', 'DESC');
            $result = $results->getFirstItem();
        }

        if (!$result->getId() || $result->getReportId() != $report->getId()) {
            $this->_fault('result_not_found');
        }

        return $result->getResultCollection()->getData();
    }
}
