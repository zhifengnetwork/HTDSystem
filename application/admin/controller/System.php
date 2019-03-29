<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\common\model\Upload as UploadModel;
use think\Cache;
use think\Db;
use org\Http;

/**
 * 系统配置
 * Class System
 * @package app\admin\controller
 */
class System extends AdminBase
{
    public function _initialize()
    {
        parent::_initialize();

    }

    /**
     * 站点配置
     */
    // public function siteConfig()
    // {
    //     $site_config = Db::name('system')->field('value')->where('name', 'site_config')->find();
    //     $site_config = unserialize($site_config['value']);
    //     return $this->fetch('site_config', ['site_config' => $site_config]);
    // }

    /**
     * 更新配置
     */
    // public function updateSiteConfig()
    // {
    //     if ($this->request->isPost()) {
    //         $site_config = $this->request->post('site_config/a');
    //         $site_config['site_tongji'] = htmlspecialchars_decode($site_config['site_tongji']);
    //         $data['value'] = serialize($site_config);

    //         $path = 'application/config.php';
    //         $str = '<?php return [';
    //         if ($site_config['site_wjt'] == 1) {
    //             $str .= "'app_debug'           => true,'log' =>['level' => ['error']],'http_exception_template'=>[404 => APP_PATH.'404.html',401 =>APP_PATH.'401.html']";
    //         } else {
    //             $str .= "'app_debug'           => false,'log' =>['level' => ['error']],'http_exception_template'=>[404 => APP_PATH.'404.html',401 =>APP_PATH.'401.html']";
    //         }
    //         $str .= ']; ';
    //         file_put_contents($path, $str);

    //         //写入CMS/BBS开关
    //         // $cbstr = "<?php return [" . "'cb_open'=>" . $site_config['cb_open'] . "]; ";
    //         // file_put_contents('application/extra/cbopen.php', $cbstr);

    //         if (Db::name('system')->where('name', 'site_config')->update($data) !== false) {
    //             Cache::set('site_config', null);

    //             return json(array('code' => 200, 'msg' => '提交成功'));
    //         } else {
    //             return json(array('code' => 200, 'msg' => '提交失败'));
    //         }
    //     }
    // }


    /* 
    *   收益配置显示页面
    *
    */
    public function setIncomeConfig(){
        $static = Db::name('income_config')->field('name,value')->where('type', 101)->select();
        $push = Db::name('income_config')->field('name,value')->where('type', 102)->select();
        $dynamic = Db::name('income_config')->field('name,value')->where('type', 103)->select();
        $global_par = Db::name('income_config')->field('name,value')->where('type', 104)->select();

        $result['push'][$push[0]['name']] = $push[0]['value'];

        foreach ($static as $key => $value) {
            $result['static'][$value['name']] = $value['value'];
        }

        foreach ($dynamic as $key => $value) {
            $result['dynamic'][$value['name']] = $value['value'];
        }
        foreach ($global_par as $key => $value) {
            $result['global_par'][$value['name']] = $value['value'];
        }

        $this->assign('static',$result['static']);

        $this->assign('push',$result['push']);
        $this->assign('dynamic',$result['dynamic']);
        $this->assign('global_par',$result['global_par']);

        // $site_config = unserialize($site_config['value']);
        
        return $this->fetch('set_income_config');
    }

