<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
 /**
* Класс для работы с корзиной
*/
class FS_Cart_Class
{
	
	function __construct()
	{
		add_action('init', array(&$this,'fast_shop_init_session'), 1);
		add_action('wp_ajax_add_to_cart', array(&$this,'add_to_cart_ajax'));
		add_action('wp_ajax_nopriv_add_to_cart', array(&$this,'add_to_cart_ajax'));
		add_action ( 'wp_head', array(&$this,'my_js_variables' ));
		
	}

	//Инициализируем сессии
	function fast_shop_init_session()
	{
		session_start();
	}

	// ajax обработка добавления в корзину
	function add_to_cart_ajax()
	{
		$p_id=esc_sql($_REQUEST['product_id']);
		$p_c=esc_sql($_REQUEST['count']);

		if (isset($_SESSION['cart'][$p_id])) {
			$count=$_SESSION['cart'][$p_id]['count'];
			$_SESSION['cart'][$p_id]=array(
				'count'=>$count+$p_c	
				);
		}else{
			$_SESSION['cart'][$p_id]=array(
				'count'=>$p_c	
				);
		}
		fs_cart_widget();
		exit;

	}
//Установка JS глобальных переменных (путь к скрипту Аякс и проверочный код)
	function my_js_variables(){ ?>
		<script type="text/javascript">
			var ajaxurl = <?php echo json_encode( admin_url( "admin-ajax.php" ) ); ?>;
			var fs_succes=<?php echo json_encode(get_option( 'fs_success', '' )); ?>;     
		</script><?php
	}

//Метод удаляет конкретный товар или все товары из корзины покупателя
	public function fs_remove_product($product_id='',$redirect=false)
	{
		if ($product_id=='') {
			unset($_SESSION['cart']);
		} else {
			$product_id=intval($_REQUEST['product_id']);
			unset($_SESSION['cart'][$product_id]);
		}

		if ($redirect===true) {
			wp_redirect(remove_query_arg(array('fs_action','fs_request','product_id')));
			exit();
		}
	}
}
