<?php
class GeoLocation extends Zend_Db_Table_Abstract
{
    protected $_name = 'GeoLocation';
    
    public function getGeoLocations($filter = array())
    {       
        $select = $this->select();
        
        if (isset($filter['latlng'])) {
        	$select->where('latlng = (?)',$filter['latlng']);
        }
        if (isset($filter['content_id'])) {
        	$select->where('content_id = (?)',$filter['content_id']);
        }
        if (isset($filter['content_type'])) {
        	$select->where('content_type = (?)',$filter['content_type']);
        }
        $select->order('primary DESC');
        
        return $this->fetchAll($select);
    }
}
?>
