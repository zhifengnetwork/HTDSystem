<?php
namespace app\admin\controller;
use app\common\controller\AdminBase;
use think\Db;

/**
 * 投资列表
 */
class Income extends AdminBase
{
    protected function _initialize()
    {
        parent::_initialize();
    }

    public function info()
    {
        $income_list = Db::name('income')->alias('in')
            ->join('htd_buy_order buy','in.order_no=buy.order_no','left')
            ->join('htd_user u','in.id=u.id','left')
            ->join('htd_currency c','buy.cu_id=c.id','left')
            ->field('in.*, u.mobile, u.username, buy.*, c.name,c.alias_name,c.note')
            ->order('in.id desc')->paginate(10);
        $this->assign('act','info');

        return $this->fetch('info',['income_list' => $income_list]);
    }

    public function search($keyword = '',$act = '', $page = 1){

        $map = [];
        $act = input('act');

        if ($keyword) {
            session('userkeyword',$keyword);
            $map['mobile'] = ['like', "%{$keyword}%"];
        }else{

            if(session('userkeyword')!=''&&$page>1){
                $map['mobile'] = ['like', "%".session('userkeyword')."%"];

            }else{
                session('userkeyword',null);
            }
        }
        $user_list = Db::name('income')->alias('in')
            ->join('htd_buy_order buy','in.order_no=buy.order_no','left')
            ->join('htd_user u','in.id=u.id','left')
            ->join('htd_currency c','buy.cu_id=c.id','left')
            ->field('in.*, u.mobile, u.username, buy.*, c.name,c.alias_name,c.note')
            ->where($map)->order('in.id DESC')->paginate(10);
        $this->assign('act', $act);
        return $this->fetch('Income_'.$act,['income_list' => $user_list]);
    }

    public function static_income()
    {
        $income_list = Db::name('income')->alias('in')
            ->join('htd_buy_order buy','in.order_no=buy.order_no','left')
            ->join('htd_user u','in.id=u.id','left')
            ->join('htd_currency c','buy.cu_id=c.id','left')
            ->field('in.*, u.mobile, u.username, buy.*, c.name,c.alias_name,c.note')
            ->order('in.id desc')->paginate(10);

        $this->assign('act','static_income');
        return $this->fetch('Income_static_income',['income_list' => $income_list]);
    }

    public function dynamic_income()
    {
        $income_list = Db::name('income')->alias('in')
            ->join('htd_buy_order buy','in.order_no=buy.order_no','left')
            ->join('htd_user u','in.id=u.id','left')
            ->join('htd_currency c','buy.cu_id=c.id','left')
            ->field('in.*, u.mobile, u.username, buy.*, c.name,c.alias_name,c.note')
            ->order('in.id desc')->paginate(10);
        $this->assign('act','dynamic_income');

        return $this->fetch('Income_dynamic_income',['income_list' => $income_list]);
    }

    public function push_income()
    {
        $income_list = Db::name('income')->alias('in')
            ->join('htd_buy_order buy','in.order_no=buy.order_no','left')
            ->join('htd_user u','in.id=u.id','left')
            ->join('htd_currency c','buy.cu_id=c.id','left')
            ->field('in.*, u.mobile, u.username, buy.*, c.name,c.alias_name,c.note')
            ->order('in.id desc')->paginate(10);
        $this->assign('act','push_income');

        return $this->fetch('Income_push_income',['income_list' => $income_list]);
    }

    public function global_dividend()
    {

        return $this->fetch();
    }

    /**
     * 审核
     * @param $id
     * @return mixed
     */
    public function check($id)
    {
        $value = Db::name('buy_order')->where('id'>=$id)->value('is_check');
        if($value == 0){
            Db::name('buy_order')->update(['is_check' => 1 ,'id' => $id]);
            return json(array('code'=>200, 'msg'=>'审核通过'));
        }elseif ($value = 1){
            return json(array('code'=>200, 'msg'=>'已经审核通过'));
        }else{
            return json(array('code'=>0, 'msg'=>"错误"));
        }

    }

}
