<?php
/*
Plugin Name: And Then...
Plugin URI: http://www.calebstauffer.com
Description: Move quickly after publishing/updating your content
Version: 0.0.1
Author: Caleb Stauffer
Author URI: http://www.calebstauffer.com
*/

$css_andthen = new css_andthen();
unset($css_andthen);

class css_andthen {

	var $usermeta = array();

	var $action = 'edit';
	var $labels = array();

	var $goto = array();

	var $post_type = 'post';
	var $post_types = array();

	function __construct() {
		add_action('init',array(&$this,'posted'));
		add_action('load-post.php',array(&$this,'load'),1);
		add_action('load-post-new.php',array(&$this,'load'),1);
		add_action('admin_head-post-new.php',array(&$this,'load_new'),2);
		add_filter('redirect_post_location',array(&$this,'redirect'),1,2);
		if (isset($_GET['message']) && isset($_GET['next'])) add_filter('post_updated_messages',array(&$this,'notices'));
		add_action('post_submitbox_misc_actions',array(&$this,'submitbox_action'),999999);
	}

	function posted() {
		if (!isset($_POST) || !is_array($_POST) || !count($_POST) || !isset($_POST['next'])) return;

		$meta = array('action' => $_POST['next']['action']);
		if ('add' == $_POST['next']['action']) {
			if (isset($_POST['next']['add'])) $meta['add'] = $_POST['next']['add'];
			if (isset($_POST['next']['parent'])) $meta['parent'] = 1;
			if (isset($_POST['next']['order'])) $meta['order'] = 1;
			if (isset($_POST['next']['orderdir'])) $meta['orderdir'] = $_POST['next']['orderdir'];
		} else if ('goto' == $_POST['next']['action'])
			$meta['goto'] = $_POST['next']['goto'];

		$user = get_current_user_id();
		if (get_user_meta($user,'publish-next',true) != $meta)
			update_user_meta($user,'publish-next',$meta);

		//echo '<pre>' . print_r($_POST,true) . '</pre>';
		//exit();
	}

	function load() {
		wp_enqueue_script('chosen',plugin_dir_url(__FILE__) . 'chosen.js',array('jquery'));
		wp_enqueue_style('chosen',plugin_dir_url(__FILE__) . 'chosen.css');

		wp_enqueue_script('andthen',plugin_dir_url(__FILE__) . 'admin.js',array('jquery','chosen'));
		wp_enqueue_style('andthen',plugin_dir_url(__FILE__) . 'admin.css',array('chosen'));

		if ($this->usermeta = get_user_meta(get_current_user_id(),'publish-next',true))
			$this->action = $this->usermeta['action'];

		if (isset($_POST['next']) && is_array($_POST['next']) && count($_POST['next']) && isset($_POST['next']['action'])) $this->action = $_POST['next']['action'];
		if (empty($this->action)) $this->action = 'edit';

		$this->labels = array(
			'edit'	=> 'Edit',
			'view'	=> 'View',
			'add'	=> 'Add new',
			'goto'	=> 'Go to',
		);

		$this->goto = array(
			'dashboard' => admin_url('index.php'),
			'widgets'	=> admin_url('widgets.php'),
			'menus'		=> admin_url('nav-menus.php'),
			'comments'	=> admin_url('edit-comments.php'),
			'media'		=> array('Media Library',admin_url('upload.php')),
		);

		if (defined('MULTISITE') && MULTISITE && is_super_admin()) {
			$this->goto['network-dashboard'] = array('Network Dashboard',network_admin_url('index.php'));
			$this->goto['network-sites'] = array('Network Sites',network_admin_url('sites.php'));
		}

		$this->post_types = get_post_types('','objects');

		foreach ($this->post_types as $name => $object)
			if (!in_array($name,array('nav_menu_item','revision')))
				$this->goto[$name] = array($object->labels->name,add_query_arg('post_type',$name,admin_url('edit.php')));

		$this->goto['post'] = array('Posts',admin_url('edit.php'));

		ksort($this->goto);
	}

