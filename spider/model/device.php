<?php
class device extends BaseDb
{
	
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    function addDevice($uuid,$platform){
    	$arr = array(
    		'uuid' => $uuid,
    		'platform' => $platform,
    		'dateline' => TIMESTAMP
    	);

    	return $this->insert('device_info',$arr,true);
    }

    function device_bind($device_id,$uid){
    	return $this->insert('device_bind',array('uid'=>$uid,'device_id'=>$device_id));
    }
}