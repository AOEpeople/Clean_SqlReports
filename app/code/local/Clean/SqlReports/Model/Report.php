<?php

/**
 * @method string getCreatedAt()
 * @method Clean_SqlReports_Model_Report setCreatedAt(string $value)
 * @method string getTitle()
 * @method Clean_SqlReports_Model_Report setTitle(string $value)
 * @method string getSqlQuery()
 * @method Clean_SqlReports_Model_Report setSqlQuery(string $query)
 * @method getOutputType()
 * @method Clean_SqlReports_Model_Report setOutputType($value)
 * @method Clean_SqlReports_Model_Report setStartDate(string $value)
 * @method string getStartDate()
 * @method Clean_SqlReports_Model_Report setEndDate(string $value)
 * @method string getEndDate()
 *
 * @method Clean_SqlReports_Model_Report setChartConfig($value)
 */
class Clean_SqlReports_Model_Report extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('cleansql/report');
    }

    /**
     * Run this report
     *
     * @param array $data
     *
     * @return Clean_SqlReports_Model_Result
     *
     * @author Lee Saferite <lee.saferite@aoe.com>
     */
    public function run($data = array())
    {
        /** @var Clean_SqlReports_Model_Result $result */
        $result = Mage::getModel('cleansql/result');
        $result->setReportId($this->getId());
        $result->setColumnConfig($this->getColumnConfig());

        $createdAt = Mage::getSingleton('core/date')->gmtDate();
        $result->setCreatedAt($createdAt);

        if (isset($data['start_date'])) {
            $startDate = Mage::getModel('core/date')->date('Y-m-d',strtotime($data['start_date']));
            $result->setStartDate($startDate);
        }
        if (isset($data['end_date'])) {
            $endDate = Mage::getModel('core/date')->date('Y-m-d',strtotime($data['end_date']));
            $result->setEndDate($endDate);
        }

        $result->save();

        return $result;
    }

    /**
     * Check if the report sql contains reporting period variables
     *
     * @return bool
     */
    public function hasReportingPeriod()
    {
        return ($this->hasReportingStartDate() || $this->hasReportingStopDate());
    }

    /**
     * Check if the report sql contains reporting period variables
     *
     * @return bool
     */
    public function hasReportingStartDate()
    {
        return (strpos($this->getSqlQuery(), '@start_date') !== false);
    }

    /**
     * Check if the report sql contains reporting period variables
     *
     * @return bool
     */
    public function hasReportingStopDate()
    {
        return (strpos($this->getSqlQuery(), '@end_date') !== false);
    }

    /**
     * @return Clean_SqlReports_Model_Result
     *
     * @author Lee Saferite <lee.saferite@aoe.com>
     */
    public function getLatestResult()
    {
        return Mage::getModel('cleansql/result')->getCollection()
            ->addFieldToFilter('report_id', $this->getId())
            ->addOrder('created_at', 'DESC')
            ->getFirstItem();
    }

    protected function _beforeSave()
    {
        parent::_beforeSave();

        $columnConfig = trim($this->getColumnConfig());
        $this->setColumnConfig($columnConfig);

        return $this;
    }

    /**
     * Delete object from database
     *
     * @return Mage_Core_Model_Abstract
     */
    public function delete()
    {
        Mage::getModel('cleansql/result')->getCollection()
            ->addFieldToFilter('report_id', $this->getId())
            ->walk('delete');

        return parent::delete();
    }
}
