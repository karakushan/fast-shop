<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly


function fs_dropdown_attr_group( $group_id = 0, $post_id = 0, $args = array() ) {

	if ( empty( $post_id ) ) {
		global $post;
		$post_id = $post->ID;
	}
	$args  = wp_parse_args( $args, array(
		'class' => ''
	) );
	$class = ! empty( $args['class'] ) ? 'class="' . $args['class'] . '"' : '';
	$terms = get_the_terms( $post_id, 'product-attributes' );

	if ( $terms ) {
		echo '<select name="' . $group_id . '" ' . $class . ' data-fs-element="attr" data-product-id="' . $post_id . '">';
		echo '<option value="">Выберите</option>';
		foreach ( $terms as $term ) {
			if ( $term->parent == $group_id ) {
				echo '<option value="' . $term->term_id . '">' . $term->name . '</option>';
			}
		}
		echo '<select>';
	}
}

/**
 * @param integer $post_id - id записи
 * @param array $args - массив аргументов: http://sachinchoolur.github.io/lightslider/settings.html
 */
function fs_lightslider( $post_id = 0, $args = array() ) {
	global $post;
	$post_id = empty( $post_id ) ? $post->ID : (int) $post_id;
	$galery  = new FS\FS_Images_Class();
	$galery->lightslider( $post_id, $args );
}

/**
 * Возвращает массив изображений галереи товара
 *
 * @param int $post_id - id поста
 * @param bool $thumbnail - включать ли миниатюру поста в список
 *
 * @return array
 */
function fs_get_slider_images( $post_id = 0, $thumbnail = true ) {
	global $post;
	$post_id = empty( $post_id ) ? $post->ID : (int) $post_id;
	$galery  = new FS\FS_Images_Class();
	$images  = $galery->fs_galery_images( $post_id, $thumbnail );

	return $images;
}


//Получает текущую цену с учётом скидки
/**
 * @param int $post_id - id поста, в данном случае товара (по умолчанию берётся из глобальной переменной $post)
 * @param boolean $filter - включить или отключить фильтры типа add_filter дя получения базовой цены (по умолчанию включены)
 *
 * @return float $price - значение цены
 */
function fs_get_price( $post_id = 0, $filter = true ) {
	$config = new \FS\FS_Config();//класс основных настроек плагина

	// устанавливаем id поста
	global $post;
	$post_id = empty( $post_id ) && isset( $post ) ? $post->ID : (int) $post_id;

	//узнаём какой тип скидки активирован в настройках (% или фикс)
	$action_type = isset( $config->options['action_count'] ) && $config->options['action_count'] == 1 ? 1 : 0;

	// получаем возможные типы цен
	$base_price   = get_post_meta( $post_id, $config->meta['price'], true );//базовая и главная цена
	$action_price = get_post_meta( $post_id, $config->meta['action_price'], true );//акионная цена
	$price        = empty( $base_price ) ? 0 : (float) $base_price;
	$action_price = empty( $action_price ) ? 0 : (float) $action_price;

	//если поле акционной цены заполнено возвращаем его
	if ( $action_price > 0 ) {
		$price = $action_price;
	}

	return (float) $price;
}

//Отображает общую сумму продуктов с одним артикулом
/**
 * @param $post_id - id
 * @param $count - к-во товаров
 * @param bool $curency
 * @param string $wpap формат отображения цены вместе с валютой
 *
 * @return int|mixed|string
 */
function fs_row_price( $post_id = 0, $count = 0, $curency = true, $wrap = '%s <span>%s</span>' ) {
	global $post;
	$post_id = empty( $post_id ) ? $post->ID : (int) $post_id;
	$price   = fs_get_price( $post_id );
	$price   = $price * $count;
	if ( $curency ) {
		$price = apply_filters( 'fs_price_format', $price );
		$price = sprintf( $wrap, $price, fs_currency() );
	}

	return $price;
}

/**
 * получает цену сумму товаров одного наименования (позиции)
 *
 * @param  [type]  $post_id [description]
 * @param  [type]  $count   [description]
 * @param  boolean $curency [description]
 * @param  string $wrap [description]
 *
 * @return [type]           [description]
 */
function fs_row_wholesale_price( $post_id, $count, $curency = true, $wrap = '%s <span>%s</span>' ) {
	global $post;
	$post_id = empty( $post_id ) ? $post->ID : (int) $post_id;
	$price   = fs_get_wholesale_price( $post_id ) * $count;
	if ( $curency ) {
		$price = apply_filters( 'fs_price_format', $price );
		$price = sprintf( $wrap, $price, fs_currency() );
	}

	return $price;
}


/**
 * Выводит текущую цену с учётом скидки
 *
 * @param string $post_id - id товара
 * @param string $wrap - html обёртка для цены
 */
function fs_the_price( $post_id = 0, $wrap = "%s <span>%s</span>" ) {
	global $post;
	$cur_symb = fs_currency();
	$post_id  = empty( $post_id ) ? $post->ID : $post_id;
	$price    = fs_get_price( $post_id );
	$price    = apply_filters( 'fs_price_format', $price );
	printf( $wrap, $price, $cur_symb );
}

/**
 * Выводит текущую оптовую цену с учётом скидки вместе с валютой сайта
 *
 * @param string $post_id - id товара
 * @param string $wrap - html обёртка для цены
 */
function fs_the_wholesale_price( $post_id = 0, $wrap = "<span>%s</span>" ) {
	$price = fs_get_wholesale_price( $post_id );
	$price = apply_filters( 'fs_price_format', $price );
	printf( $wrap, $price . ' <span>' . fs_currency() . '</span>' );
}

/**
 * Получает текущую оптовую цену с учётом скидки
 *
 * @param string $post_id - id товара
 *
 * @return float price      - значение цены
 */
