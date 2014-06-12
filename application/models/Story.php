<?php
class Story extends Zend_Db_Table_Abstract
{
    protected $_name = 'Story';

    public function getStories($filter = array())
    {
    	$select = $this->select()->setIntegrityCheck(false);

        if(isset($filter['fields']) && is_array($filter['fields'])) {
        	$select->from(array('s' => $this->_name),$filter['fields']);
        }
        else {
        	$select->from(array('s' => $this->_name));
        }
		
        if (isset($filter['id']) && (int)$filter['id'] > 0) {
        	$select->where('s.id = (?)',(int)$filter['id']);
        }
        if (isset($filter['in']) && is_array($filter['in'])) {
            $select->where('s.id IN(?)',$filter['in']);
        }
       	if (isset($filter['state'])) {
        	$select->where('s.state = (?)',$filter['state']);
        }
        if (isset($filter['category']) && (int)$filter['category'] > 0) {
        	$select->where('s.category = (?)',(int)$filter['category']);
        }
        if (isset($filter['producer']) && (int)$filter['producer'] > 0) {
        	$select->where('s.producer = (?)',(int)$filter['producer']);
        }
        if (isset($filter['location']) && (int)$filter['location'] > 0) {
        	$select->where('s.location = (?)',(int)$filter['location']);
        }
        if (isset($filter['media']) && (int)$filter['media'] > 0) {
        	$select->where('s.media = (?)',(int)$filter['media']);
        }
        if (isset($filter['cio']) && trim($filter['cio']) != '') {
        	$categories_instead_of = explode(",",$filter['cio']);
        	$select->where('s.category NOT IN (?)',$categories_instead_of);
        }
        if (isset($filter['tags']) && trim($filter['tags']) != '') {
        	$tags = explode(",",$filter['tags']);
        	foreach ($tags as $k => $v) {
        		$tags[$k] = 's.tags LIKE "%;'.$v.';%"';
        	}
        	$select->where('('.implode(" OR ",$tags).')');
        }
        if (isset($filter['producers']) && trim($filter['producers']) != '') {
        	$producers = explode(",",$filter['producers']);
        	$select->where('s.producer IN (?)',$producers);
        }
        if (isset($filter['otio']) && trim($filter['otio']) != '') {
        	$organisation_types_instead_of = explode(",",$filter['otio']);
        	$select->joinLeft(array('p' => 'Producer'),'s.producer = p.id',array());
        	$select->where('p.organisation_type NOT IN (?)',$organisation_types_instead_of);
        }
		// TDM
		// Filter for user
		  if (isset($filter['userviews']) && (int)$filter['userviews'] > 0) {
        	$user_id = $filter['userviews'];
        	$select->joinLeft(array('log' => 'Log'),'s.id = log.content_id',array());
        	$select->where("log.content_type = 'story' and ( action='watched' or action='shared' )  and log.user = (?)",$user_id);
			
        }
		
		 if (isset($filter['userrecommends']) && (int)$filter['userrecommends'] > 0) {
        	$user_id = $filter['userrecommends'];
        	$select->joinLeft(array('log' => 'Log'),'s.id = log.content_id',array());
        	$select->where("log.content_type = 'story' and action='shared' and log.user = (?)",$user_id);
			
        }
		
		// end TDM
		

        if (isset($filter['mio']) && trim($filter['mio']) != '') {
        	$medias_instead_of = explode(",",$filter['mio']);
        	$select->where('s.media NOT IN (?)',$medias_instead_of);
        }
        if (isset($filter['duration']) && trim($filter['duration']) != '') {
        	$duration_parts = explode(",",$filter['duration']);
        	if (isset($duration_parts[0]) && (int)$duration_parts[0] != 0) {
        		$select->where('s.duration >= (?)',((int)$duration_parts[0] * 60));
        	}
        	if (isset($duration_parts[1]) && (int)$duration_parts[1] != 0) {
        		$select->where('s.duration <= (?)',((int)$duration_parts[1] * 60));
        	}
        }
        if (isset($filter['adult_content'])) {
        	$select->where('s.adult_content = (?)',(int)$filter['adult_content']);
        }
        if (isset($filter['dates']) && trim($filter['dates']) != '') {
        	$dates_parts = explode(",",$filter['dates']);
        	if (isset($dates_parts[0]) && (int)$dates_parts[0] != 0) {
        		$select->where('UNIX_TIMESTAMP(s.date) >= (?)',strtotime($dates_parts[0].'-01-01 00:00:00'));
        	}
        	if (isset($dates_parts[1]) && (int)$dates_parts[1] != 0) {
        		$select->where('UNIX_TIMESTAMP(s.date) <= (?)',strtotime($dates_parts[1].'-12-31 23:59:59'));
        	}
        }
      
        if (isset($filter['country']) && trim($filter['country']) != '') {
        	$select->joinLeft(array('l' => 'Location'),'l.id = s.location',array());
        	$select->joinLeft(array('c' => 'Country'),'c.id = l.country',array());
        	$select->where('c.iso2 = (?)',$filter['country']);
        }
       
        if (isset($filter['sort_by']) && isset($filter['order']) && in_array($filter['sort_by'],$this->info(Zend_Db_Table_Abstract::COLS))) {
            $select->order('s.'.$filter['sort_by'].' '.$filter['order']);
        }
        else {
            $select->order('s.date_updated DESC');
        }
        
        if (isset($filter['limit'])) {
        	$select->limit($filter['limit']);
        }
		

        $result = $this->fetchAll($select)->toArray();
		if (isset($filter['user_names'])) {
			foreach ($result as $k => $v) {
				$select = $this->select()->setIntegrityCheck(false)->from('UserAdmin',array('name'));
				$select->where('id IN(?)',array($v['user'],$v['updated_by']));
				$rows = $this->fetchAll($select)->toArray();
				$result[$k]['user'] = $rows[0]['name'];
				$result[$k]['updated_by'] = (isset($rows[1])) ? $rows[1]['name'] : $result[$k]['user'];
			}

			if (isset($filter['sort_by']) && isset($filter['order'])) {
				if ($filter['sort_by'] == 'author') {
					$sorter = new App_Sort('user');
					$result = $sorter->sort($result,$filter['order']);
				}
				if ($filter['sort_by'] == 'last_edited_by') {
					$sorter = new App_Sort('updated_by');
					$result = $sorter->sort($result,$filter['order']);
				}
			}
		} 
		
		return $result;
    }

}
?>
