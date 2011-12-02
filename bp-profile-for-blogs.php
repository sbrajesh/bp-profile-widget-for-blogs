<?php
 /**
 * Plugin Name: Buddypress Profile Widget for Blogs
 * Version: 1.2.2
 * Description: Let Blog Admins show all/some of their Buddypress profile fields on their blogs as widget
 * credits: Concept by Bowe(http://bp-tricks.com) and Mercime(http://buddypress.org/developers/mercime)
 * Requires at least: BuddyPress 1.5
 * Tested up to: BuddyPress 1.5.1+WordPress Multisite 3.2.1
 * License: GNU General Public License 2.0 (GPL) http://www.gnu.org/licenses/gpl.html
 * Author: Brajesh Singh
 * Author URI: http://buddydev.com
 * Plugin URI:http://buddydev.com/buddypress/buddypress-profile-widget-for-blogs/
 * Last updated:December 2, 2011
 * Network: true
 */
 
 /***
    Copyright (C) 2010 Brajesh Singh(buddydev.com)

    This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 3 of the License, or  any later version.

    This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

    You should have received a copy of the GNU General Public License along with this program; if not, see <http://www.gnu.org/licenses>.

    */
 
 class BpDev_BPProfile_Widget extends WP_Widget
 {
 	function __construct() {
		parent::__construct( false, $name = __( 'BP Profile for Blogs', 'bpdev' ) );
	}
	function widget($args, $instance) {
		extract( $args );
	
		echo $before_widget; 
		echo $before_title
		  . $instance['title']
		  . $after_title; 
			
		BpDev_BPProfile_Widget::bpdev_show_blog_profile($instance);/*** show the profile fields**/
			
	
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		foreach($new_instance as $key=>$val)
		$instance[$key]=$val;//update the instance
		
		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => __('My Profile','bpdev'), 'show_avatar' => "yes",'user_role'=>'administrator') );
		$title = strip_tags( $instance['title'] );
		extract($instance,EXTR_SKIP);
		
	?>

		<p><label for="bpdev-widget-title"><?php _e('Title:', 'bpdev'); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo attribute_escape( stripslashes( $title ) ); ?>" /></label></p>

                <p><label for="bpdev-widget-role"><?php _e('List profiles for:', 'bpdev'); ?> <select  id="<?php echo $this->get_field_id( 'user_role' ); ?>" name="<?php echo $this->get_field_name( 'user_role' ); ?>" ><?php wp_dropdown_roles($user_role);?> </select></label></p>
		

                <p>
				<label for="bpdev-widget-show-avatar"><?php _e( 'Show Avatar' , 'bpdev'); ?>
					<input type="radio" id="<?php echo $this->get_field_id( 'show_avatar' ); ?>" name="<?php echo $this->get_field_name( 'show_avatar' ); ?>"  value="yes" <?php if( $show_avatar  =="yes") echo "checked='checked'";?> >Yes
					<input type="radio" id="<?php echo $this->get_field_id( 'show_avatar' ); ?>" name="<?php echo $this->get_field_name( 'show_avatar' ); ?>"  value="no" <?php if(attribute_escape(  $show_avatar  )!="yes") echo "checked='chexcked'";?>>No
				</label>
			</p>
			<?php
			//get all xprofile fields and ask user whether to show them or not
			
			?>
			<h3><?php _e("Profile Fields Visibility","bpdev");?></h3>
			<table>
	<?php if ( function_exists( 'bp_has_profile' ) ) : if ( bp_has_profile(  ) ) : while ( bp_profile_groups() ) : bp_the_profile_group(); ?>
		<?php while ( bp_profile_fields() ) : bp_the_profile_field(); ?>
			
			<?php $fld_name=bp_get_the_profile_field_input_name();
			$fld_val=${$fld_name};
			?>
			<tr><td>
			<label for="<?php echo $fld_name; ?>"><?php bp_the_profile_field_name() ?></label>
			</td><td>
			<input type="radio" id="<?php echo $this->get_field_id( $fld_name ); ?>" name="<?php echo $this->get_field_name( $fld_name); ?>"  value="yes" <?php if($fld_val=="yes") echo "checked='checked'";?> >Show
			<input type="radio" id="<?php echo $this->get_field_id( $fld_name); ?>" name="<?php echo $this->get_field_name( $fld_name); ?>"  value="no" <?php if($fld_val!="yes") echo "checked='checked'";?>>Hide
			</td>
			</tr>
			
		<?php endwhile;
		endwhile;
		endif;
		endif;?>
</table>
		
	<?php
	}

