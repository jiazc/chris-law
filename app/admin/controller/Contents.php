<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\admin\controller;
use think\Db;
use think\Log;
/*
 * 定义时间格式
 */
const date_format = 'Y-m-d H:i:sa';
/**
 * Description of Contents
 *
 * @author jia.zhichao
 * @date 2017-4-21 
 * @time 17:21:50
 */
class Contents extends Base{
    /**
     * 插入单张图片方法
     * @param type $jump_url
     * @return string
     */
    public function upload_img($jump_url) {
        $img_url='';
        $file = request()->file('file0');
        if($file) {
            if(config('storage.storage_open')){
                    //七牛
                    $upload = \Qiniu::instance();
                    $info = $upload->upload();
                    $error = $upload->getError();
                    if ($info) {
                        $img_url= config('storage.domain').$info[0]['key'];
                    }else{
                            $this->error($error,url($jump_url));//否则就是上传错误，显示错误原因
                    }
            }else{
                    $validate = config('upload_validate');

                $info = $file->validate($validate)->rule('uniqid')->move(ROOT_PATH . config('upload_path') . DS . date('Y-m-d'));
                if ($info) {
                        $img_url = config('upload_url').config('upload_path'). '/' . date('Y-m-d') . '/' . $info->getFilename();
                        //写入数据库
                        $data['uptime'] = time();
                        $data['filesize'] = $info->getSize();
                        $data['path'] = $img_url;
                        Db::name('plug_files')->insert($data);
                } else {
                        $this->error($file->getError(), url($jump_url));//否则就是上传错误，显示错误原因
                }
            }
        }
        return $img_url;
    }

        /**
         * 法律条文列表
         */
       public function law_list(){
            $title=input('title');
            $key=input('key');
            $law_type = input('keyType');
            Log::write($law_type);
            //map架构查询条件数组
            $map=array();          
            if(!empty($key) && $law_type==="t_key"){
                $map['law_title|key_word']= array('like',"%".$key."%");
            }else if(!empty($key) && $law_type==="b_key"){
                $map['law_detail']= array('like',"%".$key."%");
            }
            $category_check=input('category','');
            if($category_check !== ''){
			$map['a.category_id']=$category_check;
		}
            $law = Db::name('law')->alias("a")->field('a.*,b.name')
                     ->join(config('database.prefix').'law_category_list b','a.category_id =b.category_id')
                    ->where($map)->order('update_time desc')->paginate(config('paginate.list_rows'),false,['query'=>get_query()]);
            $show = $law->render();
            $show=preg_replace("(<a[^>]*page[=|/](\d+).+?>(.+?)<\/a>)","<a href='javascript:ajax_page($1);'>$2</a>",$show);
            $this->assign('page',$show);
            $law_category_list=Db::name('law_category_list')->select();
            $this->assign('law_category_list',$law_category_list);
            $this->assign('category_check',$category_check);
            $this->assign('title',$title);
            $this->assign('keyy',$key);           
            $this->assign('law',$law);
            if(request()->isAjax()){
			return $this->fetch('ajax_law_list');
		}else{
			return $this->fetch();
		}
        }
        /**
         * 法律条文添加显示
         */
       public function law_add(){
           $category_check=input('category','');
            $law_category_list=Db::name('law_category_list')->select();
            $this->assign('law_category_list',$law_category_list);
            $this->assign('category_check',$category_check);
           return $this->fetch();
        }
        /**
         * 法律条文添加操作
         */
       public function law_runadd(){
           if (!request()->isAjax()){
			$this->error('提交方式不正确',url('admin/Contents/law_list'));
		}
            $data= array(
                'law_title'=> input('law_title'),
                'key_word'=> input('key_word'),
                'category_id'=> input('category'),
                'law_detail'=> input('law_detail'),
                'update_time'=>date(date_format),
                'create_time'=>date(date_format),            
            );
            Db::name('law')->insert($data);
            $law_id=Db::name('law')->field('law_id')->order('law_id desc')->limit('1')->find();
            $string=implode('',$law_id);
            $this->success('法律条文保存成功,返回列表页',url('admin/Contents/law_list',array('law_id'=>$string)));
        }
        /**
         * 法律条文编辑显示
         */
        public function law_edit(){
            $law_id = input('law_id');  
            $law_category_list=Db::name('law_category_list')->select();
            if (empty($law_id)){
                    $this->error('参数错误',url('admin/Contents/law_list'));
            }
            $law_list=Db::name('law')->where(array('law_id'=>$law_id))->find();
            $this->assign('law_list',$law_list);
            $category_check = $law_list['category_id'];
            $this->assign('law_category_list',$law_category_list);
            $this->assign('category_check',$category_check);
            return $this->fetch();
        }
        /**
         * 法律条文编辑操作
         */
         public function law_runedit(){
             if (!request()->isAjax()){
			$this->error('提交方式不正确',url('admin/Contents/law_list'));
		}
            $data= array(
                'law_id'=> input('law_id'),
                'key_word'=> input('key_word'),
                'category_id'=> input('category'),
                'law_title'=> input('law_title'),
                'law_detail'=> input('law_detail'),
                'update_time'=>date(date_format),
            );
            if(request()->file('file0')){
                $data['law_img']= $this->upload_img('admin/Contents/law_list');
            }
            $rst=Db::name('law')->update($data);
            if($rst!==false){
			$this->success('法律条文修改成功，返回列表页',
                                url('admin/Contents/law_list',array('law_id'=>input('law_id'))));
		}else{
			$this->error('法律条文修改失败',url('admin/Contents/law_list'));
		}
         }
        /**
         * 法律条文删除操作
         */
        public function law_del(){
            $p=input('p');
            $rst=Db::name('law')->where(array('law_id'=>input('law_id')))->delete();
            if($rst!==false){
                    $this->success('法律条文删除成功',url('admin/Contents/law_list',array('p' => $p)));
            }else{
                    $this->error('法律条文删除失败',url('admin/Contents/law_list',array('p' => $p)));
            }
        }
                /**
         * 法律条文全部删除
         */
        public function law_alldel()
	{
		$p = input('p');
		$ids = input('law_id/a');
		if(empty($ids)){
			$this -> error("请选择删除法律条文",url('admin/Contents/law_list',array('p'=>$p)));//判断是否选择了产品ID
		}
		if(is_array($ids)){//判断获取产品ID的形式是否数组
			$where = 'law_id in('.implode(',',$ids).')';
		}else{
			$where = 'law_id='.$ids;
		}
		$rst=Db::name('law')->where($where)->delete();
		if($rst!==false){
			$this->success("法律条文删除成功",url('admin/Contents/law_list',array('p'=>$p)));
		}else{
			$this -> error("法律条文删除失败！",url('admin/Contents/law_list',array('p' => $p)));
		}
	}

