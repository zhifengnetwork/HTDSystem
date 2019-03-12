<?php
namespace app\index\controller;

use app\common\controller\HomeBase;
use app\index\model\Artcomment as ArtcommentModel;
use think\Db;
use think\Cache;

class Artcomment extends HomeBase
{
	public function _initialize()
	{
		parent::_initialize();
	}
    public function add()
    {
    	
        if (!session('userid') || !session('username')) {
            $this->error('亲！请登录');
        } else {
            $res = hook('watercheck',array('type'=>2,'id'=>input('id')),true,'type'); 
            if($res&&isset($res['code'])){                
               if($res['code']==0) $this->error($res['msg'],$_SERVER["HTTP_REFERER"],'',10);	
            }
        	$site_config = Cache::get('site_config');
            $comment = new ArtcommentModel;
            $id = input('id');
            $uid = session('userid');
            if (request()->isPost()) {
            	if(session('userstatus')!=2&&session('userstatus')!=5&&$site_config['email_sh'] ==0){
            		return json(array('code' => 0, 'msg' => '您的邮箱还未激活'));
            	}       	
            	
                $data = input('post.');
                $data['time'] = time();
                $data['fid'] = $id;
                $data['uid'] = session('userid');
				$member = Db::name('user');
				$model = Db::name('article');

                $model->where('id', $id)->setInc('reply', 1);
                //文章作者
                $zuid=$model->where('id', $id)->value('uid');
                send_message(session('userid'),$zuid,$id,3); 
				if($data['tid']>0){
                    Db::name('comment')->where('id',$data['tid'])->setInc('reply');
                    //回帖作者不是自己就发站内信
                    $commentuser=Db::name('comment')->where('id',$data['tid'])->value('uid');
                    send_message(session('userid'),$commentuser,$id,3);		
				}
				$data['content']= remove_xss($data['content']);
                if ($comment->add($data)) {
                	point_note($site_config['jifen_comment'],session('userid'),'commentadd',$comment->id);
                    return json(array('code' => 200, 'msg' => '评论成功'));
                } else {
                    return json(array('code' => 0, 'msg' => '评论失败'));
                }
            }
        }
    }
	public function edit()
    {
        if (!session('userid') || !session('username')) {
            $this->error('亲！请登录');
        } else {
            $res = hook('watercheck',array('type'=>2,'id'=>input('id')),true,'type'); 
            if($res&&isset($res['code'])){                
               if($res['code']==0) $this->error($res['msg'],$_SERVER["HTTP_REFERER"],'',10);	
            } 

            $id = input('id');
            session('commenteditid',$id);     
            $uid = session('userid');
            $comment = new ArtcommentModel;
            $a = $comment->find($id);
            if (empty($id) || $a == null || $a['uid'] != $uid) {
                $this->error('亲！您迷路了');
            } else {
                if (request()->isPost()) {
                    $data = input('post.');
                    $data['id']=session('commenteditid');
                    session('commenteditid', null);
                    $data['content']= remove_xss($data['content']);
                    if ($comment->edit($data)) {
                        return json(array('code' => 200, 'msg' => '修改成功'));
                    } else {
                        return json(array('code' => 0, 'msg' => '修改失败'));
                    }
                }
                $tptc = $comment->alias('c')->join('forum f', 'f.id=c.fid')->field('c.*,f.title')->find($id);
		        $this->assign('tptc', $tptc);
                return view();
            }
        }
    }

   
}