		function load_new() {
			global $post;
			if (isset($_GET['parent_id'])) $post->post_parent = $_GET['parent_id'];
			if (isset($_GET['menu_order'])) $post->menu_order = $_GET['menu_order'];

			//echo '<pre>' . print_r($this->usermeta,true) . '</pre>';
		}

	function redirect($url,$post_id) {
		$orig = $url;
		if (!isset($_POST['next']) || !count($_POST['next']) || 'edit' == $this->action) return $url;
		if ('view' == $this->action) return get_permalink($post_id);

		$args = array();
		$query = explode('&',substr($url,strpos($url,'?')+1));
		foreach ($query as $arg) {
			list($k,$v) = explode('=',$arg);
			$args[$k] = $v;
		}

		$post = $_POST;
		$next = $post['next'];

		if ('add' == $this->action) {
			$url = admin_url('post-new.php');
			$url = add_query_arg('post_type',$next['add'],$url);
			if (isset($next['parent'])) $url = add_query_arg('parent_id',$post['parent_id'],$url);
			if (isset($next['order'])) {
				$order = $post['menu_order'];
				if ('increment' == $next['orderdir']) $order++;
				else $order--;
				$url = add_query_arg('menu_order',$order,$url);
			}
		} else if ('goto' == $this->action) {
			if (is_array($this->goto[$next['goto']])) $url = $this->goto[$next['goto']][1];
			else $url = $this->goto[$next['goto']];
		}

		if (isset($args['message'])) $url = add_query_arg('message',$args['message'],$url);
		$url = add_query_arg('next',$post_id,$url);
		//die($url);
		return $url;
	}

	function notices($notices) {
		$post_ID = $_GET['next'];
		$post_type = get_post_type_object(get_post_type($post_ID));
		
		if (!$notice = $notices[$post_type->name][$_GET['message']]) {
			$notice = $notices['post'][$_GET['message']];
			$notice = str_replace('Post',$post_type->labels->singular_name,$notice);
			$notice = str_replace('post',strtolower($post_type->labels->singular_name),$notice);
			$notices[$post_type->name][$_GET['message']] = $notice;
		}

		$notice = str_replace(' <a ',' <a href="' . get_edit_post_link($post_ID) . '" title="Edit \'' . get_the_title($post_ID) . '\'">Edit ' . strtolower($post_type->labels->singular_name) . '</a> | <a title="View \'' . get_the_title($post_ID) . '\'" ',$notice);
		$notices[$post_type->name][$_GET['message']] = $notice;

		//echo '<pre>' . print_r($notices,true) . '</pre>';
		//die();
		return $notices;
	}

