<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\admin\model;
use think\Model;

/**
 * 媒体类型模型
 *
 * @author jia.zhichao
 * @date 2017-5-4 
 * @time 10:16:06
 */
class VideoCategoryList extends Model{
    public function video()
	{
		return $this->hasMany('Video','category_id')->bind('name');
	}
}
