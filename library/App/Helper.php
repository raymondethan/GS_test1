 <?php 
 class App_Helper
 {
    
 	static function _secondsToDhms($seconds)
    {
        $result = array();
        $result['day'] = floor($seconds / (60 * 60 * 24));
        $remainder = $seconds % (60 * 60 * 24);
        $result['hour'] = floor($remainder / (60 * 60));
        $remainder = $remainder % (60 * 60);
        $result['minute'] = floor($remainder / 60);
        $result['second'] = $remainder % 60;
        $remainder = $seconds % (60 * 60 * 24);
        return $result;
    }
    
}
?>