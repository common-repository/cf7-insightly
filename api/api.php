<?php
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

if(!class_exists('vxcf_insightly_api')){
    
class vxcf_insightly_api extends vxcf_insightly{
    public $info='' ; // info
    public $url='';
    public $api_key='';
    public $error= "";
    public $objects=array('Contacts'=>'Contacts','Organisations'=>'Organisations','Leads'=>'Leads','Opportunities'=>'Opportunities','Tasks'=>'Tasks','Projects'=>'Projects','Prospect'=>'Prospect'); 
    public $timeout= "15";

function __construct($info) {
     
    if(isset($info['data'])){ 
       $this->info= $info;
       if(!empty($info['data']['url'])){
        $this->url=trim($info['data']['url'],'/');   
       }
       
       if(!empty($info['data']['api_key'])){
        $this->api_key=$info['data']['api_key'];   
       }
    }
    
    }
public function get_token(){
  $users=$this->get_users();
  
    $info=$this->info;
    $info=isset($info['data']) ? $info['data'] : array();

    if(is_array($users) && count($users)>0){
    $info['valid_token']='true';    
    }else{
      unset($info['valid_token']);  
    }
  //  var_dump($info); //die();
    return $info;
}
public function get_insightly_token(){
    $info=$this->info;
    $token=$this->get_token(); 
$info['data']['api_token']=$token;
$info['data']['_time']=time();
$this->info=$info;    
$this->update_info($info,$info['id']);
return $token;
}


public function get_crm_fields($module,$fields_type=false){
    
    $object=$this->get_object($module);
    
   if($fields_type){
       $path='CustomFields/'.$object;
       if(empty($object)){
           $path=$module;
       }
$fields=$this->post_crm($path,'get');
  
if(is_array($fields) && isset($fields['Message'])){
 return $fields['Message'];   
}

$field_options=array(); 
if(is_array($fields) && count($fields)>0){
      if(!empty($object)){ 
       foreach($fields as $k=>$v){ 

           if( strtolower($v['FIELD_FOR']) == strtolower($object) && $v['EDITABLE'] === true && in_array($v['FIELD_TYPE'],array('MULTISELECT','DROPDOWN') ) ){
      $temp=array('name'=>$v['FIELD_NAME'],'label'=>$v['FIELD_LABEL'],'type'=>$v['FIELD_TYPE'],'is_custom'=>'1');
       
 if(!empty($v['CUSTOM_FIELD_OPTIONS']) && is_array($v['CUSTOM_FIELD_OPTIONS'])){
    foreach($v['CUSTOM_FIELD_OPTIONS'] as $option){
     $temp['options'][]=array('name'=>$option['OPTION_VALUE'],'value'=>$option['OPTION_VALUE'],'is_default'=>$option['OPTION_DEFAULT']);   //name=$option['OPTION_ID']
    } 
 }
               $field_options[$v['FIELD_NAME']]=$temp;
           }
       }
}
else if($path == 'LeadStatuses'){
        //
    $temp=array('name'=>$module,'label'=>'Lead Statuses','is_custom'=>'1');        
     foreach($fields as $k=>$option){
   $temp['options'][]=array('name'=>$option['LEAD_STATUS_ID'],'value'=>$option['LEAD_STATUS'],'is_default'=>$option['DEFAULT_STATUS']); 
     } 
      $field_options[$module]=$temp;  
      }
else if($path == 'LeadSources'){
        //
    $temp=array('name'=>$module,'label'=>'Lead Sources','is_custom'=>'1');        
     foreach($fields as $k=>$option){
   $temp['options'][]=array('name'=>$option['LEAD_SOURCE_ID'],'value'=>$option['LEAD_SOURCE'],'is_default'=>$option['DEFAULT_VALUE']); 
     } 
      $field_options[$module]=$temp;  
}
else{
    $temp=array('name'=>$module,'label'=>str_replace('Cat',' Cat',$module),'is_custom'=>'1');        
     foreach($fields as $k=>$option){
         if(isset($option['ACTIVE']) && $option['ACTIVE'] === true){
   $temp['options'][]=array('name'=>$option['CATEGORY_ID'],'value'=>$option['CATEGORY_NAME']); 
     } }
      $field_options[$module]=$temp;  
}
  }

return $field_options;
} 

    $web=array();
    $web['Contacts']='["SALUTATION","FIRST_NAME","LAST_NAME","IMAGE_URL","BACKGROUND","OWNER_USER_ID","DATE_CREATED_UTC","DATE_UPDATED_UTC","SOCIAL_LINKEDIN","SOCIAL_FACEBOOK","SOCIAL_TWITTER","DATE_OF_BIRTH","PHONE","PHONE_HOME","PHONE_MOBILE","PHONE_OTHER","PHONE_ASSISTANT","PHONE_FAX","EMAIL_ADDRESS","ASSISTANT_NAME","ADDRESS_MAIL_STREET","ADDRESS_MAIL_CITY","ADDRESS_MAIL_STATE","ADDRESS_MAIL_POSTCODE","ADDRESS_MAIL_COUNTRY","ADDRESS_OTHER_STREET","ADDRESS_OTHER_CITY","ADDRESS_OTHER_STATE","ADDRESS_OTHER_POSTCODE","ADDRESS_OTHER_COUNTRY","LAST_ACTIVITY_DATE_UTC","NEXT_ACTIVITY_DATE_UTC","ORGANISATION_ID","TITLE","TAGS","CONTACT_ID"]';
   
    $web['Organisations']='["ORGANISATION_NAME","BACKGROUND","IMAGE_URL","OWNER_USER_ID","DATE_CREATED_UTC","DATE_UPDATED_UTC","LAST_ACTIVITY_DATE_UTC","NEXT_ACTIVITY_DATE_UTC","PHONE","PHONE_FAX","WEBSITE","ADDRESS_BILLING_STREET","ADDRESS_BILLING_CITY","ADDRESS_BILLING_STATE","ADDRESS_BILLING_COUNTRY","ADDRESS_BILLING_POSTCODE","ADDRESS_SHIP_STREET","ADDRESS_SHIP_CITY","ADDRESS_SHIP_STATE","ADDRESS_SHIP_POSTCODE","ADDRESS_SHIP_COUNTRY","SOCIAL_LINKEDIN","SOCIAL_FACEBOOK","SOCIAL_TWITTER","EMAIL_DOMAIN","TAGS","ORGANISATION_ID"]';
   
    $web['Leads']='["SALUTATION","FIRST_NAME","LAST_NAME","LEAD_SOURCE_ID","LEAD_STATUS_ID","TITLE","CONVERTED","CONVERTED_CONTACT_ID","CONVERTED_DATE_UTC","CONVERTED_OPPORTUNITY_ID","CONVERTED_ORGANISATION_ID","DATE_CREATED_UTC","DATE_UPDATED_UTC","EMAIL","EMPLOYEE_COUNT","FAX","INDUSTRY","LEAD_DESCRIPTION","LEAD_RATING","MOBILE","OWNER_USER_ID","PHONE","RESPONSIBLE_USER_ID","WEBSITE","ADDRESS_STREET","ADDRESS_CITY","ADDRESS_STATE","ADDRESS_POSTCODE","ADDRESS_COUNTRY","LAST_ACTIVITY_DATE_UTC","NEXT_ACTIVITY_DATE_UTC","ORGANISATION_NAME","TAGS","LEAD_ID"]';
    
    $web['Opportunities']='["OPPORTUNITY_NAME","OPPORTUNITY_DETAILS","OPPORTUNITY_STATE","RESPONSIBLE_USER_ID","CATEGORY_ID","IMAGE_URL","BID_CURRENCY","BID_AMOUNT","BID_TYPE","BID_DURATION","ACTUAL_CLOSE_DATE","DATE_CREATED_UTC","DATE_UPDATED_UTC","OPPORTUNITY_VALUE","PROBABILITY","FORECAST_CLOSE_DATE","OWNER_USER_ID","LAST_ACTIVITY_DATE_UTC","NEXT_ACTIVITY_DATE_UTC","PIPELINE_ID","STAGE_ID","ORGANISATION_ID","TAGS","OPPORTUNITY_ID"]';
    
    $web['Tasks']='["TITLE","CATEGORY_ID","DUE_DATE","COMPLETED_DATE_UTC","COMPLETED","DETAILS","STATUS","PRIORITY","PERCENT_COMPLETE","START_DATE","MILESTONE_ID","RESPONSIBLE_USER_ID","OWNER_USER_ID","DATE_CREATED_UTC","DATE_UPDATED_UTC","EMAIL_ID","PROJECT_ID","REMINDER_DATE_UTC","REMINDER_SENT","OWNER_VISIBLE","STAGE_ID","ASSIGNED_BY_USER_ID","PARENT_TASK_ID","RECURRENCE","OPPORTUNITY_ID","ASSIGNED_TEAM_ID","ASSIGNED_DATE_UTC","CREATED_USER_ID","TASK_ID"]';
    
    $web['Projects']='["PROJECT_ID","PROJECT_NAME","STATUS","PROJECT_DETAILS","STARTED_DATE","COMPLETED_DATE","OPPORTUNITY_ID","CATEGORY_ID","PIPELINE_ID","STAGE_ID","IMAGE_URL","OWNER_USER_ID","DATE_CREATED_UTC","DATE_UPDATED_UTC","LAST_ACTIVITY_DATE_UTC","NEXT_ACTIVITY_DATE_UTC","RESPONSIBLE_USER_ID","TAGS"]'; 
    
    $web['Prospect']='["PROSPECT_ID","LEAD_ID","CONTACT_ID","ORGANISATION_ID","SALUTATION","FIRST_NAME","LAST_NAME","ORGANISATION_NAME","TITLE","EMAIL_ADDRESS","PHONE","MOBILE","FAX","WEBSITE","ADDRESS_STREET","ADDRESS_CITY","ADDRESS_STATE","ADDRESS_POSTCODE","ADDRESS_COUNTRY","INDUSTRY","EMPLOYEE_COUNT","SCORE","DESCRIPTION","DO_NOT_EMAIL","DO_NOT_CALL","OPTED_OUT","LAST_ACTIVITY_DATE_UTC","CREATED_USER_ID","OWNER_USER_ID","DATE_CREATED_UTC","DATE_UPDATED_UTC","DO_NOT_SYNC","LEAD_CONVERSION_DATE_UTC","TAGS"]';

   $search=array("FIRST_NAME","LAST_NAME","EMAIL","PHONE","STREET","CITY","STATE","POSTCODE","COUNTRY","ORGANISATION_NAME","OPPORTUNITY_NAME","OPPORTUNITY_STATE","CATEGORY_ID","OWNER_USER_ID","FORECAST_CLOSE_DATE","ACTUAL_CLOSE_DATE","PROJECT_NAME","STATUS","PIPELINE_ID","STAGE_ID","TITLE","TAG");

   $req=array("ORGANISATION_NAME","TITLE","LAST_NAME","OPPORTUNITY_NAME",'OPPORTUNITY_STATE',"PROJECT_NAME","PUBLICLY_VISIBLE","STATUS","COMPLETED");
   //,"RESPONSIBLE_USER_ID"
 $eg=array('STATUS'=>'COMPLETED, DEFERRED, IN PROGRESS, NOT STARTED, WAITING','COMPLETED'=>'true,false','PUBLICLY_VISIBLE'=>'true,false','OPPORTUNITY_STATE'=>'WON,LOST,ABANDONED,SUSPENDED');  

 $auto=array('TASK_ID','OPPORTUNITY_ID','LEAD_ID','ORGANISATION_ID','CONTACT_ID');
   $res=array();
if(!empty($web[$module])){
    $web_fields=json_decode($web[$module],true);
 foreach($web_fields as $name){
     $v=array('name'=>$name,'type'=>'TEXT');
     if(in_array($name,$auto)){
         $v['type']='AUTONUMBER';
     }
     $v['label']=ucwords(str_replace('_',' ',strtolower($name)));
       if($name == 'BACKGROUND'){
      $v['label']='Description (Background)';   
     }
        if(in_array($name,$search)){
         $v['search_able']='true';
     }
     if(in_array($name,$req)){
         $v['req']='true';
     }
        $ops=array();
      if($name == 'LEAD_SOURCE_ID'){
      $fields_arr=$this->post_crm('LeadSources','get');
      if(is_array($fields_arr)){ 
        foreach($fields_arr as $option){
   $ops[]=array('value'=>$option['LEAD_SOURCE_ID'],'name'=>$option['LEAD_SOURCE']); 
     }
      }
     $v['is_custom']='true';     
      }else if($name == 'LEAD_STATUS_ID'){
      $fields_arr=$this->post_crm('LeadStatuses','get');   
      if(is_array($fields_arr)){ 
        foreach($fields_arr as $option){
   $ops[]=array('value'=>$option['LEAD_STATUS_ID'],'name'=>$option['LEAD_STATUS']); 
     } }
    $v['is_custom']='true';  
      }
      if(!empty($ops)){
          $eg=array();
          foreach($ops as $o){
           $eg[]=$o['value'].'='.$o['name'];
          }
      $v['type']='ChoiceList';    
      $v['eg']=implode(',',array_slice($eg,0,20));  
      $v['options']=$ops;    
      }
         if(isset($eg[$name])){
         $v['eg']=$eg[$name];
     }
     
  $res[$name]=$v;   
 }   
}
else{
    $res['RECORD_NAME']=array('name'=>'RECORD_NAME','label'=>'Name','req'=>'true','search_able'=>'true','type'=>'text');
}

$fields=$this->post_crm('CustomFields/'.$module,'get');
//var_dump($fields,$object);
if(is_array($fields) ){
       foreach($fields as $k=>$v){ 
if( strtolower($v['FIELD_FOR']) == strtolower($object)  ){ //  && $v['EDITABLE'] === true
      $temp=array('name'=>$v['FIELD_NAME'],'label'=>$v['FIELD_LABEL'],'type'=>$v['FIELD_TYPE'],'is_custom'=>'true');
           if(!empty($v['CUSTOM_FIELD_OPTIONS'])){
 
             $eg=$op=array();
                 foreach($v['CUSTOM_FIELD_OPTIONS'] as $c){
             $eg[]=$c['OPTION_VALUE'];
             $temp['options'][]=array('name'=>$c['OPTION_VALUE'],'value'=>$c['OPTION_VALUE'],'is_default'=>$c['OPTION_DEFAULT']);    
             }    
         $temp['eg']=implode(',',$eg);    
             }
               $res[$v['FIELD_NAME']]=$temp;
           }
       }
  $res['vx_list_files']=array('name'=>'vx_list_files',"type"=>'files','label'=>'Files - Related List','custom'=>true);
  $res['vx_list_files2']=array('name'=>'vx_list_files2',"type"=>'files','label'=>'Files 2 - Related List','custom'=>true);
  $res['vx_list_files3']=array('name'=>'vx_list_files3',"type"=>'files','label'=>'Files 3 - Related List','custom'=>true);
  $res['vx_list_files4']=array('name'=>'vx_list_files4',"type"=>'files','label'=>'Files 4 - Related List','custom'=>true);
  $res['vx_list_files5']=array('name'=>'vx_list_files5',"type"=>'files','label'=>'Files 5 - Related List','custom'=>true);      
   }else if(!empty($fields['Message'])){
  $res=$fields['Message'];  
}

return $res;
    }
public function get_crm_objects(){

$objects=$this->objects; 
$res=$this->post_crm('CustomObjects');
if(is_array($res) && isset($res[0])){
    foreach($res as $v){
    $objects[$v['OBJECT_NAME']]=$v['PLURAL_LABEL'];    
    }
}

return $objects;
}
public function get_object($module){
    $modul=ucfirst($module);
    $objects=array('Opportunities'=>'Opportunity','Tasks'=>'Task','Projects'=>'Project','Contacts'=>'Contact','Organisations'=>'Organisation','Notes'=>'Note','Leads'=>'Lead','Prospects'=>'Prospect');
if(isset($objects[$modul])){
  $module=$objects[$modul];  
}
return $module;
}
  /**
  * Get campaigns from salesforce
  * @return array Salesforce campaigns
  */
public function get_lead_sources(){ 
$arr=array();   $msg='No Lead Source Found';

      $res=$this->post_crm('LeadSources','get');

      if(!empty($res) && is_array($res) && count($res)>0){
       if(!empty($res['Message'])){
 $msg=$res['Message'];   
}else{

          foreach($res as $k=>$v){
 

     $arr[$v['LEAD_SOURCE_ID']]=$v['LEAD_SOURCE'];         

          }          
          }
      }
  return empty($arr) ? $msg : $arr;
}
public function get_lead_status_list(){ 
$arr=array();   $msg='No Lead Status Found';

      $res=$this->post_crm('LeadStatuses','get');

      if(!empty($res) && is_array($res) && count($res)>0){
        if(!empty($res['Message'])){
 $msg=$res['Message'];   
}else{
          foreach($res as $k=>$v){
 

     $arr[$v['LEAD_STATUS_ID']]=$v['LEAD_STATUS'];         
          }
                
          }
      }

  return empty($arr) ? $msg : $arr;
}
public function get_users(){ 
$arr=array();   $msg='No User Found';

      $res=$this->post_crm('Users','get');

      if(!empty($res) && is_array($res) && count($res)>0){
       if(!empty($res['Message'])){
 $msg=$res['Message'];   
}else{
          foreach($res as $k=>$v){
 

     $arr[$v['USER_ID']]=$v['FIRST_NAME'].' '.$v['LAST_NAME'].'('.$v['EMAIL_ADDRESS'].')';         
          }
                
          }
      }

  return empty($arr) ? $msg : $arr;
}
public function get_cats($module){ 
$arr=array();   $msg='No Category Found';
$object=$this->get_object($module);
if(isset($object)){
      $res=$this->post_crm($object.'Categories','get');
}

      if(!empty($res) && is_array($res) && count($res)>0){
       if(!empty($res['Message'])){
 $msg=$res['Message'];   
}else{
          foreach($res as $k=>$v){
 
if(isset($v['ACTIVE']) && $v['ACTIVE'] === true){
     $arr[$v['CATEGORY_ID']]=$v['CATEGORY_NAME'];         
}
                
          }
      }
      }
  return empty($arr) ? $msg : $arr;
}

public function push_object($module,$fields,$meta){ 
//check primary key
 $extra=$tags=array();
///$path='Contacts/58145986/Links';
//$path='Leads/40278133/ImageField/custom_image__c/aa.png';
//$path='Leads';
//$file=file_get_contents('e:/img.png');
//$path='Organisations/133103090/EmailDomains';
//$path='Opportunities/26940238';
//$post=array('LINK_OBJECT_NAME'=>'Organisations','LINK_OBJECT_ID'=>'133103090');
//$search_response=$this->post_crm($path,'get');
//var_dump($search_response); die();
// $module='Contacts';
  /*$s=array('field_name'=>'custom_text__c','field_value'=>'xxxaa');
 $s=array('field_name'=>'ORGANISATION_NAME','field_value'=>'ahmad');
 $s=array('field_name'=>'CONTACT_FIELD_3','field_value'=>'option one');
 //$s=array('email'=>'bioinfo35@gmail.com');
// $s=array('phone_number'=>'8104763057');
$search_response=$this->post_crm($module.'/Search','get',$s);
var_dump($search_response);
die();*/

  $debug = isset($_GET['vx_debug']) && current_user_can('manage_options');
  $event= isset($meta['event']) ? $meta['event'] : '';
  $id= isset($meta['crm_id']) ? $meta['crm_id'] : '';
  $crm_fields=!empty($meta['fields']) ? $meta['fields'] : array();
 //var_dump($crm_fields); 
     if(!empty($fields['EMAIL_DOMAIN']['value'])){
         $val=$fields['EMAIL_DOMAIN']['value'];
      if(strpos($val,'@') !== false){ $val=substr($val,strpos($val,'@')+1); }
      $fields['EMAIL_DOMAIN']['value']=$val;   

      }
      if(!empty($fields['ORGANISATION_NAME']['value'])){
          $val=$fields['ORGANISATION_NAME']['value'];
      $fields['ORGANISATION_NAME']['value']=strtok($val,'@'); 
      }
//  $module='Organisations';
  $object=$this->get_object($module);
//$entry=$this->get_entry('Organisations','127556369');
//var_dump($entry); die();
  $id_key=strtoupper($object.'_id');
  if(!isset($this->objects[$module])){
    $id_key='RECORD_ID';  
  }
   $files=array();
  for($i=1; $i<6; $i++){
$field_n='vx_list_files';
if($i>1){ $field_n.=$i; }
  if(isset($fields[$field_n])){
    $files=$this->verify_files($fields[$field_n]['value'],$files);
    unset($fields[$field_n]);  
  }
}
 $item=array();  
  if($debug){ ob_start();}
if(isset($meta['primary_key']) && $meta['primary_key']!="" && isset($fields[$meta['primary_key']]['value']) && $fields[$meta['primary_key']]['value']!=""){    
$search=$fields[$meta['primary_key']]['value'];
$field=$meta['primary_key'];

//$field='LEAD_ID'; $search='40278126';

$search_response=$this->post_crm($module.'/Search','get',array('field_name'=>$field,'field_value'=>$search));
//var_dump( $search_response,$fields,$search,$module,array('field_name'=>$field,'field_value'=>$search) ); die();

if(is_array($search_response) && count($search_response)>0){
if($field == 'EMAIL_DOMAIN'){
  
    foreach($search_response as $v){
        if(!empty($v['EMAILDOMAINS'])){
            foreach($v['EMAILDOMAINS'] as $vv){
                if($vv['EMAIL_DOMAIN'] == $search){
                    $item=$v;
                }
            }
        }
    }
}else{
    if($id_key == 'RECORD_ID'){
    $item=$search_response;    
    }else{
  $item=end($search_response);
    }
  if(!empty($crm_fields)){
      foreach($crm_fields as $k=>$v){
      if(in_array($k,array('DATE_CREATED_UTC','DATE_UPDATED_UTC'))){ continue; }
          $val='';
          if(!empty($v['is_custom']) && !empty($item['CUSTOMFIELDS'])){
           foreach($item['CUSTOMFIELDS'] as $vv){
               if($vv['FIELD_NAME'] == $k){
                   $val=$vv['FIELD_VALUE'];
               }
           }  
          }else if($k == 'TAGS' && !empty($item['TAGS'])){ 
            $val=array();
             foreach($item['TAGS'] as $vv){
              $tags[]=$vv['TAG_NAME'];   
             } 
          }else if(!empty($item[$k])){
             $val=$item[$k];
          }
          if(!empty($val) && empty($fields[$k]['value'])){
       $fields[$k]=array('value'=>$val,'label'=>str_replace('_',' ',$k)); 
          }   
      }
  }
  $search_response=$item;
}
//var_dump($item,$tags); die();
  if(isset($item[$id_key])){
  $id=$item[$id_key]; 
  }
 
}
  $extra["body"]=$search;
  $extra["response"]=$search_response;
  if($debug){
  ?>
  <h3>Search field</h3>
  <p><?php print_r($field) ?></p>
  <h3>Search term</h3>
  <p><?php print_r($search) ?></p>
    <h3>POST Body</h3>
  <p><?php print_r($body) ?></p>
  <h3>Search response</h3>
  <p><?php print_r($res) ?></p>  
  <?php
  }

    
  }


     if(in_array($event,array('delete_note','add_note'))){    
  if(isset($meta['related_object'])){
    $extra['Note Object']= $meta['related_object'];
  }
  if(isset($meta['note_object_link'])){
    $extra['note_object_link']=$meta['note_object_link'];
  }
}


$post=$arr=array(); $status=$action=""; $send_body=true;
 $entry_exists=false;
/* if(is_array() && count($fields)>0){
     foreach($fields as $k=>$v){
    $post[]=array('name'=>$k,'value'=>$v['value']);     
     }
 }*/

$is_main=false; $object_url=''; $post=array();
$method='post';
if($id == ""){
    //insert new object
$action="Added";  $status="1"; $is_main=true;
$object_url=$module;
}else{
 $entry_exists=true;
    if($event == 'add_note'){ 
$action="Added"; 
  $status="1";
 $id_key='NOTE_ID';  $is_main=true;
  if(!empty($meta['related_object']) && !empty($id)){
  $object_url=$meta['related_object'].'/'.$id.'/Notes';
  }
 if(!empty($fields['title']['value'])){
     $post['TITLE']=$fields['title'];
 } 
 if(!empty($fields['body']['value'])){
     $post['BODY']=$fields['body'];
 }
 $fields=$post; $post=array();
  }else if(in_array($event,array('delete','delete_note'))){
 $send_body=false;
     if($event == 'delete_note'){ 
   $module='Notes';
     }
     $action="Deleted";
  $status="5";  
$object_url=$module.'/'.$id;
$method='delete';
  }else{
    //update object
 $action="Updated"; $status="2";

 if(empty($meta['update'])){
 $method='put';
 $object_url=$module;
 $is_main=true;
}
  }
}
$link=""; $error="";
if($is_main){ 
$domains=$links=$img_fields=array();  
foreach($fields as $k=>$v){
$val=$v['value'];
//
if( !empty($crm_fields[$k]['type'])){
   $type=$crm_fields[$k]['type'];
    if(in_array($type,array('AUTONUMBER'))){
      continue;  
    }if(in_array($type,array('IMAGE'))){
        $img_fields[$k]=$val;
      continue;  
    }
    if(in_array($type,array('MULTISELECT')) && !empty($val)){
        if(!is_array($val)){
        $val=array_filter(explode(',',$val));
        }
         $val=array_map('trim',$val);
         $val=implode(';',$val);
    }
} 
if($k == 'DATE_OF_BIRTH'){ $val=date('Y-m-d\TH:i:s.000\Z',strtotime($val)); }
   if($k == 'EMAIL_DOMAIN'){ 
   $domains[]=$val;
       continue;
   }
      if($k == 'TAGS'){
      //tags
         $tags=array_merge($tags,array_map('trim',explode(' ',$val)));    continue;  
      }
   if(in_array($k,array('Organisations','Contacts','Opportunities') ) ){
       if(  $k == 'Organisations'){
    $k='ORGANISATION_ID'; 
         }else{
$links[]=array('id'=>$val,'object'=>$k);  continue;             
         }
}

       if(!empty($crm_fields[$k]['is_custom'])){
        $post['CUSTOMFIELDS'][]=array('FIELD_NAME'=>$k,'FIELD_VALUE'=>$val); 
         continue; 
       } 
       if($k == 'id'){
        $k=$id_key;   
       }

      //all other fields
     $post[$k]=$val;         
      }
    if(!empty($id)){
  $fields[$id_key]=array('value'=>$id,'label'=>'ID');
  $post[$id_key]=$id;   
  } 
}

 // unset($post['CUSTOMFIELDS']);
  //unset($post['TAGS']);
//$post['ORGANISATION_ID']='133102657';
//$post['PIPELINE_ID']=987990;
//$post['STAGE_ID']=4070262;
if(!empty($object_url)){
    if($object_url == 'Prospects'){
        $object_url='Prospect';
    }
  //var_dump($post); die();  
$arr=$this->post_crm($object_url, $method , $post);   
//var_dump($post,$arr,$fields,$object_url, $method); die('------------');  
if($is_main){

if( is_array($arr) && !empty($arr[$id_key]) ){
      $id=$arr[$id_key];     $upload_dir=wp_upload_dir();
if(!empty($id) && function_exists('file_get_contents')){
foreach($img_fields as $k=>$v){
$path=$module.'/'.$id.'/ImageField/'.$k.'/'.basename($v);
$v=str_replace($upload_dir['baseurl'],$upload_dir['basedir'],$v); 
$extra['img fiels '.$k]= $this->post_crm($path, 'put' , file_get_contents($v));       
}
 if(!empty($files)){
     $upload_dir=wp_upload_dir();
    foreach($files as $k=>$file){  
  $path=$module.'/'.$id.'/FileAttachments';
$file=str_replace($upload_dir['baseurl'],$upload_dir['basedir'],$file); 
$tag_res=$this->post_curl($path,$file);
$extra['img list '.$k]=$tag_res;
} }

}
        
  if(strpos($this->url,'insightly.com') !== false){
  $module_single=strtolower(rtrim($module,'s'));
  if($module_single != 'Note'){
  $link=$this->url.'/list/'.$module_single.'/?blade=/details/'.$module_single.'/'.$id;  
  }  
  }else{  
    $link=$this->url.'/';
    if($event != 'add_note'){
        $link.=$module;
        if($module == 'Tasks'){
    $link.='/TaskDetails';    
        }else{
    $link.='/details';
        }    
    }else{ //notes
        $link.=$object_url;
    }
    $link.='/'.$id;   
  }
 
  ///
  if(!empty($tags)){
      $tags=array_unique($tags);
      $post=array();
    foreach($tags as $k=>$tag){
    $tag=preg_replace('/[^A-Za-z0-9\-\_ ]/', '', $tag);
    $post[]=array('TAG_NAME'=>$tag);    
    }  
  $path=$module.'/'.$id.'/Tags';
$tag_res=$this->post_crm($path,'put',$post);
$extra['Tags']=$tag_res;
}   
//var_dump($tags,$tag_res); die();
 
 if(!empty($links)){
    foreach($links as $k=>$tag){  
  $path=$module.'/'.$id.'/Links';
 $post=array('LINK_OBJECT_NAME'=>$tag['object'],'LINK_OBJECT_ID'=>$tag['id']);
$tag_res=$this->post_crm($path,'post',$post);
$extra['Link '.$k]=$tag_res;
} }
  if(!empty($domains)){
    foreach($domains as $k=>$tag){  
  $path=$module.'/'.$id.'/EmailDomains';
 $post=array('EMAIL_DOMAIN'=>$tag);
$tag_res=$this->post_crm($path,'post',$post);
$extra['Domain '.$k]=$tag_res;
} }
    }else{
       $status=''; $id='';
  $error=$this->get_error($arr);
    }
  }
  }

  if($debug){
  ?>
  <h3>Account Information</h3>
  <p><?php //print_r($this->info) ?></p>
  <h3>Data Sent</h3>
  <p><?php print_r($post) ?></p>
  <h3>Fields</h3>
  <p><?php echo json_encode($fields) ?></p>
  <h3>Response</h3>
  <p><?php print_r($response) ?></p>
  <h3>Object</h3>
  <p><?php print_r($module."--------".$action) ?></p>
  <?php
 echo  $contents=trim(ob_get_clean());
  if($contents!=""){
  update_option($this->id."_debug",$contents);   
  }
  }
       //add entry note
 if(!empty($meta['__vx_entry_note']) && !empty($id)){
 $disable_note=$this->post('disable_entry_note',$meta);
   if(!($entry_exists && !empty($disable_note))){
       $entry_note=$meta['__vx_entry_note'];
      $note_post=array('TITLE'=>$entry_note['title'],'BODY'=>$entry_note['body']);

$note_response= $this->post_crm($module.'/'.$id.'/Notes','post',$note_post);

  $extra['Note Title']=$entry_note['title'];
  $extra['Note Body']=$entry_note['body'];
  $extra['Note Response']=$note_response;
 
   }  
 }

return array("error"=>$error,"id"=>$id,"link"=>$link,"action"=>$action,"status"=>$status,"data"=>$fields,"response"=>$arr,"extra"=>$extra);
}
public function verify_files($files,$old=array()){
        if(!is_array($files)){
        $files_temp=json_decode($files,true);
     if(is_array($files_temp)){
    $files=$files_temp;     
     }else if(!empty($files)){ //&& filter_var($files,FILTER_VALIDATE_URL)
      $files=array_map('trim',explode(',',$files));   
     }else{
      $files=array();    
     }   
    }
    if(is_array($files) && is_array($old) && !empty($old)){
   $files=array_merge($old,$files);     
    }
  return $files;  
}
public function verify_country($country=''){
    $json='{"BD": "Bangladesh", "BE": "Belgium", "BF": "Burkina Faso", "BG": "Bulgaria", "BA": "Bosnia and Herzegovina", "BB": "Barbados", "WF": "Wallis and Futuna", "BL": "Saint Barthelemy", "BM": "Bermuda", "BN": "Brunei", "BO": "Bolivia", "BH": "Bahrain", "BI": "Burundi", "BJ": "Benin", "BT": "Bhutan", "JM": "Jamaica", "BV": "Bouvet Island", "BW": "Botswana", "WS": "Samoa", "BQ": "Bonaire, Saint Eustatius and Saba ", "BR": "Brazil", "BS": "Bahamas", "JE": "Jersey", "BY": "Belarus", "BZ": "Belize", "RU": "Russia", "RW": "Rwanda", "RS": "Serbia", "TL": "East Timor", "RE": "Reunion", "TM": "Turkmenistan", "TJ": "Tajikistan", "RO": "Romania", "TK": "Tokelau", "GW": "Guinea-Bissau", "GU": "Guam", "GT": "Guatemala", "GS": "South Georgia and the South Sandwich Islands", "GR": "Greece", "GQ": "Equatorial Guinea", "GP": "Guadeloupe", "JP": "Japan", "GY": "Guyana", "GG": "Guernsey", "GF": "French Guiana", "GE": "Georgia", "GD": "Grenada", "GB": "United Kingdom", "GA": "Gabon", "SV": "El Salvador", "GN": "Guinea", "GM": "Gambia", "GL": "Greenland", "GI": "Gibraltar", "GH": "Ghana", "OM": "Oman", "TN": "Tunisia", "JO": "Jordan", "HR": "Croatia", "HT": "Haiti", "HU": "Hungary", "HK": "Hong Kong", "HN": "Honduras", "HM": "Heard Island and McDonald Islands", "VE": "Venezuela", "PR": "Puerto Rico", "PS": "Palestinian Territory", "PW": "Palau", "PT": "Portugal", "SJ": "Svalbard and Jan Mayen", "PY": "Paraguay", "IQ": "Iraq", "PA": "Panama", "PF": "French Polynesia", "PG": "Papua New Guinea", "PE": "Peru", "PK": "Pakistan", "PH": "Philippines", "PN": "Pitcairn", "PL": "Poland", "PM": "Saint Pierre and Miquelon", "ZM": "Zambia", "EH": "Western Sahara", "EE": "Estonia", "EG": "Egypt", "ZA": "South Africa", "EC": "Ecuador", "IT": "Italy", "VN": "Vietnam", "SB": "Solomon Islands", "ET": "Ethiopia", "SO": "Somalia", "ZW": "Zimbabwe", "SA": "Saudi Arabia", "ES": "Spain", "ER": "Eritrea", "ME": "Montenegro", "MD": "Moldova", "MG": "Madagascar", "MF": "Saint Martin", "MA": "Morocco", "MC": "Monaco", "UZ": "Uzbekistan", "MM": "Myanmar", "ML": "Mali", "MO": "Macao", "MN": "Mongolia", "MH": "Marshall Islands", "MK": "Macedonia", "MU": "Mauritius", "MT": "Malta", "MW": "Malawi", "MV": "Maldives", "MQ": "Martinique", "MP": "Northern Mariana Islands", "MS": "Montserrat", "MR": "Mauritania", "IM": "Isle of Man", "UG": "Uganda", "TZ": "Tanzania", "MY": "Malaysia", "MX": "Mexico", "IL": "Israel", "FR": "France", "IO": "British Indian Ocean Territory", "SH": "Saint Helena", "FI": "Finland", "FJ": "Fiji", "FK": "Falkland Islands", "FM": "Micronesia", "FO": "Faroe Islands", "NI": "Nicaragua", "NL": "Netherlands", "NO": "Norway", "NA": "Namibia", "VU": "Vanuatu", "NC": "New Caledonia", "NE": "Niger", "NF": "Norfolk Island", "NG": "Nigeria", "NZ": "New Zealand", "NP": "Nepal", "NR": "Nauru", "NU": "Niue", "CK": "Cook Islands", "XK": "Kosovo", "CI": "Ivory Coast", "CH": "Switzerland", "CO": "Colombia", "CN": "China", "CM": "Cameroon", "CL": "Chile", "CC": "Cocos Islands", "CA": "Canada", "CG": "Republic of the Congo", "CF": "Central African Republic", "CD": "Democratic Republic of the Congo", "CZ": "Czech Republic", "CY": "Cyprus", "CX": "Christmas Island", "CR": "Costa Rica", "CW": "Curacao", "CV": "Cape Verde", "CU": "Cuba", "SZ": "Swaziland", "SY": "Syria", "SX": "Sint Maarten", "KG": "Kyrgyzstan", "KE": "Kenya", "SS": "South Sudan", "SR": "Suriname", "KI": "Kiribati", "KH": "Cambodia", "KN": "Saint Kitts and Nevis", "KM": "Comoros", "ST": "Sao Tome and Principe", "SK": "Slovakia", "KR": "South Korea", "SI": "Slovenia", "KP": "North Korea", "KW": "Kuwait", "SN": "Senegal", "SM": "San Marino", "SL": "Sierra Leone", "SC": "Seychelles", "KZ": "Kazakhstan", "KY": "Cayman Islands", "SG": "Singapore", "SE": "Sweden", "SD": "Sudan", "DO": "Dominican Republic", "DM": "Dominica", "DJ": "Djibouti", "DK": "Denmark", "VG": "British Virgin Islands", "DE": "Germany", "YE": "Yemen", "DZ": "Algeria", "US": "United States", "UY": "Uruguay", "YT": "Mayotte", "UM": "United States Minor Outlying Islands", "LB": "Lebanon", "LC": "Saint Lucia", "LA": "Laos", "TV": "Tuvalu", "TW": "Taiwan", "TT": "Trinidad and Tobago", "TR": "Turkey", "LK": "Sri Lanka", "LI": "Liechtenstein", "LV": "Latvia", "TO": "Tonga", "LT": "Lithuania", "LU": "Luxembourg", "LR": "Liberia", "LS": "Lesotho", "TH": "Thailand", "TF": "French Southern Territories", "TG": "Togo", "TD": "Chad", "TC": "Turks and Caicos Islands", "LY": "Libya", "VA": "Vatican", "VC": "Saint Vincent and the Grenadines", "AE": "United Arab Emirates", "AD": "Andorra", "AG": "Antigua and Barbuda", "AF": "Afghanistan", "AI": "Anguilla", "VI": "U.S. Virgin Islands", "IS": "Iceland", "IR": "Iran", "AM": "Armenia", "AL": "Albania", "AO": "Angola", "AQ": "Antarctica", "AS": "American Samoa", "AR": "Argentina", "AU": "Australia", "AT": "Austria", "AW": "Aruba", "IN": "India", "AX": "Aland Islands", "AZ": "Azerbaijan", "IE": "Ireland", "ID": "Indonesia", "UA": "Ukraine", "QA": "Qatar", "MZ": "Mozambique"}';
  $list=json_decode($json,true);
  if(isset($list[$country])){
  $country=$list[$country];    
  }  

  return $country;
}
public function get_error($arr){
    $error='';
         if(isset($arr['Message'])){
           $error=$arr['Message'];
       }else if(isset($arr[0]['Message'])){
           $error=$arr[0]['Message'];
       }else if(isset($arr['MESSAGE'])){
           $error=$arr['MESSAGE'];
       }else if(isset($arr[0]['MESSAGE'])){
           $error=$arr[0]['MESSAGE'];
       }else if(is_string($arr) && !empty($arr)){
        $error=$arr;   
       }
     return $error;  
}
public function post_crm($path,$method='get',$body=''){
        
  
   $url='https://api.insightly.com/v3.1/'.$path; 
   $head=array('Authorization'=> 'Basic ' .base64_encode($this->api_key)); 
    $head['Content-Type']='application/json';  
 if(!empty($body) && $method=='file'){
     $files = array(); $file_name='file';
if(!empty($body['attachments'])){
$files=$body['attachments'];
unset($body['attachments']);
}
$boundary = wp_generate_password( 24 );
$delimiter = '-------------' . $boundary;
$head['Content-Type']='multipart/form-data; boundary='.$delimiter;
$body = $this->build_data_files($boundary, $body, $files,$file_name);
$head['Content-Length']=strlen($body);
$method='post';
}else if($method!='get' && is_array($body)&& count($body)>0)
   { 
      $body=json_encode($body);
   }  

$args = array(
  'body' => $body,
  'headers'=> $head,
  'method' => strtoupper($method), // GET, POST, PUT, DELETE, etc.
  'sslverify' => false,
  'timeout' => 30,
  );
  
  $response = wp_remote_request($url, $args);
//var_dump($response,$head,$body,$url); die();
  if(is_wp_error($response)) { 
  $this->errorMsg = $response->get_error_message();
  return false;
  }
$body=json_decode($response['body'],true);

//var_dump($body); die('----------------');

return is_array($body) ? $body : $response['body'];
}
public function get_entry($module,$id){

    $params= array('module_name' => $module,'id' => $id);
      $arr=$this->post_crm($module.'/'.$id,'get');
//var_dump($arr); die('---------');
if(isset($arr['CUSTOMFIELDS'])){
  foreach($arr['CUSTOMFIELDS'] as $k=>$v){
    $arr[$v['FIELD_NAME']]=$v['FIELD_VALUE'];  
  }  
}

if(isset($arr['ADDRESSES'][0])){
  foreach($arr['ADDRESSES'][0] as $k=>$v){
    $arr[$k]=$v;  
  }  
}
if(isset($arr['CONTACTINFOS'][0])){
  foreach($arr['CONTACTINFOS'] as $k=>$v){
    $arr[$v['TYPE']]=$v['DETAIL'];  
  }  
}
if(isset($arr['TAGS'][0])){
    $tags=array();
  foreach($arr['TAGS'] as $k=>$v){
    $tags[]=$v['TAG_NAME'];  
  }
$arr['tags']=implode(', ',$tags);    
}

      return $arr;     
}
public function post_curl($path,$file){
$file = curl_file_create(realpath($file));
// form data with wp_remote_post does not work , insightly returns 400(bad request) whith file_get_contents , fopen etc data but works with Curl file 
// only pro version uses this curl code
$body=array('file'=>$file);
$url='https://api.insightly.com/v3.1/'.$path;  
$head=array('Authorization: Basic ' .base64_encode($this->api_key),'Content-Type: multipart/form-data'); 
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,$url);
curl_setopt($ch, CURLOPT_HTTPHEADER, $head);
curl_setopt($ch, CURLOPT_POST,1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//curl_setopt($ch, CURLOPT_HEADER, 1);
//curl_setopt($ch, CURLINFO_HEADER_OUT, true);
$result=curl_exec ($ch);
//$info=curl_getinfo($ch);
curl_close ($ch);
return json_decode($result,true);
}
public function build_data_files($boundary, $fields, $files, $file_name='attachments[]'){
    $data = '';
    $eol = "\r\n";

    $delimiter = '-------------' . $boundary;

    foreach ($fields as $name => $content) {
        $data .= "--" . $delimiter . $eol
            . 'Content-Disposition: form-data; name="' . $name . "\"".$eol.$eol
            . $content . $eol;
    }

    foreach ($files as $name => $file) {
    $name=basename($file);
   $content = file_get_contents($file);
        $data .= "--" . $delimiter . $eol
            . 'Content-Disposition: form-data; name="'.$file_name.'"; filename="'.$name.'"' . $eol
            //. 'Content-Type: image/png'.$eol
           . 'Content-Transfer-Encoding: binary'.$eol;

        $data .= $eol;
        $data .= $content . $eol;
    }
    $data .= "--" . $delimiter . "--".$eol;


    return $data;
}

}
}
?>