<?php
/* Plugin Name: dxw3 Contact Form
 * Plugin URI:  https://dx-w3.com/en/wordpress-plugins
 * Description: Plug and play simple and fast contact form.
 * Version:     1.0.0
 * Author:      dxw3
 * Author URI:  https://www.dx-w3.com/
 * License:     GPL-2.0
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

if ( !defined( 'ABSPATH' ) ) exit;

// Usage: add form on the page by adding the shortcode: [dxw3-form]

function dxw3_jquery() {
    if ( ! wp_script_is( 'jquery', 'enqueued' ) ) wp_enqueue_script( 'jquery' );
}
add_action( 'wp_enqueue_scripts', 'dxw3_jquery' );

function dxw3_plugin_sheets() {
    wp_enqueue_style( 'dxw3-form',  plugins_url() . '/dxw3-contact-form/dxw3-form-styles.css' );
}
add_action( 'wp_footer', 'dxw3_plugin_sheets' );

add_action( 'init', 'dxw3_add_shortcode' );
function dxw3_add_shortcode() {
    add_shortcode( 'dxw3-form' , 'dxw3_form_output' );
}

function dxw3_form_output() { 
    $html = '<div class="dxw3_contact_form_div">
    <form class="dxw3_contact_form">
        <div class="dxw3_form_content">
            <label for="name">Name <input type="text" name="fullname" required></label>
            <label for="email">Email <input type="email" name="email" required></label>
        </div>
        <div class="dxw3_form_content">
            <label for="phone">Phone <input type="tel" name="phone" placeholder="numbers only, min 6" pattern="^[+]?[0-9]{6,}$" required></label>
        </div>
        <div class="dxw3_form_content">
            Call request 
            &nbsp;&nbsp;
            Yes&nbsp;<input type="radio" value="Yes" name="call">
            No&nbsp;<input type="radio" value="No" name="call">
        </div>
        <div class="dxw3_form_content">
            <label for="message">Message 
            <textarea name="message"></textarea></label>
        </div>
        <input type="submit" name="submit" value="Send">
        <p id="dxw3-sent"></p>
    </form>
    </div>';    
    return $html;
}

add_action( 'wp_ajax_dxw3_form', 'dxw3_formF' );
add_action( 'wp_ajax_nopriv_dxw3_form', 'dxw3_formF' ); 
function dxw3_formF() {   			  
    $name = $_POST[ 'formdata' ][ 0 ][ 'value' ];
    $email = $_POST[ 'formdata' ][ 1 ][ 'value' ];
    $phone = $_POST[ 'formdata' ][ 2 ][ 'value' ];
    $call = $_POST[ 'formdata' ][ 3 ][ 'value' ];
    $message = $_POST[ 'formdata' ][ 4 ][ 'value' ];
    $formcontent = "\n From: $name \n Email: $email \n Phone: $phone \n Call Back: $call \n Message: $message";
    $recipient = get_option( 'admin_email' );
    $subject = 'Contact Form';
    wp_mail( $recipient, $subject, $formcontent );
    wp_send_json_success( $formcontent );
}

add_action( 'wp_footer' , 'formScript' );
function formScript() {
    global $post;
    if( has_shortcode( $post->post_content, 'dxw3-form' ) ) {
    ?>
        <script>
        (function ( $ ) {
            $( function() {
                console.log('script');
                $( document ).on( 'submit', '.dxw3_contact_form', function( e ) {
                    console.log(e);
                    e.preventDefault();
                    $( '.dxw3_contact_form #dxw3-sent' ).text( 'Wait..' );
                    var form_data = $( this ).serializeArray();
                    $.ajax({
                        type: 'POST',
                        url: '<?php echo admin_url( 'admin-ajax.php' ) ?>',
                        data: { 
                        formdata: form_data,
                        action: 'dxw3_form'
                        },
                        success: function( data ) {
                            console.log(data);
                        $( '.dxw3_contact_form' ).trigger( 'reset' );
                        $( '.dxw3_contact_form #dxw3-sent' ).text( 'The message was sent successfully.' );
                        }
                    });    
                });
            });
        })( jQuery );
        </script>
    <?php
    }
}
