<?php
/*
Plugin Name: User Bio Widget
Description: Easily display the Biographical Info of your user profile in your blog's sidebar. Allows you to choose from multiple authors/users on your blog and also gives you the ability to display the author's Gravatar, with some size and alignment options.
Version: 0.1
Author: Anthony Bubel
Author URI: http://anthonybubel.com/
*/

class User_Bio_Widget extends WP_Widget {

	function User_Bio_Widget() {
		$widget_ops = array('classname' => 'widget_user_bio', 'description' => "Display your User Profile's Biographical Info with the option to also display your Gravatar." );
		$this->WP_Widget('user_bio', 'User Bio', $widget_ops);
	}
	
	function widget($args, $instance) {
		extract( $args );		

		echo $before_widget;
		echo $before_title . esc_attr($instance['title']) . $after_title;

		global $wpdb;
		$bio = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM wp_usermeta WHERE meta_key = 'description' AND wp_usermeta.user_id = " . $instance['author'] . ";"));
		$email = $wpdb->get_var($wpdb->prepare("SELECT user_email FROM wp_users WHERE ID = " . $instance['author'] . ";"));
		
		if ( 'display' == $instance['gravatar'] ) {
			if ( function_exists('get_avatar') ) {
				$grav_image = get_avatar( $email, $size = $instance['grav_size'] );
			}
			
			if ( 'center' == $instance['grav_align'] ) {
				$output = '<div class="ub-grav" style="margin:5px;text-align:center;">' . $grav_image . '</div>';
			} else { 
				$output = '<div class="ub-grav" style="margin:5px;float: ' . $instance['grav_align'] . ';">' . $grav_image . '</div>';
			}
			
			if ( !empty($bio) ) {
				$output .= $bio;
			}
		} elseif ( !empty($bio) && 'display' != $instance['gravatar'] ) {
			$output = $bio;
		} elseif ( empty($bio) && 'display' != $instance['gravatar'] ) {
			$output = 'One of these days I will add something to my user profile!';
		}
		
		echo $output;

		echo "\n" . $after_widget;
	}

	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['author'] = $new_instance['author'];
		$instance['gravatar'] = $new_instance['gravatar'];
		$instance['grav_size'] = $new_instance['grav_size'];
		$instance['grav_align'] = $new_instance['grav_align'];
		
		return $instance;
	}

	function form($instance) {

		$instance = wp_parse_args( (array) $instance, array('title'=>'', 'author'=>'', 'gravatar'=>'', 'grav_size'=>'96', 'grav_align'=>'none') );
		
		$title = esc_attr($instance['title']);
		$author = $instance['author'];
		$gravatar = $instance['gravatar'];
		$grav_size = $instance['grav_size'];
		$grav_align = $instance['grav_align'];
			
			echo '<p><label for="<' . $this->get_field_id('title') . '">' . __('Title:') . '
			<input class="widefat" id="<' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . $title . '" />
			</label></p>
			<p><label for="<' . $this->get_field_id('author') . '">' . __('Author:') . '
			<select id="<' . $this->get_field_id('author') . '" name="' . $this->get_field_name('author') . '" class="widefat">';

			global $wpdb;
			$authors = $wpdb->get_results($wpdb->prepare("SELECT distinct ID,display_name FROM wp_users,wp_usermeta WHERE wp_users.ID=wp_usermeta.user_id AND wp_usermeta.meta_key='wp_user_level' AND wp_usermeta.meta_value != 0;"));
				foreach ( $authors as $author ){
					echo '<option value="'. $author->ID .'"';
					if($author->ID == $instance['author']){
						echo ' selected ';
					}
					echo '>'. $author->display_name . '</option>'."\n";
				}
			echo '</select></label></p>';
			
?> 
			<p><label for="<?php echo $this->get_field_id('gravatar'); ?>"><?php echo __('Display this author\'s <a href="http://gravatar.com/" title="Gravatar">Gravatar</a>'); ?>
			<input id="<?php echo $this->get_field_id('gravatar'); ?>" name="<?php echo $this->get_field_name('gravatar'); ?>" type="checkbox" value="display" <?php if($gravatar == "display") echo 'CHECKED'; ?> onchange="if ( this.checked == false ) jQuery( 'p#extra-options' ).slideUp(); else jQuery( 'p#extra-options' ).slideDown();" />
			</label></p>
<?php
			echo '<p id="extra-options"';
				if ( 'display' != $gravatar ) echo ' style="display: none;">';
			echo '<strong>' . __('Gravatar Settings:') . '</strong><br /><br />';
			
			$sizes = array('64' => 'Small - 64px', '96' => 'Medium - 96px', '128' => 'Large - 128px', '256' => 'Extra Large - 256px');
			echo '<label for="' . $this->get_field_id('grav_size') . '">' .  __('Size:') . '
				<select id="' . $this->get_field_id('grav_size') . '" name="' . $this->get_field_name('grav_size') . '">';
			foreach ( $sizes as $size => $size_display ) {
				echo  '<option value="' . $size . '" ';
				if ( $size == $grav_size ) echo 'selected ';
				echo '>' . __($size_display) . '</option>' . "\n";
			}
			echo '</select></label><br /><br />';

			$alignments = array('None', 'Left', 'Center', 'Right');
			echo '<label for="' . $this->get_field_id('grav_align') . '">' .  __('Alignment:') . '
				<select id="' . $this->get_field_id('grav_align') . '" name="' . $this->get_field_name('grav_align') . '">';
			foreach ( $alignments as $alignment ) {
				echo  '<option value="' . strtolower($alignment) . '" ';
				if ( strtolower($alignment) == $grav_align ) echo 'selected ';
				echo '>' . __($alignment) . '</option>' . "\n";
			}
			echo '</select></label><br /><br />';
	}
	
} //END User_Bio_Widget class

	function UserBioInit() {
		register_widget('User_Bio_Widget');
	}

	add_action('widgets_init', 'UserBioInit');
?>