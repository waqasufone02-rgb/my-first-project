<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// BEGIN ENQUEUE PARENT ACTION
// AUTO GENERATED - Do not modify or remove comment markers above or below:

if ( !function_exists( 'chld_thm_cfg_locale_css' ) ):
    function chld_thm_cfg_locale_css( $uri ){
        if ( empty( $uri ) && is_rtl() && file_exists( get_template_directory() . '/rtl.css' ) )
            $uri = get_template_directory_uri() . '/rtl.css';
        return $uri;
    }
endif;
add_filter( 'locale_stylesheet_uri', 'chld_thm_cfg_locale_css' );

// END ENQUEUE PARENT ACTION

// WM Creations - Custom WhatsApp Order Gateway for Customized Products

add_filter( 'woocommerce_payment_gateways', 'wm_add_whatsapp_gateway' );
function wm_add_whatsapp_gateway( $gateways ) {
    $gateways[] = 'WM_WhatsApp_Gateway';
    return $gateways;
}

add_action( 'plugins_loaded', 'wm_init_whatsapp_gateway' );
function wm_init_whatsapp_gateway() {

    if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
        return;
    }

    class WM_WhatsApp_Gateway extends WC_Payment_Gateway {

        public function __construct() {
            $this->id                 = 'wm_whatsapp';
            $this->method_title       = 'WhatsApp Order';
            $this->method_description = 'Customized products ka payment WhatsApp ke zariye liya jata hai.';
            $this->title              = 'Order via WhatsApp';
            $this->description        = 'Yeh Customized Product hai. Is ka order WhatsApp ke zariye confirm aur payment wusool ki jati hai. Order place karne ke baad hamari team aapko WhatsApp number 0333-7888820 par contact karegi design confirm karne aur payment lene ke liye.';
            $this->enabled            = 'yes';
            $this->has_fields         = false;

            $this->init_form_fields();
            $this->init_settings();

            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
        }

        public function init_form_fields() {
            $this->form_fields = array(
                'enabled' => array(
                    'title'   => 'Enable/Disable',
                    'type'    => 'checkbox',
                    'label'   => 'Enable WhatsApp Order Gateway',
                    'default' => 'yes',
                ),
            );
        }

        public function process_payment( $order_id ) {
            $order = wc_get_order( $order_id );
            $order->update_status( 'on-hold', 'Customized order - WhatsApp confirmation aur payment ka intezar.' );
            $order->reduce_order_stock();
            WC()->cart->empty_cart();

            return array(
                'result'   => 'success',
                'redirect' => $this->get_return_url( $order ),
            );
        }
    }
}

add_filter( 'woocommerce_available_payment_gateways', 'wm_conditional_payment_gateways' );
function wm_conditional_payment_gateways( $available_gateways ) {

    if ( is_admin() || ! isset( WC()->cart ) || WC()->cart->is_empty() ) {
        return $available_gateways;
    }

    $has_customized_product = false;

    foreach ( WC()->cart->get_cart() as $cart_item ) {
        if ( has_term( 'customized-products', 'product_cat', $cart_item['product_id'] ) ) {
            $has_customized_product = true;
            break;
        }
    }

    if ( $has_customized_product ) {
        unset( $available_gateways['cod'] );
        unset( $available_gateways['bacs'] );
    } else {
        unset( $available_gateways['wm_whatsapp'] );
    }

    return $available_gateways;
}


// Moved: customized WhatsApp notice relocated via product redesign hooks
// add_action( 'woocommerce_single_product_summary', 'wm_whatsapp_order_button', 25 );
function wm_whatsapp_order_button() {
    global $product;

    if ( ! $product ) {
        return;
    }

    if ( has_term( 'customized-products', 'product_cat', $product->get_id() ) ) {

        $product_name = $product->get_name();
        $product_link = get_permalink( $product->get_id() );
        $whatsapp_number = '923337888820';
        $message = "Assalam-o-Alaikum, main ye order karna chahta/chahti hoon, mujhe iski mazeed detail farmayen.\n\nProduct: " . $product_name . "\nLink: " . $product_link;
        $whatsapp_link = 'https://wa.me/' . $whatsapp_number . '?text=' . rawurlencode( $message );

        echo '<div class="wm-custom-notice">
            <p>
                ⚠️ <b>Ye Customized Product Hai</b> — is par aapki pasand ke mutabiq naam, tasveer waghera print ki jayegi. Chunki har order khaas tor par sirf aapke liye taiyar kiya jata hai, is liye order confirm karne se pehle delivery charges advance mein ada karna zaroori hai.
            </p>
            <a href="' . esc_url( $whatsapp_link ) . '" target="_blank" class="wm-whatsapp-btn">
                📱 Order via WhatsApp
            </a>
        </div>';
    }
}

// Bulk Order / Reseller discount notice - har product par dikhta hai
// Moved: bulk notice now in wm_product_extra_notices (before ATC)
// add_action( 'woocommerce_single_product_summary', 'wm_bulk_reseller_notice', 26 );
function wm_bulk_reseller_notice() {
    global $product;

    if ( ! $product ) {
        return;
    }

    $product_name    = $product->get_name();
    $product_link    = get_permalink( $product->get_id() );
    $whatsapp_number = '923337888820';
    $message = "Assalam-o-Alaikum, mujhe bulk order ya reseller discount ke baare mein maloomat chahiye.\n\nProduct: " . $product_name . "\nLink: " . $product_link;
    $whatsapp_link = 'https://wa.me/' . $whatsapp_number . '?text=' . rawurlencode( $message );

    echo '<div class="wm-bulk-notice">
        <p>
            📦 <b>Bulk Quantity Order ya Reseller Hain?</b> Bade quantity orders aur resellers ke liye khaas discount diya jata hai. Mazeed maloomat ke liye WhatsApp par rabta karein.
        </p>
        <a href="' . esc_url( $whatsapp_link ) . '" target="_blank" class="wm-whatsapp-btn wm-whatsapp-btn-outline">
            📱 WhatsApp Par Rabta Karein
        </a>
    </div>';
}

// WM Creations - Custom Header Layout

add_action( 'generate_before_header', 'wm_announcement_bar' );
function wm_announcement_bar() {
    echo '<div style="background:#1A3FA0; color:#fff; text-align:center; padding:8px; font-size:13px;">
        Free delivery Pakistan bhar mein — order abhi karein!
    </div>';
}

add_action( 'generate_inside_navigation', 'wm_header_icons_row' );
function wm_header_icons_row() {

    if ( ! class_exists( 'WooCommerce' ) ) {
        return;
    }

    $cart_url    = wc_get_cart_url();
    $cart_count  = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
    $account_url = get_permalink( get_option( 'woocommerce_myaccount_page_id' ) );

    echo '<form class="wm-search-form" action="' . esc_url( home_url( '/' ) ) . '" method="get">
        <input type="text" name="s" class="wm-search-input" placeholder="Products search karein">
        <input type="hidden" name="post_type" value="product">
        <button type="submit" class="wm-search-submit">Search</button>
    </form>';

    echo '<div class="wm-icons-row">
        <a href="' . esc_url( home_url( '/' ) ) . '" class="wm-icon-link">🏠</a>
        <a href="' . esc_url( $cart_url ) . '" class="wm-icon-link">
            🛒
            <span class="wm-cart-count">' . $cart_count . '</span>
        </a>
        <a href="' . esc_url( $account_url ) . '" class="wm-icon-link">👤</a>
    </div>';
}

add_action( 'generate_after_header', 'wm_mobile_toolbar', 5 );
function wm_mobile_toolbar() {

    if ( ! class_exists( 'WooCommerce' ) ) {
        return;
    }

    $cart_url    = wc_get_cart_url();
    $cart_count  = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
    $account_url = get_permalink( get_option( 'woocommerce_myaccount_page_id' ) );

    echo '<div class="wm-mobile-toolbar">';

        echo '<button id="wm-menu-toggle-btn" class="wm-menu-toggle" type="button" aria-label="Menu">☰</button>';

        echo '<form class="wm-mobile-search-form" action="' . esc_url( home_url( '/' ) ) . '" method="get">
            <input type="text" name="s" placeholder="Products search karein">
            <input type="hidden" name="post_type" value="product">
            <button type="submit">Search</button>
        </form>';

        echo '<div class="wm-mobile-icons-row">
            <a href="' . esc_url( home_url( '/' ) ) . '">🏠</a>
            <a href="' . esc_url( $cart_url ) . '" class="wm-mobile-cart-link">
                🛒<span class="wm-cart-count">' . $cart_count . '</span>
            </a>
            <a href="' . esc_url( $account_url ) . '">👤</a>
        </div>';

    echo '</div>';
}

