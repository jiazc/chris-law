<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\admin\model;
use think\Model;
/**
 * 媒体库
 *
 * @author jia.zhichao
 * @date 2017-5-4 
 * @time 10:34:08
 */
class Video extends Model{
    public function category()
	{
		return $this->belongsTo('VideoCategoryList','category_id');
	}
}
