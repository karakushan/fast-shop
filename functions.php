<?php 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Получаем категории товара в виде списка ul
function fs_product_categories()
{
	$product_taxonomy= new FS_Taxonomies_Class();
	$product_taxonomy->get_product_terms('catalog');

}
// Получаем производителей товара в виде списка ul
function fs_product_manufacturer()
{
	$product_taxonomy= new FS_Taxonomies_Class();
	$product_taxonomy->get_product_terms('manufacturer');

}

// Получаем страны производителей товара в виде списка ul
function fs_product_countries()
{
	$product_taxonomy= new FS_Taxonomies_Class();
	$product_taxonomy->get_product_terms('countries');

}

function fs_lightslider($post_id='',$args='')
{
	$galery=new FS_Images_Class();
	

	$galery=$galery->fs_galery_list($post_id,array(90,90));
	if (!$galery) {
		echo "string";
	}else{
		echo "<ul id=\"product_slider\">";
		echo $galery;
		echo "</ul>";

		echo "<script> var product_sc={";
		echo $args;
		echo "}; 
		jQuery(document).ready(function($) {
			$('#product_slider').lightSlider(product_sc); 
		});
	</script>";

}

}

//Получает текущую цену с учётом скидки
function fs_get_price($post_id='')
{
	global $post;

	if($post_id=='') $post_id=$post->ID;
	$price=get_post_meta( $post_id, 'fs_price', true );
	$action=get_post_meta( $post_id, 'fs_discount', true );
	if (!$action) {
		$action=0;
	}
	if (!$price) {
		$price=0;
	}
	$price=round($price-($price*$action/100),2);
	$price=number_format($price, 2, '.', ' ');
	return $price;
}

//Отображает общую сумму продуктов с одним артикулом
function fs_row_price($post_id,$count,$curency=true,$cur_tag_before=' <span>',$cur_tag_after='</span>')
{
	$price=fs_get_price($post_id)*$count;
	$price=number_format($price, 2, '.', ' ');

	if ($curency) {
		$cur_symb=get_option( 'currency_icon', '$');

		$price=$price.$cur_tag_before.$cur_symb.$cur_tag_after;
	}

	return $price;	

}

//Выводит текущую цену с символом валюты и с учётом скидки
function fs_the_price($post_id='',$curency=true,$cur_tag_before=' <span>',$cur_tag_after='</span>')
{
	global $post;
	$cur_symb='';
	if($post_id=='') $post_id=$post->ID;
	$price=get_post_meta( $post_id, 'fs_price', true );
	$action=get_post_meta( $post_id, 'fs_discount', true );
	$displayed_price=get_post_meta($post->ID, 'fs_displayed_price', true);
	if (!$action) {
		$action=0;
	}
	if (!$price) {
		$price=0;
	}
	$price=round($price-($price*$action/100),2);

	if ($curency) {
		$cur_symb=get_option( 'currency_icon', '$');
	}
	if ($displayed_price!="") {
		$displayed_price=str_replace('%d', '%01.2f', $displayed_price);
		printf($displayed_price,$price,$cur_symb);
	} else {
		echo $price.$cur_tag_before.$cur_symb.$cur_tag_after;
	}
	
	
}

/**
 * Получает общую сумму всех продуктов в корзине
 * @param  boolean $show       показывать (по умолчанию) или возвращать
 * @param  string  $cur_before html перед символом валюты
 * @param  string  $cur_after  html после символа валюты
 * @return возвращает или показывает общую сумму с валютой             		
 */
function fs_total_amount($show=true,$cur_before=' <span>',$cur_after='</span>')
{
	$price=0;
	if (count($_SESSION['cart'])) {
		foreach ($_SESSION['cart'] as $key => $count){
			$all_price[$key]=$count['count']*fs_get_price($key); 
		}
		$price=round(array_sum($all_price),2);
		$price=number_format($price,2,'.','');

	}
	$cur_symb=get_option( 'currency_icon', '$');
	if ($show==false) {
		return $price;
	} else {
		echo $price.$cur_before.$cur_symb.$cur_after;
	}
	
}

/**
 * Получаем содержимое корзины в виде массива
 * @return массив элементов корзины в виде:
 *         'id' - id товара,
 *         'name' - название товара,
 *         'count' - количество единиц одного продукта,
 *         'price' - цена за единицу,
 *         'all_price' - общая цена	                 
 */
function fs_get_cart()
{
	if (!isset($_SESSION['cart'])) return;

	$products = array();
	$cur_symb=get_option( 'currency_icon', '$');
	if (count($_SESSION['cart'])) {
		foreach ($_SESSION['cart'] as $key => $count){
			$price=fs_get_price($key);
			$all_price=round($price*$count['count'],2);
			$all_price=number_format($all_price, 2, '.', ' ');
			$products[$key]=array(
				'id'=>$key,
				'name'=>get_the_title($key),
				'count'=>$count['count'],
				'link'=>get_permalink($key),
				'price'=>$price.' '.$cur_symb,
				'all_price'=>$all_price.' '.$cur_symb
				);
		}
	}
	return $products;
}

