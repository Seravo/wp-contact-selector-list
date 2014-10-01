<?php
/*
Plugin Name: WP Kontaktilista
Plugin URI: http://seravo.fi
Description: Adds [contacts-list] shortcode and custom post type for handling custom post type
Version: 1.2.2
Author: Seravo Oy / Onni Hakala
Author URI: http://seravo.fi
*/

include ('lib/wp-cuztom/cuztom.php');


$contact = new Cuztom_Post_Type( array('Kontakti','Kontaktit') );
//$contact->add_taxonomy( array('Tyyppi','Tyypit')); 
$contact->add_meta_box(
        'contact_box',
        'Yhteyshenkilön tiedot',
        array(
            array(
              'name' => 'phone',
              'label' => 'Puhelin',
              'description' => 'Yhteyshenkilön puhelinnumero',
              'type' => 'text'
              ),
            array(
              'name' => 'name',
              'label' => 'Nimi',
              'description' => 'Yhteyshenkilön nimi',
              'type' => 'text'
              ),
            array(
              'name' => 'email',
              'label' => 'Sähköposti',
              'description' => 'Yhteyshenkilön sähköposti',
              'type' => 'text'
              ),
            array(
              'name' => 'address',
              'label' => 'Osoite',
              'description' => 'Yhteyshenkilön katuosoite',
              'type' => 'text'
              ),
            array(
              'name' => 'postalcode',
              'label' => 'Postinumero',
              'description' => 'Yhteyshenkilön postinumero',
              'type' => 'text'
              )
            
        ));
add_action( 'manage_posts_custom_column', 'contacts_manage_columns', 10, 2 );

function contacts_manage_columns( $column, $post_id ) {

  switch( $column ) {

    case 'phone' :

      /* Get the post meta. */
      $phone = get_post_meta( $post_id, '_contact_box_phone', true );
      if ( empty( $phone ) )
        echo '<span style="color:red">' + __( 'Ei tiedossa' ) + '</span>';
      else
        echo $phone;
      break;

    case 'name' :
      $name = get_post_meta( $post_id, '_contact_box_name', true );
      if ( empty( $name ) )
        echo '<span style="color:red">' + __( 'Ei tiedossa' ) + '</span>';
      else
        echo $name;
      break;
    case 'email' :
      $email = get_post_meta( $post_id, '_contact_box_email', true );
      if ( empty( $email ) )
        echo '<span style="color:red">' + __( 'Ei tiedossa' ) + '</span>';
      else
        echo $email;
      break;
    case 'address' :
      $meta = get_post_meta( $post_id, '_contact_box_address', true );
      if ( empty( $meta ) )
        echo '<span style="color:red">' + __( 'Ei tiedossa' ) + '</span>';
      else
        echo $meta;
      break;
    case 'postalcode' :
      $meta = get_post_meta( $post_id, '_contact_box_postalcode', true );
      if ( empty( $meta ) )
        echo '<span style="color:red">' + __( 'Ei tiedossa' ) + '</span>';
      else
        echo $meta;
      break;
    /* Just break out of the switch statement for everything else. */
    default :
      break;
  }
}

/*
 * Add columns to edit.php main view
 */
function add_contact_columns($columns) {
  $new_columns = array(
    'cb' => $columns['cb'],
    'title' => $columns['title'],
    'name' => 'Nimi',
    'phone' => 'Puhelin',
    'email' => 'Sähköposti',
    'address' => 'Osoite',
    'postalcode' => 'Postinumero',
    'date' => 'Päivämäärä'
  );
  return $new_columns ;
}
add_filter('manage_edit-kontakti_columns' , 'add_contact_columns', 100, 1);

/*
 * Make columns sortable
 */
add_filter( 'manage_edit-kontakti_sortable_columns', 'contacts_sortable_columns' );

function contacts_sortable_columns( $columns ) {

  $columns['phone'] = 'phone';
  $columns['email'] = 'email';
  $columns['name'] = 'name';
  $columns['address'] = 'address';
  $columns['postalcode'] = 'postalcode';
  
  return $columns;
}

function contacts_list_scripts() {
  //enqueue selectize.js
  wp_register_style( 'select2', plugins_url('/lib/css/select2.css', __FILE__));
  wp_register_script( 'select2', plugins_url('/lib/js/select2.min.js', __FILE__), array( 'jquery' ));
  wp_register_script( 'contacts', plugins_url('/lib/js/contacts.js', __FILE__), array( 'jquery' ));
  wp_enqueue_style( 'select2');
  wp_enqueue_script( 'select2');
  wp_enqueue_script( 'contacts');
}
add_action( 'wp_enqueue_scripts', 'contacts_list_scripts' );


//Loops all contacts and adds selector which lists all contacts and hidden list off all targets
function contacts_list_function() {
  $query = new WP_Query( array('post_type' => 'kontakti','orderby' => 'title', 'order' => 'ASC'));
  
  if ( $query->have_posts()) :
  $return_string = "<div class='contact-list-container'>"; 
  //Html list structure
  $selector_list = "<select id='contact-selector'>";
  $contact_list = "<div class='contact-list'>";
  
    while ( $query->have_posts() ): $query->the_post();
      $slug = basename(get_permalink());
      $index = $query->current_post;
      $selector_list .= "<option value='{$slug}'>".get_the_title()."</option>";
      $contact_list .= "<div class='contact-box {$slug} {$index} overview' style='display: none;'><h3>".
                        get_the_title()."</h3><div class='row'>";
      $contact_list .= "<div class='span4'><table><tbody>";
      
      $name = get_post_meta( get_the_ID(), '_contact_box_name', true );
      if ( ! empty($name)) {
        $contact_list .= "<tr class='name'><th>".__("Nimi")."</th><td><strong>{$name}</strong></td></tr>";
      }
      $phone = get_post_meta( get_the_ID(), '_contact_box_phone', true );
      if ( ! empty($phone)) {
        $contact_list .= "<tr class='phone'><th>".__("Puhelin")."</th><td><strong>{$phone}</strong></td></tr>";
      }
      $email = get_post_meta( get_the_ID(), '_contact_box_email', true );
      if ( ! empty($email)) {
        $contact_list .= "<tr class='email'><th>".__("Sähköposti")."</th><td><strong>{$email}</strong></td></tr>";
      }
      $address = get_post_meta( get_the_ID(), '_contact_box_address', true );
      if ( ! empty($address)) {
        $contact_list .= "<tr class='address'><th>".__("Osoite")."</th><td><strong>{$address}</strong></td></tr>";
      }
      $postalcode = get_post_meta( get_the_ID(), '_contact_box_postalcode', true );
      if ( ! empty($postalcode)) {
        $contact_list .= "<tr class='postalcode'><th>".__("Postinumero")."</th><td><strong>{$postalcode}</strong></td></tr>";
      }
      
      $contact_list .= "</tbody></table></div>";
      $contact_list .= "<div class='contact-content span4'>".get_the_content()."</div>";
      $contact_list .= "</div></div>";
    endwhile;
  $contact_list .= "</div>";
  $selector_list .= "</select>";
  $return_string .= $selector_list.$contact_list."</div>";
  //end html list structure
  endif;
  return $return_string;
}
function register_contact_shortcodes(){
  add_shortcode('contacts-list', 'contacts_list_function');
}
add_action( 'init', 'register_contact_shortcodes');

