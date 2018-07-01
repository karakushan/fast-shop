<?php
/**
 * Created by PhpStorm.
 * User: karak
 * Date: 01.07.2018
 * Time: 14:12
 */ ?>
<textarea name="<?php echo esc_attr( $name ) ?>" id="<?php echo esc_attr( $args['id'] ) ?>"
          class="<?php echo esc_attr( $args['class'] ) ?>"
          rows="<?php echo esc_attr( $args['textarea_rows'] ) ?>"><?php echo esc_html( $args['value'] ) ?></textarea>