/**
 * Отображает ссылку для удаления товара 
 * @param  [type] $product_id id удаляемого товара
 * @param  string $html       содержимое тега a
 * @param  string $class      css класс присваемый ссылке
 * @return [type]             отображает ссылку для удаления товара
 */
function fs_delete_position($product_id,$html='',$class='')
{

	$confirm=sprintf(__('Are you sure you want to remove the items %d  from the trash?', 'fast-shop' ),$product_id);
	$nonce=wp_create_nonce('fs_action');
	$curent_url=add_query_arg(array('fs_action'=>'delete_product','fs_request'=>$nonce,'product_id'=>$product_id));
	$remove_title=__('Remove this item','fast-shop');
	echo "<a href=\"#\" class=\"$class\" title=\"$remove_title\" onclick=\"if (confirm('$confirm') ) { document.location.href='$curent_url'}\">$html</a>";
}


/**
 * Получает к-во всех товаров в корзине
 * @param  boolean $show [description]
 * @return [type]        [description]
 */
function fs_product_count($show=false)
{
	$count=0;
	if (count($_SESSION['cart'])) {
		foreach ($_SESSION['cart'] as $key => $count){
			$all_count[$key]=$count['count'];
		}
		$count=array_sum($all_count);
	}

	if ($show==false) {
		return $count;
	} else {
		echo $count;
	}
}


//Выводит текущую цену с символом валюты без учёта скидки
function fs_old_price($post_id='',$curency=true,$cur_tag_before=' <span>',$cur_tag_after='</span>')
{
	global $post;
	
	if($post_id=='') $post_id=$post->ID;
	
	$action=get_post_meta( $post_id, 'fs_discount', true );
	if($action=='' || $action<=0) return;
	$cur_symb='';
	
	$price=get_post_meta( $post_id, 'fs_price', true );
	if (!$price) {
		$price=0;
	}
	if ($curency) {
		$cur_symb=get_option( 'currency_icon', '$');
	}
	echo $price.$cur_tag_before.$cur_symb.$cur_tag_after;
}



/**
 * [Отображает кнопку "в корзину" со всеми необходимыми атрибутамии]
 * @param  string $post_id   [id поста (оставьте пустым в цикле wordpress)]
 * @param  string $label     [надпись на кнопке]
 * @param  string $attr      [атрибуты тега button такие как класс и прочее]
 * @param  string $preloader [html код прелоадера]
 * @param  string $send_icon [html код иконки успешного добавления в корзину]
 * @return [type]            [выводит html код кпопки добавления в корзину]
 */
function fs_add_to_cart($post_id='',$label='',$attr='',$preloader='',$send_icon='')
{
	global $post;
	if($post_id=='') $post_id=$post->ID;
	if ($preloader=='') $preloader='<div class="cssload-container"><div class="cssload-speeding-wheel"></div></div>';

	if ($label=='') {
		$label=__( 'Add to cart', 'fast-shop' );
	}

	echo "<button data-fs-action=\"add-to-cart\" data-product-id=\"$post_id\" data-count=\"1\" data-product-name=\"".get_the_title($post_id)."\" $attr>$label <div class=\"send_ok\">$send_icon</div><span class=\"fs-preloader\">$preloader</span></button> ";
}

//Отображает кнопку сабмита формы заказа
function fs_order_send($label='Отправить заказ',$attr='',$preloader='<div class="cssload-container"><div class="cssload-speeding-wheel"></div></div>')
{
	echo "<button type=\"submit\" $attr data-fs-action=\"order-send\">$label <span class=\"fs-preloader\">$preloader</span></button>";
}

//Получает количество просмотров статьи
function fs_post_views($post_id='')
{
	global $post;
	if($post_id=='') $post_id=$post->ID;
	$views=get_post_meta( $post_id, 'views', true );

	if (!$views) {
		$views=0;
	}
	return $views;
}

/**
 * показывает и скрывает вижет корзины в шаблоне
 * @param  boolean $show_hide показывать скрывать если нет продуктов, false показывать всегда
 * @return показывает виджет корзины
 */