        /**
         * 法律条文预览
         */
        public function law_preview(){
            $law_id = input('id');          
            if (empty($law_id)){
                    $this->error('参数错误',url('admin/Contents/law_list'));
            }
            $law=Db::name('law')->where(array('law_id'=>$law_id))->find();
		$sl_data['id']=$law['law_id'];
		$sl_data['law_title']=$law['law_title'];
		$sl_data['law_detail']=$law['law_detail'];
                $sl_data['key_word']=$law['key_word'];
		$sl_data['code']=1;
		return json($sl_data);
        }
       /*
         * 法律分类类别列表显示
         */
        public function sort_lawtype_list(){
            $law_category_list=Db::name('law_category_list')->order('category_id')->select();
            $this->assign('law_category_list',$law_category_list);
            return $this->fetch();
        }
        /*
         * 法律分类类别列表删除
         */
        public function sort_lawtype_del()
	{
		$rst=Db::name('law_category_list')->where(array('category_id'=>input('category_id')))->delete();
		if($rst!==false){
			$this->success('分类标签删除成功',url('admin/Contents/sort_lawtype_list'));
		}else{
			$this->error('分类标签删除失败',url('admin/Contents/sort_lawtype_list'));
		}
	}
        	/*
     * 法律分类类别添加
	 *
     */
	public function sort_lawtype_runadd()
	{
		Db::name('law_category_list')->insert(input('post.'));
		$this->success('分类添加成功',url('admin/Contents/sort_lawtype_list'));
	}

	/*
     * 法律分类类别修改
	 
     */
	public function sort_lawtype_runedit()
	{
		$sl_data=array(
			'category_id'=>input('category_id'),
			'name'=>input('name'),			
		);
		Db::name('law_category_list')->update($sl_data);
		$this->success('分类标签修改成功',url('admin/Contents/sort_lawtype_list'));
	}
}
