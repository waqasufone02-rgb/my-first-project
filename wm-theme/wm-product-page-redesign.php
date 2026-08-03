<?php
/**
 * WM Creations - Product Page Redesign
 * Is poori file ko child theme ke functions.php ke END mein paste kar dein.
 * (Opening <?php tag dobara mat lagana agar functions.php mein pehle se hai)
 */

// ============================================================
// WM Creations - PRODUCT PAGE REDESIGN (layout + UI)
// ============================================================

// 1) Single product se sidebar hatao
add_filter( 'generate_sidebar_layout', 'wm_product_no_sidebar', 20 );
function wm_product_no_sidebar( $layout ) {
    if ( is_product() ) {
        return 'no-sidebar';
    }
    return $layout;
}

add_action( 'wp', 'wm_remove_product_sidebar' );
function wm_remove_product_sidebar() {
    if ( is_product() ) {
        remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
    }
}

// 2) Trending badge admin option
add_action( 'add_meta_boxes', 'wm_add_trending_to_badges_box', 20 );
function wm_add_trending_to_badges_box() {
    // existing badges box ko extend karne ke liye save hook already alag handle
}

add_action( 'save_post', 'wm_save_trending_badge_meta' );
function wm_save_trending_badge_meta( $post_id ) {
    if ( ! isset( $_POST['wm_badges_nonce'] ) || ! wp_verify_nonce( $_POST['wm_badges_nonce'], 'wm_save_badges' ) ) {
        return;
    }
    update_post_meta( $post_id, '_wm_trending', isset( $_POST['wm_trending'] ) ? 'yes' : '' );
}

// Badges meta box HTML override - trending checkbox add
add_action( 'add_meta_boxes', 'wm_replace_badges_meta_box', 30 );
function wm_replace_badges_meta_box() {
    remove_meta_box( 'wm_product_badges', 'product', 'side' );
    add_meta_box( 'wm_product_badges', 'Product Badges', 'wm_badges_meta_box_html_v2', 'product', 'side', 'default' );
}
function wm_badges_meta_box_html_v2( $post ) {
    $bestseller  = get_post_meta( $post->ID, '_wm_bestseller', true );
    $fastselling = get_post_meta( $post->ID, '_wm_fast_selling', true );
    $trending    = get_post_meta( $post->ID, '_wm_trending', true );
    wp_nonce_field( 'wm_save_badges', 'wm_badges_nonce' );
    echo '<label style="display:block; margin-bottom:8px;"><input type="checkbox" name="wm_bestseller" value="yes" ' . checked( $bestseller, 'yes', false ) . '> ⭐ Bestseller badge dikhayein</label>';
    echo '<label style="display:block; margin-bottom:8px;"><input type="checkbox" name="wm_fast_selling" value="yes" ' . checked( $fastselling, 'yes', false ) . '> 📈 Fast Selling badge dikhayein</label>';
    echo '<label style="display:block;"><input type="checkbox" name="wm_trending" value="yes" ' . checked( $trending, 'yes', false ) . '> 🔥 Trending Now badge dikhayein</label>';
    echo '<p style="font-size:11px; color:#888; margin-top:8px;">Sirf tab check karein jab asal mein sach ho — customer trust ke liye zaroori hai.</p>';
}

