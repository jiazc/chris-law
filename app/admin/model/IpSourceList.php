<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\admin\model;
use think\Model;

/**
 * IP来源列表模型
 *
 * @author jia.zhichao
 * @date 2017-5-4 
 * @time 10:16:06
 */
class IpSourceList extends Model{
    public function ip_source()
	{
		return $this->hasMany('Ip','ip_source')->bind('name');
	}
}
