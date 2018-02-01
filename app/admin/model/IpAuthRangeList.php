<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\admin\model;
use think\Model;

/**
 * IP授权范围模型
 *
 * @author jia.zhichao
 * @date 2017-5-4 
 * @time 10:16:06
 */
class IpAuthRangeList extends Model{
    public function ip_auth_range()
	{
		return $this->hasMany('Ip','ip_auth_range')->bind('name');
	}
}
