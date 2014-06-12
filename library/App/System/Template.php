<?php
class  App_System_Template{

    var $page;

    function __construct($path){
    		$this->page=file_get_contents($path);
            }

    function assign($arr,&$page="",$prefix=""){
             if($page=="")$page=&$this->page;
             $keys=array_keys($arr);
             $values=array_values($arr);
             $keys_of_empty_values=array_keys($arr,"");
             for ($i=0; $i<count($keys_of_empty_values); $i++) {
                 $this->remove_if_block($keys_of_empty_values[$i],$page);
                 if($prefix!=""){
                 $this->remove_if_block($prefix.".".$keys_of_empty_values[$i],$page);
                 }
             };
             if(!function_exists("modify_keys")){
             function modify_keys(&$value,$key,$prefix) {
                    $value='${'.$prefix.($prefix!=''?'.':'').$value.'}';
                }}
             array_walk($keys,"modify_keys",$prefix );
             $page=str_replace($keys,$values,$page);
    }

    function getByTagName($tag,&$page=""){
             	if($page=="")$page=&$this->page;
                if((!strstr($page,"{".$tag."}") || !strstr($page,"{/".$tag."}"))===true) return "";
                $start  =       strpos($page,"{".$tag."}")+strlen("{".$tag."}");
                $end    =       strpos($page,"{/".$tag."}");
                return substr($page,$start,$end-$start);
    }

    function countTags($tag,&$page=""){
             	if($page=="")$page=&$this->page;
                if((!strstr($page,"{".$tag."}") || !strstr($page,"{/".$tag."}"))===true) return "";
                $start  =       strpos($page,"{".$tag."}")+strlen("{".$tag."}");
                $end    =       strpos($page,"{/".$tag."}");
                return substr($page,$start,$end-$start);
    }
    
    function getTemplates($control_name,&$page=""){
                if($page=="")$page=&$this->page;
	            $temp=array();
	            $temp["ALL"]=$this->getByTagName($control_name,$page);
	            $temp["ITEM_TEMPLATE"]=$this->getByTagName("ITEM_TEMPLATE",$temp["ALL"]);
	            if($temp["ITEM_TEMPLATE"]!=""){
	                $temp["ALTER_ITEM_TEMPLATE"]=$this->getByTagName("ALTER_ITEM_TEMPLATE",$temp["ALL"]);
                    if($temp["ALTER_ITEM_TEMPLATE"]=="")$temp["ALTER_ITEM_TEMPLATE"]=$temp["ITEM_TEMPLATE"];
	                $temp["SEPARATOR_TEMPLATE"]=$this->getByTagName("SEPARATOR_TEMPLATE",$temp["ALL"]);
	            }else{
	                $temp["ITEM_TEMPLATE"]=$temp["ALL"];
	                $temp["ALTER_ITEM_TEMPLATE"]=$temp["ALL"];
	            }
                return $temp;
    }
    
    public function dataBind($data,$tname,&$page=""){
    	while($this->_dataBind($data,$tname,$page)===true);
    } 

    private function _dataBind($data,$tname,&$page=""){
    	if($page=="")$page=&$this->page;
		$temp=$this->getTemplates("ROWS:".$tname);
		if($temp["ALL"] == ""){
			return false;
		}
        $rows="";
        for($i=0;$i<count($data);$i++){
        $row=($i%2==0) ? $temp["ITEM_TEMPLATE"] : $temp["ALTER_ITEM_TEMPLATE"];
            $this->assign(array_filter($data[$i],"isScalar"),$row,$tname);
            foreach(array_filter($data[$i],"isArray") as $key=>$value){
            	$this->_dataBind($value,$key,$row);
            }
            $rows.=$row;
            if($i<count($data)-1 && isset($temp["SEPARATOR_TEMPLATE"])){
            	$rows.=$temp["SEPARATOR_TEMPLATE"];
            }
        }       
        $start = strpos($page,"{ROWS:".$tname."}");if($start===false){return false;}
        $end = strpos($page,"{/ROWS:".$tname."}",$start);if(!$end || $start>$end){return false;}
        $end += strlen("{/ROWS:".$tname."}"); 
        $page = substr($page,0,$start).$rows.substr($page,$end);
        return true;
    }

    function remove_if_block($tag,&$page=""){  	
             if($page=="")$page=&$this->page;
             while(strstr($page,"{IF:".$tag."}")){
                $start=strpos($page,"{IF:".$tag."}");if($start===FALSE)return;
                $end=strpos($page,"{/IF:".$tag."}",$start);if($end===FALSE)return;
                $end+=strlen("{/IF:".$tag."}");
                $page=substr($page,0,$start).substr($page,$end);
             }
    }

    function remove_if_tags($tag,&$page=""){
             if($page=="")$page=&$this->page;
             $page=str_replace(array("{IF:".$tag."}","{/IF:".$tag."}"),"",$page);
    }

    function clear(){
    	$this->page = str_replace('${i18n','$.{i18n',$this->page);
        while(strstr($this->page,'${')){
            $start=strpos($this->page,'${');if($start===FALSE)return;
            $end=strpos($this->page,"}",$start)+1;
            $this->page=substr($this->page,0,$start).substr($this->page,$end);
        }
        $this->page = str_replace('$.{i18n','${i18n',$this->page);
        while(strstr($this->page,"{IF:")){
            $start=strpos($this->page,"{IF:");if($start===FALSE)return;
            $end=strpos($this->page,"}",$start)+1;
            $this->page=substr($this->page,0,$start).substr($this->page,$end);
        }
        while(strstr($this->page,"{/IF:")){
            $start=strpos($this->page,"{/IF:");if($start===FALSE)return;
            $end=strpos($this->page,"}",$start)+1;
            $this->page=substr($this->page,0,$start).substr($this->page,$end);
        }
    }
}

////////////////////////
 function isArray($item) {
    return is_array($item);
}

function isScalar($item) {
    return ((is_scalar($item)||$item==null)&& !is_array($item));
}
////////////////////////
?>