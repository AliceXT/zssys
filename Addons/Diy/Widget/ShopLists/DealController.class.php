<?php



namespace Addons\Diy\Widget\ShopLists;



use Addons\Diy\Controller\WidgetController;



class DealController extends WidgetController {

	// widget的说明，必填写

	function info() {

		return array (

				'title' => '商品列表模块', // 必填，显示在选择widget显示的名称

				'icon' => C ( 'TMPL_PARSE_STRING.__IMG__' ) . '/m/icon_list.png'  // 可为空，获取选择的模板的html代码，为空则使用通用的方法获取

				);

	}



	// 模块参数配置

	function param() {

		return '';

	}



	// 模块解析

	function show($widget) {

		$map ['token'] = get_token ();

		if ($widget ['data_from'] == 2) {

			// 部分商品 cate_id=1,2,3&search_key=[search_key]&shopping_list=1

			$condition = str_replace ( array (

					'[cate_id]',

					'[search_key]'

			), array (

					$_REQUEST ['cate_id'],

					$_REQUEST ['search_key']

			), $widget ['data_condition'] );

			parse_str ( $condition, $output );



			if (! empty ( $output ['cate_id'] )) {

				$map = getIdsForMap ( $output ['cate_id'], $map, 'cate_id' );

			}

			if (! empty ( $output ['search_key'] )) {

				$key = safe ( $output ['search_key'] );

				$map ['title'] = array (

						'like',

						"%{$key}%"

				);

			}

			if (isset ( $output ['shopping_list'] )) {

				// 购物清单 TODO

			}

		} elseif ($widget ['data_from'] == 1) {

			// 指定商品

			$map = getIdsForMap ( $widget ['data_ids'], $map );

		}



		if (isset ( $_REQUEST ['cate_id'] )) {

			$cid = intval ( $_REQUEST ['cate_id'] );

			$map ['_string'] = ' (cate_id=' . $cid .' or cate_id_1=' .$cid. ' or cate_id_2=' . $cid . ') ';

			// dump($map);exit;

		}


		/**
		*   @author AliceXT
		*	2015-10-24 for 显示，隐藏商品
		**/
		$map['shangjia'] = "上架";
		$map['show'] = '1';
		
		$list = M ( 'shop_product' )->where ( $map )->order ( $widget ['order'] )->selectPage ( $widget ['list_row'] );

		/*change end*/

		// dump(M('shop_product')->getLastSql());exit;

		foreach($list['list_data'] as & $x)

		{

			$x['discount_price'] = number_format($x['discount_price']/100, 2);

			$x['market_price'] = number_format($x['market_price']/100, 2);

		}

		$this->assign ( 'list', $list );



		// dump ( $widget );

		return $this->getWidgetHtml ( $widget );

	}

}