add_action( 'wp_head', 'wm_header_flex_css' );
function wm_header_flex_css() {
    echo '<style>

        .inside-header {
            display:grid;
            grid-template-columns: auto 1fr auto;
            align-items:center;
            gap:15px;
            padding:14px 20px;
        }
        .main-navigation,
        .main-navigation .inside-navigation {
            display:contents;
        }
        .main-navigation .inside-navigation ul.menu { display:none !important; }

        .main-navigation button.menu-toggle,
        .main-navigation #menu-toggle,
        #menu-toggle {
            display:none !important;
        }

        .wm-menu-toggle {
            display:none;
            background:#1A3FA0;
            color:#fff;
            border:none;
            cursor:pointer;
        }

        .wm-search-form {
            grid-column:2;
            justify-self:center;
            display:flex;
            align-items:center;
            background:#fff;
            border:1px solid #dde3ee;
            border-radius:30px;
            padding:4px 4px 4px 20px;
            width:100%;
            max-width:460px;
            box-shadow:0 2px 10px rgba(26,63,160,0.08);
            transition: box-shadow 0.25s ease, border-color 0.25s ease;
        }
        .wm-search-form:focus-within {
            border-color:#1A3FA0;
            box-shadow:0 4px 16px rgba(26,63,160,0.2);
        }
        .wm-search-input {
            border:none;
            outline:none;
            background:transparent;
            flex:1;
            font-size:14px;
            padding:11px 6px;
            min-width:0;
        }
        .wm-search-submit {
            background:#1A3FA0;
            color:#fff;
            border:none;
            padding:11px 24px;
            border-radius:26px;
            cursor:pointer;
            font-weight:600;
            font-size:14px;
            transition: background 0.25s ease;
            white-space:nowrap;
        }
        .wm-search-submit:hover { background:#142F80; }

        .wm-icons-row { grid-column:3; justify-self:end; display:flex; align-items:center; gap:18px; }
        .wm-icon-link { position:relative; text-decoration:none; color:#1A3FA0; font-size:22px; }
        .wm-cart-count {
            position:absolute; top:-6px; right:-10px;
            background:#1A3FA0; color:#fff; font-size:10px;
            border-radius:50%; width:16px; height:16px;
            display:flex; align-items:center; justify-content:center;
        }

        .wm-category-wrapper {
            position:relative;
            display:flex;
            align-items:center;
            justify-content:center;
            flex-wrap:wrap;
            gap:12px;
            padding:12px 20px;
            background:#fff;
        }
        .wm-category-wrapper::before,
        .wm-category-wrapper::after {
            content:"";
            position:absolute;
            left:50%;
            transform:translateX(-50%);
            width:92%;
            height:2px;
            background:linear-gradient(to right, transparent, #4FA8E0 15%, #4FA8E0 85%, transparent);
        }
        .wm-category-wrapper::before { top:0; }
        .wm-category-wrapper::after  { bottom:0; }

        .wm-category-list {
            list-style:none;
            margin:0;
            padding:0;
            display:flex;
            gap:12px;
            flex-wrap:wrap;
            justify-content:center;
        }
        .wm-category-list li { list-style:none; margin:0; position:relative; }
        .wm-category-list li a {
            display:inline-block;
            padding:10px 24px;
            background:linear-gradient(135deg,#F1F5FB,#E6EEFB);
            color:#1A3FA0;
            border-radius:30px;
            text-decoration:none;
            font-size:14px;
            font-weight:600;
            letter-spacing:0.2px;
            border:1px solid #E3EAF6;
            box-shadow:0 2px 6px rgba(26,63,160,0.08);
            transition: all 0.25s ease;
        }
        .wm-category-list li a:hover {
            background:linear-gradient(135deg,#1A3FA0,#2B54C4);
            color:#fff;
            transform:translateY(-3px);
            box-shadow:0 8px 16px rgba(26,63,160,0.28);
            border-color:#1A3FA0;
        }
        .wm-category-list li.current-menu-item a {
            background:#1A3FA0;
            color:#fff;
        }

        .wm-category-list li.menu-item-has-children > a::after {
            content: "▾";
            margin-left: 6px;
            font-size: 11px;
        }

        .wm-category-list .sub-menu {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            margin-top: 0;
            padding: 16px 10px 10px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 12px 30px rgba(26,63,160,0.18);
            min-width: 220px;
            z-index: 200;
            list-style: none;
        }
        .wm-category-list li:hover > .sub-menu {
            display: block;
        }
        .wm-category-list .sub-menu li {
            width: 100%;
        }
        .wm-category-list .sub-menu li a {
            display: block;
            width: 100%;
            box-sizing: border-box;
            padding: 10px 16px;
            background: transparent;
            box-shadow: none;
            border: none;
            border-radius: 8px;
            text-align: left;
            font-weight: 500;
        }
        .wm-category-list .sub-menu li a:hover {
            background: #F1F5FB;
            color: #1A3FA0;
            transform: none;
            box-shadow: none;
        }

        .wm-mobile-toolbar { display:none; }

        @media (max-width: 768px) {

            .wm-search-form,
            .wm-icons-row {
                display:none !important;
            }

            .inside-header {
                display:flex;
                flex-direction:column;
                align-items:center;
                padding:15px;
                text-align:center;
            }

            .wm-mobile-toolbar {
                display:grid;
                grid-template-columns: auto 1fr;
                align-items:center;
                gap:10px;
                padding:12px 15px;
                background:#fff;
                border-top:1px solid #f0f0f0;
            }
            .wm-menu-toggle {
                display:flex;
                align-items:center;
                justify-content:center;
                width:44px;
                height:44px;
                background:#1A3FA0;
                color:#fff;
                border:none;
                border-radius:8px;
                font-size:20px;
                cursor:pointer;
            }
            .wm-mobile-search-form {
                display:flex;
                align-items:center;
                background:#fff;
                border:1px solid #dde3ee;
                border-radius:30px;
                padding:4px 4px 4px 16px;
                box-shadow:0 2px 10px rgba(26,63,160,0.08);
            }
            .wm-mobile-search-form input[type="text"] {
                border:none;
                outline:none;
                background:transparent;
                flex:1;
                font-size:14px;
                padding:10px 6px;
                min-width:0;
            }
            .wm-mobile-search-form button {
                background:#1A3FA0;
                color:#fff;
                border:none;
                padding:10px 18px;
                border-radius:26px;
                font-weight:600;
                font-size:13px;
                cursor:pointer;
                white-space:nowrap;
            }
            .wm-mobile-icons-row {
                grid-column: 1 / -1;
                display:flex;
                justify-content:center;
                align-items:center;
                gap:24px;
                padding-top:8px;
            }
            .wm-mobile-icons-row a {
                position:relative;
                text-decoration:none;
                color:#1A3FA0;
                font-size:22px;
            }

            .wm-category-wrapper {
                padding:10px 15px;
                justify-content:stretch;
            }
            .wm-category-list {
                display:none;
                flex-direction:column;
                width:100%;
                gap:8px;
            }
            .wm-category-list.wm-menu-open {
                display:flex;
                padding-top:10px;
            }
            .wm-category-list li { width:100%; }
            .wm-category-list li a {
                width:100%;
                box-sizing:border-box;
                text-align:center;
                border-radius:8px;
            }

            .wm-category-list li.menu-item-has-children > a::after {
                float: right;
                transition: transform 0.25s ease;
            }
            .wm-category-list li.menu-item-has-children.wm-sub-open > a::after {
                transform: rotate(180deg);
            }
            .wm-category-list .sub-menu {
                display: none;
                position: static;
                box-shadow: none;
                background: #F8FAFD;
                margin: 6px 0 0;
                padding: 6px;
                border-radius: 8px;
            }
            .wm-category-list li.wm-sub-open > .sub-menu {
                display: block;
            }
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var btn  = document.getElementById("wm-menu-toggle-btn");
            var list = document.getElementById("wm-category-list");
            if (btn && list) {
                btn.addEventListener("click", function () {
                    list.classList.toggle("wm-menu-open");
                });
            }

            var parentItems = document.querySelectorAll(".wm-category-list li.menu-item-has-children");
            parentItems.forEach(function (item) {
                var link = item.querySelector("a");
                link.addEventListener("click", function (e) {
                    if (window.innerWidth <= 768) {
                        e.preventDefault();
                        item.classList.toggle("wm-sub-open");
                    }
                });
            });
        });
    </script>';
}

add_action( 'generate_after_header', 'wm_category_menu_bar', 10 );
function wm_category_menu_bar() {
    if ( has_nav_menu( 'wm_category_menu' ) ) {
        echo '<div class="wm-category-wrapper">';
        wp_nav_menu( array(
            'theme_location' => 'wm_category_menu',
            'container'      => false,
            'menu_class'     => 'wm-category-list',
            'menu_id'        => 'wm-category-list',
        ) );
        echo '</div>';
    }
}
add_action( 'after_setup_theme', 'wm_register_category_menu' );
function wm_register_category_menu() {
    register_nav_menu( 'wm_category_menu', 'WM Category Menu' );
}


// ============================================================
// WM Creations - PRODUCT VIDEO (shared: single product + archive/category pages)
// ============================================================

add_action( 'add_meta_boxes', 'wm_add_video_meta_box' );
function wm_add_video_meta_box() {
    add_meta_box(
        'wm_product_video',
        'Product Video (YouTube Link)',
        'wm_video_meta_box_html',
        'product',
        'side',
        'default'
    );
}
function wm_video_meta_box_html( $post ) {
    $value = get_post_meta( $post->ID, '_wm_youtube_url', true );
    wp_nonce_field( 'wm_save_video_meta', 'wm_video_meta_nonce' );
    echo '<label for="wm_youtube_url" style="font-weight:600; display:block; margin-bottom:6px;">YouTube Video URL:</label>';
    echo '<input type="text" id="wm_youtube_url" name="wm_youtube_url" value="' . esc_attr( $value ) . '" style="width:100%;" placeholder="https://www.youtube.com/watch?v=...">';
    echo '<p style="font-size:12px; color:#666; margin-top:6px;">Agar video na ho to khali chhor dein — "Watch Video" button nahi dikhega.</p>';
}
add_action( 'save_post', 'wm_save_video_meta_box' );
function wm_save_video_meta_box( $post_id ) {
    if ( ! isset( $_POST['wm_video_meta_nonce'] ) || ! wp_verify_nonce( $_POST['wm_video_meta_nonce'], 'wm_save_video_meta' ) ) {
        return;
    }
    if ( isset( $_POST['wm_youtube_url'] ) ) {
        $raw = wp_unslash( $_POST['wm_youtube_url'] );
        // Pehle raw input se video ID nikal lein (chahe plain link ho ya embed <iframe> code),
        // taake tags strip hone se pehle hi ID mil jaye aur link corrupt na ho
        $video_id = wm_get_youtube_id( $raw );
        if ( $video_id ) {
            update_post_meta( $post_id, '_wm_youtube_url', 'https://www.youtube.com/watch?v=' . $video_id );
        } else {
            update_post_meta( $post_id, '_wm_youtube_url', sanitize_text_field( $raw ) );
        }
    }
}

function wm_get_youtube_id( $url ) {
    preg_match( '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?|shorts)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $match );
    return isset( $match[1] ) ? $match[1] : false;
}

add_action( 'template_redirect', 'wm_handle_buy_now_redirect' );
function wm_handle_buy_now_redirect() {
    if ( isset( $_GET['wm-buy-now'] ) && isset( $_GET['add-to-cart'] ) && is_numeric( $_GET['add-to-cart'] ) ) {
        WC()->cart->empty_cart();
        WC()->cart->add_to_cart( intval( $_GET['add-to-cart'] ) );
        wp_safe_redirect( wc_get_checkout_url() );
        exit;
    }
}


// ============================================================
// WM Creations - CUSTOM NAME/PICTURE FIELDS (Customized Products)
// ============================================================

// Admin: har product ke liye Name/Picture field on/off karna
add_action( 'add_meta_boxes', 'wm_add_custom_fields_meta_box' );
function wm_add_custom_fields_meta_box() {
    add_meta_box( 'wm_custom_fields', 'Customer Input Fields', 'wm_custom_fields_meta_box_html', 'product', 'side', 'default' );
}
function wm_custom_fields_meta_box_html( $post ) {
    $show_name = get_post_meta( $post->ID, '_wm_show_name_field', true );
    $show_pic  = get_post_meta( $post->ID, '_wm_show_picture_field', true );
    wp_nonce_field( 'wm_save_custom_fields', 'wm_custom_fields_nonce' );
    echo '<label style="display:block; margin-bottom:8px;"><input type="checkbox" name="wm_show_name_field" value="yes" ' . checked( $show_name, 'yes', false ) . '> "Type Your Name" box dikhayein</label>';
    echo '<label style="display:block;"><input type="checkbox" name="wm_show_picture_field" value="yes" ' . checked( $show_pic, 'yes', false ) . '> "Upload Picture" box dikhayein</label>';
}
add_action( 'save_post', 'wm_save_custom_fields_meta_box' );
function wm_save_custom_fields_meta_box( $post_id ) {
    if ( ! isset( $_POST['wm_custom_fields_nonce'] ) || ! wp_verify_nonce( $_POST['wm_custom_fields_nonce'], 'wm_save_custom_fields' ) ) {
        return;
    }
    update_post_meta( $post_id, '_wm_show_name_field', isset( $_POST['wm_show_name_field'] ) ? 'yes' : '' );
    update_post_meta( $post_id, '_wm_show_picture_field', isset( $_POST['wm_show_picture_field'] ) ? 'yes' : '' );
}

// Frontend custom fields: variable = before variations; simple = before ATC
// (hooks registered in wm_restructure_product_summary)
function wm_render_custom_fields_markup() {
    global $product;

    if ( ! $product ) {
        return;
    }

    $show_name = get_post_meta( $product->get_id(), '_wm_show_name_field', true );
    $show_pic  = get_post_meta( $product->get_id(), '_wm_show_picture_field', true );

    if ( ! $show_name && ! $show_pic ) {
        return;
    }

    echo '<div class="wm-custom-fields-box wm-custom-fields-v2">';
    echo '<p class="wm-custom-fields-heading">✏️ Customize Your Design</p>';

    if ( $show_name ) {
        echo '<label class="wm-field-label">Naam Likhein *</label>
              <input type="text" name="wm_custom_name" class="wm-field-input" placeholder="Type Your Name Here" required>';
    }

    if ( $show_pic ) {
        echo '<label class="wm-field-label">Tasveer Upload Karein</label>
              <div class="wm-upload-wrap">
                <input type="file" name="wm_custom_picture" id="wm_custom_picture" class="wm-field-input wm-file-input" accept="image/*">
                <label for="wm_custom_picture" class="wm-upload-btn">📷 Choose Photo</label>
                <span class="wm-upload-filename">No file chosen</span>
              </div>
              <p class="wm-field-note">Agar pictures ek se zyada hain, to kindly WhatsApp kar dijiye — <a href="https://wa.me/923337888820" target="_blank">0333-7888820</a></p>';
    }

    if ( has_term( 'customized-products', 'product_cat', $product->get_id() ) ) {
        echo '<p class="wm-custom-mini-note">⚠️ Custom print items ki <strong>delivery charges advance</strong> deni zaroori hain.</p>';
    }

    echo '</div>';
}

function wm_render_custom_fields_redesign() {
    wm_render_custom_fields_markup();
}

function wm_render_custom_fields_simple_only() {
    global $product;
    if ( ! $product || $product->is_type( 'variable' ) ) {
        return;
    }
    wm_render_custom_fields_markup();
}

// Backward-compatible alias (unhooked; kept to avoid fatals if referenced elsewhere)
function wm_render_custom_fields() {
    wm_render_custom_fields_markup();
}

// Add to Cart form ko file upload ke qabil banana (enctype)
add_action( 'wp_footer', 'wm_enctype_js' );
function wm_enctype_js() {
    if ( ! is_product() ) {
        return;
    }
    echo '<script>
        document.addEventListener("DOMContentLoaded", function () {
            var form = document.querySelector("form.cart");
            if (form) {
                form.setAttribute("enctype", "multipart/form-data");
            }
        });
    </script>';
}

// Validation: Naam field required ho to khali na jaye
add_filter( 'woocommerce_add_to_cart_validation', 'wm_validate_custom_fields', 10, 2 );
function wm_validate_custom_fields( $passed, $product_id ) {
    $show_name = get_post_meta( $product_id, '_wm_show_name_field', true );
    if ( $show_name && empty( $_POST['wm_custom_name'] ) ) {
        wc_add_notice( 'Please apna naam likhein.', 'error' );
        $passed = false;
    }
    return $passed;
}

// Cart mein Naam + Picture data save karna
add_filter( 'woocommerce_add_cart_item_data', 'wm_add_custom_fields_to_cart', 10, 2 );
function wm_add_custom_fields_to_cart( $cart_item_data, $product_id ) {
    if ( ! empty( $_POST['wm_custom_name'] ) ) {
        $cart_item_data['wm_custom_name'] = sanitize_text_field( $_POST['wm_custom_name'] );
    }

    if ( ! empty( $_FILES['wm_custom_picture']['name'] ) ) {
        require_once ABSPATH . 'wp-admin/includes/file.php';

        $allowed_types = array( 'image/jpeg', 'image/png', 'image/webp' );
        if ( in_array( $_FILES['wm_custom_picture']['type'], $allowed_types, true ) ) {
            $movefile = wp_handle_upload( $_FILES['wm_custom_picture'], array( 'test_form' => false ) );
            if ( $movefile && ! isset( $movefile['error'] ) ) {
                $cart_item_data['wm_custom_picture'] = $movefile['url'];
            }
        }
    }

    if ( isset( $cart_item_data['wm_custom_name'] ) || isset( $cart_item_data['wm_custom_picture'] ) ) {
        $cart_item_data['unique_key'] = md5( microtime() . wp_rand() );
    }

    return $cart_item_data;
}

// Cart/Checkout page par ye data dikhana
add_filter( 'woocommerce_get_item_data', 'wm_display_custom_fields_in_cart', 10, 2 );
function wm_display_custom_fields_in_cart( $item_data, $cart_item ) {
    if ( isset( $cart_item['wm_custom_name'] ) ) {
        $item_data[] = array(
            'name'  => 'Naam',
            'value' => esc_html( $cart_item['wm_custom_name'] ),
        );
    }
    if ( isset( $cart_item['wm_custom_picture'] ) ) {
        $item_data[] = array(
            'name'  => 'Uploaded Picture',
            'value' => '<a href="' . esc_url( $cart_item['wm_custom_picture'] ) . '" target="_blank">View Image</a>',
        );
    }
    return $item_data;
}

// Order mein permanently save karna (taake admin order dekh sake)
add_action( 'woocommerce_checkout_create_order_line_item', 'wm_save_custom_fields_to_order', 10, 4 );
function wm_save_custom_fields_to_order( $item, $cart_item_key, $values, $order ) {
    if ( isset( $values['wm_custom_name'] ) ) {
        $item->add_meta_data( 'Naam', $values['wm_custom_name'] );
    }
    if ( isset( $values['wm_custom_picture'] ) ) {
        $item->add_meta_data( 'Uploaded Picture', $values['wm_custom_picture'] );
    }
    if ( isset( $values['wm_box_label'] ) ) {
        $item->add_meta_data( 'Box/Packaging', $values['wm_box_label'] );
    }
}


// ============================================================
// WM Creations - PACK PRICING (Buy 2/3, Save More)
// ============================================================

// Admin: Pack of 2 aur Pack of 3 ki total price set karna
add_action( 'add_meta_boxes', 'wm_add_pack_pricing_meta_box' );
function wm_add_pack_pricing_meta_box() {
    add_meta_box( 'wm_pack_pricing', 'Pack Pricing (Buy More, Save More)', 'wm_pack_pricing_meta_box_html', 'product', 'side', 'default' );
}
function wm_pack_pricing_meta_box_html( $post ) {
    $pack2_price = get_post_meta( $post->ID, '_wm_pack2_price', true );
    $pack3_price = get_post_meta( $post->ID, '_wm_pack3_price', true );
    wp_nonce_field( 'wm_save_pack_pricing', 'wm_pack_pricing_nonce' );
    echo '<p style="font-size:11px; color:#666; margin-top:0;">Khali chhoड़ dein agar wo pack option nahi chahiye.</p>';
    echo '<label style="display:block; font-weight:600; margin-bottom:4px;">Pack of 2 — Total Price (Rs)</label>';
    echo '<input type="number" step="0.01" name="wm_pack2_price" value="' . esc_attr( $pack2_price ) . '" style="width:100%; margin-bottom:14px;" placeholder="e.g. 1300">';
    echo '<label style="display:block; font-weight:600; margin-bottom:4px;">Pack of 3 — Total Price (Rs)</label>';
    echo '<input type="number" step="0.01" name="wm_pack3_price" value="' . esc_attr( $pack3_price ) . '" style="width:100%;" placeholder="e.g. 1800">';
}
add_action( 'save_post', 'wm_save_pack_pricing_meta_box' );
function wm_save_pack_pricing_meta_box( $post_id ) {
    if ( ! isset( $_POST['wm_pack_pricing_nonce'] ) || ! wp_verify_nonce( $_POST['wm_pack_pricing_nonce'], 'wm_save_pack_pricing' ) ) {
        return;
    }
    if ( isset( $_POST['wm_pack2_price'] ) ) {
        update_post_meta( $post_id, '_wm_pack2_price', sanitize_text_field( $_POST['wm_pack2_price'] ) );
    }
    if ( isset( $_POST['wm_pack3_price'] ) ) {
        update_post_meta( $post_id, '_wm_pack3_price', sanitize_text_field( $_POST['wm_pack3_price'] ) );
    }
}

// Frontend: Pack selection cards (Add to Cart se pehle)
add_action( 'woocommerce_before_add_to_cart_button', 'wm_render_pack_options', 5 );
function wm_render_pack_options() {
    global $product;

    if ( ! $product ) {
        return;
    }

    $base_price  = floatval( $product->get_price() );
    $pack2_price = get_post_meta( $product->get_id(), '_wm_pack2_price', true );
    $pack3_price = get_post_meta( $product->get_id(), '_wm_pack3_price', true );

    if ( ! $pack2_price && ! $pack3_price ) {
        return;
    }

    echo '<div class="wm-pack-options">';
    echo '<p class="wm-pack-title">Pack Select Karein</p>';
    echo '<div class="wm-pack-cards">';

    echo '<label class="wm-pack-card wm-pack-selected" data-qty="1">
            <input type="radio" name="wm_pack_select" value="1" checked style="display:none;">
            <span class="wm-pack-name">Pack of 1</span>
            <span class="wm-pack-price">Rs ' . number_format( $base_price ) . '</span>
          </label>';

    if ( $pack2_price ) {
        $pack2_price = floatval( $pack2_price );
        $save2     = max( 0, ( $base_price * 2 ) - $pack2_price );
        $save2_pct = $base_price > 0 ? round( ( $save2 / ( $base_price * 2 ) ) * 100 ) : 0;
        echo '<label class="wm-pack-card" data-qty="2">
                <input type="radio" name="wm_pack_select" value="2" style="display:none;">
                <span class="wm-pack-name">Pack of 2</span>
                <span class="wm-pack-price">Rs ' . number_format( $pack2_price ) . '</span>
                <span class="wm-pack-save">Save ' . $save2_pct . '%</span>
              </label>';
    }

    if ( $pack3_price ) {
        $pack3_price = floatval( $pack3_price );
        $save3     = max( 0, ( $base_price * 3 ) - $pack3_price );
        $save3_pct = $base_price > 0 ? round( ( $save3 / ( $base_price * 3 ) ) * 100 ) : 0;
        echo '<label class="wm-pack-card" data-qty="3">
                <span class="wm-pack-best">Best Value</span>
                <input type="radio" name="wm_pack_select" value="3" style="display:none;">
                <span class="wm-pack-name">Pack of 3</span>
                <span class="wm-pack-price">Rs ' . number_format( $pack3_price ) . '</span>
                <span class="wm-pack-save">Save ' . $save3_pct . '%</span>
              </label>';
    }

    echo '</div></div>';
}

// Pack select hone par quantity field ko sync + hide karna
add_action( 'wp_footer', 'wm_pack_pricing_js' );
function wm_pack_pricing_js() {
    if ( ! is_product() ) {
        return;
    }
    echo '<script>
        document.addEventListener("DOMContentLoaded", function () {
            var packBox = document.querySelector(".wm-pack-options");
            if (packBox) {
                var qtyWrap = document.querySelector("form.cart .quantity");
                if (qtyWrap) qtyWrap.style.display = "none";
            }

            document.querySelectorAll(".wm-pack-card").forEach(function (card) {
                card.addEventListener("click", function () {
                    var group = card.closest(".wm-pack-options, .wm-box-options");
                    if (!group) return;
                    group.querySelectorAll(".wm-pack-card").forEach(function (c) {
                        c.classList.remove("wm-pack-selected");
                    });
                    card.classList.add("wm-pack-selected");
                    var radio = card.querySelector("input[type=radio]");
                    if (radio) radio.checked = true;

                    if (card.hasAttribute("data-qty")) {
                        var qty = card.getAttribute("data-qty");
                        var qtyInput = document.querySelector("form.cart input.qty");
                        if (qtyInput) qtyInput.value = qty;
                    }
                });
            });
        });
    </script>';
}

// Cart mein sahi quantity + pack price save karna
add_filter( 'woocommerce_add_cart_item_data', 'wm_add_pack_data_to_cart', 20, 2 );
function wm_add_pack_data_to_cart( $cart_item_data, $product_id ) {
    if ( ! empty( $_POST['wm_pack_select'] ) ) {
        $pack_qty = intval( $_POST['wm_pack_select'] );
        $pack_qty = in_array( $pack_qty, array( 1, 2, 3 ), true ) ? $pack_qty : 1;

        $product    = wc_get_product( $product_id );
        $base_price = $product ? floatval( $product->get_price() ) : 0;

        if ( $pack_qty === 2 ) {
            $pack_total = get_post_meta( $product_id, '_wm_pack2_price', true );
        } elseif ( $pack_qty === 3 ) {
            $pack_total = get_post_meta( $product_id, '_wm_pack3_price', true );
        } else {
            $pack_total = $base_price;
        }

        if ( $pack_total ) {
            $cart_item_data['wm_pack_qty']        = $pack_qty;
            $cart_item_data['wm_pack_unit_price'] = floatval( $pack_total ) / $pack_qty;
            $cart_item_data['unique_key']         = md5( microtime() . wp_rand() );
        }
    }
    return $cart_item_data;
}

// Cart total calculate karte waqt Pack price + Box surcharge dono apply karna
add_action( 'woocommerce_before_calculate_totals', 'wm_apply_pack_pricing' );
function wm_apply_pack_pricing( $cart ) {
    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
        return;
    }
    foreach ( $cart->get_cart() as $cart_item ) {
        $has_pack = isset( $cart_item['wm_pack_unit_price'] );
        $has_box  = isset( $cart_item['wm_box_price'] );

        if ( ! $has_pack && ! $has_box ) {
            continue;
        }

        $unit_price = $has_pack ? floatval( $cart_item['wm_pack_unit_price'] ) : floatval( $cart_item['data']->get_price() );

        if ( $has_box ) {
            $unit_price += floatval( $cart_item['wm_box_price'] );
        }

        $cart_item['data']->set_price( $unit_price );
    }
}

// Cart/Checkout par "Pack of X" aur Box selection label dikhana
add_filter( 'woocommerce_get_item_data', 'wm_display_pack_in_cart', 10, 2 );
function wm_display_pack_in_cart( $item_data, $cart_item ) {
    if ( isset( $cart_item['wm_pack_qty'] ) ) {
        $item_data[] = array(
            'name'  => 'Pack',
            'value' => 'Pack of ' . intval( $cart_item['wm_pack_qty'] ),
        );
    }
    if ( isset( $cart_item['wm_box_label'] ) ) {
        $item_data[] = array(
            'name'  => 'Box/Packaging',
            'value' => esc_html( $cart_item['wm_box_label'] ),
        );
    }
    return $item_data;
}


// ============================================================
// WM Creations - BOX / PACKAGING OPTIONS (extra charge add hoti hai)
// ============================================================

add_action( 'add_meta_boxes', 'wm_add_box_options_meta_box' );
function wm_add_box_options_meta_box() {
    add_meta_box( 'wm_box_options', 'Box / Packaging Options', 'wm_box_options_meta_box_html', 'product', 'side', 'default' );
}
function wm_box_options_meta_box_html( $post ) {
    $enable     = get_post_meta( $post->ID, '_wm_box_enable', true );
    $box2_label = get_post_meta( $post->ID, '_wm_box2_label', true );
    $box2_price = get_post_meta( $post->ID, '_wm_box2_price', true );
    $box3_label = get_post_meta( $post->ID, '_wm_box3_label', true );
    $box3_price = get_post_meta( $post->ID, '_wm_box3_price', true );
    wp_nonce_field( 'wm_save_box_options', 'wm_box_options_nonce' );

    echo '<label style="display:block; margin-bottom:10px;"><input type="checkbox" name="wm_box_enable" value="yes" ' . checked( $enable, 'yes', false ) . '> Box options dikhayein</label>';
    echo '<p style="font-size:11px; color:#666; margin-top:0;">Pehla option hamesha "Simple Box — Free" hota hai.</p>';
    echo '<label style="display:block; font-weight:600; margin-bottom:2px;">Box 2 — Naam</label>';
    echo '<input type="text" name="wm_box2_label" value="' . esc_attr( $box2_label ) . '" style="width:100%; margin-bottom:6px;" placeholder="e.g. Gift Box">';
    echo '<label style="display:block; font-weight:600; margin-bottom:2px;">Box 2 — Price (Rs)</label>';
    echo '<input type="number" step="0.01" name="wm_box2_price" value="' . esc_attr( $box2_price ) . '" style="width:100%; margin-bottom:14px;" placeholder="e.g. 50">';
    echo '<label style="display:block; font-weight:600; margin-bottom:2px;">Box 3 — Naam</label>';
    echo '<input type="text" name="wm_box3_label" value="' . esc_attr( $box3_label ) . '" style="width:100%; margin-bottom:6px;" placeholder="e.g. Premium Box">';
    echo '<label style="display:block; font-weight:600; margin-bottom:2px;">Box 3 — Price (Rs)</label>';
    echo '<input type="number" step="0.01" name="wm_box3_price" value="' . esc_attr( $box3_price ) . '" style="width:100%;" placeholder="e.g. 250">';
}
add_action( 'save_post', 'wm_save_box_options_meta_box' );
function wm_save_box_options_meta_box( $post_id ) {
    if ( ! isset( $_POST['wm_box_options_nonce'] ) || ! wp_verify_nonce( $_POST['wm_box_options_nonce'], 'wm_save_box_options' ) ) {
        return;
    }
    update_post_meta( $post_id, '_wm_box_enable', isset( $_POST['wm_box_enable'] ) ? 'yes' : '' );
    update_post_meta( $post_id, '_wm_box2_label', isset( $_POST['wm_box2_label'] ) ? sanitize_text_field( $_POST['wm_box2_label'] ) : '' );
    update_post_meta( $post_id, '_wm_box2_price', isset( $_POST['wm_box2_price'] ) ? sanitize_text_field( $_POST['wm_box2_price'] ) : '' );
    update_post_meta( $post_id, '_wm_box3_label', isset( $_POST['wm_box3_label'] ) ? sanitize_text_field( $_POST['wm_box3_label'] ) : '' );
    update_post_meta( $post_id, '_wm_box3_price', isset( $_POST['wm_box3_price'] ) ? sanitize_text_field( $_POST['wm_box3_price'] ) : '' );
}

// Frontend: Box selection cards
add_action( 'woocommerce_before_add_to_cart_button', 'wm_render_box_options', 7 );
function wm_render_box_options() {
    global $product;

    if ( ! $product ) {
        return;
    }

    $enable = get_post_meta( $product->get_id(), '_wm_box_enable', true );
    if ( ! $enable ) {
        return;
    }

    $box2_label = get_post_meta( $product->get_id(), '_wm_box2_label', true );
    $box2_price = get_post_meta( $product->get_id(), '_wm_box2_price', true );
    $box3_label = get_post_meta( $product->get_id(), '_wm_box3_label', true );
    $box3_price = get_post_meta( $product->get_id(), '_wm_box3_price', true );

    echo '<div class="wm-box-options">';
    echo '<p class="wm-pack-title">Box / Packaging Select Karein</p>';
    echo '<div class="wm-pack-cards">';

    echo '<label class="wm-pack-card wm-pack-selected" data-price="0">
            <input type="radio" name="wm_box_select" value="0" checked style="display:none;">
            <span class="wm-pack-name">Simple Box</span>
            <span class="wm-pack-price">Free</span>
          </label>';

    if ( $box2_price !== '' ) {
        echo '<label class="wm-pack-card" data-price="' . esc_attr( floatval( $box2_price ) ) . '">
                <input type="radio" name="wm_box_select" value="' . esc_attr( floatval( $box2_price ) ) . '" style="display:none;">
                <span class="wm-pack-name">' . esc_html( $box2_label ? $box2_label : 'Box 2' ) . '</span>
                <span class="wm-pack-price">+Rs ' . number_format( floatval( $box2_price ) ) . '</span>
              </label>';
    }

    if ( $box3_price !== '' ) {
        echo '<label class="wm-pack-card" data-price="' . esc_attr( floatval( $box3_price ) ) . '">
                <input type="radio" name="wm_box_select" value="' . esc_attr( floatval( $box3_price ) ) . '" style="display:none;">
                <span class="wm-pack-name">' . esc_html( $box3_label ? $box3_label : 'Box 3' ) . '</span>
                <span class="wm-pack-price">+Rs ' . number_format( floatval( $box3_price ) ) . '</span>
              </label>';
    }

    echo '</div></div>';
}

// Cart mein box selection + uski price save karna (security: sirf configured prices hi accept hongi)
add_filter( 'woocommerce_add_cart_item_data', 'wm_add_box_data_to_cart', 21, 2 );
function wm_add_box_data_to_cart( $cart_item_data, $product_id ) {
    if ( isset( $_POST['wm_box_select'] ) && $_POST['wm_box_select'] !== '' ) {
        $box_price = floatval( $_POST['wm_box_select'] );

        $box2_price = get_post_meta( $product_id, '_wm_box2_price', true );
        $box3_price = get_post_meta( $product_id, '_wm_box3_price', true );

        $valid_prices = array( 0.0 );
        if ( $box2_price !== '' ) {
            $valid_prices[] = floatval( $box2_price );
        }
        if ( $box3_price !== '' ) {
            $valid_prices[] = floatval( $box3_price );
        }

        if ( in_array( $box_price, $valid_prices, true ) ) {
            $cart_item_data['wm_box_price'] = $box_price;

            $label = 'Simple Box (Free)';
            if ( $box_price > 0 && $box2_price !== '' && $box_price === floatval( $box2_price ) ) {
                $label = get_post_meta( $product_id, '_wm_box2_label', true );
            } elseif ( $box_price > 0 && $box3_price !== '' && $box_price === floatval( $box3_price ) ) {
                $label = get_post_meta( $product_id, '_wm_box3_label', true );
            }
            $cart_item_data['wm_box_label'] = $label;
            $cart_item_data['unique_key']   = md5( microtime() . wp_rand() );
        }
    }
    return $cart_item_data;
}


// ============================================================
// WM Creations - SINGLE PRODUCT PAGE
// ============================================================

add_action( 'woocommerce_product_thumbnails', 'wm_video_button_on_gallery', 25 );
function wm_video_button_on_gallery() {
    global $product;

    if ( ! $product ) {
        return;
    }

    $video_url = get_post_meta( $product->get_id(), '_wm_youtube_url', true );

    if ( $video_url ) {
        $video_id = wm_get_youtube_id( $video_url );
        if ( $video_id ) {
            echo '<button type="button" class="wm-watch-video-btn" data-video-id="' . esc_attr( $video_id ) . '">▶ Watch Video</button>';
        }
    }
}

add_action( 'woocommerce_after_add_to_cart_button', 'wm_buy_now_button' );
function wm_buy_now_button() {
    global $product;
    echo '<a href="?add-to-cart=' . esc_attr( $product->get_id() ) . '&wm-buy-now=1" class="wm-buy-now-btn">⚡ Buy Now</a>';
}

// "Save Rs X (Y%)" badge - jab product sale par ho
add_action( 'woocommerce_single_product_summary', 'wm_savings_badge', 11 );
function wm_savings_badge() {
    global $product;

    if ( ! $product || ! $product->is_on_sale() ) {
        return;
    }

    $regular = floatval( $product->get_regular_price() );
    $sale    = floatval( $product->get_sale_price() );

    if ( $regular <= 0 || $sale <= 0 ) {
        return;
    }

    $save_amt = $regular - $sale;
    $save_pct = round( ( $save_amt / $regular ) * 100 );

    echo '<span class="wm-save-badge">Save Rs ' . number_format( $save_amt ) . ' (' . $save_pct . '%)</span>';
}

add_action( 'wp_head', 'wm_product_page_css' );
function wm_product_page_css() {
    if ( ! is_product() ) {
        return;
    }
    echo '<style>
        .woocommerce-product-gallery { position:relative; }
        .wm-watch-video-btn {
            position:absolute; bottom:18px; left:18px; z-index:10;
            display:flex; align-items:center; gap:8px;
            background:rgba(26,63,160,0.92); color:#fff; border:none;
            padding:11px 20px; border-radius:30px; font-size:14px; font-weight:700;
            cursor:pointer; box-shadow:0 6px 18px rgba(0,0,0,0.25);
            backdrop-filter:blur(4px); transition: all 0.25s ease;
        }
        .wm-watch-video-btn:hover { background:#1A3FA0; transform:translateY(-2px); box-shadow:0 10px 24px rgba(0,0,0,0.32); }

        .woocommerce div.product form.cart {
            display:flex; flex-wrap:wrap; align-items:center; gap:14px;
        }
        .woocommerce div.product form.cart .button {
            background:linear-gradient(135deg,#1A3FA0,#2B54C4); color:#fff; border:none;
            padding:14px 32px; border-radius:30px; font-weight:700; font-size:14px;
            letter-spacing:0.3px; box-shadow:0 6px 16px rgba(26,63,160,0.25);
            transition: all 0.25s ease; text-align:center;
        }
        .woocommerce div.product form.cart .button:hover { transform:translateY(-2px); box-shadow:0 10px 22px rgba(26,63,160,0.35); }
        .wm-buy-now-btn {
            display:inline-flex; align-items:center; justify-content:center; gap:8px; background:#fff; color:#1A3FA0;
            border:2px solid #1A3FA0; padding:12px 30px; border-radius:30px; font-weight:700;
            font-size:14px; text-decoration:none; transition: all 0.25s ease;
        }
        .wm-buy-now-btn:hover { background:#1A3FA0; color:#fff; transform:translateY(-2px); box-shadow:0 10px 22px rgba(26,63,160,0.25); }

        @media (max-width:700px) {
            .woocommerce div.product form.cart .button, .wm-buy-now-btn { width:100%; justify-content:center; text-align:center; }
            .wm-watch-video-btn { bottom:12px; left:12px; padding:9px 16px; font-size:12.5px; }
        }

        /* ---------- Conversion Boosters ---------- */
        .wm-badges-row { display:flex; gap:8px; margin:10px 0; flex-wrap:wrap; }
        .wm-badge { display:inline-flex; align-items:center; gap:4px; padding:6px 14px; border-radius:20px; font-size:12px; font-weight:700; }
        .wm-badge-best { background:#E8F8EE; color:#1E8E3E; border:1px solid #B7E4C7; }
        .wm-badge-fast { background:#FDEDEE; color:#D93025; border:1px solid #F5C2C7; }

        .wm-social-proof {
            display:inline-flex; align-items:center; gap:8px;
            background:#F1F5FB; border:1px solid #DCE5F5; border-radius:10px;
            padding:10px 16px; font-size:13.5px; color:#3A4A6B; margin:4px 0 14px;
        }

        .wm-freeship-box {
            background:#FFF9EE; border:1px solid #F5E3BE; border-radius:12px;
            padding:16px 18px; margin:14px 0;
        }
        .wm-freeship-label { margin:0 0 10px; font-size:14px; font-weight:700; color:#8A5B00; display:flex; justify-content:space-between; flex-wrap:wrap; gap:6px; }
        .wm-freeship-label span { font-weight:500; font-size:12.5px; color:#A9791E; }
        .wm-freeship-bar { background:#F0E2C0; height:8px; border-radius:10px; overflow:hidden; }
        .wm-freeship-fill { background:linear-gradient(90deg,#F5A623,#FFC94D); height:100%; border-radius:10px; transition:width .4s ease; }
        .wm-freeship-note { margin:8px 0 0; font-size:12.5px; color:#8A5B00; }
        .wm-freeship-note.wm-freeship-done { color:#1E8E3E; font-weight:700; }

        .wm-custom-notice {
            margin-top:16px; padding:16px 18px; background:#FFF8E1; border:1px solid #FFE082; border-radius:10px;
        }
        .wm-custom-notice p { margin:0 0 12px; font-size:13.5px; line-height:1.6; color:#664D03; }

        .wm-bulk-notice {
            margin-top:14px; padding:16px 18px; background:#F1F5FB; border:1px solid #DCE5F5; border-radius:10px;
        }
        .wm-bulk-notice p { margin:0 0 12px; font-size:13.5px; line-height:1.6; color:#3A4A6B; }

        .wm-whatsapp-btn {
            display:inline-flex; align-items:center; gap:8px; background:#25D366; color:#fff;
            padding:11px 22px; border-radius:8px; text-decoration:none; font-weight:700; font-size:14px;
            transition:all .25s ease;
        }
        .wm-whatsapp-btn:hover { background:#1EBE5A; transform:translateY(-2px); box-shadow:0 6px 14px rgba(37,211,102,0.3); }
        .wm-whatsapp-btn-outline {
            background:#fff; color:#1E8E3E; border:2px solid #25D366;
        }
        .wm-whatsapp-btn-outline:hover { background:#25D366; color:#fff; }

        .wm-custom-fields-box { margin:16px 0; padding:16px; background:#F8FAFD; border:1px solid #EAF0FA; border-radius:12px; }
        .wm-field-label { display:block; font-size:13px; font-weight:700; color:#1A3FA0; margin:0 0 6px; }
        .wm-field-input { width:100%; padding:11px 14px; border:1px solid #dde3ee; border-radius:8px; font-size:14px; margin-bottom:14px; box-sizing:border-box; background:#fff; }
        .wm-field-input:last-of-type { margin-bottom:0; }
        .wm-field-note { font-size:12px; color:#8A5B00; margin:6px 0 0; background:#FFF9EE; padding:8px 12px; border-radius:8px; border:1px solid #F5E3BE; }
        .wm-field-note a { color:#1A3FA0; font-weight:700; }

        .wm-pack-options { margin:16px 0; }
        .wm-pack-title { font-size:14px; font-weight:700; color:#1A3FA0; margin:0 0 10px; }
        .wm-pack-cards { display:flex; gap:10px; flex-wrap:wrap; }
        .wm-pack-card {
            position:relative; flex:1; min-width:100px; text-align:center; cursor:pointer;
            border:2px solid #dde3ee; border-radius:12px; padding:14px 10px; transition:all .2s ease;
            display:flex; flex-direction:column; gap:4px; background:#fff;
        }
        .wm-pack-card.wm-pack-selected { border-color:#1A3FA0; background:#F1F5FB; box-shadow:0 4px 12px rgba(26,63,160,0.15); }
        .wm-pack-name { font-weight:700; font-size:13.5px; color:#1A3FA0; }
        .wm-pack-price { font-size:14px; font-weight:800; color:#2B54C4; }
        .wm-pack-save { font-size:11px; font-weight:700; color:#1E8E3E; background:#E8F8EE; border-radius:12px; padding:2px 8px; align-self:center; }
        .wm-pack-best {
            position:absolute; top:-10px; right:-6px; background:#F5A623; color:#fff; font-size:9px; font-weight:800;
            padding:3px 8px; border-radius:10px; box-shadow:0 2px 6px rgba(0,0,0,0.2);
        }
        @media (max-width:700px) {
            .wm-pack-card { min-width:90px; padding:10px 6px; }
            .wm-pack-name { font-size:12px; }
            .wm-pack-price { font-size:12.5px; }
        }

        /* ---------- Overall Page Polish ---------- */
        .woocommerce div.product {
            max-width: 640px;
            margin: 0 auto;
            padding: 0 4px 40px;
        }
        @media (min-width: 992px) {
            .woocommerce div.product { max-width: 1100px; }
        }

        .product_title.entry-title {
            font-size: 26px;
            font-weight: 800;
            color: #16305E;
            line-height: 1.3;
            margin: 6px 0 8px;
        }

        .woocommerce div.product p.price,
        .woocommerce div.product span.price {
            color: #1A3FA0;
            font-size: 28px;
            font-weight: 800;
            display: flex;
            align-items: baseline;
            gap: 10px;
            flex-wrap: wrap;
            margin: 2px 0 12px;
        }
        .woocommerce div.product p.price del {
            color: #B0BDD4;
            font-size: 16px;
            font-weight: 500;
            opacity: 1;
        }
        .woocommerce div.product p.price ins {
            text-decoration: none;
            color: #1A3FA0;
        }

        .wm-save-badge {
            display: inline-block;
            background: linear-gradient(135deg,#EA4335,#FF7A59);
            color: #fff;
            font-size: 11.5px;
            font-weight: 800;
            padding: 5px 12px;
            border-radius: 20px;
            margin: 0 0 16px;
        }

        .woocommerce div.product .woocommerce-product-rating {
            margin: 0 0 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .woocommerce div.product .product_meta {
            margin-top: 26px;
            padding-top: 16px;
            border-top: 1px solid #EAF0FA;
            font-size: 12.5px;
            color: #8CA0C4;
            line-height: 1.9;
        }
        .woocommerce div.product .product_meta a {
            color: #1A3FA0;
            text-decoration: none;
            font-weight: 600;
        }

        /* Description / Reviews Tabs */
        .woocommerce-tabs {
            margin-top: 34px;
        }
        .woocommerce-tabs ul.tabs {
            display: flex;
            gap: 8px;
            list-style: none;
            margin: 0;
            padding: 0;
            border-bottom: 2px solid #EAF0FA;
            flex-wrap: wrap;
        }
        .woocommerce-tabs ul.tabs li {
            margin: 0 0 -2px;
        }
        .woocommerce-tabs ul.tabs li a {
            display: inline-block;
            padding: 10px 20px;
            background: #F1F5FB;
            color: #1A3FA0;
            border-radius: 10px 10px 0 0;
            text-decoration: none;
            font-weight: 700;
            font-size: 13.5px;
            transition: all 0.2s ease;
        }
        .woocommerce-tabs ul.tabs li.active a {
            background: #1A3FA0;
            color: #fff;
        }
        .woocommerce-tabs .panel {
            padding: 20px;
            background: #fff;
            border: 1px solid #EAF0FA;
            border-radius: 0 12px 12px 12px;
            font-size: 14px;
            line-height: 1.75;
            color: #3A4A6B;
        }
        .woocommerce-tabs .panel h2 {
            font-size: 17px;
            color: #1A3FA0;
            margin-top: 0;
        }

        /* Related Products */
        .related.products {
            margin-top: 40px;
            padding-top: 30px;
            border-top: 1px solid #EAF0FA;
        }
        .related.products > h2 {
            font-size: 20px;
            font-weight: 800;
            color: #1A3FA0;
            margin: 0 0 20px;
        }

        @media (max-width: 700px) {
            .product_title.entry-title { font-size: 20px; }
            .woocommerce div.product p.price,
            .woocommerce div.product span.price { font-size: 22px; }
            .woocommerce-tabs ul.tabs li a { padding: 8px 14px; font-size: 12.5px; }
            .woocommerce-tabs .panel { padding: 16px; font-size: 13px; }
        }
    </style>';
}


// ============================================================
// WM Creations - CONVERSION BOOSTERS (Badges, Social Proof, Free Shipping)
// ============================================================

// Admin: Bestseller / Fast Selling badge toggles (per product)
add_action( 'add_meta_boxes', 'wm_add_badges_meta_box' );
function wm_add_badges_meta_box() {
    add_meta_box( 'wm_product_badges', 'Product Badges', 'wm_badges_meta_box_html', 'product', 'side', 'default' );
}
function wm_badges_meta_box_html( $post ) {
    $bestseller  = get_post_meta( $post->ID, '_wm_bestseller', true );
    $fastselling = get_post_meta( $post->ID, '_wm_fast_selling', true );
    $trending    = get_post_meta( $post->ID, '_wm_trending', true );
    wp_nonce_field( 'wm_save_badges', 'wm_badges_nonce' );
    echo '<label style="display:block; margin-bottom:8px;"><input type="checkbox" name="wm_bestseller" value="yes" ' . checked( $bestseller, 'yes', false ) . '> ⭐ Bestseller badge dikhayein</label>';
    echo '<label style="display:block; margin-bottom:8px;"><input type="checkbox" name="wm_fast_selling" value="yes" ' . checked( $fastselling, 'yes', false ) . '> 📈 Fast Selling badge dikhayein</label>';
    echo '<label style="display:block;"><input type="checkbox" name="wm_trending" value="yes" ' . checked( $trending, 'yes', false ) . '> 🔥 Trending Now badge dikhayein</label>';
    echo '<p style="font-size:11px; color:#888; margin-top:8px;">Sirf tab check karein jab asal mein sach ho — customer trust ke liye zaroori hai.</p>';
}
add_action( 'save_post', 'wm_save_badges_meta_box' );
function wm_save_badges_meta_box( $post_id ) {
    if ( ! isset( $_POST['wm_badges_nonce'] ) || ! wp_verify_nonce( $_POST['wm_badges_nonce'], 'wm_save_badges' ) ) {
        return;
    }
    update_post_meta( $post_id, '_wm_bestseller', isset( $_POST['wm_bestseller'] ) ? 'yes' : '' );
    update_post_meta( $post_id, '_wm_fast_selling', isset( $_POST['wm_fast_selling'] ) ? 'yes' : '' );
    update_post_meta( $post_id, '_wm_trending', isset( $_POST['wm_trending'] ) ? 'yes' : '' );
}

// Asal sales data se "X customers bought in last N days" calculate karna (cached 1 ghanta)
function wm_get_recent_sales_count( $product_id, $days = 7 ) {
    $cache_key = 'wm_recent_sales_' . $product_id;
    $cached = get_transient( $cache_key );
    if ( false !== $cached ) {
        return intval( $cached );
    }

    $count = 0;
    $date_after = date( 'Y-m-d H:i:s', strtotime( '-' . intval( $days ) . ' days' ) );

    $orders = wc_get_orders( array(
        'status'       => array( 'completed', 'processing', 'on-hold' ),
        'date_created' => '>' . $date_after,
        'limit'        => -1,
        'return'       => 'ids',
    ) );

    foreach ( $orders as $order_id ) {
        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            continue;
        }
        foreach ( $order->get_items() as $item ) {
            if ( $item->get_product_id() == $product_id ) {
                $count += $item->get_quantity();
            }
        }
    }

    set_transient( $cache_key, $count, HOUR_IN_SECONDS );
    return $count;
}

// Badges moved above title (wm_product_top_badges).
// Social proof + free shipping moved to wm_product_extra_notices (before ATC).
// Kept as no-op for backward compatibility if anything still calls it.
function wm_conversion_boosters() {
    // intentionally empty — content relocated in product page redesign
}




// ============================================================
// WM Creations - SHOP / CATEGORY PAGE IMPROVEMENTS
// (4 products per row, no sidebar, banners, video badge)
// ============================================================

add_filter( 'generate_sidebar_layout', 'wm_shop_no_sidebar' );
function wm_shop_no_sidebar( $layout ) {
    if ( is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy() || is_product() ) {
        return 'no-sidebar';
    }
    return $layout;
}

add_action( 'wp', 'wm_remove_woo_sidebar' );
function wm_remove_woo_sidebar() {
    if ( is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy() || is_product() ) {
        remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
    }
}

add_filter( 'loop_shop_columns', 'wm_shop_columns' );
function wm_shop_columns() {
    return 4;
}

// Category banner field (wp-admin)
add_action( 'product_cat_add_form_fields', 'wm_category_banner_field_add' );
function wm_category_banner_field_add() {
    ?>
    <div class="form-field">
        <label for="wm_banner_url">Banner Image URL</label>
        <input type="url" name="wm_banner_url" id="wm_banner_url" value="" placeholder="https://...banner.jpg">
        <p class="description">Category page par top banner (1200x300 recommended). Khali = category thumbnail use hogi.</p>
    </div>
    <?php
}

add_action( 'product_cat_edit_form_fields', 'wm_category_banner_field_edit', 10, 2 );
function wm_category_banner_field_edit( $term, $taxonomy ) {
    $value = get_term_meta( $term->term_id, 'wm_banner_url', true );
    ?>
    <tr class="form-field">
        <th scope="row"><label for="wm_banner_url">Banner Image URL</label></th>
        <td>
            <input type="url" name="wm_banner_url" id="wm_banner_url" value="<?php echo esc_attr( $value ); ?>" style="width:100%;" placeholder="https://...banner.jpg">
            <p class="description">Category page par top banner. Khali = WooCommerce category thumbnail.</p>
        </td>
    </tr>
    <?php
}

add_action( 'created_product_cat', 'wm_save_category_banner' );
add_action( 'edited_product_cat', 'wm_save_category_banner' );
function wm_save_category_banner( $term_id ) {
    if ( isset( $_POST['wm_banner_url'] ) ) {
        update_term_meta( $term_id, 'wm_banner_url', esc_url_raw( $_POST['wm_banner_url'] ) );
    }
}

add_action( 'woocommerce_before_main_content', 'wm_archive_banner', 15 );
function wm_archive_banner() {
    if ( ! ( is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy() ) ) {
        return;
    }

    $banner_url = '';
    $banner_alt = '';

    if ( is_shop() ) {
        $shop_id = wc_get_page_id( 'shop' );
        if ( $shop_id && has_post_thumbnail( $shop_id ) ) {
            $banner_url = get_the_post_thumbnail_url( $shop_id, 'large' );
            $banner_alt = get_the_title( $shop_id );
        }
    } elseif ( is_product_category() ) {
        $term = get_queried_object();
        $banner_alt = $term->name;
        $custom = get_term_meta( $term->term_id, 'wm_banner_url', true );
        if ( $custom ) {
            $banner_url = $custom;
        } else {
            $thumb_id = get_term_meta( $term->term_id, 'thumbnail_id', true );
            if ( $thumb_id ) {
                $banner_url = wp_get_attachment_image_url( $thumb_id, 'large' );
            }
        }
    }

    if ( ! $banner_url ) {
        return;
    }

    echo '<div class="wm-archive-banner">
        <img src="' . esc_url( $banner_url ) . '" alt="' . esc_attr( $banner_alt ) . '">
    </div>';
}

// Product grid - stars, buttons
add_action( 'woocommerce_after_shop_loop_item_title', 'wm_loop_star_rating', 4 );
function wm_loop_star_rating() {
    global $product;

    if ( ! $product ) {
        return;
    }

    $average = $product->get_average_rating();
    $count   = $product->get_rating_count();
    $rating  = round( floatval( $average ) );

    echo '<div class="wm-loop-stars">';
    for ( $i = 1; $i <= 5; $i++ ) {
        echo ( $i <= $rating ) ? '★' : '☆';
    }
    echo '<span class="wm-loop-count">(' . intval( $count ) . ')</span>';
    echo '</div>';
}

add_action( 'woocommerce_after_shop_loop_item', 'wm_loop_buy_now_button', 15 );
function wm_loop_buy_now_button() {
    global $product;
    echo '<a href="?add-to-cart=' . esc_attr( $product->get_id() ) . '&wm-buy-now=1" class="wm-loop-buy-btn">Buy Now</a>';
}

add_action( 'woocommerce_after_shop_loop_item', 'wm_loop_btn_row_open', 9 );
function wm_loop_btn_row_open() {
    echo '<div class="wm-loop-btn-row">';
}
add_action( 'woocommerce_after_shop_loop_item', 'wm_loop_btn_row_close', 20 );
function wm_loop_btn_row_close() {
    echo '</div>';
}

// Video badge on product image
add_action( 'woocommerce_before_shop_loop_item_title', 'wm_loop_thumb_wrap_open', 9 );
function wm_loop_thumb_wrap_open() {
    echo '<div class="wm-thumb-wrap">';
}

add_action( 'woocommerce_before_shop_loop_item_title', 'wm_loop_video_badge', 11 );
function wm_loop_video_badge() {
    global $product;

    if ( ! $product ) {
        return;
    }

    $video_url = get_post_meta( $product->get_id(), '_wm_youtube_url', true );
    if ( ! $video_url ) {
        return;
    }

    $video_id = wm_get_youtube_id( $video_url );
    if ( ! $video_id ) {
        return;
    }

    echo '<button type="button" class="wm-video-badge" data-video-id="' . esc_attr( $video_id ) . '">
        <span class="wm-play-circle">▶</span>
        Watch Video
    </button>';
}

add_action( 'woocommerce_before_shop_loop_item_title', 'wm_loop_thumb_wrap_close', 12 );
function wm_loop_thumb_wrap_close() {
    echo '</div>';
}

add_action( 'wp_head', 'wm_shop_layout_css', 99 );
function wm_shop_layout_css() {
    if ( ! ( is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy() ) ) {
        return;
    }
    echo '<style>

        .woocommerce.no-sidebar .site-content,
        .woocommerce.no-sidebar .content-area,
        body.woocommerce-page.no-sidebar .site-content,
        body.woocommerce-page.no-sidebar .content-area {
            width: 100% !important;
            max-width: 100% !important;
        }
        .woocommerce.no-sidebar .site-main,
        body.woocommerce-page.no-sidebar .site-main {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 20px;
        }
        .woocommerce.no-sidebar #secondary,
        body.woocommerce-page.no-sidebar #secondary {
            display: none !important;
        }

        .wm-archive-banner {
            width: 100%;
            max-width: 1280px;
            margin: 0 auto 24px;
            padding: 0 20px;
            box-sizing: border-box;
        }
        .wm-archive-banner img {
            width: 100%;
            height: auto;
            max-height: 280px;
            object-fit: cover;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(26,63,160,0.12);
            display: block;
        }

        ul.products {
            display: grid !important;
            grid-template-columns: repeat(4, 1fr) !important;
            gap: 24px !important;
            list-style: none !important;
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
        }
        ul.products::before,
        ul.products::after {
            display: none !important;
            content: none !important;
        }
        ul.products li.product {
            background: #fff;
            border: 1px solid #EAF0FA;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(26,63,160,0.06);
            transition: all 0.3s ease;
            padding: 0 !important;
            text-align: center;
            position: relative;
            width: 100% !important;
            max-width: 100% !important;
            float: none !important;
            margin: 0 !important;
            clear: none !important;
            display: flex !important;
            flex-direction: column !important;
        }
        ul.products li.product:hover {
            transform: translateY(-6px);
            box-shadow: 0 14px 28px rgba(26,63,160,0.18);
            border-color: #4FA8E0;
        }

        .wm-thumb-wrap {
            position: relative;
            overflow: hidden;
            display: block;
            line-height: 0;
        }
        ul.products li.product a img {
            width: 100% !important;
            aspect-ratio: 1 / 1;
            object-fit: cover;
            border-radius: 0 !important;
            margin: 0 !important;
            height: auto !important;
        }
        .wm-video-badge {
            position: absolute;
            bottom: 8px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 6;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: rgba(26,63,160,0.93);
            color: #fff;
            border: 1.5px solid rgba(255,255,255,0.85);
            padding: 4px 10px;
            border-radius: 30px;
            font-size: 9px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            transition: all 0.25s ease;
            white-space: nowrap;
        }
        .wm-video-badge:hover {
            background: #25D366;
            border-color: #fff;
        }
        .wm-play-circle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 14px;
            height: 14px;
            background: #fff;
            color: #1A3FA0;
            border-radius: 50%;
            font-size: 6px;
            line-height: 1;
        }

        ul.products li.product .woocommerce-loop-product__title {
            font-size: 14px;
            font-weight: 700;
            color: #1A3FA0;
            padding: 14px 14px 2px;
            margin: 0;
        }
        ul.products li.product .wm-loop-stars {
            font-size: 12px;
            color: #F5A623;
            padding: 4px 14px 2px;
            letter-spacing: 1px;
        }
        ul.products li.product .wm-loop-count {
            color: #8CA0C4;
            font-size: 11px;
            margin-left: 4px;
        }
        ul.products li.product .price {
            font-size: 15px;
            font-weight: 800;
            color: #2B54C4;
            padding: 2px 14px 14px;
            display: block;
            flex-grow: 1;
        }
        ul.products li.product .wm-loop-btn-row {
            display: flex;
            gap: 8px;
            margin: 0 14px 16px;
            margin-top: auto;
        }
        ul.products li.product .button {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 !important;
            background: linear-gradient(135deg,#1A3FA0,#2B54C4);
            color: #fff !important;
            border: none;
            padding: 9px 4px;
            border-radius: 24px;
            font-weight: 700;
            font-size: 11.5px;
            text-decoration: none;
            text-align: center;
            line-height: normal;
            white-space: nowrap;
            transition: all 0.25s ease;
        }
        ul.products li.product .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 18px rgba(26,63,160,0.3);
        }
        ul.products li.product .wm-loop-buy-btn {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            color: #1A3FA0;
            border: 1.5px solid #1A3FA0;
            padding: 9px 4px;
            border-radius: 24px;
            font-weight: 700;
            font-size: 11.5px;
            text-decoration: none;
            text-align: center;
            white-space: nowrap;
            transition: all 0.25s ease;
        }
        ul.products li.product .wm-loop-buy-btn:hover {
            background: #1A3FA0;
            color: #fff;
            transform: translateY(-2px);
        }
        .onsale {
            position: absolute !important;
            top: 14px !important;
            left: 14px !important;
            right: auto !important;
            bottom: auto !important;
            background: linear-gradient(135deg,#EA4335,#FF7A59) !important;
            color: #fff !important;
            font-size: 11px !important;
            font-weight: 800 !important;
            letter-spacing: 0.5px !important;
            text-transform: uppercase !important;
            padding: 6px 14px !important;
            border-radius: 30px !important;
            box-shadow: 0 4px 12px rgba(234,67,53,0.4) !important;
            z-index: 8 !important;
            margin: 0 !important;
            width: auto !important;
            height: auto !important;
            min-width: 0 !important;
            min-height: 0 !important;
            line-height: normal !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            text-align: center !important;
        }
        .woocommerce-ordering select.orderby {
            border: 1.5px solid #dde3ee !important;
            border-radius: 10px !important;
            padding: 10px 16px !important;
            color: #1A3FA0 !important;
            font-weight: 600 !important;
            font-size: 14px !important;
            background: #F8FAFD !important;
        }

        @media (max-width: 1024px) {
            ul.products {
                grid-template-columns: repeat(3, 1fr) !important;
                gap: 18px !important;
            }
        }

        @media (max-width: 700px) {
            .woocommerce.no-sidebar .site-main,
            body.woocommerce-page.no-sidebar .site-main {
                padding: 0 6px !important;
            }
            .wm-archive-banner {
                padding: 0 6px !important;
            }
            ul.products {
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 8px !important;
            }
            ul.products li.product .woocommerce-loop-product__title { font-size: 11.5px; padding: 8px 8px 2px; }
            ul.products li.product .wm-loop-stars { font-size: 10px; padding: 3px 8px 2px; }
            ul.products li.product .price { font-size: 12.5px; padding: 2px 8px 8px; }
            ul.products li.product .wm-loop-btn-row { margin: 0 8px 10px; gap: 5px; }
            ul.products li.product .button,
            ul.products li.product .wm-loop-buy-btn { font-size: 9px; padding: 7px 2px; }
            .wm-archive-banner img { max-height: 140px; border-radius: 10px; }
            .wm-video-badge { font-size: 8px; padding: 3px 8px; gap: 3px; bottom: 6px; }
            .wm-play-circle { width: 11px; height: 11px; font-size: 5px; }
            .woocommerce-ordering select.orderby { width: 100% !important; }
        }
    </style>';
}

add_action( 'wp_footer', 'wm_video_modal_markup' );
function wm_video_modal_markup() {
    if ( ! ( is_product() || is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy() ) ) {
        return;
    }
    echo '<div id="wm-video-modal" class="wm-video-modal">
        <div class="wm-video-modal-inner">
            <button type="button" class="wm-video-modal-close" id="wm-video-modal-close" aria-label="Close">&times;</button>
            <div class="wm-video-modal-frame" id="wm-video-modal-frame"></div>
        </div>
    </div>';
}

add_action( 'wp_footer', 'wm_video_modal_js' );
function wm_video_modal_js() {
    if ( ! ( is_product() || is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy() ) ) {
        return;
    }
    echo '<style>
        .wm-video-modal {
            display:none;
            position:fixed;
            top:0; left:0; right:0; bottom:0;
            background:rgba(10,20,40,0.85);
            z-index:99999;
            align-items:center;
            justify-content:center;
            padding:20px;
        }
        .wm-video-modal.wm-open {
            display:flex;
        }
        .wm-video-modal-inner {
            position:relative;
            width:100%;
            max-width:900px;
            background:#000;
            border-radius:14px;
            overflow:hidden;
            box-shadow:0 20px 60px rgba(0,0,0,0.5);
        }
        .wm-video-modal-frame {
            position:relative;
            width:100%;
            padding-top:56.25%;
        }
        .wm-video-modal-frame iframe {
            position:absolute;
            top:0; left:0; width:100%; height:100%;
            border:none;
        }
        .wm-video-modal-close {
            position:absolute;
            top:-42px;
            right:0;
            background:#fff;
            color:#1A3FA0;
            border:none;
            width:34px;
            height:34px;
            border-radius:50%;
            font-size:22px;
            line-height:1;
            cursor:pointer;
            font-weight:700;
        }
        @media (max-width:700px) {
            .wm-video-modal-close { top:-40px; right:0; }
        }
    </style>
    <script>
        document.addEventListener("click", function (e) {
            var btn = e.target.closest(".wm-watch-video-btn, .wm-video-badge");
            if (btn) {
                var videoId = btn.getAttribute("data-video-id");
                if (!videoId) return;
                e.preventDefault();
                var frame = document.getElementById("wm-video-modal-frame");
                frame.innerHTML = \'<iframe src="https://www.youtube.com/embed/\' + videoId + \'?autoplay=1&rel=0" allow="autoplay; encrypted-media" allowfullscreen></iframe>\';
                document.getElementById("wm-video-modal").classList.add("wm-open");
            }
            if (e.target.id === "wm-video-modal-close" || e.target.id === "wm-video-modal") {
                document.getElementById("wm-video-modal").classList.remove("wm-open");
                document.getElementById("wm-video-modal-frame").innerHTML = "";
            }
        });

        // Grid + spacing ko JS se force apply karna - CSS caching/minify issue bypass karne ke liye
        (function () {
            var grid = document.querySelector("ul.products");
            if (!grid) return;

            function wmApplyGrid() {
                var w = window.innerWidth;
                var isMobile = w <= 700;
                var cols = 4;
                if (w <= 700) { cols = 2; }
                else if (w <= 1024) { cols = 3; }

                grid.style.setProperty("display", "grid", "important");
                grid.style.setProperty("grid-template-columns", "repeat(" + cols + ", 1fr)", "important");
                grid.style.setProperty("gap", (isMobile ? "8px" : "24px"), "important");
                grid.style.setProperty("width", "100%", "important");
                grid.style.setProperty("align-items", "stretch", "important");

                document.querySelectorAll("ul.products li.product").forEach(function (el) {
                    el.style.setProperty("display", "flex", "important");
                    el.style.setProperty("flex-direction", "column", "important");
                    el.style.setProperty("height", "100%", "important");
                    el.style.setProperty("box-sizing", "border-box", "important");
                });

                document.querySelectorAll("ul.products li.product .woocommerce-loop-product__title").forEach(function (el) {
                    el.style.setProperty("font-size", isMobile ? "11.5px" : "14px", "important");
                    el.style.setProperty("padding", isMobile ? "8px 8px 2px" : "14px 14px 2px", "important");
                    el.style.setProperty("box-sizing", "border-box", "important");
                });

                document.querySelectorAll("ul.products li.product .wm-loop-stars").forEach(function (el) {
                    el.style.setProperty("font-size", isMobile ? "10px" : "12px", "important");
                    el.style.setProperty("padding", isMobile ? "3px 8px 2px" : "4px 14px 2px", "important");
                });

                document.querySelectorAll("ul.products li.product .price").forEach(function (el) {
                    el.style.setProperty("font-size", isMobile ? "12.5px" : "15px", "important");
                    el.style.setProperty("padding", isMobile ? "2px 8px 8px" : "2px 14px 14px", "important");
                    el.style.setProperty("flex-grow", "1", "important");
                    el.style.setProperty("box-sizing", "border-box", "important");
                });

                document.querySelectorAll("ul.products li.product .wm-loop-btn-row").forEach(function (el) {
                    el.style.setProperty("margin", isMobile ? "0 8px 10px" : "0 14px 16px", "important");
                    el.style.setProperty("margin-top", "auto", "important");
                    el.style.setProperty("gap", isMobile ? "5px" : "8px", "important");
                    el.style.setProperty("display", "flex", "important");
                });

                document.querySelectorAll("ul.products li.product .button, ul.products li.product .wm-loop-buy-btn").forEach(function (el) {
                    el.style.setProperty("font-size", isMobile ? "9px" : "11.5px", "important");
                    el.style.setProperty("padding", isMobile ? "7px 2px" : "9px 4px", "important");
                    el.style.setProperty("display", "flex", "important");
                    el.style.setProperty("align-items", "center", "important");
                    el.style.setProperty("justify-content", "center", "important");
                    el.style.setProperty("flex", "1", "important");
                    el.style.setProperty("box-sizing", "border-box", "important");
                    el.style.setProperty("text-align", "center", "important");
                    el.style.setProperty("white-space", "nowrap", "important");
                });

                document.querySelectorAll(".wm-video-badge").forEach(function (el) {
                    el.style.setProperty("font-size", isMobile ? "8px" : "13px", "important");
                    el.style.setProperty("padding", isMobile ? "3px 8px" : "9px 18px", "important");
                    el.style.setProperty("gap", isMobile ? "3px" : "8px", "important");
                });
                document.querySelectorAll(".wm-play-circle").forEach(function (el) {
                    el.style.setProperty("width", isMobile ? "11px" : "26px", "important");
                    el.style.setProperty("height", isMobile ? "11px" : "26px", "important");
                    el.style.setProperty("font-size", isMobile ? "5px" : "11px", "important");
                });

                document.querySelectorAll(".site-main, .content-area, #primary, .inside-article").forEach(function (el) {
                    el.style.setProperty("max-width", "100%", "important");
                    el.style.setProperty("padding-left", isMobile ? "6px" : "20px", "important");
                    el.style.setProperty("padding-right", isMobile ? "6px" : "20px", "important");
                    el.style.setProperty("margin-left", "0", "important");
                    el.style.setProperty("margin-right", "0", "important");
                    el.style.setProperty("box-sizing", "border-box", "important");
                });

                document.querySelectorAll(".woocommerce-ordering select.orderby").forEach(function (el) {
                    el.style.setProperty("border", "1.5px solid #dde3ee", "important");
                    el.style.setProperty("border-radius", "10px", "important");
                    el.style.setProperty("padding", isMobile ? "8px 12px" : "10px 16px", "important");
                    el.style.setProperty("color", "#1A3FA0", "important");
                    el.style.setProperty("font-weight", "600", "important");
                    el.style.setProperty("font-size", isMobile ? "12.5px" : "14px", "important");
                    el.style.setProperty("background", "#F8FAFD", "important");
                    el.style.setProperty("width", isMobile ? "100%" : "auto", "important");
                    el.style.setProperty("box-sizing", "border-box", "important");
                });

                // Sale badge - attractive gradient pill (GP ke default circle ko override karna)
                document.querySelectorAll(".onsale").forEach(function (el) {
                    el.style.setProperty("position", "absolute", "important");
                    el.style.setProperty("top", "14px", "important");
                    el.style.setProperty("left", "14px", "important");
                    el.style.setProperty("right", "auto", "important");
                    el.style.setProperty("bottom", "auto", "important");
                    el.style.setProperty("background", "linear-gradient(135deg,#EA4335,#FF7A59)", "important");
                    el.style.setProperty("color", "#fff", "important");
                    el.style.setProperty("font-size", isMobile ? "9px" : "11px", "important");
                    el.style.setProperty("font-weight", "800", "important");
                    el.style.setProperty("letter-spacing", "0.5px", "important");
                    el.style.setProperty("text-transform", "uppercase", "important");
                    el.style.setProperty("padding", isMobile ? "4px 10px" : "6px 14px", "important");
                    el.style.setProperty("border-radius", "30px", "important");
                    el.style.setProperty("box-shadow", "0 4px 12px rgba(234,67,53,0.4)", "important");
                    el.style.setProperty("z-index", "8", "important");
                    el.style.setProperty("margin", "0", "important");
                    el.style.setProperty("width", "auto", "important");
                    el.style.setProperty("height", "auto", "important");
                    el.style.setProperty("min-width", "0", "important");
                    el.style.setProperty("min-height", "0", "important");
                    el.style.setProperty("line-height", "normal", "important");
                    el.style.setProperty("display", "inline-flex", "important");
                    el.style.setProperty("align-items", "center", "important");
                    el.style.setProperty("justify-content", "center", "important");
                });
            }

            wmApplyGrid();
            window.addEventListener("resize", wmApplyGrid);
        })();
    </script>';
}


// ============================================================
// WM Creations - PRELOADER (Loading Screen)
// ============================================================

add_action( 'wp_body_open', 'wm_preloader_markup' );
function wm_preloader_markup() {
    echo '<div id="wm-preloader">
        <div class="wm-preloader-inner">
            <img src="https://wmshop.pk/wp-content/uploads/2026/07/wm-creations-logo.jpg" alt="WM Creations" class="wm-preloader-logo">
            <p class="wm-preloader-text">Surprise taiyar ho raha hai, thoda intezar...</p>
        </div>
    </div>';
}

add_action( 'wp_head', 'wm_preloader_css' );
function wm_preloader_css() {
    echo '<style>
        #wm-preloader {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: #fff;
            z-index: 999999;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: opacity 0.5s ease, visibility 0.5s ease;
        }
        #wm-preloader.wm-preloader-hide {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }
        .wm-preloader-inner {
            text-align: center;
        }
        .wm-preloader-logo {
            width: 90px;
            height: auto;
            animation: wmPreloaderPulse 1.4s ease-in-out infinite;
        }
        .wm-preloader-text {
            margin-top: 16px;
            font-size: 14px;
            font-weight: 600;
            color: #1A3FA0;
            letter-spacing: 0.2px;
        }
        @keyframes wmPreloaderPulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(0.85); opacity: 0.6; }
        }
        @media (max-width: 700px) {
            .wm-preloader-logo { width: 70px; }
            .wm-preloader-text { font-size: 12.5px; }
        }
    </style>';
}

add_action( 'wp_footer', 'wm_preloader_js' );
function wm_preloader_js() {
    echo '<script>
        window.addEventListener("load", function () {
            var loader = document.getElementById("wm-preloader");
            if (loader) {
                setTimeout(function () {
                    loader.classList.add("wm-preloader-hide");
                }, 400);
            }
        });
    </script>';
}


// ============================================================
// WM Creations - PRODUCT PAGE REDESIGN (layout + UI)
// Integrated into child theme functions.php (do not also require
// wm-product-page-redesign.php — that would duplicate functions).
// ============================================================

add_action( 'wp', 'wm_restructure_product_summary' );
function wm_restructure_product_summary() {
    if ( ! is_product() ) {
        return;
    }

    // Old positions remove (buy now stays on after_add_to_cart_button @10)
    remove_action( 'woocommerce_single_product_summary', 'wm_conversion_boosters', 15 );
    remove_action( 'woocommerce_single_product_summary', 'wm_whatsapp_order_button', 25 );
    remove_action( 'woocommerce_single_product_summary', 'wm_bulk_reseller_notice', 26 );
    remove_action( 'woocommerce_before_add_to_cart_button', 'wm_render_custom_fields' );

    // Badges bilkul upar (title se pehle)
    add_action( 'woocommerce_single_product_summary', 'wm_product_top_badges', 3 );

    // Custom name/picture: variations se PEHLE (variable) / ATC se pehle (simple)
    add_action( 'woocommerce_before_variations_form', 'wm_render_custom_fields_redesign', 5 );
    add_action( 'woocommerce_before_add_to_cart_button', 'wm_render_custom_fields_simple_only', 3 );

    // Free shipping + social + bulk — pack/variations ke baad, buttons se pehle
    // Pack remains on woocommerce_before_add_to_cart_button priority 5
    add_action( 'woocommerce_before_add_to_cart_button', 'wm_product_extra_notices', 20 );

    // Order through WhatsApp — after Buy Now (priority 10)
    add_action( 'woocommerce_after_add_to_cart_button', 'wm_order_via_whatsapp_button', 20 );
}

function wm_product_top_badges() {
    global $product;
    if ( ! $product ) {
        return;
    }

    $bestseller  = get_post_meta( $product->get_id(), '_wm_bestseller', true );
    $fastselling = get_post_meta( $product->get_id(), '_wm_fast_selling', true );
    $trending    = get_post_meta( $product->get_id(), '_wm_trending', true );

    if ( ! $bestseller && ! $fastselling && ! $trending ) {
        return;
    }

    echo '<div class="wm-badges-row wm-badges-top">';
    if ( $bestseller ) {
        echo '<span class="wm-badge wm-badge-best">⭐ Bestseller</span>';
    }
    if ( $fastselling ) {
        echo '<span class="wm-badge wm-badge-fast">📈 Fast Selling</span>';
    }
    if ( $trending ) {
        echo '<span class="wm-badge wm-badge-trend">🔥 Trending Now</span>';
    }
    echo '</div>';
}

function wm_product_extra_notices() {
    global $product;
    if ( ! $product ) {
        return;
    }

    // Social proof
    $recent = wm_get_recent_sales_count( $product->get_id(), 7 );
    if ( $recent > 0 ) {
        echo '<div class="wm-social-proof">👥 <b>' . intval( $recent ) . '+</b> customers bought this in the last 7 days</div>';
    }

    // Free shipping box
    $threshold = 2000;
    $price     = floatval( $product->get_price() );
    $percent   = $threshold > 0 ? min( 100, ( $price / $threshold ) * 100 ) : 0;

    echo '<div class="wm-freeship-box">';
    echo '<p class="wm-freeship-label">🚚 Free Shipping <span>Rs ' . number_format( $threshold ) . '+ Order Par — No Code Required</span></p>';
    echo '<div class="wm-freeship-bar"><div class="wm-freeship-fill" style="width:' . esc_attr( $percent ) . '%;"></div></div>';
    if ( $price >= $threshold ) {
        echo '<p class="wm-freeship-note wm-freeship-done">🎉 Ye product free shipping ke liye qualify karta hai!</p>';
    } else {
        $remaining = $threshold - $price;
        echo '<p class="wm-freeship-note">Sirf Rs ' . number_format( $remaining ) . ' aur order karein free shipping ke liye</p>';
    }
    echo '</div>';

    // Bulk / reseller
    $product_name    = $product->get_name();
    $product_link    = get_permalink( $product->get_id() );
    $whatsapp_number = '923337888820';
    $message         = "Assalam-o-Alaikum, mujhe bulk order ya reseller discount ke baare mein maloomat chahiye.\n\nProduct: " . $product_name . "\nLink: " . $product_link;
    $whatsapp_link   = 'https://wa.me/' . $whatsapp_number . '?text=' . rawurlencode( $message );

    echo '<div class="wm-bulk-notice">
        <p>📦 <b>Bulk Quantity Order ya Reseller Hain?</b> Bade quantity orders aur resellers ke liye khaas discount diya jata hai.</p>
        <a href="' . esc_url( $whatsapp_link ) . '" target="_blank" class="wm-whatsapp-btn wm-whatsapp-btn-outline">📱 WhatsApp Par Rabta Karein</a>
    </div>';
}

function wm_order_via_whatsapp_button() {
    global $product;
    if ( ! $product ) {
        return;
    }

    $product_name    = $product->get_name();
    $product_link    = get_permalink( $product->get_id() );
    $whatsapp_number = '923337888820';
    $message         = "Assalam-o-Alaikum, main ye order karna chahta/chahti hoon, mujhe iski mazeed detail farmayen.\n\nProduct: " . $product_name . "\nLink: " . $product_link;
    $whatsapp_link   = 'https://wa.me/' . $whatsapp_number . '?text=' . rawurlencode( $message );

    echo '<a href="' . esc_url( $whatsapp_link ) . '" target="_blank" class="wm-order-whatsapp-btn">📱 Order through WhatsApp</a>';
}

add_action( 'wp_head', 'wm_product_redesign_assets', 100 );
function wm_product_redesign_assets() {
    if ( ! is_product() ) {
        return;
    }
    ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* Full width product, no sidebar feel */
        body.single-product.no-sidebar #secondary,
        body.single-product #secondary { display:none !important; }
        body.single-product.no-sidebar .content-area,
        body.single-product .content-area,
        body.single-product.no-sidebar .site-main {
            width:100% !important;
            max-width:100% !important;
        }
        .woocommerce div.product {
            max-width: 1180px !important;
            margin: 0 auto !important;
            padding: 10px 16px 50px !important;
        }

        /* Title */
        .product_title.entry-title {
            font-family: "Plus Jakarta Sans", sans-serif !important;
            font-size: 28px !important;
            font-weight: 800 !important;
            color: #122B5C !important;
            line-height: 1.28 !important;
            letter-spacing: -0.02em !important;
            margin: 8px 0 10px !important;
        }

        /* Badges top */
        .wm-badges-top {
            display:flex;
            gap:8px;
            flex-wrap:wrap;
            margin: 0 0 10px;
        }
        .wm-badge-trend {
            background:#FFF1E8;
            color:#D35400;
            border:1px solid #FFD0A8;
        }

        /* Gallery: rounded main + thumbs BELOW */
        .woocommerce div.product div.images,
        .woocommerce-product-gallery {
            display:block !important;
        }
        .woocommerce-product-gallery__wrapper,
        .woocommerce-product-gallery .flex-viewport {
            width:100% !important;
            margin:0 0 12px !important;
            border-radius:18px !important;
            overflow:hidden !important;
            box-shadow:0 10px 28px rgba(26,63,160,0.12) !important;
        }
        .woocommerce-product-gallery__image,
        .woocommerce-product-gallery__image img,
        .woocommerce div.product div.images img,
        .woocommerce-product-gallery .flex-viewport img {
            border-radius:18px !important;
        }
        .woocommerce-product-gallery ol.flex-control-nav,
        .woocommerce-product-gallery ol.flex-control-thumbs,
        .woocommerce-product-gallery .flex-control-thumbs {
            display:flex !important;
            flex-direction:row !important;
            flex-wrap:wrap !important;
            width:100% !important;
            max-width:100% !important;
            max-height:none !important;
            overflow:visible !important;
            margin:0 !important;
            padding:0 !important;
            gap:8px !important;
            float:none !important;
        }
        .woocommerce-product-gallery .flex-control-thumbs li {
            width:72px !important;
            min-width:72px !important;
            height:72px !important;
            float:none !important;
            margin:0 !important;
        }
        .woocommerce-product-gallery .flex-control-thumbs li img {
            border-radius:10px !important;
            border:2px solid #EAF0FA !important;
            opacity:1 !important;
            width:72px !important;
            height:72px !important;
            object-fit:cover !important;
            cursor:pointer !important;
            display:block !important;
        }
        .woocommerce-product-gallery .flex-control-thumbs li img.flex-active,
        .woocommerce-product-gallery .flex-control-thumbs li img:hover {
            border-color:#1A3FA0 !important;
            box-shadow:0 4px 12px rgba(26,63,160,0.2) !important;
        }

        /* Variation selects -> button UI */
        .woocommerce div.product form.variations_form table.variations {
            width:100%;
            border:0;
            margin:14px 0;
        }
        .woocommerce div.product form.variations_form table.variations td,
        .woocommerce div.product form.variations_form table.variations th {
            display:block;
            width:100%;
            padding:0;
            border:0;
            background:transparent;
        }
        .woocommerce div.product form.variations_form table.variations label {
            display:block;
            font-family:"Plus Jakarta Sans",sans-serif;
            font-size:14px;
            font-weight:700;
            color:#1A3FA0;
            margin:12px 0 8px;
        }
        .woocommerce div.product form.variations_form table.variations select {
            position:absolute !important;
            left:-9999px !important;
            width:1px !important;
            height:1px !important;
            opacity:0 !important;
        }
        .wm-swatch-wrap {
            display:flex;
            flex-wrap:wrap;
            gap:8px;
            margin-bottom:6px;
        }
        .wm-swatch-btn {
            appearance:none;
            border:1.5px solid #DCE5F5;
            background:#F8FAFD;
            color:#1A3FA0;
            border-radius:999px;
            padding:10px 16px;
            font-size:13px;
            font-weight:700;
            font-family:"Plus Jakarta Sans",sans-serif;
            cursor:pointer;
            transition:all .2s ease;
            line-height:1.2;
        }
        .wm-swatch-btn:hover {
            border-color:#4FA8E0;
            background:#EEF5FF;
            transform:translateY(-1px);
        }
        .wm-swatch-btn.wm-swatch-active {
            background:linear-gradient(135deg,#1A3FA0,#2B54C4);
            border-color:#1A3FA0;
            color:#fff;
            box-shadow:0 6px 14px rgba(26,63,160,0.25);
        }

        /* Custom fields redesign */
        .wm-custom-fields-v2 {
            margin:16px 0 8px;
            padding:18px;
            background:linear-gradient(180deg,#F7FAFF 0%, #EEF4FF 100%);
            border:1px solid #D9E6FA;
            border-radius:16px;
            box-shadow:0 6px 18px rgba(26,63,160,0.06);
        }
        .wm-custom-fields-heading {
            margin:0 0 14px;
            font-family:"Plus Jakarta Sans",sans-serif;
            font-size:15px;
            font-weight:800;
            color:#1A3FA0;
        }
        .wm-upload-wrap {
            display:flex;
            align-items:center;
            gap:10px;
            flex-wrap:wrap;
            margin-bottom:8px;
        }
        .wm-file-input {
            position:absolute !important;
            left:-9999px !important;
            opacity:0 !important;
            width:1px !important;
            height:1px !important;
        }
        .wm-upload-btn {
            display:inline-flex;
            align-items:center;
            gap:6px;
            background:#fff;
            border:1.5px dashed #4FA8E0;
            color:#1A3FA0;
            border-radius:12px;
            padding:12px 16px;
            font-weight:700;
            font-size:13px;
            cursor:pointer;
        }
        .wm-upload-filename {
            font-size:12.5px;
            color:#5A6B8C;
        }
        .wm-custom-mini-note {
            margin:10px 0 0;
            font-size:12.5px;
            color:#8A5B00;
            background:#FFF9EE;
            border:1px solid #F5E3BE;
            border-radius:10px;
            padding:10px 12px;
        }

        /* ATC + Buy Now + WhatsApp */
        .woocommerce div.product form.cart {
            display:flex !important;
            flex-wrap:wrap !important;
            align-items:stretch !important;
            gap:12px !important;
        }
        .woocommerce div.product form.cart .single_add_to_cart_button,
        .woocommerce div.product form.cart .button {
            flex:1 1 220px;
            min-height:52px;
            background:linear-gradient(135deg,#1A3FA0,#2B54C4) !important;
            color:#fff !important;
            border:none !important;
            border-radius:14px !important;
            font-family:"Plus Jakarta Sans",sans-serif !important;
            font-weight:800 !important;
            font-size:15px !important;
            letter-spacing:0.2px;
            box-shadow:0 10px 22px rgba(26,63,160,0.28) !important;
        }
        .wm-buy-now-btn {
            flex:1 1 220px;
            min-height:52px;
            display:inline-flex !important;
            align-items:center;
            justify-content:center;
            background:#fff !important;
            color:#1A3FA0 !important;
            border:2px solid #1A3FA0 !important;
            border-radius:14px !important;
            font-family:"Plus Jakarta Sans",sans-serif !important;
            font-weight:800 !important;
            font-size:15px !important;
            text-decoration:none !important;
            box-shadow:0 6px 16px rgba(26,63,160,0.08);
        }
        .wm-buy-now-btn:hover {
            background:#1A3FA0 !important;
            color:#fff !important;
        }
        .wm-order-whatsapp-btn {
            flex:1 1 100%;
            display:inline-flex !important;
            align-items:center;
            justify-content:center;
            gap:8px;
            min-height:52px;
            margin-top:4px;
            background:linear-gradient(135deg,#25D366,#1EBE57) !important;
            color:#fff !important;
            border-radius:14px !important;
            font-family:"Plus Jakarta Sans",sans-serif !important;
            font-weight:800 !important;
            font-size:15px !important;
            text-decoration:none !important;
            box-shadow:0 10px 22px rgba(37,211,102,0.28);
        }
        .wm-order-whatsapp-btn:hover {
            filter:brightness(0.96);
            transform:translateY(-1px);
        }

        /* Extra plugin WhatsApp hide on product */
        body.single-product .nta-woo-products-button {
            display:none !important;
        }

        /* ATC + Buy Now no overlap */
        .woocommerce div.product form.cart .quantity,
        .woocommerce div.product form.cart .single_add_to_cart_button,
        .woocommerce div.product form.cart .button,
        .wm-buy-now-btn {
            float:none !important;
            position:static !important;
            margin:0 !important;
        }

        /* Slightly tighter product option spacing */
        .wm-custom-fields-v2 { margin:8px 0 !important; padding:12px !important; }
        .woocommerce div.product form.variations_form table.variations { margin:4px 0 8px !important; }
        .woocommerce div.product form.variations_form table.variations label { margin:8px 0 6px !important; }
        .wm-swatch-wrap { gap:6px !important; margin:0 0 6px !important; }
        .wm-swatch-btn { padding:8px 12px !important; font-size:12.5px !important; }
        .wm-pack-options, .wm-box-options { margin:8px 0 !important; }
        .wm-pack-title { margin:0 0 8px !important; }
        .wm-pack-cards { gap:8px !important; }
        .wm-pack-card { padding:10px 8px !important; }
        .wm-freeship-box, .wm-bulk-notice { margin:8px 0 !important; padding:12px 14px !important; }

        @media (max-width: 700px) {
            .product_title.entry-title { font-size:22px !important; }
            .woocommerce-product-gallery .flex-control-thumbs li,
            .woocommerce-product-gallery .flex-control-thumbs li img {
                width:64px !important;
                min-width:64px !important;
                height:64px !important;
            }
            .woocommerce div.product form.cart .single_add_to_cart_button,
            .woocommerce div.product form.cart .button,
            .wm-buy-now-btn,
            .wm-order-whatsapp-btn {
                flex:1 1 100%;
                width:100%;
            }
        }
    </style>
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        // File name preview
        var fileInput = document.getElementById("wm_custom_picture");
        if (fileInput) {
            fileInput.addEventListener("change", function () {
                var nameEl = document.querySelector(".wm-upload-filename");
                if (nameEl) {
                    nameEl.textContent = (fileInput.files && fileInput.files[0]) ? fileInput.files[0].name : "No file chosen";
                }
            });
        }

        // Convert variation dropdowns into buttons
        function buildSwatches() {
            document.querySelectorAll("form.variations_form select").forEach(function (select) {
                if (select.dataset.wmSwatchReady === "1") return;
                select.dataset.wmSwatchReady = "1";

                var wrap = document.createElement("div");
                wrap.className = "wm-swatch-wrap";

                Array.prototype.forEach.call(select.options, function (opt) {
                    if (!opt.value) return;
                    var btn = document.createElement("button");
                    btn.type = "button";
                    btn.className = "wm-swatch-btn";
                    btn.textContent = opt.text;
                    btn.dataset.value = opt.value;

                    if (select.value === opt.value) {
                        btn.classList.add("wm-swatch-active");
                    }

                    btn.addEventListener("click", function () {
                        wrap.querySelectorAll(".wm-swatch-btn").forEach(function (b) {
                            b.classList.remove("wm-swatch-active");
                        });
                        btn.classList.add("wm-swatch-active");
                        select.value = opt.value;
                        select.dispatchEvent(new Event("change", { bubbles: true }));
                        if (window.jQuery) {
                            jQuery(select).trigger("change");
                        }
                    });

                    wrap.appendChild(btn);
                });

                select.parentNode.insertBefore(wrap, select.nextSibling);
            });
        }

        buildSwatches();

        // Rebuild after Woo variation updates
        if (window.jQuery) {
            jQuery(document.body).on("woocommerce_update_variation_values check_variations", function () {
                document.querySelectorAll(".wm-swatch-wrap").forEach(function (el) { el.remove(); });
                document.querySelectorAll("form.variations_form select").forEach(function (select) {
                    select.dataset.wmSwatchReady = "0";
                });
                buildSwatches();
            });
        }
    });
    </script>
    <?php
}

// ============================================================
// WM Creations - CART / CHECKOUT BUTTON FIX
// ============================================================

add_action( 'wp_head', 'wm_cart_checkout_btn_css' );
function wm_cart_checkout_btn_css() {
    if ( ! is_cart() && ! is_checkout() ) {
        return;
    }
    echo '<style>
        .woocommerce-cart .wc-proceed-to-checkout a.checkout-button,
        .woocommerce-cart a.checkout-button.button.alt,
        .woocommerce a.checkout-button {
            color: #ffffff !important;
            background-color: #1A3FA0 !important;
            border-color: #1A3FA0 !important;
            font-weight: 700 !important;
        }
        .woocommerce-cart .wc-proceed-to-checkout a.checkout-button:hover,
        .woocommerce-cart a.checkout-button.button.alt:hover,
        .woocommerce a.checkout-button:hover {
            color: #ffffff !important;
            background-color: #2B54C4 !important;
            border-color: #2B54C4 !important;
        }
    </style>';
}