function fs_get_wholesale_price( $post_id = 0 ) {
	$config = new \FS\FS_Config();
	global $post;
	$post_id = empty( $post_id ) ? $post->ID : (int) $post_id;

	$old_price = get_post_meta( $post_id, $config->meta['wholesale_price'], 1 );
	$new_price = get_post_meta( $post_id, $config->meta['wholesale_price_action'], 1 );
	$price     = ! empty( $new_price ) ? (float) $new_price : (float) $old_price;
	if ( empty( $price ) ) {
		$price = 0;
	}

	return $price;
}

/**
 * Получает общую сумму всех продуктов в корзине
 *
 * @param  boolean $show показывать (по умолчанию) или возвращать
 * @param  string $cur_before html перед символом валюты
 * @param  string $cur_after html после символа валюты
 *
 * @return возвращает или показывает общую сумму с валютой
 */
function fs_total_amount( $products = array(), $show = true, $wrap = '%s <span>%s</span>' ) {

	$all_price = array();
	$price     = '';
	$products  = ! empty( $_SESSION['cart'] ) ? $_SESSION['cart'] : $products;
	foreach ( $products as $key => $count ) {
		$all_price[ $key ] = $count['count'] * fs_get_price( $key );
	}
	$price = array_sum( $all_price );

	if ( $show == false ) {
		return $price;
	} else {
		$price = apply_filters( 'fs_price_format', $price );
		$price = sprintf( $wrap, $price, fs_currency() );
		echo $price;
	}

}

/**
 * Получает общую сумму всех продуктов в корзине
 *
 * @param  boolean $show показывать (по умолчанию) или возвращать
 * @param  string $cur_before html перед символом валюты
 * @param  string $cur_after html после символа валюты
 *
 * @return возвращает или показывает общую сумму с валютой
 */
function fs_total_amount_filtering( $products = array(), $show = true, $wrap = '%s <span>%s</span>', $filter = false ) {
	$all_price = array();
	$products  = ! empty( $_SESSION['cart'] ) ? $_SESSION['cart'] : $products;
	foreach ( $products as $key => $count ) {
		$all_price[ $key ] = $count['count'] * fs_get_price( $key, $filter );
	}
	$price = array_sum( $all_price );
	$price = apply_filters( 'fs_price_format', $price );
	$price = sprintf( $wrap, $price, fs_currency() );
	if ( $show == false ) {
		return $price;
	} else {
		echo $price;
	}
}

/**
 * выводит или отдаёт общую сумму всех товаров по оптовой цене
 *
 * @param bool $echo - выводить или возвращать (по умолчанию показывать)
 * @param string $wrap - обёртка для выводимой цены
 *
 * @return mixed|number|void
 */
function fs_total_wholesale_amount( $products = array(), $echo = true, $wrap = '%s <span>%s</span>' ) {
	$all_price = array();
	if ( empty( $products ) && ! empty( $_SESSION['cart'] ) ) {
		$products = $_SESSION['cart'];
	}
	if ( $products ) {
		foreach ( $products as $key => $count ) {
			$all_price[ $key ] = $count['count'] * fs_get_wholesale_price( $key );
		}
	}
	$amount = array_sum( $all_price );
	$amount = apply_filters( 'fs_price_format', $amount );
	$amount = sprintf( $wrap, $amount, fs_currency() );
	if ( $echo ) {
		echo $amount;
	} else {
		return $amount;
	}
}

/**
 * возвращает к-во всех товаров в корзине
 * @return [type] [description]
 */
