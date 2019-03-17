<?php

use think\Db;

function cu_name($cu_id){
	$name = Db::name('currency')->where('id',$cu_id)->value('alias_name');
	$name = $name ? $name : "此币种不存在";
	
	return $name;
}

function user($user_id){
	$user = Db::name('user')->where('id',$user_id)->field('id,username')->find();

	$name = $user ? $user['id'].'/'.$user['username'] : "用户名不存在";

	return $name;
}

function user_name($user_id){
	$user = Db::name('user')->where('id',$user_id)->field('username')->find();

	$name = $user ? $user['username'] : "用户名不存在";

	return $name;
}

function mobile($user_id){
	$mobile = Db::name('user')->where('id',$user_id)->field('mobile')->find();

	$mobile = $mobile ? $mobile['mobile'] : "用户电话不存在";

	return $mobile;
}