    /* 
    *   更新修改收益配置
    *
    */
    public function getToSetIncome(){
        if ($this->request->isPost()) {
            $param = input('post.');
            $static = $param['static'];
            $push = $param['push'];
            $dynamic = $param['dynamic'];
            $global_par = $param['global_par'];
            
            $config = Db::name('income_config');

            foreach ($static as $key => $value) {
                $staticKey[] = $key;
            }
            
            for ($i = 0; $i < count($staticKey); $i++) {
                $bool1 = $config->where('name',$staticKey[$i])->find();

                if ($bool1) {
                    $config->where('name',$staticKey[$i])->update(['value'=>$static[$staticKey[$i]]]);
                } else {
                    $config->insert(['name'=>$staticKey[$i],'value'=>$static[$staticKey[$i]],'type'=>101]);
                }
            }

            if ($config->where('name','push_rate')->find()) {
                $config->where('name','push_rate')->update(['value'=>$push['push_rate']]);
            } else {
                $config->insert(['name'=>'push_rate','value'=>$push['push_rate'],'type'=>102]);
            }

            foreach ($dynamic as $key => $value) {
                $dynamicKey[] = $key;
            }
            
            for ($i = 0; $i < count($dynamicKey); $i++) {
                $bool2 = $config->where('name',$dynamicKey[$i])->find();

                if ($bool2) {
                    $config->where('name',$dynamicKey[$i])->update(['value'=>$dynamic[$dynamicKey[$i]]]);
                } else {
                    $config->insert(['name'=>$dynamicKey[$i],'value'=>$dynamic[$dynamicKey[$i]],'type'=>103]);
                }
            }
            // 全球分红
            foreach ($global_par as $key => $value) {
                $globalsKey[] = $key;
            }
            
            for ($i = 0; $i < count($globalsKey); $i++) {
                $bool3 = $config->where('name',$globalsKey[$i])->find();

                if ($bool3) {
                    $config->where('name',$globalsKey[$i])->update(['value'=>$global_par[$globalsKey[$i]]]);
                    // 美元汇率更新到币种表usdt
                    if($globalsKey[$i] == 'exchange_usd'){
                        $update['price'] = $global_par[$globalsKey[$i]];
                        Db::name('currency')->where(['alias_name' => 'USDT'])->update($update);
                    }
                }else{
                    $config->insert(['name'=>$globalsKey[$i],'value'=>$global_par[$globalsKey[$i]],'type'=>104]);
                }
            }

            return json(array('code' => 200, 'msg' => '提交成功'));
        }
    }

    /* 
    *   数字货币列表
    *
    */
    public function currencyIndex(){

        $currencyData = Db::name('currency')->select();
        return $this->fetch('currency_index', ['currencyData' => $currencyData]);
    }
  

    public function get_theme_info($tpl_name)
    {
        $theme_url = DS . 'template' . DS;
        $info=array();
        if(is_dir(ROOT_PATH . $theme_url . $tpl_name . DS)){
            $info = include ROOT_PATH . $theme_url . $tpl_name . DS . 'config.php';
            
            $info['image'] = WEB_URL . $theme_url . $tpl_name . DS . 'images' . DS . 'covershow.png';
            $info['tpl_name'] = $tpl_name;
            return $info;
        }
       
        
    }