// 3) Purane booster/badges hooks hatao, naya order lagao
add_action( 'wp', 'wm_restructure_product_summary' );
function wm_restructure_product_summary() {
    if ( ! is_product() ) {
        return;
    }

    // Old positions remove
    remove_action( 'woocommerce_single_product_summary', 'wm_conversion_boosters', 15 );
    remove_action( 'woocommerce_single_product_summary', 'wm_whatsapp_order_button', 25 );
    remove_action( 'woocommerce_single_product_summary', 'wm_bulk_reseller_notice', 26 );
    remove_action( 'woocommerce_before_add_to_cart_button', 'wm_render_custom_fields' );
    remove_action( 'woocommerce_after_add_to_cart_button', 'wm_buy_now_button' );

    // Badges bilkul upar (title se pehle)
    add_action( 'woocommerce_single_product_summary', 'wm_product_top_badges', 3 );

    // Custom name/picture: variations se PEHLE (variable) / ATC se pehle (simple)
    add_action( 'woocommerce_before_variations_form', 'wm_render_custom_fields_redesign', 5 );
    add_action( 'woocommerce_before_add_to_cart_button', 'wm_render_custom_fields_simple_only', 3 );

    // Free shipping + social + bulk — pack/variations ke baad, buttons se pehle
    add_action( 'woocommerce_before_add_to_cart_button', 'wm_product_extra_notices', 20 );

    // Buy Now + WhatsApp Order — ATC ke baad
    add_action( 'woocommerce_after_add_to_cart_button', 'wm_buy_now_button', 10 );
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

function wm_product_extra_notices() {
    global $product;
    if ( ! $product ) {
        return;
    }

    // Social proof
    if ( function_exists( 'wm_get_recent_sales_count' ) ) {
        $recent = wm_get_recent_sales_count( $product->get_id(), 7 );
        if ( $recent > 0 ) {
            echo '<div class="wm-social-proof">👥 <b>' . intval( $recent ) . '+</b> customers bought this in the last 7 days</div>';
        }
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

// 4) CSS + JS for redesign
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

        /* Gallery: rounded main + thumbs LEFT */
        .woocommerce div.product div.images,
        .woocommerce-product-gallery {
            display:flex !important;
            flex-direction:row !important;
            align-items:flex-start !important;
            gap:12px !important;
        }
        .woocommerce-product-gallery__wrapper,
        .woocommerce-product-gallery .flex-viewport {
            flex:1 1 auto !important;
            order:2 !important;
            border-radius:18px !important;
            overflow:hidden !important;
            box-shadow:0 10px 28px rgba(26,63,160,0.12) !important;
        }
        .woocommerce-product-gallery__image,
        .woocommerce-product-gallery__image img,
        .woocommerce div.product div.images img {
            border-radius:18px !important;
        }
        .woocommerce-product-gallery ol.flex-control-nav.flex-control-thumbs {
            order:1 !important;
            display:flex !important;
            flex-direction:column !important;
            width:78px !important;
            max-height:520px !important;
            overflow-y:auto !important;
            margin:0 !important;
            padding:0 !important;
            gap:8px !important;
            float:none !important;
        }
        .woocommerce-product-gallery ol.flex-control-nav.flex-control-thumbs li {
            width:78px !important;
            float:none !important;
            margin:0 !important;
        }
        .woocommerce-product-gallery ol.flex-control-nav.flex-control-thumbs li img {
            border-radius:10px !important;
            border:2px solid #EAF0FA !important;
            opacity:1 !important;
            width:100% !important;
            height:78px !important;
            object-fit:cover !important;
            cursor:pointer !important;
        }
        .woocommerce-product-gallery ol.flex-control-nav.flex-control-thumbs li img.flex-active,
        .woocommerce-product-gallery ol.flex-control-nav.flex-control-thumbs li img:hover {
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

        @media (max-width: 700px) {
            .product_title.entry-title { font-size:22px !important; }
            .woocommerce-product-gallery ol.flex-control-nav.flex-control-thumbs {
                width:60px !important;
            }
            .woocommerce-product-gallery ol.flex-control-nav.flex-control-thumbs li,
            .woocommerce-product-gallery ol.flex-control-nav.flex-control-thumbs li img {
                width:60px !important;
                height:60px !important;
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
                        jQuery(select).trigger("change");
                    });

                    wrap.appendChild(btn);
                });

                select.parentNode.insertBefore(wrap, select.nextSibling);
            });
        }

        buildSwatches();

        // Rebuild after Woo variation updates
        jQuery(document.body).on("woocommerce_update_variation_values check_variations", function () {
            document.querySelectorAll(".wm-swatch-wrap").forEach(function (el) { el.remove(); });
            document.querySelectorAll("form.variations_form select").forEach(function (select) {
                select.dataset.wmSwatchReady = "0";
            });
            buildSwatches();
        });
    });
    </script>
    <?php
}

// Cart checkout button fix (Change 1)
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
