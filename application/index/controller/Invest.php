<?php
namespace app\index\controller;

use app\common\controller\HomeBase;
use think\Cache;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;

class Invest extends HomeBase
{
   public function index_ylf(){
      return $this->fetch();
   }
}