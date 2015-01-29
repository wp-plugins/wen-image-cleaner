<?php

//	Check
if( ! class_exists( 'WEN_Addons' ) ) {

	//	Define
	class WEN_Addons {

		//	Menu Parent
		public static $menu_name = 'wen-addons';

		//	API URI
		public static $api_uri = 'http://wenthemes.com/wen-api/';

		//	Instance
		private static $instance = null;


		//	Get Instance
		public static function getInstance() {

			//	Check
			if( !self:: $instance ) {

				//	Create
				self::$instance = new self();
			}

			//	Return
			return self::$instance;
		}


		//	Construct
		public function __construct() {

			//  Add Action to Add Admin Menu
			add_action( 'admin_menu', array( &$this, 'admin_init' ), 5 );
			add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue_scripts' ) );
		}

		//	Enqueue scripts and styles
		public function enqueue_scripts(){

			//	Enqueue Thickbox
			add_thickbox();
		}

		//	Admin Init
		public function admin_init() {

			//  Check
			if( !has_nav_menu(self::$menu_name) ) {

			    //  Add Top Level Page
			    add_menu_page( __( 'WEN Addons', 'wen-image-cleaner' ), __( 'WEN Addons', 'wen-image-cleaner' ), 'manage_options', self::$menu_name, 'wen_addons_page_render', plugin_dir_url( dirname(__FILE__) . 'index.php' ) . 'wen-addons/favicon.png', 100 );
			}
		}

		//	Read the Available Plugins List
	    public function get_plugins_list(){

	    	//	Transient Info
	    	$transient_key = 'wen_addons_plugins_list';
	    	$transient_period = 24 * HOUR_IN_SECONDS;

	    	//	Get Transient
	    	$output = get_transient( $transient_key );

    		//	Validate Output
	    	if ( false === $output ) {

	    	  	//	Clear Output
	    		$output = array();

	    		//	Data
	    		$data = array();

	    		//	Get Raw Contents
	    		$raw_content = @file_get_contents(self::$api_uri);

	    		//	Plugins List
	    		$plugins_array = array();

	    		//	Validate API Data
	    		if ( ! empty( $raw_content ) && FALSE !== $raw_content ) {
	    			$content = json_decode( $raw_content, true );
	    			if (
	    				isset( $content['status'] ) && 1 == $content['status']
	    				&& isset( $content['total'] ) && $content['status'] > 0
	    				) {
	    				$data = $content['data'];
	    			}
	    		}

	    		//	Set Output
				$output = $data;

				//	Store Transient
				set_transient( $transient_key, $output, $transient_period );
	    	}

	    	//	Return
	    	return $output;
	    }

	    //	Display Landing Page
	    public function display() {

	    	//	Get all installed plugins
			$all_installed_plugins_list = get_plugins();
			$all_plugins = array_keys( $all_installed_plugins_list);

	    	//	Get WEN plugins
			$wen_plugins = $this->get_plugins_list();
?>
<div class="wrap">
	<h2><?php echo __( 'WEN Addons', 'wen-image-cleaner' ); ?></h2><br/>

	<div class="wp-list-table widefat">
		<?php if( sizeof( $wen_plugins ) > 0 ) { ?>
		<div id="the-list">
			<?php foreach( $wen_plugins as $wen_addon ) {?>
			<div class="plugin-card">
				<div class="plugin-card-top">
					<a href="<?php echo esc_url( $wen_addon['url'] ); ?>" target="_blank" class="plugin-icon">
					<?php if ( ! empty( $wen_addon['mini_logo_url'] ) ): ?>
						<img src="<?php echo esc_url( $wen_addon['mini_logo_url'] ); ?>" alt="<?php echo esc_attr( $wen_addon['title'] ); ?>" />
					<?php else: ?>
						<img src="<?php echo esc_url( $wen_addon['image']['thumbnail']['url'] ); ?>" alt="<?php echo esc_attr( $wen_addon['title'] ); ?>" />
					<?php endif ?>
					</a>
					<div class="name column-name">
						<h4><a href="<?php echo esc_url( $wen_addon['url'] ); ?>" target="_blank"><?php echo $wen_addon['title']; ?></a></h4>
					</div>
					<div class="action-links">
						<ul class="plugin-action-buttons">
							<li>
							<?php
								$button_text  = __( 'Read more', 'wen-image-cleaner' );
								$button_url   = esc_url( $wen_addon['url'] ) ;
								$button_tag   = 'a' ;
								$button_class = 'button' ;
							 	$button_target   = '_blank' ;
							 	$plugin_price = $wen_addon['meta']['price'];
							 ?>
							 <?php
								 $plugin_key = $wen_addon['slug'].'/'.$wen_addon['slug'].'.php';
								 if ( in_array( $plugin_key, $all_plugins ) ) {
								 	// Plugin is already installed, so disable button
								 	$button_text = __( 'Installed', 'wen-image-cleaner' );
								 	$button_class = 'button button-disabled' ;
								 	$button_tag   = 'span' ;
								 }
								 else {
								 	if( $plugin_price > 0 ){
										$button_text   = __( 'Buy Now', 'wen-image-cleaner' ) ;
									}
									else{
										$button_text   = __( 'Install now', 'wen-image-cleaner' ) ;
										$button_url = add_query_arg(array(
												'tab'       => 'plugin-information',
												'plugin'    => isset( $wen_addon['meta']['repo_slug'] ) ? $wen_addon['meta']['repo_slug'] : ''  ,
												'TB_iframe' => 'true',
												'width'     => 772,
												'height'    => 623,
											),
											admin_url( 'plugin-install.php' )
										);
										$button_target   = '' ;
										$button_class   = 'button thickbox' ;
									}

								 }
							  ?>
							  <?php
							  	echo '<'. $button_tag;
							  	echo ' class="'.esc_attr( $button_class ).'" ';
							  	if ( 'a' == $button_tag ) {
								  	echo ' href="'.esc_url( $button_url ).'" ';
								  	if ( ! empty( $button_target ) ) {
									  	echo ' target="'.esc_attr( $button_target ).'" ';
								  	}
							  	}
							  	echo '>';
							  	echo esc_html( $button_text );
							  	echo '</'. $button_tag.'>';
							  ?>
							</li>
						</ul>
					</div>
					<div class="desc column-description" style="margin-right:0;">
						<p><?php echo $wen_addon['excerpt']; ?></p>
						<p><?php _e( 'Version', 'wen-image-cleaner' ); ?>&nbsp;<?php echo $wen_addon['meta']['current_version']; ?></cite></p>
					</div>
				</div>
			</div>
			<?php } ?>
		</div>
		<?php } else { echo '<p>' . __( 'No addons available', 'wen-image-cleaner' ) . '</p>'; } ?>
	</div>
</div>
<?php
	    }
	}

	//	Init WEN Addons Instance
	WEN_Addons::getInstance();

	//	Check
	if( !function_exists('wen_addons_page_render') ) {

		//	Define Function
		function wen_addons_page_render() {

			//	Display
			WEN_Addons::getInstance()->display();
		}
	}
}
