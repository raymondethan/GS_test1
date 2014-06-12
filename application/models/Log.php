<?php
class Log extends Zend_Db_Table_Abstract
{
    protected $_name = 'Log';

    public function getCommunityFeeds($user_id,$limit)
    {
    $select = $this->select()->setIntegrityCheck(false)->from('UserFriends',array('ids' => 'GROUP_CONCAT(user_friend_id SEPARATOR ",")'));
    $select->where('user_id = (?)',$user_id);
      $user_friends = $this->fetchRow($select);
      $user_friends_ids = (string)$user_friends->ids;
      $user_ids = $user_id.(($user_friends_ids != '') ? ',' : '').$user_friends_ids;

      $select = $this->select()->setIntegrityCheck(false)->from(array('l' => $this->_name),array('action','date'));
      $select->joinLeft(array('u' => 'User'),'u.id = l.user',array('fb_user_id','first_name','last_name'));
      $select->joinLeft(array('s' => 'Story'),'s.id = l.content_id',array('story_id' => 'id','story_name' => 'name','story_url_title' => 'url_title'));
      $select->where('l.user IN ('.$user_ids.')')->where('l.content_type = (?)','story');
      $select->order('l.date DESC');
      $select->limit($limit);
      
      return $this->fetchAll($select);
    }
	
	/* 
		TDM
		Simple method to get count num of stories viewed by user. Will need extra cols added to sql query
	*/
	public function getFriendsMaps($user_id,$limit) {
	  $select = $this->select()->setIntegrityCheck(false)->from('UserFriends',array('ids' => 'GROUP_CONCAT(user_friend_id SEPARATOR ",")'));
	  $select->where('user_id = (?)',$user_id);
      $user_friends = $this->fetchRow($select);
      $user_friends_ids = (string)$user_friends->ids;
	  
      // TMB
      //$user_ids = $user_id.(($user_friends_ids != '') ? ',' : '').$user_friends_ids;
      $user_ids = $user_friends_ids;
	  if ($user_ids <> ""){
		$sql = "SELECT count(1) as cnt, user, u.first_name, u.last_name, u.fb_user_id, (select count(1) from Log l2 where l2.user = l.user and action='shared') as shared  FROM Log l left join User u on u.id = l.user where action = 'watched'  and l.user in (".$user_ids.")  group by user ";
		return $this->getAdapter()->fetchAll($sql);
	  }
	  return "";
    }
	
	public function getFacebookMapUpdates() {
	  $sql = "	SELECT u.fb_user_id, first_name, last_name, count(1) FROM `User` u, Log l WHERE u.id = l.user and l.content_type = 'story' and action = 'shared' "
		." and date between  NOW() + INTERVAL -1 DAY  and now() group by u.fb_user_id ";
	  return $this->getAdapter()->fetchAll($sql);
	 
    }

	public function hasBeenPromotedByUser($user_id,$story_id) {
	  $sql = "select * from Log l where l.user = ".$user_id." and  content_id = ".$story_id." and action = 'shared' ";

	  $rowSet =  $this->getAdapter()->fetchAll($sql);

	  if ((isset($rowSet))&&(count($rowSet) > 0)){
		return 1;
	  }
	  else{
		return 0;
	  }

	  
    }
	
	public function removeStoryFromMap($user_id,$story_id) {
	  $sql = "update Log l set action='watched', date = now() where l.user = ".$user_id." and  content_id = ".$story_id." and action = 'shared' ";
	  
	  $data = array(
			'date'      => 'now()',
			'action'      => 'watched'
		);
 
	$where[] = "content_id = ".$story_id;
	$where[] = "action = 'shared'";
 
//$n = $db->update('bugs', $data, $where);
	  //$this->getAdapter()->fetchAll($sql);
	  $this->getAdapter()->update('Log', $data, $where);
	 // $n = $db->update('bugs', $data, $where);
	  return $sql;
    }
	

	// end TDM
	 
	
}
?>