    public function template()
    {
        //数据库获取
        $tpl_use = array('cms_tpl' => 'c_default', 'bbs_tpl' => 'b_default', 'user_tpl' => 'u_default');
        $res = Db::name('system')->where('name', 'template')->find();
        if ($res) {
            $tpl_use = unserialize($res['value']);
        } else {
            $data['name'] = 'template';
            $data['value'] = serialize($tpl_use);
            $reslut = Db::name('system')->insert($data);
        }
        $theme_list = array();
        $cms_tpl = $this->get_theme_info($tpl_use['cms_tpl']);
        $bbs_tpl = $this->get_theme_info($tpl_use['bbs_tpl']);
        $user_tpl = $this->get_theme_info($tpl_use['user_tpl']);
        $theme_array = get_subdirs(ROOT_PATH . 'template' . DS);
        foreach ($theme_array as $tpl_name) {
            
                if (in_array($tpl_name, $tpl_use)||strpos($tpl_name,'_') ===false||check_addon_ser($tpl_name)===false) {
                    continue;
                }

                $theme_list[] = $this->get_theme_info($tpl_name);
            
        }

        $this->assign('cms_tpl', $cms_tpl);
        $this->assign('bbs_tpl', $bbs_tpl);
        $this->assign('user_tpl', $user_tpl);
        $this->assign('theme_list', $theme_list);
        return view();
    }
    public function deltpl($tpl_name)
    {
        $theme_array =get_subdirs(ROOT_PATH . 'template' . DS);
        if (in_array($tpl_name, $theme_array)) { // 判断删除操作的模板是否真实存在
            delete_dir_file(ROOT_PATH . 'template' . DS . $tpl_name);
            return json(array('code' => 200, 'msg' => '模板文件删除成功'));
        } else {
            return json(array('code' => 0, 'msg' => '目录不存在'));
        }
    }
    public function usetpl($tpl_name)
    {
        $theme_array = get_subdirs(ROOT_PATH . 'template' . DS);
        if (in_array($tpl_name, $theme_array)) { // 判断删除操作的模板是否真实存在
            // 替换系统设置中模板值
            $res = Db::name('system')->where('name', 'template')->value('value');
            $tpl_use = unserialize($res);
            if (strpos($tpl_name, "_") !== false) {

                $arr = explode('_', $tpl_name);
                switch ($arr[0]) {
                    case 'c':
                        $tpl_use['cms_tpl'] = $tpl_name;
                        break;
                    case 'b':
                        $tpl_use['bbs_tpl'] = $tpl_name;
                        break;
                    case 'u':
                        $tpl_use['user_tpl'] = $tpl_name;
                        break;
                    default:
                        return json(array('code' => 0, 'msg' => '模板错误'));
                }

                $reslut = Db::name('system')->where('name', 'template')->update(['value' => serialize($tpl_use)]);
                if ($reslut) {
                    $config = array(
                        'C_TPL' => $tpl_use['cms_tpl'],
                        'B_TPL' => $tpl_use['bbs_tpl'],
                        'U_TPL' => $tpl_use['user_tpl'],
                    );

                    $path = 'application/extra/web.php';
                    $str = '<?php return [';

                    foreach ($config as $key => $value) {
                        $str .= '\'' . $key . '\'' . '=>' . '\'' . $value . '\'' . ',';
                    };
                    $str .= ']; ';

                    file_put_contents($path, $str);
                    return json(array('code' => 200, 'msg' => '模板启用成功'));

                } else {
                    return json(array('code' => 0, 'msg' => '失败'));
                }
            }
        }
    }
    public function temponline()
    {
        $info = $ota_info = '';
        $res = Db::name('system')->where('name', 'otaservice')->find();
        if ($res) {
            $ota_info = unserialize($res['value']);
        }

        if ($ota_info) {

            $htd = new Http();
            $addon_array = get_subdirs(ROOT_PATH . 'template' . DS);
            $url = $ota_info['updateurl'] . '?upkey=' . $ota_info['updatekey'] . '&type=tmplist';
            $data = $htd->get_curl($url);
            $arr = json_decode($data, true);
            if ($data) {
                if ($arr['code'] == 200) {
                    $info = $arr['info'];
                    foreach($info as $k=>$v){
                        $info[$k]['is_down']=0;
                      if (in_array($v['addonname'], $addon_array)) {
                        $info[$k]['is_down']=1;
                      }
                      $res=$this->get_theme_info($v['addonname']);
                     if($res){
                        if($res['version']<$v['version'])
                        {
                               $info[$k]['is_down']=2;
                        }
                     }    
                    }
                } else {
                    $info = $arr['msg'];
                }
            }
        }
        $this->assign('keyvalue', $ota_info['updatekey']);
        $this->assign('theme_list', $info);
        return view();
    }

    /**
     * 清除缓存
     */
    public function clear()
    {
        delete_dir_file(CACHE_PATH);
        array_map('unlink', glob(TEMP_PATH . '/*.php'));
        if (!file_exists(TEMP_PATH)) {
            return json(array('code' => 200, 'msg' => '暂无缓存'));
        } else {
            rmdir(TEMP_PATH);
            return json(array('code' => 200, 'msg' => '更新缓存成功'));
        }

    }
    public function doUploadPic()
    {
        $uploadmodel = new UploadModel();
        $info = $uploadmodel->upfile('images', 'FileName');
        echo $info['headpath'];
    }
    public function ajax_mail_test()
    {
        $data = $this->request->param();

        if (!$data['email']) {
            return json(array('code' => 0, 'msg' => '邮箱地址为空'));
        } else {

            $data['body'] = '测试邮件内容';
            $data['title'] = '测试邮件标题';

            $res = send_mail_local($data['email'], $data['title'], $data['body']);
            if ($res) {
                return json(array('code' => 1, 'msg' => '邮件已发送，请到邮箱进行查收'));
            } else {
                return json(array('code' => 0, 'msg' => '发送失败，请检查邮件服务器配置'));
            }

        }
    }

