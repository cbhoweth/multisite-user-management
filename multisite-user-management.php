<?php
/*
Plugin Name: Multisite User Management
Plugin URI: http://github.com/thenbrent/multisite-user-management
Description: Running WordPress in Multisite mode? You no longer need to be manually add new users to a blog. With this plugin, when a new user activates their account, they assigned a role for each blog on your site. You determine which role under the Super Admin | Options menu.
Author: Brent Shepherd
Version: 0.1
Network: true
*/

// When a user activates their account, allocate them the default role set for each site
function msum_activate_user( $user_id ){
	
	$dashboard_blog = get_site_option( 'dashboard_blog' );

	foreach( get_blog_list( 0, 'all' ) as $key => $blog ) { 
		if( $blog[ 'blog_id' ] == $dashboard_blog )
			continue;
		switch_to_blog( $blog[ 'blog_id' ] );

		$role = get_option( 'msum_default_user_role' );

		if( $role != 'none' )
			add_user_to_blog( $blog[ 'blog_id' ], $user_id, $role );

		restore_current_blog();
	}
}
add_action( 'wpmu_activate_user', 'msum_activate_user', 10, 1 );


// Print Default Role selection boxes on the 'Site Admin | Options' page
function msum_options(){ ?>
	<h3><?php _e( 'MultiSite User Management', 'msum' ); ?></h3>
	<?php if( basename( dirname( __FILE__ ) ) == 'mu-plugins' ) { ?>
		<p><?php _e( 'Select the default roles for your sites. New Users receive these roles when activating their account. The default role for the dashboard site is set above.', 'msum' ); ?></p>
		<table class="form-table"><?php
			$dashboard_blog = get_site_option( 'dashboard_blog' );
			foreach( get_blog_list( 0, 'all' ) as $key => $blog ) { 
				if( $blog[ 'blog_id' ] == $dashboard_blog )
					continue;
				switch_to_blog( $blog[ 'blog_id' ] );
				?>
				<tr valign="top">
					<th scope="row"><?php echo get_bloginfo( 'name' ); //echo $blog_name; ?></th>
					<td>
						<select name="default_user_role[<?php echo $blog[ 'blog_id' ]; ?>]" id="default_user_role[<?php echo $blog[ 'blog_id' ]; ?>]">
							<option value="none"><?php _e( '-- None --', 'msum' )?></option>
							<?php wp_dropdown_roles( get_option( 'msum_default_user_role' ) ); ?>
						</select>
					</td> 
				</tr>
			<?php restore_current_blog();
			} ?>
		</table>
	<?php } else { 	?>
		<p><b>Whoops, it looks like <em><?php echo basename( __FILE__ ); ?></em> is not located in <em>/wp-content/mu-plugins/</em></b></p> 
		<p>It is located in <em><?php echo dirname( __FILE__ ); ?></em>. Please move it to <em>/wp-content/mu-plugins/</em> for it to work correctly.</p>
		<?php
	}
}
add_action('wpmu_options', 'msum_options');


// Update Default Roles on submission of the multisite options page
function msum_options_update(){

	foreach( $_POST[ 'default_user_role' ] as $blog_id => $role ) { 
		switch_to_blog( $blog_id );
		update_option( 'msum_default_user_role', $role );
		restore_current_blog();
	}
}
add_action( 'update_wpmu_options', 'msum_options_update' );


// Clean up when plugin is deleted
function msum_uninstall(){
	foreach( get_blog_list( 0, 'all' ) as $key => $blog ) { 
		switch_to_blog( $blog[ 'blog_id' ] );
		delete_option( 'msum_default_user_role', $role );
		restore_current_blog();
	}
}
register_uninstall_hook( __FILE__, 'msum_uninstall' );
