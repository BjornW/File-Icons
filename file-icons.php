<?php
/*******************************************************************************
Plugin Name: File Icons
Plugin URI: http://www.burobjorn.nl
Description: Add icons to links, files and downloads using CSS classes & regular expressions
Author: Bjorn Wijers <burobjorn at burobjorn dot nl> 
Version: 3.0bd
Author URI: http://www.burobjorn.nl
*******************************************************************************/   
   
/*  Copyright 2012
  

File Icons is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

File Icons is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( ! class_exists('FileIcons')) {
  class FileIcons {
  
    /**
     * @var string The options string name for this plugin
    */
    var $options_name = 'fi_options';
    
    /**
     * @var string $localization_domain Domain used for localization
    */
    var $localization_domain = 'fi_';
    
    /**
     * @var string $plugin_url The path to this plugin
    */ 
    var $plugin_url = '';
    
    /**
     * @var string $plugin_path The path to this plugin
    */
    var $plugin_path = '';
        
    /**
     * @var array $options Stores the options for this plugin
    */
    var $options = array();

    var $context_page;
    
    /**
     * PHP 4 Compatible Constructor
    */
    function ClassName(){ $this->__construct(); }
    
    /**
     * PHP 5 Constructor
    */        
    function __construct()
    {
      // language setup
      $locale = get_locale();
      $mo     = dirname(__FILE__) . '/languages/' . $this->localization_domain . '-' . $locale . '.mo';
      load_textdomain($this->localization_domain, $mo);

      // 'constants' setup
      $this->plugin_url  = WP_PLUGIN_URL . '/' . dirname(plugin_basename(__FILE__)).'/';
      $this->plugin_path = WP_PLUGIN_DIR . '/' . dirname(plugin_basename(__FILE__)).'/';
      
      // initialize the options
      //This is REQUIRED to initialize the options when the plugin is loaded!
      $this->get_options();
      
      // Wordpress actions        
      add_action( 'admin_menu', array(&$this, 'admin_menu_link') );
      add_action( 'init' , array(&$this, 'init') );
    }
       
    function init() 
    {
      // parse WordPress Posts & Pages for links
      add_action( 'the_content', array( &$this, 'add_css_classes_to_content' ) );
      
      // parse WordPress widget text for links
      add_action( 'widget_text', array( &$this, 'add_css_classes_to_content' ) );

      add_filter( 'contextual_help', array(&$this, 'add_context_help'), 10, 3);

      add_action( 'wp_enqueue_scripts', array(&$this, 'custom_style') );
    }

    // add contextual help
    function add_context_help( $context_help, $screen_id, $screen ) 
    {
      if( $this->context_page == $screen_id ) {
        $context_help  = '<h1>' . __('File Icons Help', $this->localization_domain) . "</h1>\n";
        $context_help .= '<p>' .__("Using the File Icons plugin you can easily add icons to links in Posts, Pages and even Widgets 
          (as long as the widget uses the widget_text filter such as WordPress default text widget).") . '</p>'; 
        
        $context_help .= '<h2>' . __('How does it work?') . '</h2>'; 
        $context_help .= '<p>' . __ ("The File Icons plugin searches for specified link characteristics using 
          <a href='https://en.wikipedia.org/wiki/Regular_expression' target='_new' title='Read about Regular Expressions on Wikipedia in a new window'>regular expressions</a>. 
          When a match is found, the plugin will add the defined css class(es) to the link. Existing css classes will be preserved 
          <em>unless</em> an existing css class name matches a class defined in the plugin. In those cases the File Icons plugin will
          overwrite the existing class name. The plugin goes through the file icons in the order they are shown 
          and uses the first match it finds. By adding the css classes defined in the File Icons plugin to your theme's stylesheet 
          you are able to style links using the added css classes, either with icons or something completely different. ") . '</p>'; 
        
        $context_help .= '<h2>' . __('How to add a new file icon?') . '</h2>';  
        $context_help .= '<p><ul>';
        $context_help .= '<li>' . __("Enter a new unique name for the file icon in the <em>'Icon Type'</em> field. 
          This is only used in the File Icons options screen to easily distinguish the different file icons regular expressions") .'</li>';
        $context_help .= '<li>' . __("Enter one or more CSS classes, delimited by one space in the <em>'CSS classes'</em> field. 
          The class(es) will only be added to links matching the specified regular expression") .'</li>';
        $context_help .= '<li>' . __("Enter a regular expression which conforms to the options used by 
          <a href='http://php.net/manual/en/function.preg-match.php' target='_new' title='Read about using preg_match() 
          in a new window'>PHP's preg_match() function</a> in the <em>'Regular Expression'</em> field.") .'</li>';
        $context_help .= '<li>' . __("Press the <em>'Add new file icon'</em> button to add your new file icon settings") . '</li>'; 
        $context_help .= '<li>' . __("Add the classes you've entered in the <em>'CSS classes'</em> field to your theme's stylesheet 
          and upload your css sprite image.") . '</li>'; 
        $context_help .= '</ul></p>';

        $context_help .= '<h2>' . __('How to edit an existing file icon?') . '</h2>';
        $context_help .= '<p>' . __("You can edit existing file icons as you see fit below the <em>'Edit existing file icons'</em> and when you're
          happy with your changes press the <em>'Save changes to existing file icons'</em> button to save your changes. Keep in mind, that if you 
          change the css class names you also need to change your stylesheet to address the new css class names."); 
        $context_help .= '</p>';  
          
        $context_help .= '<h2>' . __('How to remove an existing file icon?') . '</h2>';
        $context_help .= '<p>' . __("Removing a file icon is easy, just check the box <em>'Remove'</em> 
          and press the <em>'Save changes to existing file icons'</em> button to remove the file icon.") . '</p>';

        $context_help .= '<h2>' . __('How to undo all changes and reset the plugin settings?') . '</h2>';
        $context_help .= '<p>' . __("Press the <em>'Reset all settings to defaults'</em> button and all settings will 
          be reset to the plugin default settings.") . '</p>';
      }

      return $context_help; 
    }


    /**
     * Retrieves the plugin options from the database.
     * @return array
    */
    function get_options() 
    {
      // don't forget to set up the default options
      if ( ! $the_options = get_option( $this->options_name) ) {
        $the_options = array(
          'version' => '3.0',
          'icons'   => $this->get_default_icons(),
          'css'     => '',
          'sprite'  => ''  
        );
        update_option($this->options_name, $the_options);
      }
      $this->options = $the_options;
    }

    /**
     * Saves the admin options to the database.
    */
    function save_admin_options()
    {
      return update_option($this->options_name, $this->options);
    }
    
    /**
     * @desc Adds the options subpanel
     *
     * NB:
     * If you change this from add_options_page, MAKE SURE you change the filter_plugin_actions function (below) to 
     * reflect the page filename (ie - options-general.php) of the page your plugin is under!
    */
    function admin_menu_link() 
    {
      $this->context_page = add_options_page('File Icons', 'File Icons', 'manage_options', basename(__FILE__), array(&$this,'admin_options_page'));
      add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array(&$this, 'filter_plugin_actions'), 'manage_options', 2 );
      add_action('admin_print_styles-' . $this->context_page, array(&$this, 'admin_scripts_styles') );
    }
    
    /**
     * @desc Adds the Settings link to the plugin activate/deactivate page
     *
     * NB:
     * If your plugin is under a different top-level menu than 
     * Settiongs (IE - you changed the function above to something other than add_options_page) 
     * Then you're going to want to change options-general.php below to the name of your top-level page
    */
    function filter_plugin_actions($links, $file) 
    {
      $settings_link = '<a href="options-general.php?page=' . basename(__FILE__) . '">' . __('Settings') . '</a>';
      array_unshift( $links, $settings_link ); // before other links
      return $links;
    }
    
    /**
    * Adds settings/options page
    */
    function admin_options_page() 
    {
     $html =''; 
      if( isset( $_POST['fi_action'] ) ) {
        $action = $_POST['fi_action'];
        switch( $action ) {
        
          case 'new': 
            if ( ! wp_verify_nonce($_POST['fi_new_wpnonce'], 'fi-new') ) { die( __('Whoops, could not add new icon. Please try again.', $this->localization_domain) ) ; } 
            $_POST['icon'] = array_map( 'stripslashes_deep', $_POST['icon'] );
            $this->options['icons'][] = $_POST['icon'];
            $msg = __('Successfully added a new file icon', $this->localization_domain);       
          break;

          case 'edit':
            if ( ! wp_verify_nonce($_POST['fi_edit_wpnonce'], 'fi-edit') ) { die( __('Whoops, could not edit icons. Please try again.', $this->localization_domain) ) ; } 
            $_POST['icons'] = array_map( 'stripslashes_deep', $_POST['icons'] );
            $this->options['icons'] = $this->remove_icons( $_POST['icons'] );
            $msg = __('Successfully changed existing file icons', $this->localization_domain);       
          break;

          case 'extra':
            if ( ! wp_verify_nonce($_POST['fi_extra_wpnonce'], 'fi-extra') ) { die( __('Whoops, could not save extra options. Please try again.', $this->localization_domain) ) ; } 
            $this->options['css']    = isset( $_POST['upload_css'] )    ? $_POST['upload_css']    : null;
            $this->options['sprite'] = isset( $_POST['upload_sprite'] ) ? $_POST['upload_sprite'] : null; 
            $msg = __('Successfully added custom sprite and/or css', $this->localization_domain);       
          break;

          case 'reset':
            if ( ! wp_verify_nonce($_POST['fi_reset_wpnonce'], 'fi-reset') ) { die( __('Whoops, could not save reset settings. Please try again.', $this->localization_domain) ) ; } 
            $this->options['icons']  = $this->get_default_icons(); 
            $this->options['css']    = '';
            $this->options['sprite'] = ''; 
            $msg = __('Reset to default settings', $this->localization_domain);       
          break;
        }
        $this->save_admin_options();
        $html = "<div class='updated'><p>$msg</p></div>";
      } 

      // build the options page  
      $html .= "<div class='wrap'>\n";
      $html .= "<h2>" . __('File Icons Settings', $this->localization_domain) . "</h2>\n";
      $html .= '<p>' . __("Consult the contextual help (press the top right button conventiently marked <em>'Help'</em>)
        for more information on the File Icons plugin") . '</p>';
      
      $html .= $this->gui_add_new_icon( false );
      $html .= $this->gui_existing_icons( false );  
      $html .= $this->gui_extra_options( false );  
      
      $html .= "</div>\n";
      echo $html;  
    }


    function gui_add_new_icon( $echo = true ) 
    {
      $html = '';
      $html .= "<form method='post' id='fi_new'>";
      $html .= wp_nonce_field('fi-new', $name = 'fi_new_wpnonce', $referer = true, $echo = false);
      $html .= "<table width='100%' cellspacing='2' cellpadding='5' class='form-table'>\n"; 
      
      $html .= "<h2>" . __('Add a new file icon') . "</h2>\n";
      $html .= "<tr valign='top'>\n"; 
      $html .= "\t<th><label for='type'>" .  __('Icon type', $this->localization_domain) . "</label></th>\n"; 
      $html .= "\t<th><label for='class'>" .  __('CSS classes', $this->localization_domain) . "</label</th>\n"; 
      $html .= "\t<th><label for='regex'>" .  __('Regular Expression', $this->localization_domain) . "</label></th>\n"; 
      $html .= "</tr>\n";
      
      $html .= "<tr valign='top'>\n"; 
      $html .= "\t<td><input id='type' type='text' maxlength='75' name='icon[type]'  value='' /></th>\n"; 
      $html .= "\t<td><input id='class' type='text' maxlength='75' name='icon[class]' value='' /></th>\n"; 
      $html .= "\t<td><input id='regex' type='text' maxlength='75' name='icon[regex]' value='' /></th>\n"; 
      $html .= "</tr>\n";
      
      $html .= "<tr>\n";
      $html .= "\t<td colspan='3'><input type='hidden' name='fi_action' value='new' /></td>\n";
      $html .= "</tr>\n";
      $html .= "<tr>\n";
      $html .= "\t<td colspan='3'><input type='submit' name='fi_new' value='" . __('Add new file icon', $this->localization_domain) . "'/></td>\n";
      $html .= "</tr>\n";
      $html .= "</table><br />\n";
      $html .= "</form>\n";
      if( $echo ) {
        echo $html;
      } else {
        return $html;
      }
    }


    function gui_existing_icons( $echo = true ) 
    { 
      $icons = $this->get_icons();
      $html = '';

      $html .= "<h2>" . __('Edit existing file icons') . "</h2>\n"; 
      $html .= "<form method='post' id='fi_edit'>";
      $html .= wp_nonce_field('fi-edit', $name = 'fi_edit_wpnonce', $referer = true, $echo = false);
      $html .= "<table width='100%' cellspacing='2' cellpadding='5' class='form-table'>\n"; 

      $html .= "<tr valign='top'>\n"; 
      $html .= "\t<th>" .  __('Icon type', $this->localization_domain) . "</th>\n"; 
      $html .= "\t<th>" .  __('CSS classes', $this->localization_domain) . "</th>\n"; 
      $html .= "\t<th>" .  __('Regular Expression', $this->localization_domain) . "</th>\n"; 
      $html .= "\t<th>" .  __('Remove') . "</th>\n"; 
      $html .= "</tr>\n";
      
      if( is_array($icons) ) {
        $i = 0;
        foreach($icons as $icon) {
          if( is_array($icon) ) {
            $html .= "<tr valign='top'>\n"; 
            $html .= "\t<td><input type='text' maxlength='75' name='icons[$i][type]'  value='" .  $icon['type'] . "' /></td>\n"; 
            $html .= "\t<td><input type='text' maxlength='75' name='icons[$i][class]' value='" .  $icon['class'] . "' /></td>\n"; 
            $html .= "\t<td><input type='text' maxlength='75' name='icons[$i][regex]' value='" .  $icon['regex'] . "' /></td>\n";
            $html .= "\t<td><input type='checkbox' id='removal_chbx$i' name='icons[$i][remove] value='true' /></td>\n"; 
            $html .= "</tr>\n";
            $i++;
          }
        }
      }

      $html .= "<tr>\n";
      $html .= "\t<td colspan='4'><input type='hidden' name='fi_action' value='edit' /></td>\n";
      $html .= "</tr>\n";
      $html .= "<tr>\n";
      $html .= "\t<td colspan='4'><input type='submit' name='fi_save' value='" . __('Save changes to existing file icons', $this->localization_domain) . "'/></td>\n";
      $html .= "</tr>\n";
      $html .= "</table><br />\n";
      $html .= "</form>\n";
      if( $echo ) {
        echo $html;
      } else {
        return $html;
      }
    }


    function gui_extra_options( $echo = true ) 
    {
      $html = '';
      $html .= "<h2>" . __('Reset settings to defaults') . "</h2>\n" ;
      $html .= "<form method='post' id='fi_reset'>";
      $html .= wp_nonce_field('fi-reset', $name = 'fi_reset_wpnonce', $referer = true, $echo = false);
      $html .= "<input type='hidden' name='fi_action' value='reset' />";
      $html .= "<input type='submit' name='fi_reset' value='" . __('Reset all settings to defaults', $this->localization_domain) . "'/>\n";
      $html .= "</form><br />\n";

      /*
      $html .= "<h2>" . __('Extra options (Experimental!)') . "</h2>\n";
      $html .= "<form method='post' id='fi_extra'>";
      $html .= wp_nonce_field('fi-extra', $name = 'fi_extra_wpnonce', $referer = true, $echo = false);
      $html .= '<p><label for="upload_sprite">' . __('Add custom icon sprite: '); 
      $html .= "<br /><input id='upload_sprite' size='36' value='{$this->options['sprite']}' name='upload_sprite' type='text' /> ";
      $html .= '<input id="upload_sprite_button" value="Upload icon sprite" type="button" /> </label></p>';
      $html .= '<p>';
      $html .= '<label for="upload_css">' . __('Add custom stylesheet: '); 
      $html .= "<br /><input id='upload_css' size='36' value='{$this->options['css']}' name='upload_css' type='text' /> ";
      $html .= "<input id='upload_css_button' value='Upload stylesheet' type='button' /> </label></p>";
      $html .= "<input type='hidden' name='fi_action' value='extra' />";
      $html .= "<input type='submit' name='fi_save_extra_options' value='" . __('Save extra options', $this->localization_domain) . "'/>\n";
      $html .= "</form><br />\n";
       */

      if( $echo ) {
        echo $html;
      } else {
        return $html;
      }
    }



    function get_default_icons() 
    {
      $icons = array();
      
      $icons[] = array( 'type' => 'Excel',      'class' => 'icon excelfile',  'regex' => '/\.xls$/i' );
      $icons[] = array( 'type' => 'Word',       'class' => 'icon worddoc',    'regex' => '/\.doc$/i' );
      $icons[] = array( 'type' => 'PDF',        'class' => 'icon pdf',        'regex' => '/\.pdf$/i' );
      $icons[] = array( 'type' => 'PowerPoint', 'class' => 'icon ppt-file',   'regex' => '/\.ppt$/i' );
      $icons[] = array( 'type' => 'HTML',       'class' => 'icon html-page',  'regex' => '/\.html$/i' );

      $icons[] = array( 'type' => 'Audio',      'class' => 'icon audio',      'regex' => '/\.mp3/i' );
      $icons[] = array( 'type' => 'Video',      'class' => 'icon play',       'regex' => '/\.mp4/i' );


      $icons[] = array( 'type' => 'Email link', 'class' => 'icon email', 'regex' => '/^mailto/i' );
      $icons[] = array( 'type' => 'Tag link',   'class' => 'icon tag',    'regex' => '/\/tag\//i' );

      $id  = ( is_multisite() ) ? 1 : null; 
      $url = get_site_url($id);
      
      $icons[] = array( 'type' => 'Internal link', 'class' => 'icon internal', 'regex' => "/^$url/i" );
      
      return $icons;
    }


    // either use the_content filter function and transform text before output or
    // add classes to the content upon saving or editing data?? 

    
    function add_css_classes_to_content( $content ) 
    {
      // make sure the content is not empty and contains a link
      if( ! empty($content) && stristr( $content, 'href' ) !== false ) {
        $dom = new DOMDocument;
        if( $dom->loadHTML( $content ) ) {
          $links = $dom->getElementsByTagName('a');
          foreach ($links as $l) {
            if( is_object($l) ) { 
              $url = ( $l->hasAttribute('href') ) ? $l->getAttribute('href') : null;
              if( $icon_class = $this->has_icon( $url ) ) {
                if( $l->hasAttribute( 'class' ) ) {
                  $existing_class = $l->getAttribute('class'); 
                  $existing_classes = explode(' ', $existing_class); 
                  $existing_classes = is_array($existing_classes) ? $existing_classes : array($existing_class); 
                  
                  $new_classes = explode(' ', $icon_class);
                  $new_classes = is_array($new_classes) ? $new_classes : array($new_classes);
                  
                  $combined_classes = array_merge($existing_classes, $new_classes);
                  
                  $icon_class = implode(' ', $combined_classes);
                } 
                $l->setAttribute('class', $icon_class);
              }      
            }
          }
          $content = $dom->saveHTML();
        }
      }
      return $content;  
    } 


    function has_icon( $string) 
    {
      if( ! is_null($string) && ! empty($string) && is_string($string) ) {
        $icons = $this->get_icons();
        foreach( $icons as $icon ) {
          if( ! empty($icon['regex']) ) {  
            if( preg_match( $icon['regex'], $string) ) {
              return $icon['class'];
            }
          }
        }
      }
      return;  
    }




    function remove_icons( $icons ) 
    {
      if( ! is_array ($icons ) ) { return $icons ;}
      $nr_icons = sizeof( $icons );  
      for($i = 0; $i < $nr_icons; $i++) {
        if( isset($icons[$i]['remove']) && $icons[$i]['remove'] == true ) {
          $icons[$i] = null;
        }
      }
      return $icons;
    }
    
    
    function get_icons() 
    {
      return $this->options['icons'];
    }

    


    function has_dom_extension() 
    {
      return extension_loaded('dom');
    }

    function admin_scripts_styles() 
    {
      wp_enqueue_script('media-upload');
      wp_enqueue_script('thickbox');
      wp_enqueue_style('thickbox');
      
      wp_register_script('file-icons-upload', $this->plugin_url .'js/file-icons.js', array('jquery','media-upload','thickbox'));
      wp_enqueue_script('file-icons-upload');
      
    }


    
    function custom_style() 
    {
      if( ! is_null( $this->options['css'] ) ) {
        wp_register_style('file-icons-custom', $this->options['css']);
        wp_enqueue_style('file-icons-custom');
      } else {
        return;
      }
    }

    

  } 
} 

// instantiate the class
if ( class_exists('FileIcons') ) { 
  $fi_var = new FileIcons();
}
?>
