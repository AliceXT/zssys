<?php

namespace Addons\WeiSite\Model;
use Think\Model;

/**
 * WeiSite模型
 */
class ProductModel extends Model{

	protected $tableName = 'product';

	public function plist($map = array())
	{
		$token = get_token();
		$map['token'] = $token;
		$list = M('product')->where($map)->select();
		return $list;

	}

	public function productDetail($map)
	{
		$token = get_token();
		$map['token'] = $token;
		$list = M('product')->where($map)->select();
		return $list[0];
	}
}