function fs_cart_widget($show_hide=true)
{ 
	global $fs_config;
	$style='';
	if ($show_hide) {
		$style="style=\"display:none\"";
		if (fs_product_count()>0) {
			$style="style=\"display:block\"";
		}
	}

	$template_theme=TEMPLATEPATH.'/fast-shop/cart-widget/widget.php';
	$template_plugin=plugin_dir_path( __FILE__ ).'/templates/front-end/cart-widget/widget.php';
	$template_none=TEMPLATEPATH.'/fast-shop/cart-widget/widget-none.php';
	$template_none_plugin=plugin_dir_path( __FILE__ ).'/templates/front-end/cart-widget/widget-none.php';

	if (!isset($_SESSION['cart'])) {
		if (file_exists($template_none)) {
			$template=$template_none;
		}else{
			$template=$template_none_plugin;
		}
	}else{
		if (count($_SESSION['cart'])==0) {
			if (file_exists($template_none)) {
				$template=$template_none;
			}else{
				$template=$template_none_plugin;
			}
		}else{
			if (file_exists($template_theme)) {
				$template=$template_theme;
			}else{
				$template=$template_plugin;
			}
		}

	}

	echo "<div id=\"fs_cart_widget\" $style>";
	include ($template);
	echo "</div>";
}

// Показывает ссылку на страницу корзины
function fs_cart_url($show=true)
{
	$cart_page_id=get_option( 'cart_url', '' );
	
	if ($show==true) {
		echo get_permalink($cart_page_id);
	}else{
		return get_permalink($cart_page_id);
	}
}

/**
 * показывает ссылку на страницу оформления заказа или оплаты
 * @param  boolean $show показывать (по умолчанию) или возвращать
 * @return строку содержащую ссылку на соответствующую страницу
 */
function fs_checkout_url($show=true)
{
	$checkout_page_id=get_option( 'pay_url', '' );	
	if ($show==true) {
		echo get_permalink($checkout_page_id);
	}else{
		return get_permalink($checkout_page_id);
	}
}

function fs_product_filter($type='select'){
	switch ($type) {
		case 'list': ?>
		<div class="sort-by ">Сортировать по: &nbsp;   
			<a href="<?php echo add_query_arg(array('filter'=>'yes','order'=>'popular',)) ?>">	популярности     </a>&nbsp;
			<a href="<?php echo add_query_arg(array('filter'=>'yes','order'=>'price_p',)) ?>">возрастанию цены     </a>&nbsp;
			<a href="<?php echo add_query_arg(array('filter'=>'yes','order'=>'price_m',)) ?>">убыванию цены</a>
		</div><!-- sort-by -->
		<?php
		break;
		case 'select':

		?>
		<select name="" id="" onchange="document.location=this.options[this.selectedIndex].value">
			<option value="">выберите способ сортировки</option>
			<option value="<?php echo add_query_arg(array('filter'=>'yes','order'=>'price_p')) ?>">цена по возрастанию</option>
			<option value="<?php echo add_query_arg(array('filter'=>'yes','order'=>'price_m')) ?>">цена по убыванию</option>
			<option value="<?php echo add_query_arg(array('filter'=>'yes','order'=>'popular')) ?>">по популярности</option>

		</select>
		<?php
		break;
	}
	

	if (isset($_GET['filter']) ) {
		$query='';
		switch ($_GET['order']) {
			case 'popular':
			$query=array(
				'post_type'=>'product',
				'meta_key'=>'views',
				'orderby'=>'meta_value_num',
				'order'=>'DESC',
				);
			break;	
			case 'price_p':
			$query=array(
				'post_type'=>'product',
				'meta_key'=>'fs_price',
				'orderby'=>'meta_value_num',
				'order'=>'ASC',

				);
			break;		
			case 'price_m':
			$query=array(
				'post_type'=>'product',
				'meta_key'=>'fs_price',
				'orderby'=>'meta_value_num',
				'order'=>'DESC',

				);
			break;
			default:
			$query=array(
				'post_type'=>'product'
				);
			break;
		}
		query_posts($query);
	}
}

//Показывает наличие продукта
function fs_aviable_product($product_id='',$aviable_text='',$no_aviable_text='')
{
	global $post;
	if ($product_id=='') $product_id=$post->ID;

	if (!is_numeric($product_id))  exit('id поста может быть только целым числом');

	$availability=get_post_meta($product_id,'fs_availability',true);
	if ($availability==1) {
		echo $aviable_text;
	}else{
		echo $no_aviable_text;
	}
}

/**
 * Отоюражает поле для ввода количества добавляемых продуктов в корзину
 * @param  string  $product_id [id продукта]
 * @param  boolean $wrap       [если указать true (по умолчанию) выведет стилизированное поле иначе обычный input type number]
 * @return [type]              [description]
 */
function fs_quantity_product($product_id='',$wrap_class='count_wrap',$args='')
{
	global $post;
	if ($product_id=='') $product_id=$post->ID;

	if (!is_numeric($product_id))  exit;
	echo "<div class=\"$wrap_class\"><input type=\"number\" name=\"count-ch\" data-fs-action=\"change_count\" data-count-id=\"".get_the_id()."\" value=\"1\" min=\"1\" $args></div>";
}

