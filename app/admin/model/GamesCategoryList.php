<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\admin\model;
use think\Model;

/**
 * 游戏类型模型
 *
 * @author jia.zhichao
 * @date 2017-5-4 
 * @time 10:16:06
 */
class GamesCategoryList extends Model{
    public function games()
	{
		return $this->hasMany('Games','category_id')->bind('name');
	}
}
