<?php
/**
 * Plugin Name: Offriend Headless Core
 * Plugin URI: https://offriend.co.th
 * Description: Core plugin for Offriend Headless CMS, providing Custom Post Types (Projects, Services, Team, Tools) and Site Settings REST API.
 * Version: 1.0.0
 * Author: Offriend Engineering
 * Author URI: https://offriend.co.th
 * License: GPL2
 * Text Domain: offriend
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Offriend_Headless_Core {

    private static $instance = null;
    const SETTINGS_OPTION_KEY = 'offriend_site_settings';

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Register Custom Post Types & Taxonomies
        add_action( 'init', array( $this, 'register_custom_post_types' ) );
        add_action( 'init', array( $this, 'register_taxonomies' ) );
        add_action( 'init', array( $this, 'register_tool_category_term_meta' ) );
        add_action( 'init', array( $this, 'register_tool_format_term_meta' ) );
        add_action( 'init', array( $this, 'ensure_tool_categories' ), 30 );
        add_action( 'init', array( $this, 'ensure_tool_formats' ), 30 );

        add_action( 'tool_category_add_form_fields', array( $this, 'render_tool_category_add_fields' ) );
        add_action( 'tool_category_edit_form_fields', array( $this, 'render_tool_category_edit_fields' ) );
        add_action( 'created_tool_category', array( $this, 'save_tool_category_term_meta' ) );
        add_action( 'edited_tool_category', array( $this, 'save_tool_category_term_meta' ) );

        add_action( 'tool_format_add_form_fields', array( $this, 'render_tool_format_add_fields' ) );
        add_action( 'tool_format_edit_form_fields', array( $this, 'render_tool_format_edit_fields' ) );
        add_action( 'created_tool_format', array( $this, 'save_tool_format_term_meta' ) );
        add_action( 'edited_tool_format', array( $this, 'save_tool_format_term_meta' ) );

        // Register Meta Boxes & REST Fields
        add_action( 'add_meta_boxes', array( $this, 'register_meta_boxes' ) );
        add_action( 'save_post', array( $this, 'save_meta_box_data' ) );
        add_action( 'rest_api_init', array( $this, 'register_rest_api_fields_and_routes' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

        // Admin Menu for Site Settings
        add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'handle_admin_actions' ) );

        // Enable CORS for Astro Frontend
        add_action( 'init', array( $this, 'handle_cors' ) );
    }

    /**
     * Enqueue WordPress Media Uploader in Admin for file uploads
     */
    public function enqueue_admin_assets( $hook ) {
        wp_enqueue_media();
    }

    /**
     * Allow Astro frontend to access REST API smoothly
     */
    public function handle_cors() {
        header( "Access-Control-Allow-Origin: *" );
        header( "Access-Control-Allow-Methods: GET, POST, OPTIONS" );
        header( "Access-Control-Allow-Headers: Authorization, Content-Type, X-WP-Wpnonce" );
    }

    /**
     * 1. Register Custom Post Types: Projects, Services, Team, Tools
     */
    public function register_custom_post_types() {
        // A. ผลงานและโครงการ (Projects)
        register_post_type( 'projects', array(
            'labels' => array(
                'name'               => 'ผลงานและโครงการ',
                'singular_name'      => 'ผลงาน',
                'add_new'            => 'เพิ่มผลงานใหม่',
                'add_new_item'       => 'เพิ่มผลงานใหม่',
                'edit_item'          => 'แก้ไขผลงาน',
                'all_items'          => 'ผลงานทั้งหมด',
                'view_item'          => 'ดูผลงาน',
                'search_items'       => 'ค้นหาผลงาน',
                'not_found'          => 'ไม่พบผลงาน',
            ),
            'public'              => true,
            'has_archive'         => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_rest'        => true,
            'rest_base'           => 'projects',
            'menu_icon'           => 'dashicons-portfolio',
            'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ),
            'rewrite'             => array( 'slug' => 'projects' ),
        ) );

        // B. บริการทั้งหมด (Services)
        register_post_type( 'services', array(
            'labels' => array(
                'name'               => 'บริการทั้งหมด',
                'singular_name'      => 'บริการ',
                'add_new'            => 'เพิ่มบริการใหม่',
                'add_new_item'       => 'เพิ่มบริการใหม่',
                'edit_item'          => 'แก้ไขบริการ',
                'all_items'          => 'บริการทั้งหมด',
                'view_item'          => 'ดูบริการ',
                'search_items'       => 'ค้นหาบริการ',
                'not_found'          => 'ไม่พบบริการ',
            ),
            'public'              => true,
            'has_archive'         => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_rest'        => true,
            'rest_base'           => 'services',
            'menu_icon'           => 'dashicons-laptop',
            'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes', 'custom-fields' ),
            'rewrite'             => array( 'slug' => 'services' ),
        ) );

        // C. บุคลากรและวิทยากร (Team)
        register_post_type( 'team', array(
            'labels' => array(
                'name'               => 'บุคลากรและวิทยากร',
                'singular_name'      => 'บุคลากร',
                'add_new'            => 'เพิ่มบุคลากรใหม่',
                'add_new_item'       => 'เพิ่มบุคลากรใหม่',
                'edit_item'          => 'แก้ไขข้อมูลบุคลากร',
                'all_items'          => 'บุคลากรทั้งหมด',
                'view_item'          => 'ดูข้อมูลบุคลากร',
                'search_items'       => 'ค้นหาบุคลากร',
                'not_found'          => 'ไม่พบข้อมูลบุคลากร',
            ),
            'public'              => true,
            'has_archive'         => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_rest'        => true,
            'rest_base'           => 'team',
            'menu_icon'           => 'dashicons-businessperson',
            'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes', 'custom-fields' ),
            'rewrite'             => array( 'slug' => 'team' ),
        ) );

        // D. เครื่องมือและเทมเพลต (Tools)
        register_post_type( 'tools', array(
            'labels' => array(
                'name'               => 'เครื่องมือและเทมเพลต',
                'singular_name'      => 'เครื่องมือ',
                'add_new'            => 'เพิ่มเครื่องมือใหม่',
                'add_new_item'       => 'เพิ่มเครื่องมือใหม่',
                'edit_item'          => 'แก้ไขเครื่องมือ',
                'all_items'          => 'เครื่องมือทั้งหมด',
                'view_item'          => 'ดูเครื่องมือ',
                'search_items'       => 'ค้นหาเครื่องมือ',
                'not_found'          => 'ไม่พบเครื่องมือ',
            ),
            'public'              => true,
            'has_archive'         => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_rest'        => true,
            'rest_base'           => 'tools',
            'menu_icon'           => 'dashicons-hammer',
            'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ),
            'rewrite'             => array( 'slug' => 'tools' ),
        ) );
    }

    /**
     * 2. Register Taxonomies
     */
    public function register_taxonomies() {
        // Project Categories
        register_taxonomy( 'project_category', 'projects', array(
            'labels' => array(
                'name'          => 'หมวดหมู่ผลงาน',
                'singular_name' => 'หมวดหมู่ผลงาน',
            ),
            'hierarchical'      => true,
            'show_in_rest'      => true,
            'show_ui'           => true,
        ) );

        // Tool Categories — managed in WP admin and consumed by the tools listing sidebar
        register_taxonomy( 'tool_category', 'tools', array(
            'labels' => array(
                'name'              => 'หมวดหมู่เครื่องมือ',
                'singular_name'     => 'หมวดหมู่เครื่องมือ',
                'menu_name'         => 'หมวดหมู่',
                'all_items'         => 'หมวดหมู่ทั้งหมด',
                'edit_item'         => 'แก้ไขหมวดหมู่',
                'view_item'         => 'ดูหมวดหมู่',
                'update_item'       => 'อัปเดตหมวดหมู่',
                'add_new_item'      => 'เพิ่มหมวดหมู่ใหม่',
                'new_item_name'     => 'ชื่อหมวดหมู่ใหม่',
                'search_items'      => 'ค้นหาหมวดหมู่',
                'not_found'         => 'ไม่พบหมวดหมู่',
                'parent_item'       => 'หมวดหมู่หลัก',
                'parent_item_colon' => 'หมวดหมู่หลัก:',
            ),
            'hierarchical'      => true,
            'public'            => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_rest'      => true,
            'rest_base'         => 'tool_category',
        ) );

        // Programs shown on the tools listing "โปรแกรม" tab
        register_taxonomy( 'tool_format', 'tools', array(
            'labels' => array(
                'name'              => 'โปรแกรม',
                'singular_name'     => 'โปรแกรม',
                'menu_name'         => 'โปรแกรม',
                'all_items'         => 'โปรแกรมทั้งหมด',
                'edit_item'         => 'แก้ไขโปรแกรม',
                'view_item'         => 'ดูโปรแกรม',
                'update_item'       => 'อัปเดตโปรแกรม',
                'add_new_item'      => 'เพิ่มโปรแกรมใหม่',
                'new_item_name'     => 'ชื่อโปรแกรมใหม่',
                'search_items'      => 'ค้นหาโปรแกรม',
                'not_found'         => 'ไม่พบโปรแกรม',
                'parent_item'       => 'โปรแกรมหลัก',
                'parent_item_colon' => 'โปรแกรมหลัก:',
            ),
            'hierarchical'      => true,
            'public'            => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_rest'      => true,
            'rest_base'         => 'tool_format',
        ) );
    }

    /**
     * Appearance options editors can set on each tool category.
     */
    public function tool_category_icon_options() {
        return array(
            'sparkles'   => 'ดาว (ทั่วไป)',
            'calculator' => 'เครื่องคิดเลข (การเงิน)',
            'kanban'     => 'บอร์ดงาน (โครงการ)',
            'receipt'    => 'เอกสาร (การขาย)',
            'users'      => 'บุคลากร',
            'megaphone'  => 'เมกะโฟน (การตลาด)',
            'palette'    => 'พาเลต (ออกแบบ)',
            'bot'        => 'บอท (AI)',
            'server'     => 'เซิร์ฟเวอร์ (ไอที)',
            'folder'     => 'โฟลเดอร์',
            'layers'     => 'เลเยอร์',
            'laptop'     => 'แล็ปท็อป',
        );
    }

    public function tool_category_color_options() {
        return array(
            'text-[#162d59]'  => 'กรมท่า (แบรนด์)',
            'text-emerald-500'=> 'เขียว',
            'text-indigo-500' => 'คราม',
            'text-rose-500'   => 'ชมพู',
            'text-amber-500'  => 'ส้ม',
            'text-fuchsia-500'=> 'ม่วง',
            'text-cyan-500'   => 'ฟ้าน้ำทะเล',
            'text-blue-500'   => 'น้ำเงิน',
            'text-slate-500'  => 'เทา',
        );
    }

    public function register_tool_category_term_meta() {
        $auth = function() {
            return current_user_can( 'manage_categories' );
        };

        register_term_meta( 'tool_category', 'icon', array(
            'type'              => 'string',
            'single'            => true,
            'show_in_rest'      => true,
            'sanitize_callback' => 'sanitize_key',
            'auth_callback'     => $auth,
            'default'           => 'folder',
        ) );
        register_term_meta( 'tool_category', 'color', array(
            'type'              => 'string',
            'single'            => true,
            'show_in_rest'      => true,
            'sanitize_callback' => 'sanitize_text_field',
            'auth_callback'     => $auth,
            'default'           => 'text-slate-500',
        ) );
        register_term_meta( 'tool_category', 'sort_order', array(
            'type'              => 'integer',
            'single'            => true,
            'show_in_rest'      => true,
            'sanitize_callback' => 'absint',
            'auth_callback'     => $auth,
            'default'           => 100,
        ) );
    }

    public function render_tool_category_add_fields() {
        $icons  = $this->tool_category_icon_options();
        $colors = $this->tool_category_color_options();
        ?>
        <div class="form-field">
            <label for="tool_category_icon">ไอคอน</label>
            <select name="tool_category_icon" id="tool_category_icon">
                <?php foreach ( $icons as $value => $label ) : ?>
                    <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, 'folder' ); ?>><?php echo esc_html( $label ); ?></option>
                <?php endforeach; ?>
            </select>
            <p>ไอคอนที่แสดงในแถบกรองหมวดหมู่บนหน้าเครื่องมือและเทมเพลต</p>
        </div>
        <div class="form-field">
            <label for="tool_category_color">สีไอคอน</label>
            <select name="tool_category_color" id="tool_category_color">
                <?php foreach ( $colors as $value => $label ) : ?>
                    <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, 'text-slate-500' ); ?>><?php echo esc_html( $label ); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-field">
            <label for="tool_category_sort_order">ลำดับการแสดง</label>
            <input type="number" name="tool_category_sort_order" id="tool_category_sort_order" value="100" min="0" step="1" />
            <p>เลขน้อยอยู่ด้านบน หมวดหมู่ “ทั้งหมด” อยู่บนสุดเสมอ</p>
        </div>
        <?php
    }

    public function render_tool_category_edit_fields( $term ) {
        $icons  = $this->tool_category_icon_options();
        $colors = $this->tool_category_color_options();
        $icon   = get_term_meta( $term->term_id, 'icon', true ) ?: 'folder';
        $color  = get_term_meta( $term->term_id, 'color', true ) ?: 'text-slate-500';
        $order  = get_term_meta( $term->term_id, 'sort_order', true );
        if ( $order === '' ) {
            $order = 100;
        }
        ?>
        <tr class="form-field">
            <th scope="row"><label for="tool_category_icon">ไอคอน</label></th>
            <td>
                <select name="tool_category_icon" id="tool_category_icon">
                    <?php foreach ( $icons as $value => $label ) : ?>
                        <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $icon ); ?>><?php echo esc_html( $label ); ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="description">ไอคอนที่แสดงในแถบกรองหมวดหมู่บนหน้าเครื่องมือและเทมเพลต</p>
            </td>
        </tr>
        <tr class="form-field">
            <th scope="row"><label for="tool_category_color">สีไอคอน</label></th>
            <td>
                <select name="tool_category_color" id="tool_category_color">
                    <?php foreach ( $colors as $value => $label ) : ?>
                        <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $color ); ?>><?php echo esc_html( $label ); ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr class="form-field">
            <th scope="row"><label for="tool_category_sort_order">ลำดับการแสดง</label></th>
            <td>
                <input type="number" name="tool_category_sort_order" id="tool_category_sort_order" value="<?php echo esc_attr( $order ); ?>" min="0" step="1" />
                <p class="description">เลขน้อยอยู่ด้านบน</p>
            </td>
        </tr>
        <?php
    }

    public function save_tool_category_term_meta( $term_id ) {
        if ( ! isset( $_POST['tool_category_icon'] ) && ! isset( $_POST['tool_category_color'] ) && ! isset( $_POST['tool_category_sort_order'] ) ) {
            return;
        }
        if ( ! current_user_can( 'edit_term', $term_id ) ) {
            return;
        }

        $icons  = $this->tool_category_icon_options();
        $colors = $this->tool_category_color_options();

        $icon = isset( $_POST['tool_category_icon'] ) ? sanitize_key( wp_unslash( $_POST['tool_category_icon'] ) ) : 'folder';
        if ( ! isset( $icons[ $icon ] ) ) {
            $icon = 'folder';
        }
        update_term_meta( $term_id, 'icon', $icon );

        $color = isset( $_POST['tool_category_color'] ) ? sanitize_text_field( wp_unslash( $_POST['tool_category_color'] ) ) : 'text-slate-500';
        if ( ! isset( $colors[ $color ] ) ) {
            $color = 'text-slate-500';
        }
        update_term_meta( $term_id, 'color', $color );

        $order = isset( $_POST['tool_category_sort_order'] ) ? absint( $_POST['tool_category_sort_order'] ) : 100;
        update_term_meta( $term_id, 'sort_order', $order );
    }

    /**
     * Create the current sidebar categories once, then attach tools that only
     * stored a free-text category name.
     */
    public function ensure_tool_categories() {
        if ( get_option( 'offriend_tool_categories_ready' ) ) {
            return;
        }

        $defaults = array(
            array( 'name' => 'การเงิน & บัญชี', 'slug' => 'finance', 'icon' => 'calculator', 'color' => 'text-emerald-500', 'order' => 10 ),
            array( 'name' => 'บริหารโครงการ', 'slug' => 'project', 'icon' => 'kanban', 'color' => 'text-[#162d59]', 'order' => 20 ),
            array( 'name' => 'เอกสาร & การขาย', 'slug' => 'sales', 'icon' => 'receipt', 'color' => 'text-indigo-500', 'order' => 30 ),
            array( 'name' => 'ทรัพยากรบุคคล & KPI', 'slug' => 'hr', 'icon' => 'users', 'color' => 'text-rose-500', 'order' => 40 ),
            array( 'name' => 'การตลาด & แผนงาน', 'slug' => 'marketing', 'icon' => 'megaphone', 'color' => 'text-amber-500', 'order' => 50 ),
            array( 'name' => 'ออกแบบ & กราฟิก', 'slug' => 'design', 'icon' => 'palette', 'color' => 'text-fuchsia-500', 'order' => 60 ),
            array( 'name' => 'ปัญญาประดิษฐ์ & AI', 'slug' => 'ai', 'icon' => 'bot', 'color' => 'text-cyan-500', 'order' => 70 ),
            array( 'name' => 'ไอที & ระบบเน็ตเวิร์ก', 'slug' => 'it', 'icon' => 'server', 'color' => 'text-blue-500', 'order' => 80 ),
        );

        foreach ( $defaults as $item ) {
            $existing = term_exists( $item['slug'], 'tool_category' );
            if ( ! $existing ) {
                $existing = term_exists( $item['name'], 'tool_category' );
            }
            if ( ! $existing ) {
                $created = wp_insert_term( $item['name'], 'tool_category', array( 'slug' => $item['slug'] ) );
                if ( is_wp_error( $created ) ) {
                    continue;
                }
                $term_id = (int) $created['term_id'];
            } else {
                $term_id = (int) ( is_array( $existing ) ? $existing['term_id'] : $existing );
            }

            if ( ! metadata_exists( 'term', $term_id, 'icon' ) ) {
                update_term_meta( $term_id, 'icon', $item['icon'] );
            }
            if ( ! metadata_exists( 'term', $term_id, 'color' ) ) {
                update_term_meta( $term_id, 'color', $item['color'] );
            }
            if ( ! metadata_exists( 'term', $term_id, 'sort_order' ) ) {
                update_term_meta( $term_id, 'sort_order', $item['order'] );
            }
        }

        $tools = get_posts( array(
            'post_type'      => 'tools',
            'post_status'    => 'any',
            'posts_per_page' => -1,
            'fields'         => 'ids',
        ) );

        foreach ( $tools as $tool_id ) {
            $assigned = wp_get_object_terms( $tool_id, 'tool_category', array( 'fields' => 'ids' ) );
            if ( ! is_wp_error( $assigned ) && ! empty( $assigned ) ) {
                continue;
            }

            $raw = get_post_meta( $tool_id, '_offriend_tool_category_name', true );
            if ( ! is_string( $raw ) || trim( $raw ) === '' ) {
                continue;
            }

            $term_ids = array();
            foreach ( array_map( 'trim', explode( ',', $raw ) ) as $name ) {
                $term_id = $this->find_or_create_tool_category( $name );
                if ( $term_id ) {
                    $term_ids[] = $term_id;
                }
            }
            if ( ! empty( $term_ids ) ) {
                wp_set_object_terms( $tool_id, $term_ids, 'tool_category', false );
            }
        }

        update_option( 'offriend_tool_categories_ready', '1' );
    }

    private function find_or_create_tool_category( $name ) {
        $name = trim( (string) $name );
        if ( $name === '' ) {
            return 0;
        }

        $found = get_term_by( 'name', $name, 'tool_category' );
        if ( $found && ! is_wp_error( $found ) ) {
            return (int) $found->term_id;
        }

        $slug = sanitize_title( $name );
        if ( $slug ) {
            $found = get_term_by( 'slug', $slug, 'tool_category' );
            if ( $found && ! is_wp_error( $found ) ) {
                return (int) $found->term_id;
            }
        }

        $created = wp_insert_term( $name, 'tool_category', $slug ? array( 'slug' => $slug ) : array() );
        if ( is_wp_error( $created ) ) {
            if ( isset( $created->error_data['term_exists'] ) ) {
                return (int) $created->error_data['term_exists'];
            }
            return 0;
        }

        update_term_meta( $created['term_id'], 'icon', 'folder' );
        update_term_meta( $created['term_id'], 'color', 'text-slate-500' );
        update_term_meta( $created['term_id'], 'sort_order', 100 );
        return (int) $created['term_id'];
    }

    /**
     * Categories actually attached to a tool, with the old free-text field as fallback.
     */
    public function get_tool_category_payload( $post_id ) {
        $terms = get_the_terms( $post_id, 'tool_category' );
        $names = array();
        $slugs = array();

        if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
            foreach ( $terms as $term ) {
                $names[] = $term->name;
                $slugs[] = $term->slug;
            }
        } else {
            $raw = get_post_meta( $post_id, '_offriend_tool_category_name', true );
            if ( is_string( $raw ) && trim( $raw ) !== '' ) {
                foreach ( array_map( 'trim', explode( ',', $raw ) ) as $name ) {
                    if ( $name === '' ) {
                        continue;
                    }
                    $names[] = $name;
                    $matched = get_term_by( 'name', $name, 'tool_category' );
                    $slugs[] = ( $matched && ! is_wp_error( $matched ) ) ? $matched->slug : sanitize_title( $name );
                }
            }
        }

        return array(
            'category_name' => implode( ', ', $names ),
            'category'      => implode( ', ', $names ),
            'category_slug' => isset( $slugs[0] ) ? $slugs[0] : '',
            'category_slugs'=> array_values( $slugs ),
        );
    }

    public function register_tool_format_term_meta() {
        register_term_meta( 'tool_format', 'sort_order', array(
            'type'              => 'integer',
            'single'            => true,
            'show_in_rest'      => true,
            'sanitize_callback' => 'absint',
            'auth_callback'     => function() {
                return current_user_can( 'manage_categories' );
            },
            'default'           => 100,
        ) );
    }

    public function render_tool_format_add_fields() {
        ?>
        <div class="form-field">
            <label for="tool_format_sort_order">ลำดับการแสดง</label>
            <input type="number" name="tool_format_sort_order" id="tool_format_sort_order" value="100" min="0" step="1" />
            <p>ชื่อที่กรอกด้านบนจะไปแสดงที่แท็บ “โปรแกรม” บนหน้าเครื่องมือและเทมเพลต เลขน้อยอยู่ด้านบน</p>
        </div>
        <?php
    }

    public function render_tool_format_edit_fields( $term ) {
        $order = get_term_meta( $term->term_id, 'sort_order', true );
        if ( $order === '' ) {
            $order = 100;
        }
        ?>
        <tr class="form-field">
            <th scope="row"><label for="tool_format_sort_order">ลำดับการแสดง</label></th>
            <td>
                <input type="number" name="tool_format_sort_order" id="tool_format_sort_order" value="<?php echo esc_attr( $order ); ?>" min="0" step="1" />
                <p class="description">เลขน้อยอยู่ด้านบนของแท็บโปรแกรม</p>
            </td>
        </tr>
        <?php
    }

    public function save_tool_format_term_meta( $term_id ) {
        if ( ! isset( $_POST['tool_format_sort_order'] ) ) {
            return;
        }
        if ( ! current_user_can( 'edit_term', $term_id ) ) {
            return;
        }
        update_term_meta( $term_id, 'sort_order', absint( $_POST['tool_format_sort_order'] ) );
    }

    /**
     * Create the current program list once, then attach tools that only stored a free-text format.
     */
    public function ensure_tool_formats() {
        if ( get_option( 'offriend_tool_formats_ready' ) ) {
            return;
        }

        $defaults = array(
            array( 'name' => 'Microsoft Excel', 'slug' => 'excel', 'order' => 10 ),
            array( 'name' => 'Google Sheets', 'slug' => 'sheets', 'order' => 20 ),
            array( 'name' => 'Web Application', 'slug' => 'web', 'order' => 30 ),
            array( 'name' => 'Figma & Canva', 'slug' => 'design', 'order' => 40 ),
            array( 'name' => 'PDF & เอกสาร', 'slug' => 'doc', 'order' => 50 ),
        );

        foreach ( $defaults as $item ) {
            $existing = term_exists( $item['slug'], 'tool_format' );
            if ( ! $existing ) {
                $existing = term_exists( $item['name'], 'tool_format' );
            }
            if ( ! $existing ) {
                $created = wp_insert_term( $item['name'], 'tool_format', array( 'slug' => $item['slug'] ) );
                if ( is_wp_error( $created ) ) {
                    continue;
                }
                $term_id = (int) $created['term_id'];
            } else {
                $term_id = (int) ( is_array( $existing ) ? $existing['term_id'] : $existing );
            }

            if ( ! metadata_exists( 'term', $term_id, 'sort_order' ) ) {
                update_term_meta( $term_id, 'sort_order', $item['order'] );
            }
        }

        $tools = get_posts( array(
            'post_type'      => 'tools',
            'post_status'    => 'any',
            'posts_per_page' => -1,
            'fields'         => 'ids',
        ) );

        foreach ( $tools as $tool_id ) {
            $assigned = wp_get_object_terms( $tool_id, 'tool_format', array( 'fields' => 'ids' ) );
            if ( ! is_wp_error( $assigned ) && ! empty( $assigned ) ) {
                continue;
            }

            $raw = get_post_meta( $tool_id, '_offriend_tool_format', true );
            if ( ! is_string( $raw ) || trim( $raw ) === '' ) {
                continue;
            }

            $term_ids = array();
            $matched_slugs = $this->match_tool_format_slugs( $raw );
            if ( ! empty( $matched_slugs ) ) {
                foreach ( $matched_slugs as $slug ) {
                    $term = get_term_by( 'slug', $slug, 'tool_format' );
                    if ( $term && ! is_wp_error( $term ) ) {
                        $term_ids[] = (int) $term->term_id;
                    }
                }
            } else {
                foreach ( array_map( 'trim', explode( ',', $raw ) ) as $name ) {
                    $term_id = $this->find_or_create_tool_format( $name );
                    if ( $term_id ) {
                        $term_ids[] = $term_id;
                    }
                }
            }

            if ( ! empty( $term_ids ) ) {
                wp_set_object_terms( $tool_id, $term_ids, 'tool_format', false );
            }
        }

        update_option( 'offriend_tool_formats_ready', '1' );
    }

    private function match_tool_format_slugs( $text ) {
        $text  = strtolower( (string) $text );
        $slugs = array();
        if ( strpos( $text, 'excel' ) !== false ) {
            $slugs[] = 'excel';
        }
        if ( strpos( $text, 'sheet' ) !== false ) {
            $slugs[] = 'sheets';
        }
        if ( strpos( $text, 'figma' ) !== false || strpos( $text, 'canva' ) !== false ) {
            $slugs[] = 'design';
        }
        if ( strpos( $text, 'pdf' ) !== false || preg_match( '/\bform\b/', $text ) || strpos( $text, 'เอกสาร' ) !== false ) {
            $slugs[] = 'doc';
        }
        if ( strpos( $text, 'web' ) !== false ) {
            $slugs[] = 'web';
        }
        return $slugs;
    }

    private function find_or_create_tool_format( $name ) {
        $name = trim( (string) $name );
        if ( $name === '' ) {
            return 0;
        }

        $found = get_term_by( 'name', $name, 'tool_format' );
        if ( $found && ! is_wp_error( $found ) ) {
            return (int) $found->term_id;
        }

        $slug = sanitize_title( $name );
        if ( $slug ) {
            $found = get_term_by( 'slug', $slug, 'tool_format' );
            if ( $found && ! is_wp_error( $found ) ) {
                return (int) $found->term_id;
            }
        }

        $created = wp_insert_term( $name, 'tool_format', $slug ? array( 'slug' => $slug ) : array() );
        if ( is_wp_error( $created ) ) {
            if ( isset( $created->error_data['term_exists'] ) ) {
                return (int) $created->error_data['term_exists'];
            }
            return 0;
        }

        update_term_meta( $created['term_id'], 'sort_order', 100 );
        return (int) $created['term_id'];
    }

    /**
     * Programs attached to a tool, with the old free-text field as fallback.
     */
    public function get_tool_format_payload( $post_id ) {
        $terms = get_the_terms( $post_id, 'tool_format' );
        $names = array();
        $slugs = array();

        if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
            usort( $terms, function( $a, $b ) {
                $order_a = get_term_meta( $a->term_id, 'sort_order', true );
                $order_b = get_term_meta( $b->term_id, 'sort_order', true );
                $order_a = ( $order_a === '' || $order_a === false ) ? 100 : (int) $order_a;
                $order_b = ( $order_b === '' || $order_b === false ) ? 100 : (int) $order_b;
                if ( $order_a === $order_b ) {
                    return strcasecmp( $a->name, $b->name );
                }
                return $order_a - $order_b;
            } );
            foreach ( $terms as $term ) {
                $names[] = $term->name;
                $slugs[] = $term->slug;
            }
        } else {
            $raw = get_post_meta( $post_id, '_offriend_tool_format', true );
            if ( is_string( $raw ) && trim( $raw ) !== '' ) {
                $matched = $this->match_tool_format_slugs( $raw );
                if ( ! empty( $matched ) ) {
                    foreach ( $matched as $slug ) {
                        $term = get_term_by( 'slug', $slug, 'tool_format' );
                        if ( $term && ! is_wp_error( $term ) ) {
                            $names[] = $term->name;
                            $slugs[] = $term->slug;
                        } else {
                            $slugs[] = $slug;
                        }
                    }
                    if ( empty( $names ) ) {
                        $names[] = trim( $raw );
                    }
                } else {
                    $names[] = trim( $raw );
                    $slugs[] = sanitize_title( $raw );
                }
            }
        }

        return array(
            'format'       => implode( ', ', $names ),
            'format_slug'  => isset( $slugs[0] ) ? $slugs[0] : '',
            'format_slugs' => array_values( $slugs ),
        );
    }

    /**
     * 3. Register Meta Boxes for Admin Post Edit Screens
     */
    public function register_meta_boxes() {
        // Projects Meta Box
        add_meta_box(
            'offriend_project_meta',
            'ข้อมูลโครงการและผลงาน (Offriend Details)',
            array( $this, 'render_project_meta_box' ),
            'projects',
            'normal',
            'high'
        );

        // Services Meta Box
        add_meta_box(
            'offriend_service_meta',
            'รายละเอียดบริการ (Offriend Details)',
            array( $this, 'render_service_meta_box' ),
            'services',
            'normal',
            'high'
        );

        // Team Meta Box
        add_meta_box(
            'offriend_team_meta',
            'ข้อมูลบุคลากรและประวัติ (Offriend Details)',
            array( $this, 'render_team_meta_box' ),
            'team',
            'normal',
            'high'
        );

        // Tools Meta Box
        add_meta_box(
            'offriend_tool_meta',
            'ข้อมูลเครื่องมือและการดาวน์โหลด (Offriend Details)',
            array( $this, 'render_tool_meta_box' ),
            'tools',
            'normal',
            'high'
        );
    }

    /**
     * Render Meta Box HTML: Projects
     */
    public function render_project_meta_box( $post ) {
        wp_nonce_field( 'offriend_meta_nonce_action', 'offriend_meta_nonce' );
        $client       = get_post_meta( $post->ID, '_offriend_project_client', true );
        $category_tag = get_post_meta( $post->ID, '_offriend_project_category_tag', true );
        $tech_stack   = get_post_meta( $post->ID, '_offriend_project_tech_stack', true );
        $year         = get_post_meta( $post->ID, '_offriend_project_year', true );
        $metrics      = get_post_meta( $post->ID, '_offriend_project_metrics', true );
        $gallery_raw  = get_post_meta( $post->ID, '_offriend_project_gallery', true );
        $gallery_items = $gallery_raw ? json_decode( $gallery_raw, true ) : array();
        if ( ! is_array( $gallery_items ) ) {
            $gallery_items = array();
        }
        ?>
        <table class="form-table" style="width: 100%;">
            <tr>
                <th style="width: 25%;"><label for="project_client">ลูกค้า / หน่วยงาน</label></th>
                <td><input type="text" id="project_client" name="project_client" value="<?php echo esc_attr( $client ); ?>" class="regular-text" placeholder="เช่น การไฟฟ้าฝ่ายผลิตแห่งประเทศไทย (กฟผ.)" /></td>
            </tr>
            <tr>
                <th><label for="project_category_tag">ป้ายหมวดหมู่</label></th>
                <td><input type="text" id="project_category_tag" name="project_category_tag" value="<?php echo esc_attr( $category_tag ); ?>" class="regular-text" placeholder="เช่น Web Application, In-House Training" /></td>
            </tr>
            <tr>
                <th><label for="project_year">ปีที่ดำเนินโครงการ</label></th>
                <td><input type="text" id="project_year" name="project_year" value="<?php echo esc_attr( $year ); ?>" class="regular-text" placeholder="เช่น 2026 หรือ 2569" /></td>
            </tr>
            <tr>
                <th><label for="project_tech_stack">Tech Stack (คั่นด้วยจุลภาค ,)</label></th>
                <td><input type="text" id="project_tech_stack" name="project_tech_stack" value="<?php echo esc_attr( $tech_stack ); ?>" class="large-text" placeholder="เช่น Next.js, TypeScript, Tailwind CSS, Headless WordPress" /></td>
            </tr>
            <tr>
                <th><label for="project_metrics">ผลลัพธ์เชิงตัวเลข / ไฮไลท์ (1 บรรทัดต่อ 1 ข้อ)</label></th>
                <td><textarea id="project_metrics" name="project_metrics" rows="3" class="large-text" placeholder="โหลดเร็วขึ้น 300%&#10;รองรับ 50,000 Concurrent Users"><?php echo esc_textarea( $metrics ); ?></textarea></td>
            </tr>
            <tr>
                <th><label>ภาพบรรยากาศโครงการ (Gallery)</label></th>
                <td>
                    <p class="description" style="margin-bottom: 12px; font-size: 13px;">
                        สามารถเพิ่ม ลบ แก้ไขคำอธิบาย และจัดเรียงภาพบรรยากาศโครงการได้ตามต้องการ โดยในหน้าเว็บจะแสดงผลตามสัดส่วนภาพจริง (Scale เดิม ไม่โดนตัดขอบ)
                    </p>
                    <input type="hidden" id="offriend_project_gallery_data" name="project_gallery" value="<?php echo esc_attr( wp_json_encode( $gallery_items ) ); ?>" />
                    
                    <div id="project_gallery_cards_wrap" style="display: flex; flex-wrap: wrap; gap: 14px; margin-bottom: 14px; min-height: 40px; align-items: flex-start;">
                        <!-- Cards dynamically rendered here -->
                    </div>

                    <button type="button" class="button button-secondary" id="btn_add_project_gallery" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 600; padding: 4px 12px;">
                        <span class="dashicons dashicons-format-gallery" style="margin-top: 2px;"></span> เพิ่ม / อัปโหลดภาพบรรยากาศ
                    </button>
                </td>
            </tr>
        </table>

        <style>
            .offriend-gallery-card {
                width: 170px;
                border: 1px solid #ccd0d4;
                border-radius: 8px;
                background: #fff;
                padding: 8px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.06);
                display: flex;
                flex-direction: column;
                gap: 6px;
                position: relative;
                transition: transform 0.15s ease, box-shadow 0.15s ease;
            }
            .offriend-gallery-card:hover {
                box-shadow: 0 4px 8px rgba(0,0,0,0.12);
            }
            .offriend-gallery-thumb-wrap {
                width: 100%;
                height: 110px;
                background: #f0f0f1;
                border-radius: 6px;
                overflow: hidden;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .offriend-gallery-thumb-wrap img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                display: block;
            }
            .offriend-gallery-caption-input {
                width: 100%;
                font-size: 11px;
                padding: 4px 6px;
                border: 1px solid #dcdcde;
                border-radius: 4px;
            }
            .offriend-gallery-actions {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 4px;
                margin-top: 2px;
            }
            .offriend-gallery-actions button {
                padding: 2px 6px;
                font-size: 11px;
                line-height: 1.4;
                cursor: pointer;
            }
        </style>

        <script>
        jQuery(document).ready(function($) {
            var galleryInput = $('#offriend_project_gallery_data');
            var galleryContainer = $('#project_gallery_cards_wrap');
            var rawData = galleryInput.val();
            var galleryData = [];

            try {
                if (rawData) {
                    galleryData = JSON.parse(rawData);
                }
            } catch (e) {
                galleryData = [];
            }

            if (!Array.isArray(galleryData)) {
                galleryData = [];
            }

            function syncGalleryData() {
                var currentItems = [];
                galleryContainer.find('.offriend-gallery-card').each(function() {
                    var card = $(this);
                    var url = card.data('url');
                    var caption = card.find('.offriend-gallery-caption-input').val();
                    if (url) {
                        currentItems.push({
                            url: url,
                            caption: caption || ''
                        });
                    }
                });
                galleryInput.val(JSON.stringify(currentItems));
            }

            function renderGalleryCards() {
                galleryContainer.empty();
                if (galleryData.length === 0) {
                    galleryContainer.html('<p style="color: #646970; font-size: 12px; margin: 6px 0;">ยังไม่มีภาพบรรยากาศ สามารถคลิกปุ่มด้านล่างเพื่อเพิ่มรูปภาพ</p>');
                    return;
                }

                galleryData.forEach(function(item, index) {
                    var safeUrl = $('<div>').text(item.url || '').html();
                    var safeCaption = $('<div>').text(item.caption || '').html();

                    var cardHtml = $(
                        '<div class="offriend-gallery-card" data-index="' + index + '" data-url="' + safeUrl + '">' +
                            '<div class="offriend-gallery-thumb-wrap">' +
                                '<img src="' + safeUrl + '" alt="preview" />' +
                            '</div>' +
                            '<input type="text" class="offriend-gallery-caption-input" placeholder="คำอธิบายภาพ..." value="' + safeCaption + '" />' +
                            '<div class="offriend-gallery-actions">' +
                                '<button type="button" class="button btn-move-left" title="เลื่อนไปซ้าย">◀</button>' +
                                '<button type="button" class="button btn-move-right" title="เลื่อนไปขวา">▶</button>' +
                                '<button type="button" class="button button-link-delete btn-delete-image" style="color: #b32d2e; font-size: 11px;">ลบ</button>' +
                            '</div>' +
                        '</div>'
                    );

                    galleryContainer.append(cardHtml);
                });
            }

            // Initial render
            renderGalleryCards();

            // Caption changed
            galleryContainer.on('input change', '.offriend-gallery-caption-input', function() {
                syncGalleryData();
            });

            // Delete item
            galleryContainer.on('click', '.btn-delete-image', function(e) {
                e.preventDefault();
                $(this).closest('.offriend-gallery-card').remove();
                syncGalleryData();
                if (galleryContainer.find('.offriend-gallery-card').length === 0) {
                    galleryData = [];
                    renderGalleryCards();
                }
            });

            // Move Left
            galleryContainer.on('click', '.btn-move-left', function(e) {
                e.preventDefault();
                var card = $(this).closest('.offriend-gallery-card');
                var prev = card.prev('.offriend-gallery-card');
                if (prev.length) {
                    card.insertBefore(prev);
                    syncGalleryData();
                }
            });

            // Move Right
            galleryContainer.on('click', '.btn-move-right', function(e) {
                e.preventDefault();
                var card = $(this).closest('.offriend-gallery-card');
                var next = card.next('.offriend-gallery-card');
                if (next.length) {
                    card.insertAfter(next);
                    syncGalleryData();
                }
            });

            // Add images via WP Media
            $('#btn_add_project_gallery').on('click', function(e) {
                e.preventDefault();

                var mediaFrame = wp.media({
                    title: 'เลือกภาพบรรยากาศโครงการ',
                    button: { text: 'เพิ่มภาพบรรยากาศ' },
                    multiple: true,
                    library: { type: 'image' }
                });

                mediaFrame.on('select', function() {
                    var selection = mediaFrame.state().get('selection');
                    // Remove empty message if any
                    if (galleryContainer.find('.offriend-gallery-card').length === 0) {
                        galleryContainer.empty();
                    }

                    selection.map(function(attachment) {
                        var item = attachment.toJSON();
                        var imageUrl = item.url;
                        var caption = item.caption || item.title || '';

                        var safeUrl = $('<div>').text(imageUrl).html();
                        var safeCaption = $('<div>').text(caption).html();

                        var cardHtml = $(
                            '<div class="offriend-gallery-card" data-url="' + safeUrl + '">' +
                                '<div class="offriend-gallery-thumb-wrap">' +
                                    '<img src="' + safeUrl + '" alt="preview" />' +
                                '</div>' +
                                '<input type="text" class="offriend-gallery-caption-input" placeholder="คำอธิบายภาพ..." value="' + safeCaption + '" />' +
                                '<div class="offriend-gallery-actions">' +
                                    '<button type="button" class="button btn-move-left" title="เลื่อนไปซ้าย">◀</button>' +
                                    '<button type="button" class="button btn-move-right" title="เลื่อนไปขวา">▶</button>' +
                                    '<button type="button" class="button button-link-delete btn-delete-image" style="color: #b32d2e; font-size: 11px;">ลบ</button>' +
                                '</div>' +
                            '</div>'
                        );

                        galleryContainer.append(cardHtml);
                    });

                    syncGalleryData();
                });

                mediaFrame.open();
            });
        });
        </script>
        <?php
    }

    /**
     * Render Meta Box HTML: Services
     */
    public function render_service_meta_box( $post ) {
        wp_nonce_field( 'offriend_meta_nonce_action', 'offriend_meta_nonce' );
        $title_th   = get_post_meta( $post->ID, '_offriend_service_title_th', true );
        $subtitle   = get_post_meta( $post->ID, '_offriend_service_subtitle', true );
        $tag        = get_post_meta( $post->ID, '_offriend_service_tag', true );
        $tech_stack = get_post_meta( $post->ID, '_offriend_service_tech_stack', true );
        $highlights = get_post_meta( $post->ID, '_offriend_service_highlights', true );
        ?>
        <table class="form-table" style="width: 100%;">
            <tr>
                <th style="width: 25%;"><label for="service_title_th">ชื่อบริการภาษาไทย</label></th>
                <td><input type="text" id="service_title_th" name="service_title_th" value="<?php echo esc_attr( $title_th ); ?>" class="large-text" placeholder="เช่น บริการพัฒนาเว็บแอปพลิเคชันและระบบพอร์ทัลระดับองค์กร" /></td>
            </tr>
            <tr>
                <th><label for="service_subtitle">คำบรรยายสั้น (Subtitle)</label></th>
                <td><input type="text" id="service_subtitle" name="service_subtitle" value="<?php echo esc_attr( $subtitle ); ?>" class="large-text" placeholder="ระบบเว็บแอปพลิเคชันสำหรับองค์กรที่รองรับการขยายตัว..." /></td>
            </tr>
            <tr>
                <th><label for="service_tag">Badge Tag</label></th>
                <td><input type="text" id="service_tag" name="service_tag" value="<?php echo esc_attr( $tag ); ?>" class="regular-text" placeholder="เช่น CUSTOM WEB ENGINEERING" /></td>
            </tr>
            <tr>
                <th><label for="service_tech_stack">Tech Stack (คั่นด้วยจุลภาค ,)</label></th>
                <td><input type="text" id="service_tech_stack" name="service_tech_stack" value="<?php echo esc_attr( $tech_stack ); ?>" class="large-text" placeholder="Next.js, React, Node.js, TypeScript, PostgreSQL" /></td>
            </tr>
            <tr>
                <th><label for="service_highlights">จุดเด่นบริการ (1 บรรทัดต่อ 1 ข้อ)</label></th>
                <td><textarea id="service_highlights" name="service_highlights" rows="4" class="large-text" placeholder="ออกแบบและพัฒนาตามโจทย์เฉพาะทาง (Bespoke Development)&#10;มาตรฐาน Clean Architecture&#10;ความปลอดภัยระดับ Enterprise Security"><?php echo esc_textarea( $highlights ); ?></textarea></td>
            </tr>
        </table>
        <?php
    }

    /**
     * Render Meta Box HTML: Team
     */
    public function render_team_meta_box( $post ) {
        wp_nonce_field( 'offriend_meta_nonce_action', 'offriend_meta_nonce' );
        $prefix         = get_post_meta( $post->ID, '_offriend_team_prefix', true );
        $name_en        = get_post_meta( $post->ID, '_offriend_team_name_en', true );
        $role_title     = get_post_meta( $post->ID, '_offriend_team_role_title', true );
        $education_line = get_post_meta( $post->ID, '_offriend_team_education_line', true );
        $resume_pdf     = get_post_meta( $post->ID, '_offriend_team_resume_pdf', true );
        $objective      = get_post_meta( $post->ID, '_offriend_team_objective', true );
        $experiences    = get_post_meta( $post->ID, '_offriend_team_experiences', true );
        ?>
        <table class="form-table" style="width: 100%;">
            <tr>
                <th style="width: 25%;"><label for="team_prefix">คำนำหน้า (ถ้ามี)</label></th>
                <td><input type="text" id="team_prefix" name="team_prefix" value="<?php echo esc_attr( $prefix ); ?>" class="regular-text" placeholder="เช่น อาจารย์, ดร." /></td>
            </tr>
            <tr>
                <th><label for="team_name_en">ชื่อ-นามสกุล ภาษาอังกฤษ</label></th>
                <td><input type="text" id="team_name_en" name="team_name_en" value="<?php echo esc_attr( $name_en ); ?>" class="regular-text" placeholder="เช่น Samit Koyom" /></td>
            </tr>
            <tr>
                <th><label for="team_role_title">ตำแหน่งงาน / ความเชี่ยวชาญ</label></th>
                <td><input type="text" id="team_role_title" name="team_role_title" value="<?php echo esc_attr( $role_title ); ?>" class="regular-text" placeholder="เช่น CEO ที่สถาบันไอทีจีเนียส, วิทยากรผู้สอน" /></td>
            </tr>
            <tr>
                <th><label for="team_education_line">วุฒิการศึกษา</label></th>
                <td><input type="text" id="team_education_line" name="team_education_line" value="<?php echo esc_attr( $education_line ); ?>" class="large-text" placeholder="เช่น ปริญญาโท คณะเทคโนโลยีและสารสนเทศ สถาบันเทคโนโลยีพระจอมเกล้าพระนครเหนือ" /></td>
            </tr>
            <tr>
                <th><label for="team_resume_pdf">ลิงก์ / ไฟล์ประวัติ (PDF)</label></th>
                <td>
                    <div style="display: flex; gap: 8px; align-items: center; max-width: 650px;">
                        <input type="text" id="team_resume_pdf" name="team_resume_pdf" value="<?php echo esc_attr( $resume_pdf ); ?>" class="large-text" style="flex: 1;" placeholder="URL ไฟล์ PDF หรืออัปโหลดไฟล์จากเครื่อง" />
                        <button type="button" class="button offriend-media-uploader-btn" data-target="#team_resume_pdf" style="white-space: nowrap;">📄 อัปโหลด / เลือกไฟล์ PDF</button>
                    </div>
                    <p class="description">สามารถวาง URL ลิงก์ไฟล์ PDF ภายนอก หรือคลิก <strong>อัปโหลด / เลือกไฟล์ PDF</strong> เพื่อเลือกหรืออัปโหลดไฟล์ประวัติจากเครื่องเข้า Media Library ได้โดยตรง</p>
                </td>
            </tr>
            <tr>
                <th><label for="team_objective">จุดมุ่งหมายในการทำงาน</label></th>
                <td><textarea id="team_objective" name="team_objective" rows="3" class="large-text" placeholder="ต้องการใช้ความรู้ความสามารถในสาขาที่เรียนและประสบการณ์ทำงาน..."><?php echo esc_textarea( $objective ); ?></textarea></td>
            </tr>
            <tr>
                <th><label for="team_experiences">ประสบการณ์ทำงาน (1 บรรทัดต่อ 1 ข้อ)</label></th>
                <td><textarea id="team_experiences" name="team_experiences" rows="5" class="large-text" placeholder="2550-2552 เป็นเว็บโปรแกรมเมอร์ (Web Programmer)...&#10;2553-2554 เป็นวิทยากรอบรม...&#10;ปัจจุบันเป็น CEO บริษัท..."><?php echo esc_textarea( $experiences ); ?></textarea></td>
            </tr>
        </table>

        <script>
        jQuery(document).ready(function($) {
            $('.offriend-media-uploader-btn').on('click', function(e) {
                e.preventDefault();
                var btn = $(this);
                var targetInput = $(btn.data('target'));

                var frame = wp.media({
                    title: 'เลือกหรืออัปโหลดไฟล์ประวัติ (PDF)',
                    button: { text: 'ใช้ไฟล์นี้' },
                    multiple: false
                });

                frame.on('select', function() {
                    var attachment = frame.state().get('selection').first().toJSON();
                    targetInput.val(attachment.url);
                });

                frame.open();
            });
        });
        </script>
        <?php
    }

    /**
     * Render Meta Box HTML: Tools
     */
    public function render_tool_meta_box( $post ) {
        wp_nonce_field( 'offriend_meta_nonce_action', 'offriend_meta_nonce' );
        $version       = get_post_meta( $post->ID, '_offriend_tool_version', true );
        $badge         = get_post_meta( $post->ID, '_offriend_tool_badge', true );
        $downloads     = get_post_meta( $post->ID, '_offriend_tool_downloads', true );
        $file_size     = get_post_meta( $post->ID, '_offriend_tool_file_size', true );
        $download_url  = get_post_meta( $post->ID, '_offriend_tool_download_url', true );
        $manual_url    = get_post_meta( $post->ID, '_offriend_tool_manual_url', true );
        $faqs          = get_post_meta( $post->ID, '_offriend_tool_faqs', true );
        ?>
        <table class="form-table" style="width: 100%;">
            <tr>
                <th style="width: 25%;">หมวดหมู่เครื่องมือ</th>
                <td>
                    <p style="margin: 0.3em 0;">เลือกหมวดหมู่จากกล่อง <strong>หมวดหมู่เครื่องมือ</strong> ในหน้านี้ เพิ่ม เปลี่ยนชื่อ เรียงลำดับ หรือลบหมวดหมู่ได้ที่เมนู <a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=tool_category&post_type=tools' ) ); ?>">เครื่องมือและเทมเพลต → หมวดหมู่</a></p>
                </td>
            </tr>
            <tr>
                <th>โปรแกรม</th>
                <td>
                    <p style="margin: 0.3em 0;">เลือกโปรแกรมจากกล่อง <strong>โปรแกรม</strong> ในหน้านี้ เครื่องมือหนึ่งชิ้นเลือกได้หลายโปรแกรม เพิ่ม เปลี่ยนชื่อ เรียงลำดับ หรือลบได้ที่เมนู <a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=tool_format&post_type=tools' ) ); ?>">เครื่องมือและเทมเพลต → โปรแกรม</a></p>
                </td>
            </tr>
            <tr>
                <th><label for="tool_version">เวอร์ชัน</label></th>
                <td><input type="text" id="tool_version" name="tool_version" value="<?php echo esc_attr( $version ); ?>" class="regular-text" placeholder="เช่น v2.0 Social, v1.0" /></td>
            </tr>
            <tr>
                <th><label for="tool_badge">ป้ายกำกับ (Badge)</label></th>
                <td><input type="text" id="tool_badge" name="tool_badge" value="<?php echo esc_attr( $badge ); ?>" class="regular-text" placeholder="เช่น ยอดนิยม 2026, แนะนำ" /></td>
            </tr>
            <tr>
                <th><label for="tool_downloads">ยอดดาวน์โหลด</label></th>
                <td><input type="text" id="tool_downloads" name="tool_downloads" value="<?php echo esc_attr( $downloads ); ?>" class="regular-text" placeholder="เช่น 4.2k+ ดาวน์โหลด" /></td>
            </tr>
            <tr>
                <th><label for="tool_file_size">ขนาดไฟล์</label></th>
                <td><input type="text" id="tool_file_size" name="tool_file_size" value="<?php echo esc_attr( $file_size ); ?>" class="regular-text" placeholder="เช่น 1.6 MB" /></td>
            </tr>
            <tr>
                <th><label for="tool_download_url">ไฟล์เครื่องมือสำหรับดาวน์โหลด</label></th>
                <td>
                    <div style="display: flex; gap: 8px; align-items: center; max-width: 650px;">
                        <input type="text" id="tool_download_url" name="tool_download_url" value="<?php echo esc_attr( $download_url ); ?>" class="large-text" style="flex: 1;" placeholder="URL ไฟล์ดาวน์โหลด หรือลิงก์ Google Drive / Sheets" />
                        <button type="button" class="button offriend-media-uploader-btn" data-target="#tool_download_url" data-size-target="#tool_file_size" style="white-space: nowrap;">📁 อัปโหลด / เลือกไฟล์</button>
                    </div>
                    <p class="description">สามารถวาง URL ลิงก์ภายนอก (Google Drive, Sheets) หรือคลิก <strong>อัปโหลด / เลือกไฟล์</strong> เพื่ออัปโหลดไฟล์จากเครื่อง (Excel, Zip, Figma ฯลฯ) เข้า Media Library ของเว็บได้โดยตรง</p>
                </td>
            </tr>
            <tr>
                <th><label for="tool_manual_url">คู่มือการใช้งาน (Manual / User Guide)</label></th>
                <td>
                    <div style="display: flex; gap: 8px; align-items: center; max-width: 650px;">
                        <input type="text" id="tool_manual_url" name="tool_manual_url" value="<?php echo esc_attr( $manual_url ); ?>" class="large-text" style="flex: 1;" placeholder="URL ไฟล์คู่มือ PDF หรือลิงก์เอกสารคู่มือออนไลน์" />
                        <button type="button" class="button offriend-media-uploader-btn" data-target="#tool_manual_url" style="white-space: nowrap;">📄 อัปโหลด / เลือกไฟล์คู่มือ PDF</button>
                    </div>
                    <p class="description">ลิงก์หรือไฟล์ PDF คู่มือการใช้งานสำหรับให้ผู้ใช้ดาวน์โหลดหรือเปิดอ่าน</p>
                </td>
            </tr>
            <tr>
                <th><label for="tool_faqs">คำถามที่พบบ่อย (FAQs)</label></th>
                <td>
                    <textarea id="tool_faqs" name="tool_faqs" rows="8" class="large-text" placeholder="Q: เทมเพลตนี้รองรับ Excel เวอร์ชันใดบ้าง&#10;A: รองรับทั้ง Excel 2016-365 และ Google Sheets 100%&#10;&#10;Q: สามารถนำไปปรับแต่งสูตรหรือใส่โลโก้องค์กรได้ไหม&#10;A: ปรับแต่งได้อิสระ ไม่มีล็อกรหัสผ่าน"><?php echo esc_textarea( $faqs ); ?></textarea>
                    <p class="description">ระบุคำถาม-คำตอบ โดยขึ้นต้นด้วย <strong>Q:</strong> (คำถาม) และ <strong>A:</strong> (คำตอบ) เว้น 1 บรรทัดระหว่างแต่ละข้อ (หรือใช้รูปแบบ <code>คำถาม | คำตอบ</code>)</p>
                </td>
            </tr>
        </table>

        <script>
        jQuery(document).ready(function($) {
            $('.offriend-media-uploader-btn').on('click', function(e) {
                e.preventDefault();
                var btn = $(this);
                var targetInput = $(btn.data('target'));
                var sizeTarget = btn.data('size-target') ? $(btn.data('size-target')) : null;

                var frame = wp.media({
                    title: 'เลือกหรืออัปโหลดไฟล์สำหรับเครื่องมือ',
                    button: { text: 'ใช้ไฟล์นี้' },
                    multiple: false
                });

                frame.on('select', function() {
                    var attachment = frame.state().get('selection').first().toJSON();
                    targetInput.val(attachment.url);
                    if (sizeTarget && (!sizeTarget.val() || sizeTarget.val() === '')) {
                        if (attachment.filesizeHumanReadable) {
                            sizeTarget.val(attachment.filesizeHumanReadable);
                        } else if (attachment.filesizeInBytes) {
                            var sizeMB = (attachment.filesizeInBytes / (1024 * 1024)).toFixed(1) + ' MB';
                            sizeTarget.val(sizeMB);
                        }
                    }
                });

                frame.open();
            });
        });
        </script>
        <?php
    }

    /**
     * Save Meta Box Data
     */
    public function save_meta_box_data( $post_id ) {
        if ( ! isset( $_POST['offriend_meta_nonce'] ) || ! wp_verify_nonce( $_POST['offriend_meta_nonce'], 'offriend_meta_nonce_action' ) ) {
            return;
        }
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // Projects
        if ( 'projects' === get_post_type( $post_id ) ) {
            if ( isset( $_POST['project_client'] ) ) update_post_meta( $post_id, '_offriend_project_client', sanitize_text_field( $_POST['project_client'] ) );
            if ( isset( $_POST['project_category_tag'] ) ) update_post_meta( $post_id, '_offriend_project_category_tag', sanitize_text_field( $_POST['project_category_tag'] ) );
            if ( isset( $_POST['project_year'] ) ) update_post_meta( $post_id, '_offriend_project_year', sanitize_text_field( $_POST['project_year'] ) );
            if ( isset( $_POST['project_tech_stack'] ) ) update_post_meta( $post_id, '_offriend_project_tech_stack', sanitize_text_field( $_POST['project_tech_stack'] ) );
            if ( isset( $_POST['project_metrics'] ) ) update_post_meta( $post_id, '_offriend_project_metrics', sanitize_textarea_field( $_POST['project_metrics'] ) );
            if ( isset( $_POST['project_gallery'] ) ) {
                $gallery_raw = wp_unslash( $_POST['project_gallery'] );
                $gallery_data = json_decode( $gallery_raw, true );
                if ( is_array( $gallery_data ) ) {
                    $sanitized_gallery = array();
                    foreach ( $gallery_data as $item ) {
                        if ( ! empty( $item['url'] ) ) {
                            $sanitized_gallery[] = array(
                                'url'     => esc_url_raw( $item['url'] ),
                                'caption' => isset( $item['caption'] ) ? sanitize_text_field( $item['caption'] ) : '',
                            );
                        }
                    }
                    update_post_meta( $post_id, '_offriend_project_gallery', wp_json_encode( $sanitized_gallery ) );
                } else {
                    delete_post_meta( $post_id, '_offriend_project_gallery' );
                }
            }
        }

        // Services
        if ( 'services' === get_post_type( $post_id ) ) {
            if ( isset( $_POST['service_title_th'] ) ) update_post_meta( $post_id, '_offriend_service_title_th', sanitize_text_field( $_POST['service_title_th'] ) );
            if ( isset( $_POST['service_subtitle'] ) ) update_post_meta( $post_id, '_offriend_service_subtitle', sanitize_text_field( $_POST['service_subtitle'] ) );
            if ( isset( $_POST['service_tag'] ) ) update_post_meta( $post_id, '_offriend_service_tag', sanitize_text_field( $_POST['service_tag'] ) );
            if ( isset( $_POST['service_tech_stack'] ) ) update_post_meta( $post_id, '_offriend_service_tech_stack', sanitize_text_field( $_POST['service_tech_stack'] ) );
            if ( isset( $_POST['service_highlights'] ) ) update_post_meta( $post_id, '_offriend_service_highlights', sanitize_textarea_field( $_POST['service_highlights'] ) );
        }

        // Team
        if ( 'team' === get_post_type( $post_id ) ) {
            if ( isset( $_POST['team_prefix'] ) ) update_post_meta( $post_id, '_offriend_team_prefix', sanitize_text_field( $_POST['team_prefix'] ) );
            if ( isset( $_POST['team_name_en'] ) ) update_post_meta( $post_id, '_offriend_team_name_en', sanitize_text_field( $_POST['team_name_en'] ) );
            if ( isset( $_POST['team_role_title'] ) ) update_post_meta( $post_id, '_offriend_team_role_title', sanitize_text_field( $_POST['team_role_title'] ) );
            if ( isset( $_POST['team_education_line'] ) ) update_post_meta( $post_id, '_offriend_team_education_line', sanitize_text_field( $_POST['team_education_line'] ) );
            if ( isset( $_POST['team_resume_pdf'] ) ) update_post_meta( $post_id, '_offriend_team_resume_pdf', esc_url_raw( $_POST['team_resume_pdf'] ) );
            if ( isset( $_POST['team_objective'] ) ) update_post_meta( $post_id, '_offriend_team_objective', sanitize_textarea_field( $_POST['team_objective'] ) );
            if ( isset( $_POST['team_experiences'] ) ) update_post_meta( $post_id, '_offriend_team_experiences', sanitize_textarea_field( $_POST['team_experiences'] ) );
        }

        // Tools
        if ( 'tools' === get_post_type( $post_id ) ) {
            $category_payload = $this->get_tool_category_payload( $post_id );
            update_post_meta( $post_id, '_offriend_tool_category_name', $category_payload['category_name'] );
            $format_payload = $this->get_tool_format_payload( $post_id );
            update_post_meta( $post_id, '_offriend_tool_format', $format_payload['format'] );
            if ( isset( $_POST['tool_version'] ) ) update_post_meta( $post_id, '_offriend_tool_version', sanitize_text_field( $_POST['tool_version'] ) );
            if ( isset( $_POST['tool_badge'] ) ) update_post_meta( $post_id, '_offriend_tool_badge', sanitize_text_field( $_POST['tool_badge'] ) );
            if ( isset( $_POST['tool_downloads'] ) ) update_post_meta( $post_id, '_offriend_tool_downloads', sanitize_text_field( $_POST['tool_downloads'] ) );
            if ( isset( $_POST['tool_file_size'] ) ) update_post_meta( $post_id, '_offriend_tool_file_size', sanitize_text_field( $_POST['tool_file_size'] ) );
            if ( isset( $_POST['tool_download_url'] ) ) update_post_meta( $post_id, '_offriend_tool_download_url', esc_url_raw( $_POST['tool_download_url'] ) );
            if ( isset( $_POST['tool_manual_url'] ) ) update_post_meta( $post_id, '_offriend_tool_manual_url', esc_url_raw( $_POST['tool_manual_url'] ) );
            if ( isset( $_POST['tool_faqs'] ) ) update_post_meta( $post_id, '_offriend_tool_faqs', sanitize_textarea_field( $_POST['tool_faqs'] ) );
        }
    }

    /**
     * Helper to parse FAQ text into structured array
     */
    public function parse_faqs_text( $text ) {
        if ( empty( trim( $text ) ) ) {
            return array();
        }
        $faqs = array();
        $text = str_replace( "\r\n", "\n", trim( $text ) );

        if ( preg_match( '/\b[Qq]\s*[:\.]/u', $text ) || preg_match( '/(?:^|\n)\s*(?:ข้อ|คำถาม)\s*[:\.]/u', $text ) ) {
            $chunks = preg_split( '/(?=(?:^|\n)\s*(?:[Qq]|ข้อ|คำถาม)\s*[:\.])/u', $text, -1, PREG_SPLIT_NO_EMPTY );
            foreach ( $chunks as $chunk ) {
                $chunk = trim( $chunk );
                if ( empty( $chunk ) ) continue;
                if ( preg_match( '/^(?:[Qq]|ข้อ|คำถาม)\s*[:\.]\s*(.+?)(?:\n\s*(?:[Aa]|ตอบ|คำตอบ)\s*[:\.]\s*(.+))$/us', $chunk, $matches ) ) {
                    $faqs[] = array(
                        'question' => trim( $matches[1] ),
                        'answer'   => trim( $matches[2] ),
                    );
                } else {
                    $lines = explode( "\n", $chunk );
                    $q = preg_replace( '/^(?:[Qq]|ข้อ|คำถาม)\s*[:\.]\s*/u', '', trim( $lines[0] ) );
                    $a = '';
                    for ( $i = 1; $i < count( $lines ); $i++ ) {
                        $line = trim( $lines[$i] );
                        $line = preg_replace( '/^(?:[Aa]|ตอบ|คำตอบ)\s*[:\.]\s*/u', '', $line );
                        $a .= ( $a ? "\n" : '' ) . $line;
                    }
                    if ( $q && $a ) {
                        $faqs[] = array(
                            'question' => $q,
                            'answer'   => trim( $a ),
                        );
                    }
                }
            }
        } else {
            $lines = explode( "\n", $text );
            foreach ( $lines as $line ) {
                $line = trim( $line );
                if ( empty( $line ) ) continue;
                if ( strpos( $line, '|' ) !== false ) {
                    $parts = explode( '|', $line, 2 );
                    $faqs[] = array(
                        'question' => trim( $parts[0] ),
                        'answer'   => trim( $parts[1] ),
                    );
                }
            }
        }
        return $faqs;
    }

    /**
     * 4. Expose Fields via REST API (Featured Image, Meta, Taxonomies)
     */
    public function register_rest_api_fields_and_routes() {
        // Register featured_image_url for all CPTs
        $cpts = array( 'post', 'projects', 'services', 'team', 'tools' );
        foreach ( $cpts as $cpt ) {
            register_rest_field( $cpt, 'featured_image_url', array(
                'get_callback' => function( $post_arr ) {
                    $media_id = get_post_thumbnail_id( $post_arr['id'] );
                    if ( $media_id ) {
                        $url = wp_get_attachment_image_url( $media_id, 'full' );
                        return $url ? $url : null;
                    }
                    return null;
                },
                'schema' => null,
            ) );
        }

        // Projects Meta Field
        register_rest_field( 'projects', 'project_meta', array(
            'get_callback' => function( $post_arr ) {
                $id = $post_arr['id'];
                $tech_str = get_post_meta( $id, '_offriend_project_tech_stack', true );
                $metrics_str = get_post_meta( $id, '_offriend_project_metrics', true );
                $gallery_raw = get_post_meta( $id, '_offriend_project_gallery', true );
                $gallery = array();
                $has_custom_gallery = false;
                if ( $gallery_raw !== '' && $gallery_raw !== false ) {
                    $has_custom_gallery = true;
                    $decoded = json_decode( $gallery_raw, true );
                    if ( is_array( $decoded ) ) {
                        $gallery = $decoded;
                    }
                }
                return array(
                    'client'             => get_post_meta( $id, '_offriend_project_client', true ) ?: '',
                    'category_tag'       => get_post_meta( $id, '_offriend_project_category_tag', true ) ?: '',
                    'year'               => get_post_meta( $id, '_offriend_project_year', true ) ?: '',
                    'tech_stack'         => $tech_str ? array_map( 'trim', explode( ',', $tech_str ) ) : array(),
                    'metrics'            => $metrics_str ? array_filter( array_map( 'trim', explode( "\n", $metrics_str ) ) ) : array(),
                    'gallery'            => $gallery,
                    'has_custom_gallery' => $has_custom_gallery,
                );
            },
        ) );

        // Services Meta Field
        register_rest_field( 'services', 'service_meta', array(
            'get_callback' => function( $post_arr ) {
                $id = $post_arr['id'];
                $tech_str = get_post_meta( $id, '_offriend_service_tech_stack', true );
                $highlights_str = get_post_meta( $id, '_offriend_service_highlights', true );
                return array(
                    'title_th'   => get_post_meta( $id, '_offriend_service_title_th', true ) ?: '',
                    'subtitle'   => get_post_meta( $id, '_offriend_service_subtitle', true ) ?: '',
                    'tag'        => get_post_meta( $id, '_offriend_service_tag', true ) ?: '',
                    'tech_stack' => $tech_str ? array_map( 'trim', explode( ',', $tech_str ) ) : array(),
                    'highlights' => $highlights_str ? array_filter( array_map( 'trim', explode( "\n", $highlights_str ) ) ) : array(),
                );
            },
        ) );

        // Team Meta Field
        register_rest_field( 'team', 'team_meta', array(
            'get_callback' => function( $post_arr ) {
                $id = $post_arr['id'];
                $exp_str = get_post_meta( $id, '_offriend_team_experiences', true );
                return array(
                    'prefix'         => get_post_meta( $id, '_offriend_team_prefix', true ) ?: '',
                    'name_en'        => get_post_meta( $id, '_offriend_team_name_en', true ) ?: '',
                    'role_title'     => get_post_meta( $id, '_offriend_team_role_title', true ) ?: '',
                    'education_line' => get_post_meta( $id, '_offriend_team_education_line', true ) ?: '',
                    'resume_pdf'     => get_post_meta( $id, '_offriend_team_resume_pdf', true ) ?: '',
                    'objective'      => get_post_meta( $id, '_offriend_team_objective', true ) ?: '',
                    'experiences'    => $exp_str ? array_filter( array_map( 'trim', explode( "\n", $exp_str ) ) ) : array(),
                );
            },
        ) );

        // Tools Meta Field
        register_rest_field( 'tools', 'tool_meta', array(
            'get_callback' => function( $post_arr ) {
                $id = $post_arr['id'];
                $category = $this->get_tool_category_payload( $id );
                $format   = $this->get_tool_format_payload( $id );
                $dl  = get_post_meta( $id, '_offriend_tool_download_url', true ) ?: '';
                $manual = get_post_meta( $id, '_offriend_tool_manual_url', true ) ?: '';
                $faqs_raw = get_post_meta( $id, '_offriend_tool_faqs', true ) ?: '';
                return array(
                    'category_name' => $category['category_name'],
                    'category'      => $category['category'],
                    'category_slug' => $category['category_slug'],
                    'category_slugs'=> $category['category_slugs'],
                    'format'        => $format['format'],
                    'format_slug'   => $format['format_slug'],
                    'format_slugs'  => $format['format_slugs'],
                    'version'       => get_post_meta( $id, '_offriend_tool_version', true ) ?: '',
                    'badge'         => get_post_meta( $id, '_offriend_tool_badge', true ) ?: '',
                    'downloads'     => get_post_meta( $id, '_offriend_tool_downloads', true ) ?: '',
                    'file_size'     => get_post_meta( $id, '_offriend_tool_file_size', true ) ?: '',
                    'download_url'  => $dl,
                    'download_link' => $dl,
                    'manual_url'    => $manual,
                    'manual_link'   => $manual,
                    'guide_url'     => $manual,
                    'faqs_raw'      => $faqs_raw,
                    'faqs'          => $this->parse_faqs_text( $faqs_raw ),
                );
            },
        ) );

        register_rest_field( 'tool_category', 'offriend', array(
            'get_callback' => function( $term ) {
                $term_id = isset( $term['id'] ) ? (int) $term['id'] : 0;
                $order   = get_term_meta( $term_id, 'sort_order', true );
                return array(
                    'icon'       => get_term_meta( $term_id, 'icon', true ) ?: 'folder',
                    'color'      => get_term_meta( $term_id, 'color', true ) ?: 'text-slate-500',
                    'sort_order' => ( $order === '' || $order === false ) ? 100 : (int) $order,
                );
            },
        ) );

        register_rest_field( 'tool_format', 'offriend', array(
            'get_callback' => function( $term ) {
                $term_id = isset( $term['id'] ) ? (int) $term['id'] : 0;
                $order   = get_term_meta( $term_id, 'sort_order', true );
                return array(
                    'sort_order' => ( $order === '' || $order === false ) ? 100 : (int) $order,
                );
            },
        ) );

        // Register Global Settings Endpoint: GET & POST /wp-json/offriend/v1/settings
        register_rest_route( 'offriend/v1', '/settings', array(
            array(
                'methods'             => 'GET',
                'callback'            => array( $this, 'get_site_settings_rest' ),
                'permission_callback' => '__return_true',
            ),
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'update_site_settings_rest' ),
                'permission_callback' => function() {
                    return current_user_can( 'manage_options' );
                },
            ),
        ) );
    }

    /**
     * Default Site Settings
     */
    public function get_default_settings() {
        return array(
            // Header & Contact
            'phone'              => '088-807-9770',
            'working_hours'      => 'จ-ศ 9:00-17:00 น.',
            'line_id'            => '@offriend',
            'line_url'           => 'https://line.me',
            'brand_slogan'       => 'เพื่อนคู่คิดคนออฟฟิศ',
            
            // Footer & Company
            'company_name_th'    => 'บริษัท ออฟเฟรนด์ จำกัด',
            'company_name_en'    => 'OFFRIEND COMPANY LIMITED',
            'company_desc'       => 'ผู้นำด้านการพัฒนาซอฟต์แวร์ระดับองค์กร สถาปัตยกรรมคลาวด์ และ Headless CMS มุ่งมั่นส่งมอบโซลูชันวิศวกรรมเทคโนโลยีที่มั่นคง ปลอดภัย และสร้างคุณค่าทางธุรกิจอย่างยั่งยืน',
            'headquarters_address' => 'อาคารไอทีจีเนียส เซ็นเตอร์ เลขที่ 123/45 ถนนสุขุมวิท แขวงคลองเตย เขตคลองเตย กรุงเทพมหานคร 10110',
            'contact_email'      => 'contact@offriend.co.th',
            'contact_phone'      => '088-807-9770',
            'copyright_text'     => 'สงวนลิขสิทธิ์ตามกฎหมาย',

            // Social links
            'facebook_url'       => 'https://facebook.com',
            'youtube_url'        => 'https://youtube.com',
            'linkedin_url'       => 'https://linkedin.com',
            
            // Hero Banners (Slide Items)
            'banners'            => array(
                array(
                    'kicker'      => 'HEADLESS ARCHITECTURE & NEXT.JS',
                    'title'       => 'ยกระดับระบบเว็บองค์กรสู่ความเร็วระดับเสี้ยววินาที',
                    'subtitle'    => 'ผสานพลัง Headless WordPress เข้ากับ Astro และ Next.js สถาปัตยกรรมสมัยใหม่ที่เสถียร ปลอดภัย และรองรับผู้ใช้งานหลักล้าน',
                    'button_text' => 'ดูผลงานโครงการ',
                    'button_url'  => '/projects',
                    'image_url'   => '',
                ),
            ),
        );
    }

    public function get_site_settings_rest() {
        $saved = get_option( self::SETTINGS_OPTION_KEY, array() );
        $defaults = $this->get_default_settings();
        $merged = wp_parse_args( $saved, $defaults );
        return rest_ensure_response( $merged );
    }

    public function update_site_settings_rest( $request ) {
        $params = $request->get_json_params();
        $current = get_option( self::SETTINGS_OPTION_KEY, $this->get_default_settings() );
        $updated = wp_parse_args( $params, $current );
        update_option( self::SETTINGS_OPTION_KEY, $updated );
        return rest_ensure_response( array(
            'success' => true,
            'message' => 'บันทึกการตั้งค่าเว็บไซต์สำเร็จ',
            'data'    => $updated,
        ) );
    }

    /**
     * 5. Register Admin Menu: "ตั้งค่าเว็บไซต์ Offriend"
     */
    public function register_admin_menu() {
        add_menu_page(
            'ตั้งค่าเว็บไซต์ Offriend',
            'ตั้งค่าเว็บไซต์',
            'manage_options',
            'offriend-settings',
            array( $this, 'render_admin_settings_page' ),
            'dashicons-admin-generic',
            2
        );
    }

    /**
     * Handle Admin Form Submissions
     */
    public function handle_admin_actions() {
        if ( isset( $_POST['offriend_save_settings'] ) && check_admin_referer( 'offriend_settings_nonce' ) ) {
            $current = get_option( self::SETTINGS_OPTION_KEY, $this->get_default_settings() );
            
            $fields = array(
                'phone', 'working_hours', 'line_id', 'line_url', 'brand_slogan',
                'company_name_th', 'company_name_en', 'company_desc', 'headquarters_address',
                'contact_email', 'contact_phone', 'copyright_text',
                'facebook_url', 'youtube_url', 'linkedin_url'
            );

            foreach ( $fields as $field ) {
                if ( isset( $_POST[$field] ) ) {
                    $current[$field] = sanitize_text_field( wp_unslash( $_POST[$field] ) );
                }
            }

            // Save banners / slides
            if ( isset( $_POST['banners'] ) && is_array( $_POST['banners'] ) ) {
                $clean_banners = array();
                foreach ( $_POST['banners'] as $banner ) {
                    if ( ! is_array( $banner ) ) continue;
                    $clean_banners[] = array(
                        'image_url'   => isset( $banner['image_url'] )   ? esc_url_raw( $banner['image_url'] )          : '',
                        'kicker'      => isset( $banner['kicker'] )      ? sanitize_text_field( $banner['kicker'] )      : '',
                        'title'       => isset( $banner['title'] )       ? sanitize_text_field( $banner['title'] )       : '',
                        'subtitle'    => isset( $banner['subtitle'] )    ? sanitize_textarea_field( $banner['subtitle'] ) : '',
                        'button_text' => isset( $banner['button_text'] ) ? sanitize_text_field( $banner['button_text'] ) : '',
                        'button_url'  => isset( $banner['button_url'] )  ? sanitize_text_field( $banner['button_url'] )  : '',
                    );
                }
                $current['banners'] = $clean_banners;
            } else {
                $current['banners'] = array();
            }

            update_option( self::SETTINGS_OPTION_KEY, $current );
            wp_redirect( add_query_arg( array( 'page' => 'offriend-settings', 'updated' => 'true' ), admin_url( 'admin.php' ) ) );
            exit;
        }

        // Handle Seed Data Action
        if ( isset( $_POST['offriend_seed_initial_data'] ) && check_admin_referer( 'offriend_seed_nonce' ) ) {
            $this->seed_initial_content();
            wp_redirect( add_query_arg( array( 'page' => 'offriend-settings', 'seeded' => 'true' ), admin_url( 'admin.php' ) ) );
            exit;
        }
    }

    /**
     * Render Admin Settings Page HTML
     */
    public function render_admin_settings_page() {
        $settings = wp_parse_args( get_option( self::SETTINGS_OPTION_KEY, array() ), $this->get_default_settings() );
        ?>
        <div class="wrap" style="max-width: 1000px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
            <h1 style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px;">
                <span class="dashicons dashicons-admin-generic" style="font-size: 32px; width: 32px; height: 32px; color: #38bdf8;"></span>
                <span>ตั้งค่าเว็บไซต์ Offriend (Headless CMS Settings)</span>
            </h1>

            <?php if ( isset( $_GET['updated'] ) ) : ?>
                <div class="notice notice-success is-dismissible"><p><strong>บันทึกการตั้งค่าเว็บไซต์เรียบร้อยแล้ว</strong> หน้าเว็บ Astro จะดึงข้อมูลล่าสุดไปแสดงผลอัตโนมัติ</p></div>
            <?php endif; ?>

            <?php if ( isset( $_GET['seeded'] ) ) : ?>
                <div class="notice notice-success is-dismissible"><p><strong>นำเข้าข้อมูลเริ่มต้น (ผลงาน, บริการ, บุคลากร, เครื่องมือ) เข้าสู่ WordPress สำเร็จ!</strong> คุณสามารถไปดูและแก้ไขที่เมนูด้านซ้ายได้ทันที</p></div>
            <?php endif; ?>

            <div style="background: #fff; padding: 25px 30px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #e2e8f0; margin-bottom: 30px;">
                <form method="post" action="">
                    <?php wp_nonce_field( 'offriend_settings_nonce' ); ?>

                    <!-- Section 1: ส่วนหัว & ช่องทางติดต่อด่วน -->
                    <h2 style="font-size: 16px; border-bottom: 2px solid #38bdf8; padding-bottom: 8px; color: #162d59; margin-top: 0;">
                        1. แถบประกาศด้านบน & ข้อมูลติดต่อด่วน (Top Bar & Contact)
                    </h2>
                    <table class="form-table" style="margin-bottom: 25px;">
                        <tr>
                            <th scope="row" style="width: 250px;"><label for="phone">เบอร์โทรศัพท์ / มือถือ</label></th>
                            <td><input type="text" id="phone" name="phone" value="<?php echo esc_attr( $settings['phone'] ); ?>" class="regular-text" /></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="working_hours">เวลาทำการ</label></th>
                            <td><input type="text" id="working_hours" name="working_hours" value="<?php echo esc_attr( $settings['working_hours'] ); ?>" class="regular-text" /></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="line_id">LINE Official ID</label></th>
                            <td><input type="text" id="line_id" name="line_id" value="<?php echo esc_attr( $settings['line_id'] ); ?>" class="regular-text" placeholder="@offriend" /></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="line_url">ลิงก์เปิด LINE (URL)</label></th>
                            <td><input type="text" id="line_url" name="line_url" value="<?php echo esc_attr( $settings['line_url'] ); ?>" class="large-text" placeholder="https://line.me/..." /></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="brand_slogan">สโลแกนใต้โลโก้</label></th>
                            <td><input type="text" id="brand_slogan" name="brand_slogan" value="<?php echo esc_attr( $settings['brand_slogan'] ); ?>" class="regular-text" /></td>
                        </tr>
                    </table>

                    <!-- Section 2: ข้อมูลส่วนท้ายเว็บไซต์ (Footer) -->
                    <h2 style="font-size: 16px; border-bottom: 2px solid #38bdf8; padding-bottom: 8px; color: #162d59;">
                        2. ข้อมูลส่วนท้ายเว็บไซต์ (Footer & Company Details)
                    </h2>
                    <table class="form-table" style="margin-bottom: 25px;">
                        <tr>
                            <th scope="row" style="width: 250px;"><label for="company_name_th">ชื่อบริษัท (ภาษาไทย)</label></th>
                            <td><input type="text" id="company_name_th" name="company_name_th" value="<?php echo esc_attr( $settings['company_name_th'] ); ?>" class="large-text" /></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="company_name_en">ชื่อบริษัท (ภาษาอังกฤษ)</label></th>
                            <td><input type="text" id="company_name_en" name="company_name_en" value="<?php echo esc_attr( $settings['company_name_en'] ); ?>" class="large-text" /></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="company_desc">คำบรรยายบริษัทใต้โลโก้</label></th>
                            <td><textarea id="company_desc" name="company_desc" rows="3" class="large-text"><?php echo esc_textarea( $settings['company_desc'] ); ?></textarea></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="headquarters_address">ที่อยู่สำนักงานใหญ่</label></th>
                            <td><textarea id="headquarters_address" name="headquarters_address" rows="2" class="large-text"><?php echo esc_textarea( $settings['headquarters_address'] ); ?></textarea></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="contact_email">อีเมลติดต่อบริษัท</label></th>
                            <td><input type="email" id="contact_email" name="contact_email" value="<?php echo esc_attr( $settings['contact_email'] ); ?>" class="regular-text" /></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="copyright_text">ข้อความสงวนลิขสิทธิ์</label></th>
                            <td><input type="text" id="copyright_text" name="copyright_text" value="<?php echo esc_attr( $settings['copyright_text'] ); ?>" class="large-text" /></td>
                        </tr>
                    </table>

                    <!-- Section 3: โซเชียลมีเดีย -->
                    <h2 style="font-size: 16px; border-bottom: 2px solid #38bdf8; padding-bottom: 8px; color: #162d59;">
                        3. ลิงก์โซเชียลมีเดีย (Social Profiles)
                    </h2>
                    <table class="form-table" style="margin-bottom: 25px;">
                        <tr>
                            <th scope="row" style="width: 250px;"><label for="facebook_url">Facebook URL</label></th>
                            <td><input type="url" id="facebook_url" name="facebook_url" value="<?php echo esc_attr( $settings['facebook_url'] ); ?>" class="large-text" /></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="youtube_url">YouTube Channel URL</label></th>
                            <td><input type="url" id="youtube_url" name="youtube_url" value="<?php echo esc_attr( $settings['youtube_url'] ); ?>" class="large-text" /></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="linkedin_url">LinkedIn URL</label></th>
                            <td><input type="url" id="linkedin_url" name="linkedin_url" value="<?php echo esc_attr( $settings['linkedin_url'] ); ?>" class="large-text" /></td>
                        </tr>
                    </table>

                    <!-- Section 4: Hero Slides / Banner Carousel -->
                    <h2 style="font-size: 16px; border-bottom: 2px solid #38bdf8; padding-bottom: 8px; color: #162d59;">
                        4. ภาพสไลด์หน้าแรก / Hero Banner Carousel
                    </h2>
                    <p style="color: #64748b; font-size: 13px; margin-bottom: 15px;">
                        กำหนดภาพสไลด์บนหน้าแรก ขนาดแนะนำ <strong>1920 × 800 px</strong> — รองรับได้สูงสุด 5 สไลด์
                    </p>

                    <div id="offriend-banners-wrapper" style="display: flex; flex-direction: column; gap: 16px; margin-bottom: 20px;">
                        <?php
                        $banners = isset( $settings['banners'] ) && is_array( $settings['banners'] ) ? $settings['banners'] : array();
                        if ( empty( $banners ) ) {
                            $banners = array( array( 'kicker' => '', 'title' => '', 'subtitle' => '', 'button_text' => '', 'button_url' => '', 'image_url' => '' ) );
                        }
                        foreach ( $banners as $bi => $banner ) :
                            $image_url   = isset( $banner['image_url'] )   ? esc_attr( $banner['image_url'] )   : '';
                            $kicker      = isset( $banner['kicker'] )      ? esc_attr( $banner['kicker'] )      : '';
                            $title       = isset( $banner['title'] )       ? esc_attr( $banner['title'] )       : '';
                            $subtitle    = isset( $banner['subtitle'] )    ? esc_attr( $banner['subtitle'] )    : '';
                            $button_text = isset( $banner['button_text'] ) ? esc_attr( $banner['button_text'] ) : '';
                            $button_url  = isset( $banner['button_url'] )  ? esc_attr( $banner['button_url'] )  : '';
                        ?>
                        <div class="offriend-banner-item" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px 20px; position: relative;">
                            <strong style="color: #162d59; font-size: 14px;">สไลด์ที่ <?php echo $bi + 1; ?></strong>
                            <button type="button" onclick="this.closest('.offriend-banner-item').remove()" style="position: absolute; top: 12px; right: 14px; background: #fee2e2; border: none; color: #dc2626; border-radius: 6px; padding: 3px 10px; cursor: pointer; font-size: 12px; font-weight: bold;">✕ ลบ</button>
                            <table class="form-table" style="margin: 10px 0 0 0;">
                                <tr>
                                    <th style="width: 180px;"><label>ภาพ Slide (1920×800)</label></th>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                            <input type="text" name="banners[<?php echo $bi; ?>][image_url]" value="<?php echo $image_url; ?>" class="large-text offriend-slide-image-url" style="max-width: 420px;" placeholder="URL รูปภาพ Slide" />
                                            <button type="button" class="button offriend-media-uploader-btn offriend-slide-upload-btn" style="white-space: nowrap;">🖼️ เลือก / อัปโหลดรูป</button>
                                        </div>
                                        <?php if ( $image_url ) : ?>
                                        <div style="margin-top: 8px;"><img src="<?php echo esc_url( $image_url ); ?>" style="max-height: 80px; border-radius: 6px; border: 1px solid #e2e8f0; object-fit: cover;" /></div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th><label>Kicker / Tag</label></th>
                                    <td><input type="text" name="banners[<?php echo $bi; ?>][kicker]" value="<?php echo $kicker; ?>" class="large-text" placeholder="เช่น HEADLESS ARCHITECTURE & NEXT.JS" /></td>
                                </tr>
                                <tr>
                                    <th><label>หัวเรื่องสไลด์</label></th>
                                    <td><input type="text" name="banners[<?php echo $bi; ?>][title]" value="<?php echo $title; ?>" class="large-text" placeholder="หัวข้อหลักของสไลด์" /></td>
                                </tr>
                                <tr>
                                    <th><label>คำบรรยายสไลด์</label></th>
                                    <td><textarea name="banners[<?php echo $bi; ?>][subtitle]" rows="2" class="large-text" placeholder="คำบรรยายประกอบสไลด์"><?php echo esc_textarea( isset( $banner['subtitle'] ) ? $banner['subtitle'] : '' ); ?></textarea></td>
                                </tr>
                                <tr>
                                    <th><label>ข้อความปุ่ม CTA</label></th>
                                    <td><input type="text" name="banners[<?php echo $bi; ?>][button_text]" value="<?php echo $button_text; ?>" class="regular-text" placeholder="เช่น ดูผลงาน" /></td>
                                </tr>
                                <tr>
                                    <th><label>ลิงก์ปุ่ม CTA</label></th>
                                    <td><input type="text" name="banners[<?php echo $bi; ?>][button_url]" value="<?php echo $button_url; ?>" class="large-text" placeholder="เช่น /projects" /></td>
                                </tr>
                            </table>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Template for new slides (hidden) -->
                    <template id="offriend-banner-template">
                        <div class="offriend-banner-item" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px 20px; position: relative;">
                            <strong style="color: #162d59; font-size: 14px;">สไลด์ใหม่</strong>
                            <button type="button" onclick="this.closest('.offriend-banner-item').remove()" style="position: absolute; top: 12px; right: 14px; background: #fee2e2; border: none; color: #dc2626; border-radius: 6px; padding: 3px 10px; cursor: pointer; font-size: 12px; font-weight: bold;">✕ ลบ</button>
                            <table class="form-table" style="margin: 10px 0 0 0;">
                                <tr>
                                    <th style="width: 180px;"><label>ภาพ Slide (1920×800)</label></th>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                            <input type="text" name="banners[__INDEX__][image_url]" value="" class="large-text offriend-slide-image-url" style="max-width: 420px;" placeholder="URL รูปภาพ Slide" />
                                            <button type="button" class="button offriend-media-uploader-btn offriend-slide-upload-btn" style="white-space: nowrap;">🖼️ เลือก / อัปโหลดรูป</button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th><label>Kicker / Tag</label></th>
                                    <td><input type="text" name="banners[__INDEX__][kicker]" value="" class="large-text" placeholder="เช่น HEADLESS ARCHITECTURE & NEXT.JS" /></td>
                                </tr>
                                <tr>
                                    <th><label>หัวเรื่องสไลด์</label></th>
                                    <td><input type="text" name="banners[__INDEX__][title]" value="" class="large-text" placeholder="หัวข้อหลักของสไลด์" /></td>
                                </tr>
                                <tr>
                                    <th><label>คำบรรยายสไลด์</label></th>
                                    <td><textarea name="banners[__INDEX__][subtitle]" rows="2" class="large-text" placeholder="คำบรรยายประกอบสไลด์"></textarea></td>
                                </tr>
                                <tr>
                                    <th><label>ข้อความปุ่ม CTA</label></th>
                                    <td><input type="text" name="banners[__INDEX__][button_text]" value="" class="regular-text" placeholder="เช่น ดูผลงาน" /></td>
                                </tr>
                                <tr>
                                    <th><label>ลิงก์ปุ่ม CTA</label></th>
                                    <td><input type="text" name="banners[__INDEX__][button_url]" value="" class="large-text" placeholder="เช่น /projects" /></td>
                                </tr>
                            </table>
                        </div>
                    </template>

                    <button type="button" id="offriend-add-banner" class="button button-secondary" style="margin-bottom: 25px;">+ เพิ่มสไลด์ใหม่</button>

                    <script>
                    jQuery(document).ready(function($) {
                        var bannerIndex = <?php echo count( $banners ); ?>;

                        // Add new slide
                        $('#offriend-add-banner').on('click', function() {
                            var tpl = document.getElementById('offriend-banner-template').innerHTML;
                            tpl = tpl.replace(/__INDEX__/g, bannerIndex);
                            var wrapper = document.getElementById('offriend-banners-wrapper');
                            var div = document.createElement('div');
                            div.innerHTML = tpl;
                            var newItem = div.firstElementChild;
                            newItem.querySelector('strong').textContent = 'สไลด์ที่ ' + (bannerIndex + 1);
                            wrapper.appendChild(newItem);
                            attachUploader(newItem);
                            bannerIndex++;
                        });

                        // Attach wp.media uploader to existing and new items
                        function attachUploader(scope) {
                            var btns = scope ? scope.querySelectorAll('.offriend-slide-upload-btn') : document.querySelectorAll('.offriend-slide-upload-btn');
                            btns.forEach(function(btn) {
                                if (btn._uploadAttached) return;
                                btn._uploadAttached = true;
                                btn.addEventListener('click', function() {
                                    var urlInput = btn.closest('td').querySelector('.offriend-slide-image-url');
                                    var frame = wp.media({
                                        title: 'เลือกหรืออัปโหลดภาพ Slide Banner (1920 × 800)',
                                        button: { text: 'ใช้ภาพนี้' },
                                        library: { type: 'image' },
                                        multiple: false
                                    });
                                    frame.on('select', function() {
                                        var att = frame.state().get('selection').first().toJSON();
                                        urlInput.value = att.url;
                                        // Show preview
                                        var preview = btn.closest('td').querySelector('img.slide-preview');
                                        if (!preview) {
                                            preview = document.createElement('img');
                                            preview.className = 'slide-preview';
                                            preview.style.cssText = 'display:block;margin-top:8px;max-height:80px;border-radius:6px;border:1px solid #e2e8f0;object-fit:cover;';
                                            btn.closest('td').appendChild(preview);
                                        }
                                        preview.src = att.url;
                                    });
                                    frame.open();
                                });
                            });
                        }

                        // Attach to initial items
                        attachUploader(null);
                    });
                    </script>

                    <p class="submit" style="padding-top: 15px; border-top: 1px solid #e2e8f0;">
                        <input type="submit" name="offriend_save_settings" id="submit" class="button button-primary" value="บันทึกการตั้งค่าทั้งหมด" style="background: #162d59; border-color: #162d59; padding: 6px 24px; font-weight: bold; height: auto;" />
                    </p>
                </form>
            </div>

            <!-- Box: นำเข้าข้อมูลเริ่มต้น 1 คลิก -->
            <div style="background: #f8fafc; padding: 20px 25px; border-radius: 12px; border: 1px dashed #cbd5e1;">
                <h3 style="margin-top: 0; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                    <span class="dashicons dashicons-database-import" style="color: #0d9488;"></span>
                    <span>นำเข้าข้อมูลเริ่มต้น (One-Click Seed Content)</span>
                </h3>
                <p style="color: #64748b; font-size: 13px; line-height: 1.6;">
                    หากต้องการนำข้อมูล ผลงาน (Projects), บริการ (Services), บุคลากร (Team) และเครื่องมือ (Tools) ที่มีอยู่เดิมเข้ามาไว้ใน WordPress เพื่อเริ่มแก้ไขต่อได้ทันที สามารถกดปุ่มด้านล่างได้เลยครับ (ระบบจะไม่สร้างซ้ำหากมีข้อมูลอยู่แล้ว)
                </p>
                <form method="post" action="">
                    <?php wp_nonce_field( 'offriend_seed_nonce' ); ?>
                    <input type="submit" name="offriend_seed_initial_data" class="button button-secondary" value="นำเข้าข้อมูลทั้งหมดเข้าสู่ WordPress ทันที" onclick="return confirm('ยืนยันการนำเข้าข้อมูลเริ่มต้นเข้าสู่ WordPress?');" />
                </form>
            </div>
        </div>
        <?php
    }

    /**
     * Seed Initial Data (Projects, Services, Team, Tools) into WordPress database
     */
    public function seed_initial_content() {
        // 1. Seed Team Members
        $team_items = array(
            array(
                'slug'           => 'samit-koyom',
                'title'          => 'สามิตร โกยม',
                'prefix'         => 'อาจารย์',
                'name_en'        => 'Samit Koyom',
                'role_title'     => 'CEO ที่สถาบันไอทีจีเนียส',
                'education_line' => 'ปริญญาโท คณะเทคโนโลยีและสารสนเทศ สถาบันเทคโนโลยีพระจอมเกล้าพระนครเหนือ',
                'objective'      => 'ต้องการใช้ความรู้ความสามารถในสาขาที่เรียนและประสบการณ์ทำงานด้านไอทีและคอมพิวเตอร์โปรแกรมมิ่งมามากกว่า 10 ปี ถ่ายทอดสู่ผู้สนใจในตลาดไอทีของเมืองไทย เพื่อให้ตลาดไอทีของไทยมีความก้าวหน้าและพัฒนาทัดเทียมนานาประเทศ',
                'experiences'    => "2550-2552 เป็นเว็บโปรแกรมเมอร์ (Web Programmer) ของบริษัทอีคอมสยามดอทคอม (ecomsiam.com)\n2553-2554 เป็นวิทยากรอบรมด้านการพัฒนาเว็บโปรแกรมมิ่ง ของสถาบันเน็ตดีไซต์ (netdesign.com)\n2555-2556 ผันตัวเองมารับงานอิสระด้านเว็บโปรแกรมมิ่ง และเป็นที่ปรึกษาด้านการออกแบบและวางแผน ไอทีให้กับบริษัทขนาดย่อม (SME) หลายแห่ง\n2557-2560 เป็นอาจารย์พิเศษประจำภาควิชาครุศาสตร์อุตสาหกรรม สาขาคอมพิวเตอร์และเทคโนโลยี สารสนเทศ มหาวิทยาลัยพระจอมเกล้าพระนครเหนือ\nปัจจุบันเป็น CEO บริษัท ไอทีจีเนียส เอ็นจิเนียริ่ง จำกัด และยังเป็นอาจารย์พิเศษ และรับสอน บรรยายด้านการออกแบบ และการเขียนเว็บโปรแกรมมิ่งกับองค์กร และสถานศึกษาทั่วไป",
            ),
            array(
                'slug'           => 'sanitwong-kamolpaporn',
                'title'          => 'สนิทวงศ์ กมลภาภรณ์',
                'prefix'         => '',
                'name_en'        => 'Sanitwong Kamolpaporn',
                'role_title'     => 'วิทยากรผู้สอน',
                'education_line' => 'ปริญญาโท คณะวิทยาการคอมพิวเตอร์และเทคโนโลยีสารสนเทศ',
                'objective'      => 'มุ่งมั่นถ่ายทอดองค์ความรู้และทักษะด้านการเขียนโปรแกรมและการพัฒนาซอฟต์แวร์สู่บุคลากรและองค์กร เพื่อเพิ่มศักยภาพการแข่งขันในยุคดิจิทัล',
                'experiences'    => "2554-2558 วิศวกรซอฟต์แวร์และนักพัฒนาระบบฐานข้อมูลองค์กร\n2559-2562 ผู้เชี่ยวชาญด้าน System Analysis & Software Engineering\nปัจจุบันเป็น วิทยากรผู้สอนและที่ปรึกษาด้านการพัฒนาซอฟต์แวร์และคลาวด์",
            ),
            array(
                'slug'           => 'kriangkrai-intharangsee',
                'title'          => 'เกรียงไกร อินทะรังษี',
                'prefix'         => '',
                'name_en'        => 'Kriangkrai Intharangsee',
                'role_title'     => 'วิทยากรผู้สอน',
                'education_line' => 'ปริญญาตรี คณะวิศวกรรมศาสตร์ สาขาวิศวกรรมคอมพิวเตอร์',
                'objective'      => 'นำประสบการณ์การพัฒนาซอฟต์แวร์และเทคโนโลยีสมัยใหม่ มาถ่ายทอดให้เข้าใจง่าย ปฏิบัติได้จริง และต่อยอดสู่การทำงานระดับองค์กร',
                'experiences'    => "2556-2560 Full-Stack Web Developer สำหรับแอปพลิเคชันระดับองค์กร\n2561-2564 ผู้เชี่ยวชาญการอบรมและพัฒนาทักษะด้านเทคโนโลยีและ AI สำหรับองค์กร\nปัจจุบันเป็น วิทยากรผู้สอนและที่ปรึกษาการพัฒนาเว็บแอปพลิเคชันยุคใหม่",
            ),
        );

        foreach ( $team_items as $t ) {
            $existing = get_page_by_path( $t['slug'], OBJECT, 'team' );
            if ( ! $existing ) {
                $post_id = wp_insert_post( array(
                    'post_title'   => $t['title'],
                    'post_name'    => $t['slug'],
                    'post_type'    => 'team',
                    'post_status'  => 'publish',
                    'post_content' => $t['objective'],
                ) );
                if ( $post_id && ! is_wp_error( $post_id ) ) {
                    update_post_meta( $post_id, '_offriend_team_prefix', $t['prefix'] );
                    update_post_meta( $post_id, '_offriend_team_name_en', $t['name_en'] );
                    update_post_meta( $post_id, '_offriend_team_role_title', $t['role_title'] );
                    update_post_meta( $post_id, '_offriend_team_education_line', $t['education_line'] );
                    update_post_meta( $post_id, '_offriend_team_objective', $t['objective'] );
                    update_post_meta( $post_id, '_offriend_team_experiences', $t['experiences'] );
                }
            }
        }

        // 2. Seed Services
        $service_items = array(
            array(
                'slug'        => 'web-development',
                'title'       => 'Enterprise Web Application & Portal',
                'title_th'    => 'บริการพัฒนาเว็บแอปพลิเคชันและระบบพอร์ทัลระดับองค์กร',
                'subtitle'    => 'ระบบเว็บแอปพลิเคชันสำหรับองค์กรที่รองรับการขยายตัว ประสิทธิภาพสูง ปลอดภัย และเสถียร',
                'tag'         => 'CUSTOM WEB ENGINEERING',
                'tech_stack'  => 'Next.js, React, TypeScript, Node.js, PostgreSQL, Docker',
                'content'     => 'พัฒนาเว็บแอปพลิเคชันและระบบพอร์ทัลเฉพาะทางสำหรับองค์กร ตั้งแต่ Back-office, ERP/CRM, Customer Portal จนถึง SaaS Platform ด้วยมาตรฐาน Clean Architecture ที่ต่อยอดและดูแลรักษาได้ง่าย',
                'highlights'  => "สถาปัตยกรรม Microservices & Modular Architecture\nความปลอดภัยระดับ Enterprise Security (OWASP Top 10)\nรองรับปริมาณการเข้าใช้งานระดับหลายหมื่น Concurrent Users",
            ),
            array(
                'slug'        => 'headless-cms',
                'title'       => 'Headless WordPress + Astro 7 Architecture',
                'title_th'    => 'บริการพัฒนาเว็บไซต์สถาปัตยกรรม Headless WordPress + Astro',
                'subtitle'    => 'ผสานหลังบ้าน WordPress ที่คุ้นเคยเข้ากับความเร็วสูงสุดระดับเสี้ยววินาทีของ Astro',
                'tag'         => 'HEADLESS CMS SPECIALIST',
                'tech_stack'  => 'Headless WordPress, Astro, GraphQL, Tailwind CSS, Vercel/Cloudflare',
                'content'     => 'ปฏิวัติเว็บไซต์องค์กรด้วย Headless Architecture แยกส่วนหน้าบ้าน (Frontend) และหลังบ้าน (Backend) ออกจากกัน โหลดหน้าเว็บเร็วขึ้น 300% ปิดช่องโหว่ความปลอดภัยของฐานข้อมูล 100%',
                'highlights'  => "Core Web Vitals ระดับ 95-100 คะแนนเต็มบน Google PageSpeed\nทีมงานจัดการเนื้อหาผ่าน WordPress Dashboard ตามปกติ\nปลอดภัยจาก DDoS และ SQL Injection โดยตรง",
            ),
            array(
                'slug'        => 'consulting',
                'title'       => 'Digital Transformation & Software Architecture Consulting',
                'title_th'    => 'บริการที่ปรึกษาด้านสถาปัตยกรรมซอฟต์แวร์และการเปลี่ยนผ่านดิจิทัล',
                'subtitle'    => 'วางแผนกลยุทธ์ด้านเทคโนโลยีและโครงสร้างพื้นฐานไอทีที่คุ้มค่าและตอบโจทย์ธุรกิจระยะยาว',
                'tag'         => 'TECH STRATEGY & ADVISORY',
                'tech_stack'  => 'System Design, Cloud Native, DevOps, CI/CD, Agile Framework',
                'content'     => 'ให้คำปรึกษาเชิงลึกโดยทีมวิศวกรและสถาปนิกซอฟต์แวร์ผู้มีประสบการณ์ตรง ช่วยวิเคราะห์ประเมินระบบเดิม (Legacy Modernization) และวางแผน Roadmap เทคโนโลยีที่ลดต้นทุนและเพิ่มความคล่องตัว',
                'highlights'  => "การวางแผน Migrate ระบบเดิมสู่ Cloud อย่างไร้รอยต่อ\nการออกแบบ Database Architecture & Data Pipeline\nการจัดทำ Tech Audit และ Security Assessment",
            ),
            array(
                'slug'        => 'training',
                'title'       => 'In-House Corporate IT & AI Engineering Training',
                'title_th'    => 'บริการจัดอบรมหลักสูตรไอที วิศวกรรมซอฟต์แวร์ และ AI สำหรับองค์กร',
                'subtitle'    => 'พัฒนาทักษะทีมงานไอทีและบุคลากรในองค์กรด้วยเวิร์กชอปปฏิบัติจริงจากวิทยากรผู้เชี่ยวชาญ',
                'tag'         => 'CORPORATE CAPACITY BUILDING',
                'tech_stack'  => 'Next.js Workshop, AI Prompting, Cloud DevOps, Full-Stack Mastery',
                'content'     => 'หลักสูตรอบรมเข้มข้นที่ออกแบบเฉพาะตามบริบทขององค์กร มุ่งเน้นการลงมือปฏิบัติจริง (Hands-on Workshops) พร้อมกรณีศึกษาจากงานจริง ให้ทีมงานพร้อมประยุกต์ใช้ได้ทันที',
                'highlights'  => "หลักสูตรปรับแต่งตามระดับความรู้ของทีมงาน (Customized Curriculum)\nวิทยากรมีประสบการณ์บรรยายให้แก่องค์กรชั้นนำระดับประเทศ\nเอกสารประกอบ เวิร์กชอปโค้ด และการประเมินผลสัมฤทธิ์หลังการอบรม",
            ),
        );

        foreach ( $service_items as $s ) {
            $existing = get_page_by_path( $s['slug'], OBJECT, 'services' );
            if ( ! $existing ) {
                $post_id = wp_insert_post( array(
                    'post_title'   => $s['title'],
                    'post_name'    => $s['slug'],
                    'post_type'    => 'services',
                    'post_status'  => 'publish',
                    'post_content' => $s['content'],
                    'post_excerpt' => $s['subtitle'],
                ) );
                if ( $post_id && ! is_wp_error( $post_id ) ) {
                    update_post_meta( $post_id, '_offriend_service_title_th', $s['title_th'] );
                    update_post_meta( $post_id, '_offriend_service_subtitle', $s['subtitle'] );
                    update_post_meta( $post_id, '_offriend_service_tag', $s['tag'] );
                    update_post_meta( $post_id, '_offriend_service_tech_stack', $s['tech_stack'] );
                    update_post_meta( $post_id, '_offriend_service_highlights', $s['highlights'] );
                }
            }
        }

        // 3. Seed Projects (Key Portfolio)
        $project_items = array(
            array(
                'slug'         => 'egat-intranet-cms',
                'title'        => 'ระบบ Intranet Portal และจัดการองค์ความรู้ การไฟฟ้าฝ่ายผลิตแห่งประเทศไทย (กฟผ.)',
                'client'       => 'การไฟฟ้าฝ่ายผลิตแห่งประเทศไทย (กฟผ.)',
                'category_tag' => 'Enterprise Portal',
                'year'         => '2569',
                'tech_stack'   => 'Next.js, TypeScript, Headless WordPress, Tailwind CSS, Docker',
                'content'      => 'พัฒนาและปรับปรุงระบบ Intranet Portal เพื่อการสื่อสารภายในองค์กรและจัดการองค์ความรู้ (Knowledge Management) ของการไฟฟ้าฝ่ายผลิตแห่งประเทศไทย รองรับพนักงานกว่า 20,000 คนทั่วประเทศ พร้อมระบบสืบค้นข้อมูลความเร็วสูง',
                'metrics'      => "รองรับผู้ใช้งานพร้อมกันกว่า 20,000 คน\nลดเวลาค้นหาเอกสารองค์กรลงกว่า 65%\nคะแนนความพึงพอใจการใช้งาน 98.4%",
            ),
            array(
                'slug'         => 'mea-smart-service-portal',
                'title'        => 'ระบบบริการลูกค้าออนไลน์ MEA Smart Service Portal',
                'client'       => 'การไฟฟ้านครหลวง (MEA)',
                'category_tag' => 'Web Application',
                'year'         => '2568',
                'tech_stack'   => 'Next.js, Node.js, Microservices, Redis, PostgreSQL, Kubernetes',
                'content'      => 'ออกแบบและพัฒนาระบบพอร์ทัลบริการออนไลน์สำหรับผู้ใช้ไฟฟ้า รองรับการขอใช้ไฟฟ้า ตรวจสอบค่าไฟฟ้า และบริการดิจิทัลแบบครบวงจรผ่านเว็บและอุปกรณ์มือถือ',
                'metrics'      => "ประมวลผลธุรกรรมมากกว่า 1,000,000 ครั้ง/เดือน\nUptime เฉลี่ย 99.98%\nผ่านมาตรฐานความปลอดภัยระดับ ISO/IEC 27001",
            ),
            array(
                'slug'         => 'nstda-research-database',
                'title'        => 'ระบบฐานข้อมูลงานวิจัยและนวัตกรรมแห่งชาติ สวทช.',
                'client'       => 'สำนักงานพัฒนาวิทยาศาสตร์และเทคโนโลยีแห่งชาติ (สวทช.)',
                'category_tag' => 'Database & Analytics',
                'year'         => '2568',
                'tech_stack'   => 'Astro, React, ElasticSearch, GraphQL, Cloudflare Workers',
                'content'      => 'พัฒนาระบบสืบค้นและจัดการฐานข้อมูลงานวิจัย นวัตกรรม และสิทธิบัตรทางวิทยาศาสตร์ระดับประเทศ เชื่อมโยงข้อมูลนักวิจัยและภาคเอกชนเพื่อการต่อยอดเชิงพาณิชย์',
                'metrics'      => "รวบรวมผลงานวิจัยกว่า 150,000 รายการ\nเวลาสืบค้นเฉลี่ยต่ำกว่า 0.2 วินาที\nเชื่อมต่อ API กับสถาบันการศึกษาทั่วประเทศกว่า 40 แห่ง",
            ),
        );

        foreach ( $project_items as $p ) {
            $existing = get_page_by_path( $p['slug'], OBJECT, 'projects' );
            if ( ! $existing ) {
                $post_id = wp_insert_post( array(
                    'post_title'   => $p['title'],
                    'post_name'    => $p['slug'],
                    'post_type'    => 'projects',
                    'post_status'  => 'publish',
                    'post_content' => $p['content'],
                    'post_excerpt' => $p['content'],
                ) );
                if ( $post_id && ! is_wp_error( $post_id ) ) {
                    update_post_meta( $post_id, '_offriend_project_client', $p['client'] );
                    update_post_meta( $post_id, '_offriend_project_category_tag', $p['category_tag'] );
                    update_post_meta( $post_id, '_offriend_project_year', $p['year'] );
                    update_post_meta( $post_id, '_offriend_project_tech_stack', $p['tech_stack'] );
                    update_post_meta( $post_id, '_offriend_project_metrics', $p['metrics'] );
                }
            }
        }
    }
}

// Initialize Plugin
Offriend_Headless_Core::get_instance();
