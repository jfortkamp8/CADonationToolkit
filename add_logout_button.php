function add_logout_button() {
    $logout_url = wp_logout_url( home_url('/campaign-toolkit-login/') );
    $logout_button = '<form action="' . esc_url($logout_url) . '" method="post">' . 
                        '<input type="submit" value="Log Out" style="background-color: #00758D; color: #fff; border-radius: 5px; padding: 10px 20px;" />' .'</form>';
		return $logout_button;
}
add_shortcode('logout_button', 'add_logout_button');
