<?php
use think\Db;

  function cu_get($cu_id)
  {
    $cu_res = db('currency')->field('id,alias_name')->where(['id'=>$cu_id])->find();
    if($cu_res){
      return $cu_res['alias_name'];
    }
  }

?>