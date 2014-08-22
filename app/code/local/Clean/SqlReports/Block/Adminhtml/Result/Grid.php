<?php

class Clean_SqlReports_Block_Adminhtml_Result_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $_sqlQueryResults;

    public function __construct()
    {
        parent::__construct();

        $this->setId('reportsGrid');
        $this->addExportType('*/*/exportCsv', $this->__('CSV'));
    }

    /**
     * @return Clean_SqlReports_Model_Result
     */
    protected function getResult()
    {
        return $this->_getHelper()->getCurrentResult();
    }

    /**
     * @return Clean_SqlReports_Helper_Data
     *
     * @author Lee Saferite <lee.saferite@aoe.com>
     */
    protected function _getHelper()
    {
        return Mage::helper('cleansql');
    }

    protected function _prepareCollection()
    {
        if (!$this->getCollection()) {
            $this->setCollection($this->getResult()->getResultCollection());
        }

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $columnConfig = array();

        // Parse the column config
        $rawColumnConfig = trim($this->getResult()->getColumnConfig());
        if($rawColumnConfig[0] === '{') {
            try {
                $columnConfig = Zend_Json::decode($rawColumnConfig);
            } catch(Zend_Json_Exception $e) {
                Mage::logException($e);
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        } else {
            $rawColumnConfig = array_filter(array_map('trim', explode("\n", str_replace(',', "\n", $rawColumnConfig))));
            foreach ($rawColumnConfig as $entry) {
                $entry = explode(':', trim($entry));
                if(empty($entry[0])) {
                    continue;
                }
                $columnConfig[$entry[0]] = array(
                    'type'   => (isset($entry[1]) && !empty($entry[1]) ? $entry[1] : null),
                    'name'   => (isset($entry[2]) && !empty($entry[2]) ? $entry[2] : null),
                    'filter' => (isset($entry[3]) ? (bool)$entry[3] : null),
                    'sort'   => (isset($entry[4]) ? (bool)$entry[4] : true),
                );
            }
        }

        /** @var Varien_Db_Adapter_Interface $connection */
        $connection = $this->getResult()->getResource()->getReadConnection();
        $tableInfo = $connection->describeTable($this->getResult()->getResultTable());
        foreach ($tableInfo as $columnKey => $columnData) {
            // Load column config
            $config = (isset($columnConfig[$columnKey]) ? $columnConfig[$columnKey] : array());

            // Ensure these base settings are defined
            $config['index'] = $columnKey;
            $config['type'] = (isset($columnConfig[$columnKey]['type']) ? $columnConfig[$columnKey]['type'] : $this->mapDdlTypeToColumnType($columnData['DATA_TYPE']));
            $config['header'] = (isset($columnConfig[$columnKey]['name']) ? $columnConfig[$columnKey]['name'] : Mage::helper('core')->__($columnKey));
            $config['filter'] = (isset($columnConfig[$columnKey]['filter']) && !$columnConfig[$columnKey]['filter'] ? false : null);
            $config['sortable'] = (isset($columnConfig[$columnKey]['sort']) ? $columnConfig[$columnKey]['sort'] : true);

            // Add column
            $this->addColumn($columnKey, $config);
        }

        return parent::_prepareColumns();
    }

    protected function mapDdlTypeToColumnType($ddlType)
    {
        $type = 'text';

        switch ($ddlType) {
            case 'date':
                $type = 'date';
                break;
            case 'timestamp':
            case 'datetime':
                $type = 'datetime';
                break;
            case 'int':
            case 'decimal':
            case 'float':
                $type = 'number';
                break;
        }

        return $type;
    }
}