function fs_total_count( $echo = true ) {
	$count = array();
	if ( isset( $_SESSION['cart'] ) ) {
		foreach ( $_SESSION['cart'] as $key => $product ) {
			$count[] = $product['count'];
		}
	}
	$all_count = array_sum( $count );
	if ( $echo ) {
		echo $all_count;
	} else {
		return $all_count;
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
function fs_get_cart( $args = array() ) {
	if ( ! isset( $_SESSION['cart'] ) ) {
		return false;
	}
	$args     = wp_parse_args( $args, array(
		'price_format' => '%s <span>%s</span>'
	) );
	$products = array();
	if ( ! empty( $_SESSION['cart'] ) ) {
		foreach ( $_SESSION['cart'] as $key => $count ) {
			if ( $key == 0 ) {
				continue;
			}
			$price      = fs_get_price( $key );
			$price_show = apply_filters( 'fs_price_format', $price );
			$c          = (int) $count['count'];
			$all_price  = $price * $c;
			$all_price  = apply_filters( 'fs_price_format', $all_price );
			$attr       = array();
			if ( ! empty( $count['attr'] ) ) {
				foreach ( $count['attr'] as $term ) {
					$t = get_term_by( 'term_taxonomy_id', $term );
					if ( $t ) {
						$attr[ $term ] = array(
							'name'       => $t->name,
							'group_name' => get_term_field( 'name', $t->parent )
						);
					}
				}
			}
			$products[ $key ] = array(
				'id'         => $key,
				'name'       => get_the_title( $key ),
				'count'      => $c,
				'thumb'      => get_the_post_thumbnail_url( $key, 'full' ),
				'attr'       => $attr,
				'link'       => get_permalink( $key ),
				'price'      => sprintf( $args['price_format'], $price_show, fs_currency() ),
				'base_price' => sprintf( $args['price_format'], fs_base_price( $key, false ), fs_currency() ),
				'all_price'  => sprintf( $args['price_format'], $all_price, fs_currency() ),
				'code'       => fs_product_code( $key ),
				'currency'   => fs_currency()
			);
		}
	}

	return $products;
}

/**
 * выводит кнопку удаления товара из корзины
 *
 * @param $product_id - ID удаляемого товара
 * @param $args - массив аргументов для кнопки или ссылки
 *        'text'  - содержимое кнопки, по умолчанию '&#10005;',
 *        'type'  - тип тега ссылка 'link' или 'button',
 *        'class' - класс для кнопки, ссылки (по умолчанию класс 'fs-delete-position')
 *
 * @return bool
 */
function fs_delete_position( $product_id, $args ) {
	$args      = wp_parse_args( $args, array(
		'text'  => '&#10005;',
		'type'  => 'link',
		'class' => 'fs-delete-position'
	) );
	$html_atts = fs_parse_attr( array(), array(
		'class'        => $args['class'],
		'title'        => sprintf( __( 'Remove items %s', 'fast-shop' ), get_the_title( $product_id ) ),
		'data-fs-type' => 'product-delete',
		'data-fs-id'   => $product_id,
		'data-fs-name' => get_the_title( $product_id )

	) );

	$text = sanitize_text_field( $args['text'] );

	switch ( $args['type'] ) {
		case 'link':
			echo '<a href="#" ' . $html_atts . '>' . $text . '</a>';
			break;
		case 'button':
			echo '<button type="button" ' . $html_atts . '>' . $text . '</button>';
			break;
		default:
			echo '<a href="#" ' . $html_atts . '>' . $text . '</a>';
			break;
	}

	return true;
}


/**
 * Выводит к-во всех товаров в корзине
 *
 * @param  array $products список товаров, по умолчанию $_SESSION['cart']
 * @param  boolean $echo выводить результат или возвращать, по умолчанию выводить
 *
 * @return [type]        [description]
 */
function fs_product_count( $products = array(), $echo = true ) {
	$all_count = array();
	if ( ! empty( $_SESSION['cart'] ) || ! is_array( $products ) ) {
		$products = isset( $_SESSION['cart'] ) ? $_SESSION['cart'] : array();
	}
	if ( count( $products ) ) {
		foreach ( $products as $key => $count ) {
			$all_count[ $key ] = $count['count'];
		}
	}
	$count = array_sum( $all_count );
	$count = (int) $count;
	if ( $echo ) {
		echo $count;
	} else {
		return $count;
	}
}


//Выводит текущую цену с символом валюты без учёта скидки
/**
 * @param int $post_id - id товара
 * @param bool $echo - вывести или возвратить (по умолчанию вывести)
 * @param string $wrap - html обёртка для цены
 *
 * @return mixed выводит отформатированную цену или возвращает её для дальнейшей обработки
 */
function fs_base_price( $post_id = 0, $echo = true, $wrap = '%s <span>%s</span>' ) {
	global $post;
	$config  = new \FS\FS_Config();
	$post_id = empty( $post_id ) ? $post->ID : $post_id;
	$price   = get_post_meta( $post_id, $config->meta['price'], 1 );
	if ( $price == fs_get_price( $post_id ) ) {
		return;
	}
	$price       = empty( $price ) ? 0 : (float) $price;
	$price_float = $price;
	$price       = apply_filters( 'fs_price_format', $price );
	$cur_symb    = fs_currency();
	if ( $echo ) {
		printf( $wrap, $price, $cur_symb );
	} else {
		return $price_float;
	}
}


/**
 * [Отображает кнопку "в корзину" со всеми необходимыми атрибутамии]
 *
 * @param  int $post_id [id поста (оставьте пустым в цикле wordpress)]
 * @param  string $label [надпись на кнопке]
 * @param  array $attr дополнительные атрибуты
 */
function fs_add_to_cart( $post_id = 0, $label = '', $attr = array() ) {
	global $post;
	$post_id = empty( $post_id ) ? $post->ID : $post_id;

	$attr = wp_parse_args( $attr,
		array(
			'json'      => array( 'count' => 1, 'attr' => new stdClass() ),
			'preloader' => '',
			'class'     => '',
			'type'      => 'button'
		)
	);

	/*
	Устанавливаем правильную, локализированную надпись на кнопке "В корзину"
	с возможность установки собственного перевода
	 */
	if ( empty( $label ) ) {
		$label = __( 'Add to cart', 'fast-shop' );
	} else {
		$my_theme = wp_get_theme();
		$label    = __( $label, $my_theme->get( 'TextDomain' ) );
	}

	//Добавляем к json свои значения
	$attr_json = json_encode( $attr['json'] );

	$attr_set   = array(
		'type'              => 'button',
		'data-action'       => 'add-to-cart',
		'data-product-id'   => $post_id,
		'data-product-name' => get_the_title( $post_id ),
		'id'                => 'fs-atc-' . $post_id,
		'data-attr'         => $attr_json
	);
	$attributes = fs_parse_attr( array(), $attr_set );

	/* позволяем устанавливать разные html элементы в качестве кнопки */
	switch ( $attr['type'] ) {
		case 'link':
			$button = '<a href="#" ' . $attributes . ' class="' . $attr['class'] . '">' . $label . '</a>';
			break;
		default:
			$button = '<button ' . $attributes . ' class="' . $attr['class'] . '">' . $label . '</button>';
			break;
	}

	echo apply_filters( 'fs_add_to_cart_filter', $button );
}

//Отображает кнопку сабмита формы заказа
function fs_order_send( $label = 'Отправить заказ', $attr = '', $preloader = '<div class="cssload-container"><div class="cssload-speeding-wheel"></div></div>' ) {
	echo "<button type=\"submit\" $attr data-fs-action=\"order-send\">$label <span class=\"fs-preloader\">$preloader</span></button>";
}

function fs_order_send_form() {
	$form = new \FS\FS_Shortcode;
	echo $form->order_send();
}

//Получает количество просмотров статьи
function fs_post_views( $post_id = '' ) {
	global $post;
	$post_id = empty( $post_id ) ? $post->ID : $post_id;

	$views = get_post_meta( $post_id, 'views', true );

	if ( ! $views ) {
		$views = 0;
	}

	return $views;
}

/**
 * показывает вижет корзины в шаблоне
 * @return показывает виджет корзины
 */
function fs_cart_widget( $attr = array() ) {

	$template_theme = TEMPLATEPATH . '/fast-shop/cart-widget/widget.php';
	$template       = plugin_dir_path( __FILE__ ) . 'templates/front-end/cart-widget/widget.php';

	if ( file_exists( $template_theme ) ) {
		$template = $template_theme;
	}
	$attr_set = array(
		'data-fs-element' => 'cart-widget'
	);
	$attr     = fs_parse_attr( $attr, $attr_set );
	echo "<div  $attr>";
	require $template;
	echo "</div>";
}

// Показывает ссылку на страницу корзины
function fs_cart_url( $show = true ) {
	$cart_page = get_permalink( fs_option( 'page_cart', 0 ) );
	if ( $show == true ) {
		echo $cart_page;
	} else {
		return $cart_page;
	}
}

/**
 * показывает ссылку на страницу оформления заказа или оплаты
 *
 * @param  boolean $show показывать (по умолчанию) или возвращать
 *
 * @return строку содержащую ссылку на соответствующую страницу
 */
function fs_checkout_url( $show = true ) {
	$checkout_page_id = fs_option( 'page_payment', 0 );
	if ( $show == true ) {
		echo get_permalink( $checkout_page_id );
	} else {
		return get_permalink( $checkout_page_id );
	}
}


/**
 * Функция поверяет наличие товара на складе
 *
 * @param int $post_id id записи
 *
 * @return bool  true - товар есть на складе, false - нет
 */
function fs_aviable_product( $post_id = 0 ) {
	global $post;
	$config       = new FS\FS_Config;
	$product_id   = empty( $post_id ) ? $post->ID : (int) $post_id;
	$availability = get_post_meta( $product_id, $config->meta['remaining_amount'], true );

	if ( $availability == '' || $availability > 0 ) {
		$aviable = true;
	} else {
		$aviable = false;
	}

	return $aviable;
}

/**
 * Отоюражает поле для ввода количества добавляемых продуктов в корзину
 *
 * @param  int $product_id - id продукта
 *
 */
function fs_quantity_product( $product_id = 0, $echo = true ) {
	global $post;
	$product_id  = ! empty( $product_id ) ? $product_id : $post->ID;
	$quantity_el = '<div class="fs-quantity-product">
    <button type="button" class="plus" data-fs-count="pluss" data-target="#product-quantify-' . $product_id . '">+</button> 
    <input type="text" name="" value="1" data-fs-action="change_count" id="product-quantify-' . $product_id . '" data-fs-product-id="' . $product_id . '"> 
    <button type="button" class="minus" data-fs-count="minus" data-target="#product-quantify-' . $product_id . '">-</button> </div>';
	$quantity_el = apply_filters( 'fs_quantity_product', $quantity_el );
	if ( $echo ) {
		echo $quantity_el;
	} else {
		return $quantity_el;
	}
}

/**
 * Выводит поле для изменения к-ва товаров в корзине
 *
 * @param $product_id
 * @param $value
 * @param array $args
 */
function fs_cart_quantity( $product_id, $value, $args = array() ) {
	$value      = intval( $value );
	$product_id = intval( $product_id );
	$args       = wp_parse_args( $args, array(
		'wrapper_class' => 'fs-qty-wrapper',
		'position'      => '%pluss% %input% %minus%',
		'pluss'         => array( 'class' => 'fs-pluss', 'content' => '+' ),
		'minus'         => array( 'class' => 'fs-minus', 'content' => '-' ),
		'input'         => array( 'class' => 'fs-cart-quantity' )
	) );

	$pluss    = '<button type="button" class="' . $args['pluss']['class'] . '" data-fs-count="pluss" data-target="#product-quantify-' . $product_id . '">' . $args['pluss']['content'] . '</button> ';
	$minus    = '<button type="button" class="' . $args['minus']['class'] . '" data-fs-count="minus" data-target="#product-quantify-' . $product_id . '">' . $args['minus']['content'] . '</button> </div>';
	$input    = '<input type="text" name="" value="' . $value . '" class="' . $args['input']['class'] . '" data-fs-type="cart-quantity" id="product-quantify-' . $product_id . '" data-product-id="' . $product_id . '">';
	$quantity = str_replace( array( '%pluss%', '%minus%', '%input%' ), array(
		$pluss,
		$minus,
		$input
	), $args['position'] );
	$quantity = '<div class="' . $args['wrapper_class'] . '">' . $quantity . '</div>';
	echo $quantity;
}

/**
 * Парсит урл и возвращает всё что находится до знака ?
 *
 * @param  string $url строка url которую нужно спарсить
 *
 * @return string      возвращает строку урл
 */
function fs_parse_url( $url = '' ) {
	$url   = ( filter_var( $url, FILTER_VALIDATE_URL ) ) ? $url : $_SERVER['REQUEST_URI'];
	$parse = explode( '?', $url );

	return $parse[0];
}

/**
 * @param string $post_id
 *
 * @return bool|mixed
 */
function fs_action( $post_id = 0 ) {
	global $post;
	$post_id = empty( $post_id ) ? $post->ID : (int) $post_id;
	if ( fs_base_price( $post_id, false ) > fs_get_price( $post_id ) ) {
		$action = true;
	} else {
		$action = false;
	}

	return $action;
}


/**
 * Возвращает объект просмотренных товаров или записей
 * @return array
 */
function fs_user_viewed() {
	$viewed = isset( $_SESSION['fs_user_settings']['viewed_product'] ) ? $_SESSION['fs_user_settings']['viewed_product'] : array();
	$posts  = new stdClass();
	if ( ! empty( $viewed ) ) {
		$posts = new WP_Query( array( 'post_type' => 'product', 'post__in' => $viewed ) );
	}

	return $posts;
}

/**
 * Получаем симовол валюты
 * @return string
 */
function fs_currency( $wrap = false ) {
	$config   = new \FS\FS_Config();
	$currency = ! empty( $config->options['currency_symbol'] ) ? $config->options['currency_symbol'] : '$';
	if ( $wrap ) {
		return sprintf( '<span>%s</span>', $currency );
	}

	return $currency;
}

/**
 * Возвращает данные опции
 *
 * @param $option_name - название опции
 * @param $default - значение по умолчанию
 *
 * @return string
 */
function fs_option( $option_name, $default = '' ) {
	$config  = new \FS\FS_Config();
	$options = $config->options;
	$option  = ! empty( $options[ $option_name ] ) ? $options[ $option_name ] : $default;
	$option  = wp_unslash( $option );

	return $option;
}

/**
 * @return bool|массив
 */
function fs_products_loop() {
	$cart = fs_get_cart();
	if ( $cart ) {
		return $cart;
	} else {
		return false;
	}
}

/**
 * Эта функция выводит кнопку удаления всех товаров в корзине
 *
 * @param string $text - надпись на кнопке (мультиязык)
 * @param string $class - класс присваемый кнопке
 */
function fs_delete_cart( $text = 'Remove all items', $class = '' ) {
	echo '<button class="' . sanitize_html_class( $class, '' ) . '" data-fs-type="delete-cart" data-url="' . wp_nonce_url( add_query_arg( array( "fs_action" => "delete-cart" ) ), "fs_action" ) . '">' . __( $text, 'fast-shop' ) . '</button> ';
}

/**
 * Выводит процент или сумму скидки(в зависимости от настрорек)
 *
 * @param  string $product_id - id товара(записи)
 * @param  string $wrap - html обёртка для скидки
 *
 * @return выводит или возвращает скидку если таковая имеется или пустая строка
 */
function fs_amount_discount( $product_id = 0, $echo = true, $wrap = '<span>%s</span>' ) {
	global $post;
	$config          = new FS\FS_Config;
	$product_id      = empty( $product_id ) ? $post->ID : $product_id;
	$action_symbol   = isset( $config->options['action_count'] ) && $config->options['action_count'] == 1 ? '<span>%</span>' : '<span>' . fs_currency() . '</span>';
	$discount_meta   = (float) get_post_meta( $product_id, $config->meta['discount'], 1 );
	$discount        = empty( $discount_meta ) ? '' : sprintf( $wrap, $discount_meta . ' ' . $action_symbol );
	$discount_return = empty( $discount_meta ) ? 0 : $discount_meta;
	if ( $echo ) {
		echo $discount;
	} else {
		return $discount_return;
	}

}


/**
 * Добавляет возможность фильтрации по определёному атрибуту
 *
 * @param string $group название группы (slug)
 * @param string $type тип фильтра 'option' (список опций в теге "select",по умолчанию) или обычный список "ul"
 * @param string $option_default первая опция (текст) если выбран 2 параметр "option"
 */
function fs_attr_group_filter( $group, $type = 'option', $option_default = 'Выберите значение' ) {
	$fs_filter = new FS\FS_Filters;
	echo $fs_filter->attr_group_filter( $group, $type, $option_default );
}

/**
 * @param int $price_max
 */
function fs_range_slider() {

	$price_max = fs_price_max();
	$curency   = fs_currency();
	$slider    = '<div class="slider">
    <div data-fs-element="range-slider" id="range-slider"></div>
    <div class="fs-price-show">
        <span data-fs-element="range-start">0 <span>' . $curency . '</span></span>
        <span data-fs-element="range-end">' . $price_max . ' <span>' . $curency . '</span>
    </span>
</div>
</div>';
	echo $slider;
}//end range_slider()

/**
 * Функция получает значение максимальной цены установленной на сайте
 * @return float|int|null|string
 */
function fs_price_max( $filter = true ) {
	global $wpdb;
	$config         = new FS\FS_Config();
	$meta_field     = $config->meta['price'];
	$meta_value_max = $wpdb->get_var( "SELECT (meta_value + 0.01 ) AS meta_values FROM $wpdb->postmeta WHERE meta_key='$meta_field' ORDER BY meta_values DESC " );
	$meta_value_max = ! is_null( $meta_value_max ) ? (float) $meta_value_max : 20000;
	if ( $filter ) {
		$max = apply_filters( 'fs_price_format', $meta_value_max );
	} else {
		$max = $meta_value_max;
	}

	return $max;
}

/**
 * функция отображает кнопку "добавить в список желаний"
 *
 * @param  integer $post_id - id записи
 * @param  array $args - дополнительные аргументы массивом
 *
 * @return [type]           [description]
 */
function fs_wishlist_button( $post_id = 0, $args = '' ) {
	global $post;
	$post_id = empty( $post_id ) ? $post->ID : $post_id;
	// определим параметры по умолчанию
	$defaults = array(
		'attr'    => '',
		'content' => __( 'add to wish list', 'fast-shop' ),
		'type'    => 'button'
	);
	$args     = wp_parse_args( $args, $defaults );
	switch ( $args['type'] ) {
		case 'link':
			echo '<a  data-fs-action="wishlist" ' . $args['attr'] . ' data-name="' . get_the_title( $post_id ) . '"  data-product-id="' . $post_id . '"><span class="whishlist-message"></span>' . $args['content'] . '</a>';
			break;

		default:
			echo '<button data-fs-action="wishlist" ' . $args['attr'] . '  data-product-id="' . $post_id . '" data-name="' . get_the_title( $post_id ) . '"><span class="whishlist-message"></span>' . $args['content'] . '</button>';
			break;
	}

}

/**
 * Функция транслитерации русских букв
 *
 * @param $s
 *
 * @return mixed|string
 */
function fs_transliteration( $s ) {
	$s = (string) $s; // преобразуем в строковое значение
	$s = strip_tags( $s ); // убираем HTML-теги
	$s = str_replace( array( "\n", "\r" ), " ", $s ); // убираем перевод каретки
	$s = preg_replace( "/\s+/", ' ', $s ); // удаляем повторяющие пробелы
	$s = trim( $s ); // убираем пробелы в начале и конце строки
	$s = function_exists( 'mb_strtolower' ) ? mb_strtolower( $s ) : strtolower( $s ); // переводим строку в нижний регистр (иногда надо задать локаль)
	$s = strtr( $s, array(
		'а' => 'a',
		'б' => 'b',
		'в' => 'v',
		'г' => 'g',
		'д' => 'd',
		'е' => 'e',
		'ё' => 'e',
		'ж' => 'j',
		'з' => 'z',
		'и' => 'i',
		'й' => 'y',
		'к' => 'k',
		'л' => 'l',
		'м' => 'm',
		'н' => 'n',
		'о' => 'o',
		'п' => 'p',
		'р' => 'r',
		'с' => 's',
		'т' => 't',
		'у' => 'u',
		'ф' => 'f',
		'х' => 'h',
		'ц' => 'c',
		'ч' => 'ch',
		'ш' => 'sh',
		'щ' => 'shch',
		'ы' => 'y',
		'э' => 'e',
		'ю' => 'yu',
		'я' => 'ya',
		'ъ' => '',
		'ь' => ''
	) );
	$s = preg_replace( "/[^0-9a-z-_ ]/i", "", $s ); // очищаем строку от недопустимых символов
	$s = str_replace( " ", "-", $s ); // заменяем пробелы знаком минус

	return $s; // возвращаем результат
}

/**
 * Подключает шаблон $template из директории темы, если шаблон остсуствует ищет в папке "/templates/front-end/" плагина
 *
 * @param $template - название папки и шаблона без расширения
 */
function fs_frontend_template( $template, $args = array() ) {
	global $wpdb;
	extract( wp_parse_args( $args, array() ) );


	$template_plugin = FS_PLUGIN_PATH . '/templates/front-end/' . $template . '.php';
	$template_theme  = TEMPLATEPATH . '/fast-shop/' . $template . '.php';
	ob_start();
	if ( file_exists( $template_theme ) ) {
		include( $template_theme );
	} elseif ( file_exists( $template_plugin ) ) {
		include( $template_plugin );
	} else {
		echo 'файл шаблона ' . $template . ' не найден в функции ' . __FUNCTION__;
	}
	$template = ob_get_clean();

	return apply_filters( 'fs_frontend_template', $template );
}

function fs_get_current_user() {
	$user = wp_get_current_user();
	if ( $user->exists() ) {
		$profile_update       = empty( $user->profile_update ) ? strtotime( $user->user_registered ) : $user->profile_update;
		$user->email          = $user->user_email;
		$user->phone          = get_user_meta( $user->ID, 'phone', 1 );
		$user->city           = get_user_meta( $user->ID, 'city', 1 );
		$user->adress         = get_user_meta( $user->ID, 'adress', 1 );
		$user->birth_day      = get_user_meta( $user->ID, 'birth_day', 1 );
		$user->profile_update = $profile_update;
		$user->gender         = get_user_meta( $user->ID, 'gender', 1 );
	}

	return $user;
}

/**
 * Получает шаблон формы входа
 * @return mixed|void
 */
function fs_login_form() {
	if ( ! is_user_logged_in() ) {
		$template = fs_frontend_template( 'auth/login' );
	} else {
		$template = fs_frontend_template( 'auth/user-authorized' );
	}

	return apply_filters( 'fs_login_form', $template );
}

/**
 * Получает шаблон формы регистрации
 * @return mixed|void
 */
function fs_register_form() {
	if ( ! is_user_logged_in() ) {
		$template = fs_frontend_template( 'auth/register' );
	} else {
		$template = fs_frontend_template( 'auth/user-authorized' );
	}

	return apply_filters( 'fs_register_form', $template );
}

/**
 * Получает шаблон формы входа
 * @return mixed|void
 */
function fs_user_cabinet() {
	$template = fs_frontend_template( 'auth/cabinet' );;

	return apply_filters( 'fs_user_cabinet', $template );
}

function fs_page_content() {
	if ( empty( $_GET['fs-page'] ) ) {
		$page = 'profile';
	}
	$page     = filter_input( INPUT_GET, 'fs-page', FILTER_SANITIZE_URL );
	$template = '';
	$pages    = array( 'profile', 'conditions' );
	if ( in_array( $page, $pages ) ) {
		$template = fs_frontend_template( 'auth/' . $page );
	} else {
		$template = fs_frontend_template( 'auth/profile' );
	}

	echo $template;
}

/**
 * Отображает кнопку быстрого заказа с модальным окном Bootstrap
 *
 * @param int $post_id
 * @param array $attr
 */
function fs_quick_order_button( $post_id = 0, $attr = array() ) {
	global $post;
	$attr    = wp_parse_args( $attr, array(
		'data-toggle' => "modal",
		'href'        => '#fast-order'
	) );
	$str_att = array();
	if ( $attr ) {
		foreach ( $attr as $key => $at ) {
			$str_att[] = sanitize_key( $key ) . '="' . $at . '"';
		}
	}
	$post_id   = empty( $post_id ) ? $post->ID : $post_id;
	$impl_attr = implode( ' ', $str_att );
	echo '<button data-fs-action="quick_order_button" data-product-id="' . $post_id . '" data-product-name="' . get_the_title( $post_id ) . '" ' . $impl_attr . '>Заказать</button>';
}

/**
 * получает артикул товара по переданному id поста
 *
 * @param  int|integer $product_id - id поста
 * @param  string $wrap - html обёртка для артикула (по умолчанию нет)
 * @param bool $echo возвращать или выводить, по умолчанию возвращать
 *
 * @return string - артикул товара
 */
function fs_product_code( $product_id = 0, $wrap = '%s', $echo = false ) {
	global $post;
	$config     = new \FS\FS_Config();
	$product_id = $product_id == 0 ? $post->ID : $product_id;
	$articul    = get_post_meta( $product_id, $config->meta['product_article'], 1 );
	if ( empty( $articul ) ) {
		$articul = $product_id;
	}
	if ( $wrap ) {
		$articul = sprintf( $wrap, $articul );
	}
	if ( $echo ) {
		echo $articul;
	} else {
		return $articul;
	}
}

/**
 * возвращает количество или запас товаров на складе (если значение пустое выводится 1)
 *
 * @param  int|integer $product_id - id товара (записи wordpress)
 *
 * @return int|integer                  запас товаров на складе
 */
function fs_remaining_amount( $product_id = 0 ) {
	global $post;
	$product_id = ! empty( $product_id ) ? $product_id : $post->ID;
	$config     = new FS\FS_Config();
	$meta_field = $config->meta['remaining_amount'];
	$amount     = get_post_meta( $product_id, $meta_field, true );
	$amount     = ( $amount === '' ) ? '' : (int) $amount;

	return $amount;
}

/**
 * возвращает все зарегистрированные типы цен
 * @return array -  массив всех зарегистрированных цен
 */
function fs_get_all_prices() {
	$config_prices = \FS\FS_Config::$prices;
	$prices        = apply_filters( 'fs_prices', $config_prices );

	return $prices;
}


function fs_get_type_price( $product_id = 0, $price_type = 'price' ) {
	global $post;
	$product_id = empty( $product_id ) ? $post->ID : $product_id;
	$prices     = fs_get_all_prices();
	$price      = get_post_meta( $product_id, $prices[ $price_type ]['meta_key'], 1 );

	return (float) $price;
}

/**
 * получаем url изображений галереи товара
 *
 * @param  int|integer $product_id [description]
 *
 * @return [type]                  [description]
 */
function fs_gallery_images_url( $product_id = 0 ) {
	global $post;
	$product_id     = empty( $product_id ) ? $post->ID : $product_id;
	$gallery        = new \FS\FS_Images_Class;
	$gallery_images = $gallery->fs_galery_images( $product_id );
	$images         = array();
	if ( is_array( $gallery_images ) ) {
		foreach ( $gallery_images as $key => $gallery_image ) {
			$images[] = wp_get_attachment_url( $gallery_image );
		}
	}

	return $images;
}

/**
 * возвращает объект  с похожими или связанными товарами
 *
 * @param  int|integer $product_id идентификатор товара(поста)
 * @param  array $args передаваемые дополнительные аргументы
 *
 * @return object                  объект с товарами
 */
function fs_get_related_products( $product_id = 0, array $args = array() ) {
	global $post;
	$product_id = empty( $product_id ) ? $post->ID : $product_id;
	$config     = new \FS\FS_Config;
	$posts      = new stdClass;
	$products   = get_post_meta( $product_id, $config->meta['related_products'], false );
	if ( ! empty( $products[0] ) && is_array( $products[0] ) ) {
		$products = array_unique( $products[0] );
		$default  = array(
			'post_type'    => 'product',
			'post__in'     => $products,
			'post__not_in' => array( $product_id )
		);
		$args     = wp_parse_args( $args, $default );
		$posts    = new WP_Query( $args );
	}

	if ( empty( $posts->post_count ) ) {
		$terms    = get_the_terms( $product_id, 'catalog' );
		$term_ids = array();
		if ( $terms ) {
			foreach ( $terms as $key => $term ) {
				$term_ids[] = $term->term_id;
			}
		}
		$posts = new WP_Query( array(
			'post_type'      => 'product',
			'posts_per_page' => 4,
			'tax_query'      => array(
				array(
					'taxonomy' => 'catalog',
					'field'    => 'term_id',
					'terms'    => $term_ids
				)
			)
		) );
	}

	return $posts;
}

/**
 * @param int $product_id
 *
 * @return float|int|string
 */
function fs_change_price_percent( $product_id = 0 ) {
	global $post;
	$product_id   = empty( $product_id ) ? $post->ID : $product_id;
	$change_price = 0;
	$config       = new FS\FS_Config;
	// получаем возможные типы цен
	$base_price   = get_post_meta( $product_id, $config->meta['price'], true );//базовая и главная цена
	$base_price   = (float) $base_price;
	$action_price = get_post_meta( $product_id, $config->meta['action_price'], true );//акионная цена
	$action_price = (float) $action_price;
	if ( ! empty( $action_price ) && ! empty( $base_price ) && $action_price < $base_price ) {

		$change_price = ( $base_price - $action_price ) / $base_price * 100;
		$change_price = round( $change_price );
	}

	return $change_price;
}

/**
 * Выводит скидку на товар в процентах
 *
 * @param int $product_id - ID товара(записи)
 * @param string $wrap - html теги, обёртка для скидки
 */
function fs_discount_percent( $product_id = 0, $wrap = '<span>-%s%s</span>' ) {
	$discount = fs_change_price_percent( $product_id );
	if ( $discount > 0 ) {
		printf( $wrap, $discount, '%' );
	}

}

/**
 * производит очистку и форматирование атрибутов в строку
 * $default заменяет атрибуты $attr
 *
 * @param  array $attr атрибуты переданные в функцию
 * @param  array $default атрибуты функции по умолчанию
 *
 * @return [type]          строка атрибутов
 */
function fs_parse_attr( $attr = array(), $default = array() ) {
	$attr = array_merge( $attr, $default );
	$attr = array_map( 'esc_attr', $attr );
	foreach ( $attr as $key => $att ) {
		$atributes[] = $key . '="' . $att . '"';
	}
	$atributes = implode( ' ', $atributes );

	return $atributes;
}


/**
 * возвращает список желаний
 * @return array список желаний
 */
function fs_get_wishlist() {
	$wishlist       = ! empty( $_SESSION['fs_wishlist'] ) ? $_SESSION['fs_wishlist'] : array();
	$wishlist_count = count( $wishlist );
	$wishlist       = array(
		'count'    => $wishlist_count,
		'page'     => get_permalink( fs_option( 'page_whishlist' ) ),
		'products' => $wishlist
	);

	return $wishlist;
}

/**
 * отображает список желаний
 *
 * @param  array $html_attr массив html атрибутов для дива обёртки
 * @param  dool $wrap выводить ли стандартную обёртку для элеменнтов
 *
 * @return [type]       [description]
 */
function fs_wishlist_widget( $html_attr, $wrap = true ) {
	$template_theme = TEMPLATEPATH . '/fast-shop/wishlist/wishlist.php';
	$template       = plugin_dir_path( __FILE__ ) . 'templates/front-end/wishlist/wishlist.php';

	if ( file_exists( $template_theme ) ) {
		$template = $template_theme;
	}
	$attr_set = array(
		'data-fs-element' => 'whishlist-widget'
	);

	$html_attr = fs_parse_attr( $html_attr, $attr_set );

	if ( $wrap ) {
		echo "<div  $html_attr>";
	}
	require $template;
	if ( $wrap ) {
		echo "</div>";
	}
}

/**
 * @param int $order_id - id заказа
 *
 * @return bool|object возвращает объект с данными заказа или false
 */
function fs_get_order( $order_id = 0 ) {
	$order = false;
	if ( $order_id ) {
		$orders = new \FS\FS_Orders_Class();
		$order  = $orders->get_order( $order_id );
	}

	return $order;
}

function fs_get_delivery( $delivery_id ) {
	$name = get_term_field( 'name', $delivery_id, 'fs-delivery-methods' );

	return $name;
}

function fs_get_payment( $payment_id ) {
	$name = get_term_field( 'name', $payment_id, 'fs-payment-methods' );

	return $name;
}

/**
 * Функция выводе одно поле формы заказа
 *
 * @param $field_name название поля, атрибут name
 * @param array $args массив аргументов типа класс, тип, обязательность заполнения, title
 */
function fs_form_field( $field_name, $args = array() ) {
	$default     = array(
		'type'        => 'text',
		'class'       => '',
		'checked'     => '',
		'id'          => '',
		'required'    => true,
		'title'       => __( 'required field', 'fast-shop' ),
		'placeholder' => '',
		'value'       => '',

	);
	$args        = wp_parse_args( $args, $default );
	$class       = ! empty( $args['class'] ) ? 'class="' . sanitize_html_class( $args['class'] ) . '"' : '';
	$id          = ! empty( $args['id'] ) ? 'id="' . sanitize_html_class( $args['id'] ) . '"' : '';
	$title       = ! empty( $args['title'] ) ? 'title="' . esc_html( $args['title'] ) . '"' : '';
	$placeholder = ! empty( $args['placeholder'] ) ? 'placeholder="' . esc_html( $args['placeholder'] ) . '"' : '';
	$value       = ! empty( $args['value'] ) ? 'value="' . esc_html( $args['value'] ) . '"' : '';
	$checked     = ! empty( $args['checked'] ) ? 'checked="' . esc_html( $args['checked'] ) . '"' : '';
	$required    = ! empty( $args['required'] ) ? 'required' : '';
	switch ( $args['type'] ) {
		case 'text':
			$field = ' <input type="text" name="' . $field_name . '"  ' . $class . ' ' . $title . ' ' . $required . ' ' . $placeholder . ' ' . $value . ' ' . $id . '> ';
			break;
		case 'email':
			$field = ' <input type="email" name="' . $field_name . '"  ' . $class . ' ' . $title . ' ' . $required . '  ' . $placeholder . ' ' . $value . ' ' . $id . '> ';
			break;
		case 'tel':
			$field = ' <input type="tel" name="' . $field_name . '"  ' . $class . ' ' . $title . ' ' . $required . '  ' . $placeholder . ' ' . $value . ' ' . $id . '> ';
			break;
		case 'radio':
			$field = ' <input type="radio" name="' . $field_name . '"  ' . $checked . ' ' . $class . ' ' . $title . ' ' . $required . '  ' . $placeholder . ' ' . $value . ' ' . $id . '> ';
			break;
		case 'textarea':
			$field = '<textarea name="' . $field_name . '"  ' . $class . ' ' . $title . ' ' . $required . '  ' . $placeholder . ' ' . $id . '></textarea>';
			break;
	}
	echo apply_filters( 'fs_form_field', $field, $field_name, $args );
}

/**
 * создаёт переменные в письмах из массива ключей
 *
 * @param array $keys - ключи массива
 *
 * @return array массив из значений типа %variable%
 */
function fs_mail_keys( $keys = array() ) {
	$email_variable = array();
	if ( $keys ) {
		foreach ( $keys as $key => $value ) {
			$email_variable[] = '%' . $key . '%';
		}
	}

	return $email_variable;
}

function fs_attr_list( $attr_group = 0 ) {
	$terms = get_terms( array(
		'taxonomy'   => 'product-attributes',
		'hide_empty' => false,
		'parent'     => $attr_group,
	) );
	$atts  = array();
	foreach ( $terms as $term ) {
		switch ( get_term_meta( $term->term_id, 'fs_att_type', 1 ) ) {
			case 'color':
				$atts[] = get_term_meta( $term->term_id, 'fs_att_color_value', 1 );
				break;
			case 'image':
				$atts[] = get_term_meta( $term->term_id, 'fs_att_image_value', 1 );
				break;
			case 'text':
				$atts[] = $term->name;
				break;
		}

	}

	return $atts;
}

/**
 * Получает информацию обо всех зарегистрированных размерах картинок.
 *
 * @global $_wp_additional_image_sizes
 * @uses   get_intermediate_image_sizes()
 *
 * @param  boolean [$unset_disabled = true] Удалить из списка размеры с 0 высотой и шириной?
 *
 * @return array Данные всех размеров.
 */
function fs_get_image_sizes( $unset_disabled = true ) {
	$wais = &$GLOBALS['_wp_additional_image_sizes'];

	$sizes = array();

	foreach ( get_intermediate_image_sizes() as $_size ) {
		if ( in_array( $_size, array( 'thumbnail', 'medium', 'medium_large', 'large' ) ) ) {
			$sizes[ $_size ] = array(
				'width'  => get_option( "{$_size}_size_w" ),
				'height' => get_option( "{$_size}_size_h" ),
				'crop'   => (bool) get_option( "{$_size}_crop" ),
			);
		} elseif ( isset( $wais[ $_size ] ) ) {
			$sizes[ $_size ] = array(
				'width'  => $wais[ $_size ]['width'],
				'height' => $wais[ $_size ]['height'],
				'crop'   => $wais[ $_size ]['crop'],
			);
		}

		// size registered, but has 0 width and height
		if ( $unset_disabled && ( $sizes[ $_size ]['width'] == 0 ) && ( $sizes[ $_size ]['height'] == 0 ) ) {
			unset( $sizes[ $_size ] );
		}
	}

	return $sizes;
}