	function submitbox_action() {
		global $post;
		$this->post_type = $post->post_type;

		$add = $child = $order = $orderdir = $goto = '';
		if (isset($this->usermeta['add']) && 'add' == $this->usermeta['action']) $add = ' ' . strtolower($this->post_types[$this->usermeta['add']]->labels->singular_name);
		if (isset($this->usermeta['parent']) && is_post_type_hierarchical($this->post_type)) $child = ' child';
		if (isset($this->usermeta['order']) && isset($this->usermeta['orderdir'])) {
			if ('increment' == $this->usermeta['orderdir']) $order = '++';
			else $order = '--';
		}

		if ('goto' == $this->action && isset($this->usermeta['goto'])) {
			if (is_array($this->goto[$this->usermeta['goto']])) {
				list($goto,$url) = $this->goto[$this->usermeta['goto']];
			} else $goto = ucfirst($this->usermeta['goto']);
		}

		if ('edit' == $this->action)		$display = 'Edit this ' . strtolower($this->post_types[$this->post_type]->labels->singular_name);
		else if ('view' == $this->action)	$display = 'View this ' . strtolower($this->post_types[$this->post_type]->labels->singular_name);
		else if ('add' == $this->action)	$display = 'Add new' . $child . $add . $order;
		else if ('goto' == $this->action)	$display = 'Go to ' . $goto;

		//echo '<pre>' . print_r($this->post_types['post'],true) . '</pre>';
		?>

		<div class="misc-pub-section misc-pub-next" id="next">

			<span class="title">Next:</span> 
			<span id="next-display"><?php echo $display ?></span> 
			<a href="#next" id="next-edit" class="edit-next hide-if-no-js"><span aria-hidden="true">Edit</span><span class="screen-reader-text">Edit next action</span></a>

			<div id="misc-pub-next-select" class="hide-if-no-js" style="display: none;">

				<input type="hidden" name="next[action]" id="next-action" value="<?php echo $this->action ?>" />

				<ul>

					<li class="next-edit">

						<input type="radio" id="next-action-edit" name="next-action" value="edit" <?php checked('edit',$this->action,true) ?> />
						<label for="next-action-edit"> Edit this <?php echo $this->post_types[$this->post_type]->name ?></label>

					</li>

					<li class="next-view">

						<input type="radio" id="next-action-view" name="next-action" value="view" <?php checked('view',$this->action,true) ?> />
						<label for="next-action-view"> View this <?php echo $this->post_types[$this->post_type]->name ?></label>

					</li>

					<li class="next-add">

						<input type="radio" id="next-action-add" name="next-action" value="add" <?php checked('add',$this->action,true) ?> />
						<label for="next-action-add">Add new:</label> <?php $this->addnew() ?>

						<?php if ($hier = is_post_type_hierarchical($this->post_type) || (1 == $this->post_types[$this->post_type]->_builtin || $attr = post_type_supports($this->post_type,'page-attributes'))) { ?>

							<ul class="next-add-options"<?php if ('add' != $this->action) echo ' style="display: none;"' ?>>

								<?php if ($hier) { ?>

									<li class="next-add-options-parent"<?php if (in_array($post->post_parent,array('',0))) echo ' style="display: none;"' ?>>
										<input type="checkbox" id="next-add-parent" name="next[parent]" value="1"<?php checked(1,isset($this->usermeta['parent']),true) ?> />
										<label for="next-add-parent"> Same parent</label>
									</li>

								<?php } ?>

								<?php if (1 == $this->post_types[$this->post_type]->_builtin || $attr) { ?>
								
									<li style="margin-bottom: 0;">
										
										<input type="checkbox" id="next-add-order" name="next[order]" value="1"<?php checked(1,isset($this->usermeta['order']),true) ?> />
										<label for="next-add-order"> 
											<select name="next[orderdir]" id="next-add-order-turn">
												<option value="increment"<?php selected('++',$order,true) ?>>Increment</option>
												<option value="decrement"<?php selected('--',$order,true) ?>>Decrement</option>
											</select> 
											menu order
										</label>

									</li>

								<?php } ?>

							</ul>

						<?php } ?>

					</li>

					<li class="next-goto">

						<input type="radio" id="next-action-goto" name="next-action" value="goto" <?php checked('goto',$this->action,true) ?> />
						<label for="next-action-goto"> Go to:</label> <?php $this->goes() ?>

					</li>
				
				</ul>

				<p>
					<a href="#next" class="save-post-next hide-if-no-js button">OK</a>
					<a href="#next" class="cancel-post-next hide-if-no-js button-cancel">Cancel</a>
				</p>

			</div>

		</div>

		<?php
	}

		function addnew() {
			?>

			<select name="next[add]" id="next-add">

				<?php
				$post_types = $this->post_types;
				ksort($post_types);
				foreach ($post_types as $name => $object)
					if (!in_array($name,array('nav_menu_item','revision')))
						echo '<option value="' . $name . '"' . selected($name,$this->post_type,false) . '>' . $object->labels->singular_name . '</option>';
				?>
			
			</select>

			<?php
		}

		function goes() {
			?>

			<select name="next[goto]" id="next-goto">

				<?php
				$selected = $this->post_type;
				if (isset($this->usermeta['goto'])) $selected = $this->usermeta['goto'];
				foreach ($this->goto as $name => $going)
					if (!in_array($name,array('attachment','nav_menu_item','revision'))) {
						if (is_array($going)) list($label,$url) = $going;
						else $label = ucfirst($name);
						echo '<option value="' . $name . '"' . selected($name,$selected,false) . '>' . $label . '</option>';
					}
				?>
			
			</select>

			<?php
		}

}

?>