//just a hack for now, code can be cleaned later
/**
 * @desc Get the admin users of current blog
 */
  //get list of users of current blog by the role
   //call like get_users('administrators') for all the admin
 function get_admins(){
     return BpDev_BPProfile_Widget::get_users('administrator');
 }

 function get_authors(){
     return BpDev_BPProfile_Widget::get_users("author");
 }

 function get_users_with_roles($user_roles=array('administrator'=>'administrator')){
     global $wpdb,$current_blog;

        $blog_prefix = $wpdb->get_blog_prefix($current_blog->blog_id);
        $role_sql = "SELECT user_id, meta_value FROM $wpdb->usermeta WHERE  meta_key = '{$blog_prefix}capabilities' ORDER BY {$wpdb->usermeta}.user_id" ;
        $role=$wpdb->get_results($wpdb->prepare($role_sql),ARRAY_A);
	//clean the role
	$all_user=array_map("bpdev_serialize_role",$role);//we are unserializing the role to make that as an array
        $users=array();
       
        foreach($all_user as $key=>$user_info){
            foreach ($user_roles as $user_role){
		if($user_info['meta_value'][$user_role]==1)//if the role is admin
			$users[]=$user_info['user_id'];
                
           continue ;
                
            }
        }
	return $users;

 }
 function get_users($user_role=null){
     global $wpdb,$current_blog;

        $blog_prefix = $wpdb->get_blog_prefix($current_blog->blog_id);
        $role_sql = "SELECT user_id, meta_value FROM $wpdb->usermeta WHERE  meta_key = '{$blog_prefix}capabilities' ORDER BY {$wpdb->usermeta}.user_id" ;
        $role=$wpdb->get_results($wpdb->prepare($role_sql),ARRAY_A);
	//clean the role
	$all_user=array_map("bpdev_serialize_role",$role);//we are unserializing the role to make that as an array
        $users=array();
	foreach($all_user as $key=>$user_info)
		if($user_info['meta_value'][$user_role]==1)//if the role is admin
			$users[]=$user_info['user_id'];

	return $users;
        
 }
 function get_admin_users_for_current_blog() {
	global $wpdb,$current_blog;
      // $memers=BpDev_BPProfile_Widget::get_users_with_roles(array("administrator"));

       //	$roles=
	return BpDev_BPProfile_Widget::get_admins();

}
 function bpdev_show_blog_profile($instance){
 //if buddypress is not active, return
 if(!function_exists("bp_get_root_domain"))
 return;
    $show_avatar=$instance["show_avatar"];//we need to preserve for multi admin
    $user_role=$instance['user_role'];
	unset($instance["show_avatar"]);
	unset($instance["title"]);//unset the title of the widget,because we will be iterating over the instance fields
	unset($instance['user_role']);//unset the title of the widget,because we will be iterating over the instance fields

global $current_blog;
$users=apply_filters("bp_profile_for_users",BpDev_BPProfile_Widget::get_users($user_role));//may be we can improve it too
	if(empty($users))
	return;
	foreach($users as $user){
	/*** we many not need this loop if the blog has single owner and /or we just want to show a single users profile
	*/
		   
		$user_id=$user;//["user_id"];
	   
		$op="<table class='my-blog-profile'>";
		if($show_avatar=="yes")
		$op.="<tr class='user-avatar'><td>".bp_core_get_userlink($user_id) ."</td><td>".bp_core_fetch_avatar(array("item_id"=>$user_id,"type"=>"thumb"))."</td></tr>";
		
		//bad approach, because buddypress does not allow to fetch the field name from field key
		if ( function_exists( 'bp_has_profile' ) ) : 
				if ( bp_has_profile( "user_id=".$user_id ) ) :
					while ( bp_profile_groups() ) : bp_the_profile_group();
						while ( bp_profile_fields() ) : bp_the_profile_field(); 
								$fld_name=bp_get_the_profile_field_input_name();
								if(array_key_exists($fld_name,$instance)&&$instance[$fld_name]=="yes")
									$op.="<tr><td>".bp_get_the_profile_field_name()."</td><td>".bp_get_profile_field_data(array('field'=>bp_get_the_profile_field_id(),'user_id'=>$user_id))."</td></tr>";
						endwhile;
					endwhile;
				endif;
			endif;
		$op.="</table>";
	 echo $op;
	 }
 }
 }
 /** Let us register the widget*/
 function bpdev_register_bpprofile_for_blogs_widgets() {
	add_action('widgets_init', create_function('', 'return register_widget("BpDev_BPProfile_Widget");') );
	}
add_action( 'bp_loaded', 'bpdev_register_bpprofile_for_blogs_widgets' );

function bpdev_serialize_role($roles){
	$roles['meta_value']=maybe_unserialize($roles['meta_value']);
return $roles;
}
?>