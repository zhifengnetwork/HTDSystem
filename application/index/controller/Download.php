<?php
namespace app\index\controller;
class Download 
{
    
    public function download_link()
    {
        $url = 'https://fir.im/tk9v';
        header("refresh:1;url=$url");
    }
}