    public function signrule()
    {
        $rules = Db::name('user_signrule')->select();
        $this->assign('rules', $rules);
        return $this->fetch('signrule');
    }
    public function updatesignrule()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            $daysarr = $data['days'];
            $scorearr = $data['score'];
            Db::name('user_signrule')->where('id', '>', 0)->delete();
            $arr = [];
            foreach ($daysarr as $i => $days) {
                $score = $scorearr[$i];
                $arr[$i] = ['days' => $days, 'score' => $score];
            }
            Db::name('user_signrule')->insertAll($arr);
            return json(array('code' => 200, 'msg' => '提交成功'));
        }
    }
    public function qqlogin()
    {
        $qqlogin_config = Db::name('system')->field('value')->where('name', 'qqlogin')->find();

        $qqlogin_config = unserialize($qqlogin_config['value']);
        $this->assign('qqlogin', $qqlogin_config);
        return $this->fetch('qqlogin');
    }
    public function ota()
    {
        $ota_info = '';
        $res = Db::name('system')->where('name', 'otaservice')->find();
        if ($res) {
            $ota_info = unserialize($res['value']);
        }
        $this->assign('ota_info', $ota_info);
        return $this->fetch();
    }
    public function updateota()
    {
        if ($this->request->isPost()) {
            $ota_config = $this->request->post();
            $data['value'] = serialize($ota_config);

            $reslut = 0;
            $res = Db::name('system')->where('name', 'otaservice')->find();
            if ($res) {
                $reslut = Db::name('system')->where('name', 'otaservice')->update($data);
            } else {
                $data['name'] = 'otaservice';
                $reslut = Db::name('system')->insert($data);
            }
            if ($reslut) {

                return json(array('code' => 200, 'msg' => '保存成功'));
            } else {
                return json(array('code' => 200, 'msg' => '提交失败'));
            }
        }
    }
    public function updateqqlogin()
    {
        if ($this->request->isPost()) {
            $site_config = $this->request->post('qqlogin/a');
            $data['value'] = serialize($site_config);

            if (Db::name('system')->where('name', 'qqlogin')->update($data) !== false) {
                //  Cache::set('site_config', null);
                session('qqconnect', null);
                return json(array('code' => 200, 'msg' => '提交成功'));
            } else {
                return json(array('code' => 200, 'msg' => '提交失败'));
            }
        }

    }
    public function qiniu()
    {
        $qiniu_config = Db::name('system')->field('value')->where('name', 'qiniu')->find();

        $qiniu_config = unserialize($qiniu_config['value']);
        $this->assign('qiniu', $qiniu_config);
        return $this->fetch('qiniu');
    }
    public function updateqiniu()
    {
        if ($this->request->isPost()) {
            $site_config = $this->request->post('qiniu/a');
            $data['value'] = serialize($site_config);

            if (Db::name('system')->where('name', 'qiniu')->update($data) !== false) {
                return json(array('code' => 200, 'msg' => '提交成功'));
            } else {
                return json(array('code' => 200, 'msg' => '提交失败'));
            }
        }

    }
    public function taoke()
    {
        $taoke_config = Db::name('system')->field('value')->where('name', 'taoke')->find();

        $taoke_config = unserialize($taoke_config['value']);
        $this->assign('taoke', $taoke_config);
        return $this->fetch('taoke');
    }
    public function updatetaoke()
    {
        if ($this->request->isPost()) {
            $taoke_config = $this->request->post('taoke/a');
            $data['value'] = serialize($taoke_config);

            $reslut = 0;
            $res = Db::name('system')->where('name', 'taoke')->find();
            if ($res) {
                $reslut = Db::name('system')->where('name', 'taoke')->update($data);
            } else {
                $data['name'] = 'taoke';
                $reslut = Db::name('system')->insert($data);
            }
            if ($reslut) {
                return json(array('code' => 200, 'msg' => '保存成功'));
            } else {
                return json(array('code' => 200, 'msg' => '提交失败'));
            }
        }

    }
    public function pay()
    {
        $pay_info = '';
        $res = Db::name('system')->where('name', 'payservice')->find();
        if ($res) {
            $pay_info = unserialize($res['value']);
        }
        $this->assign('pay_info', $pay_info);
        return $this->fetch();
    }
    public function updatepay()
    {
        if ($this->request->isPost()) {
            $ota_config = $this->request->post();
            $data['value'] = serialize($ota_config);

            $reslut = 0;
            $res = Db::name('system')->where('name', 'payservice')->find();
            if ($res) {
                $reslut = Db::name('system')->where('name', 'payservice')->update($data);
            } else {
                $data['name'] = 'payservice';
                $reslut = Db::name('system')->insert($data);
            }
            if ($reslut) {
                return json(array('code' => 200, 'msg' => '保存成功'));
            } else {
                return json(array('code' => 200, 'msg' => '提交失败'));
            }
        }
    }
}
