<?php
namespace app\index\controller;
class Download 
{
    
    public function download_link()
    {
        $url = 'https://fir.im/f7vp';
        header("refresh:1;url=$url");
    }
}