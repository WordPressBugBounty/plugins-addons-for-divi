<?php
/**
 * Divi 5 module loader for Divi Torque Lite.
 *
 * Auto-detects whether Divi 5 is active by looking for its
 * DependencyInterface. On Divi 4 (or any site without D5), this file
 * returns early and zero D5 code runs — D4 modules keep working as-is.
 *
 * @package DiviTorqueLite
 * @since   4.3.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Auto-detect Divi 5. Without it, exit silently — D4 modules keep working.
$dtl_d5_dependency_interface = ABSPATH . 'wp-content/themes/Divi/includes/builder-5/server/Framework/DependencyManagement/Interfaces/DependencyInterface.php';
if (!file_exists($dtl_d5_dependency_interface)) {
    return;
}
require_once $dtl_d5_dependency_interface;

// slug => [ folder, fully-qualified class ]
$dtl_d5_modules = array(
    'gradient_heading' => array('GradientHeading', '\\DiviTorqueLite\\Modules\\GradientHeading\\GradientHeading'),
    'divider'          => array('Divider', '\\DiviTorqueLite\\Modules\\Divider\\Divider'),
    'icon_box'         => array('IconBox', '\\DiviTorqueLite\\Modules\\IconBox\\IconBox'),
    'inline_notice'    => array('InlineNotice', '\\DiviTorqueLite\\Modules\\InlineNotice\\InlineNotice'),
    'dual_button'      => array('DualButton', '\\DiviTorqueLite\\Modules\\DualButton\\DualButton'),
    'info_box'         => array('InfoBox', '\\DiviTorqueLite\\Modules\\InfoBox\\InfoBox'),
    'info_card'        => array('InfoCard', '\\DiviTorqueLite\\Modules\\InfoCard\\InfoCard'),
    'team_box'         => array('TeamBox', '\\DiviTorqueLite\\Modules\\TeamBox\\TeamBox'),
    'image_carousel'   => array('ImageCarousel', '\\DiviTorqueLite\\Modules\\ImageCarousel\\ImageCarousel'),
    'image_carousel_item' => array('ImageCarouselItem', '\\DiviTorqueLite\\Modules\\ImageCarouselItem\\ImageCarouselItem'),
    'logo_carousel'    => array('LogoCarousel', '\\DiviTorqueLite\\Modules\\LogoCarousel\\LogoCarousel'),
    'logo_carousel_item' => array('LogoCarouselItem', '\\DiviTorqueLite\\Modules\\LogoCarouselItem\\LogoCarouselItem'),
    'skill_bar'        => array('SkillBar', '\\DiviTorqueLite\\Modules\\SkillBar\\SkillBar'),
    'skill_bar_item'   => array('SkillBarItem', '\\DiviTorqueLite\\Modules\\SkillBarItem\\SkillBarItem'),
    'logo_grid'        => array('LogoGrid', '\\DiviTorqueLite\\Modules\\LogoGrid\\LogoGrid'),
    'logo_grid_item'   => array('LogoGridItem', '\\DiviTorqueLite\\Modules\\LogoGridItem\\LogoGridItem'),
    'business_hour'    => array('BusinessHour', '\\DiviTorqueLite\\Modules\\BusinessHour\\BusinessHour'),
    'business_hour_item' => array('BusinessHourItem', '\\DiviTorqueLite\\Modules\\BusinessHourItem\\BusinessHourItem'),
    'scroll_image'     => array('ScrollImage', '\\DiviTorqueLite\\Modules\\ScrollImage\\ScrollImage'),
    'review'           => array('Review', '\\DiviTorqueLite\\Modules\\Review\\Review'),
    'flip_box'         => array('FlipBox', '\\DiviTorqueLite\\Modules\\FlipBox\\FlipBox'),
    'testimonial'      => array('Testimonial', '\\DiviTorqueLite\\Modules\\Testimonial\\Testimonial'),
    'number_counter'   => array('NumberCounter', '\\DiviTorqueLite\\Modules\\NumberCounter\\NumberCounter'),
    'compare_image'    => array('CompareImage', '\\DiviTorqueLite\\Modules\\CompareImage\\CompareImage'),
    'video_modal'      => array('VideoModal', '\\DiviTorqueLite\\Modules\\VideoModal\\VideoModal'),
    'animated_text'    => array('AnimatedText', '\\DiviTorqueLite\\Modules\\AnimatedText\\AnimatedText'),
    'news_ticker'      => array('NewsTicker', '\\DiviTorqueLite\\Modules\\NewsTicker\\NewsTicker'),
    'post_list'        => array('PostList', '\\DiviTorqueLite\\Modules\\PostList\\PostList'),
    'contact_form_7'   => array('ContactForm7', '\\DiviTorqueLite\\Modules\\ContactForm7\\ContactForm7'),
    'twitter_feed'     => array('TwitterFeed', '\\DiviTorqueLite\\Modules\\TwitterFeed\\TwitterFeed'),
    'twitter_feed_carousel' => array('TwitterFeedCarousel', '\\DiviTorqueLite\\Modules\\TwitterFeedCarousel\\TwitterFeedCarousel'),
    // Divi 5-only native modules (no D4 ancestor, no conversion outline).
    'accordion'        => array('Accordion', '\\DiviTorqueLite\\Modules\\Accordion\\Accordion'),
    'accordion_item'   => array('AccordionItem', '\\DiviTorqueLite\\Modules\\AccordionItem\\AccordionItem'),
    'modal_popup'      => array('ModalPopup', '\\DiviTorqueLite\\Modules\\ModalPopup\\ModalPopup'),
    'tabs'             => array('Tabs', '\\DiviTorqueLite\\Modules\\Tabs\\Tabs'),
    'tab_item'         => array('TabItem', '\\DiviTorqueLite\\Modules\\TabItem\\TabItem'),
    'fancy_text'       => array('FancyText', '\\DiviTorqueLite\\Modules\\FancyText\\FancyText'),
    'table_of_contents' => array('TableOfContents', '\\DiviTorqueLite\\Modules\\TableOfContents\\TableOfContents'),
    'breadcrumbs'      => array('Breadcrumbs', '\\DiviTorqueLite\\Modules\\Breadcrumbs\\Breadcrumbs'),
    'faq'              => array('Faq', '\\DiviTorqueLite\\Modules\\Faq\\Faq'),
    'faq_item'         => array('FaqItem', '\\DiviTorqueLite\\Modules\\FaqItem\\FaqItem'),
    'post_carousel'    => array('PostCarousel', '\\DiviTorqueLite\\Modules\\PostCarousel\\PostCarousel'),
);

$dtl_d5_loaded = array();
foreach ($dtl_d5_modules as $slug => $info) {
    $module_file = __DIR__ . '/modules/' . $info[0] . '/' . $info[0] . '.php';
    if (!file_exists($module_file)) {
        continue;
    }
    require_once $module_file;
    $dtl_d5_loaded[$slug] = $info[1];
}

// Register modules in the D5 dependency tree.
add_action(
    'divi_module_library_modules_dependency_tree',
    function ($dependency_tree) use ($dtl_d5_loaded) {
        foreach ($dtl_d5_loaded as $class) {
            if (class_exists($class)) {
                $dependency_tree->add_dependency(new $class());
            }
        }
    }
);

// Tell Divi 5 where each module's conversion-outline.json lives.
add_filter(
    'divi.moduleLibrary.conversion.moduleConversionOutlineFile',
    function ($file_path, $module_name) {
        $outlines = array(
            'divitorque/gradient-heading' => 'gradient-heading/conversion-outline.json',
            'divitorque/divider'          => 'divider/conversion-outline.json',
            'divitorque/icon-box'         => 'icon-box/conversion-outline.json',
            'divitorque/inline-notice'    => 'inline-notice/conversion-outline.json',
            'divitorque/dual-button'      => 'dual-button/conversion-outline.json',
            'divitorque/info-box'         => 'info-box/conversion-outline.json',
            'divitorque/info-card'        => 'info-card/conversion-outline.json',
            'divitorque/team-box'         => 'team-box/conversion-outline.json',
            'divitorque/image-carousel'   => 'image-carousel/conversion-outline.json',
            'divitorque/image-carousel-item' => 'image-carousel-item/conversion-outline.json',
            'divitorque/logo-carousel'    => 'logo-carousel/conversion-outline.json',
            'divitorque/logo-carousel-item' => 'logo-carousel-item/conversion-outline.json',
            'divitorque/skill-bar'        => 'skill-bar/conversion-outline.json',
            'divitorque/skill-bar-item'   => 'skill-bar-item/conversion-outline.json',
            'divitorque/logo-grid'        => 'logo-grid/conversion-outline.json',
            'divitorque/logo-grid-item'   => 'logo-grid-item/conversion-outline.json',
            'divitorque/business-hour'    => 'business-hour/conversion-outline.json',
            'divitorque/business-hour-item' => 'business-hour-item/conversion-outline.json',
            'divitorque/scroll-image'     => 'scroll-image/conversion-outline.json',
            'divitorque/review'           => 'review/conversion-outline.json',
            'divitorque/flip-box'         => 'flip-box/conversion-outline.json',
            'divitorque/testimonial'      => 'testimonial/conversion-outline.json',
            'divitorque/number-counter'   => 'number-counter/conversion-outline.json',
            'divitorque/compare-image'    => 'compare-image/conversion-outline.json',
            'divitorque/video-modal'      => 'video-modal/conversion-outline.json',
            'divitorque/animated-text'    => 'animated-text/conversion-outline.json',
            'divitorque/news-ticker'      => 'news-ticker/conversion-outline.json',
            'divitorque/post-list'        => 'post-list/conversion-outline.json',
            'divitorque/contact-form-7'   => 'contact-form-7/conversion-outline.json',
            'divitorque/twitter-feed'     => 'twitter-feed/conversion-outline.json',
            'divitorque/twitter-feed-carousel' => 'twitter-feed-carousel/conversion-outline.json',
        );
        if (isset($outlines[$module_name])) {
            return DIVI_TORQUE_LITE_MODULES_JSON_PATH . $outlines[$module_name];
        }
        return $file_path;
    },
    9,
    2
);

// Expose Divi's own border-radii converter to our conversion outlines so the
// Animated Text per-element radius fields (prefix_radius / animated_radius /
// suffix_radius, D4 "on|TL|TR|BR|BL" pipe strings) migrate into the D5 element
// border radius object. It isn't in Divi's default valueExpansionFunctionMap,
// so register it under the name our outline references.
add_filter(
    'divi.moduleLibrary.conversion.valueExpansionFunctionMap',
    function ($map) {
        if (is_array($map) && ! isset($map['convertBorderRadii'])) {
            $map['convertBorderRadii'] = '\\ET\\Builder\\Packages\\Conversion\\AdvancedOptionConversion::convertBorderRadii';
        }
        return $map;
    }
);

/**
 * Enqueue D5 Visual Builder bundle inside the VB iframe.
 */
add_action(
    'divi_visual_builder_assets_before_enqueue_scripts',
    function () {
        if (!class_exists('\ET\Builder\VisualBuilder\Assets\PackageBuildManager')) {
            return;
        }

        $dist_url = DIVI_TORQUE_LITE_DIST_URL . 'divi5/';
        $dist_dir = DIVI_TORQUE_LITE_DIR . 'dist/divi5/';

        // Cache-bust on the built file's mtime so a rebuilt bundle always
        // loads in the builder (the static plugin version never changes
        // between builds, which otherwise serves stale cached JS).
        $ver = function ($file) use ($dist_dir) {
            $path = $dist_dir . $file;
            return file_exists($path) ? (string) filemtime($path) : DIVI_TORQUE_LITE_VERSION;
        };

        wp_enqueue_script('wp-api-fetch');

        // Swiper carousel library — loaded in the VB iframe so carousel
        // modules render a live preview (registered before the bundle so the
        // bundle can depend on it).
        \ET\Builder\VisualBuilder\Assets\PackageBuildManager::register_package_build([
            'name'    => 'divi-torque-lite-swiper-vb-script',
            'version' => DIVI_TORQUE_LITE_VERSION,
            'script'  => [
                'src'                => DIVI_TORQUE_LITE_ASSETS . 'libs/swiper/swiper-bundle.min.js',
                'deps'               => [],
                'enqueue_top_window' => false,
                'enqueue_app_window' => true,
            ],
        ]);

        \ET\Builder\VisualBuilder\Assets\PackageBuildManager::register_package_build([
            'name'    => 'divi-torque-lite-swiper-vb-style',
            'version' => DIVI_TORQUE_LITE_VERSION,
            'style'   => [
                'src'                => DIVI_TORQUE_LITE_ASSETS . 'libs/swiper/swiper-bundle.min.css',
                'deps'               => [],
                'enqueue_top_window' => false,
                'enqueue_app_window' => true,
            ],
        ]);

        // Popper + Tippy — loaded in the VB iframe so the Logo Grid tooltips
        // render a live preview. Order matters: tippy v6 requires the Popper
        // global, so popper is registered (and depended on) first.
        \ET\Builder\VisualBuilder\Assets\PackageBuildManager::register_package_build([
            'name'    => 'divi-torque-lite-popper-vb-script',
            'version' => DIVI_TORQUE_LITE_VERSION,
            'script'  => [
                'src'                => DIVI_TORQUE_LITE_ASSETS . 'libs/popper/popper.min.js',
                'deps'               => [],
                'enqueue_top_window' => false,
                'enqueue_app_window' => true,
            ],
        ]);

        \ET\Builder\VisualBuilder\Assets\PackageBuildManager::register_package_build([
            'name'    => 'divi-torque-lite-tippy-vb-script',
            'version' => DIVI_TORQUE_LITE_VERSION,
            'script'  => [
                'src'                => DIVI_TORQUE_LITE_ASSETS . 'libs/tippy/tippy.min.js',
                'deps'               => ['divi-torque-lite-popper-vb-script'],
                'enqueue_top_window' => false,
                'enqueue_app_window' => true,
            ],
        ]);

        \ET\Builder\VisualBuilder\Assets\PackageBuildManager::register_package_build([
            'name'    => 'divi-torque-lite-tippy-vb-style',
            'version' => DIVI_TORQUE_LITE_VERSION,
            'style'   => [
                'src'                => DIVI_TORQUE_LITE_ASSETS . 'libs/tippy/tippy.min.css',
                'deps'               => [],
                'enqueue_top_window' => false,
                'enqueue_app_window' => true,
            ],
        ]);

        // Counter-up library (vanilla, window.counterUp) — loaded in the VB
        // iframe so the Number Counter module renders a live count-up preview
        // (registered before the bundle so the bundle can depend on it).
        \ET\Builder\VisualBuilder\Assets\PackageBuildManager::register_package_build([
            'name'    => 'divi-torque-lite-counter-up-vb-script',
            'version' => DIVI_TORQUE_LITE_VERSION,
            'script'  => [
                'src'                => DIVI_TORQUE_LITE_ASSETS . 'libs/counter-up/counter-up.min.js',
                'deps'               => [],
                'enqueue_top_window' => false,
                'enqueue_app_window' => true,
            ],
        ]);

        // Animated Text's "typed" engine is now a self-contained vanilla
        // implementation bundled in the D5 bundle (src/divi5/modules/
        // animated-text/typing.js) — no external typed.js library, so nothing
        // extra to load in the VB (the old window.Typed UMD global was fragile:
        // an AMD/module environment made it register elsewhere and never set
        // the global, so the preview silently never typed).
        \ET\Builder\VisualBuilder\Assets\PackageBuildManager::register_package_build([
            'name'    => 'divi-torque-lite-d5-bundle-script',
            'version' => $ver('bundle.js'),
            'script'  => [
                'src'                => $dist_url . 'bundle.js',
                'deps'               => ['divi-module-library', 'divi-vendor-wp-hooks', 'divi-torque-lite-swiper-vb-script', 'divi-torque-lite-tippy-vb-script', 'divi-torque-lite-counter-up-vb-script'],
                'enqueue_top_window' => false,
                'enqueue_app_window' => true,
            ],
        ]);

        \ET\Builder\VisualBuilder\Assets\PackageBuildManager::register_package_build([
            'name'    => 'divi-torque-lite-d5-bundle-style',
            'version' => $ver('bundle.css'),
            'style'   => [
                'src'                => $dist_url . 'bundle.css',
                'deps'               => [],
                'enqueue_top_window' => false,
                'enqueue_app_window' => true,
            ],
        ]);

        // Branded "Divi Torque" inserter folder grouping all our modules.
        // Two scripts with opposite footer timing (see custom-folder docs):
        //  - register: in the footer, after divi-module-library initialises.
        //  - assign:   before the footer, so the moduleMapping filter fires
        //              before modules are registered.
        \ET\Builder\VisualBuilder\Assets\PackageBuildManager::register_package_build([
            'name'    => 'divi-torque-lite-folder-assign',
            'version' => DIVI_TORQUE_LITE_VERSION,
            'script'  => [
                'src'                => DIVI_TORQUE_LITE_ASSETS . 'divi5/folder-assign.js',
                'deps'               => ['lodash', 'divi-vendor-wp-hooks'],
                'enqueue_top_window' => false,
                'enqueue_app_window' => true,
                'args'               => ['in_footer' => false],
            ],
        ]);

        \ET\Builder\VisualBuilder\Assets\PackageBuildManager::register_package_build([
            'name'    => 'divi-torque-lite-folder-register',
            'version' => DIVI_TORQUE_LITE_VERSION,
            'script'  => [
                'src'                => DIVI_TORQUE_LITE_ASSETS . 'divi5/folder-register.js',
                'deps'               => ['divi-module-library'],
                'enqueue_top_window' => false,
                'enqueue_app_window' => true,
                'args'               => ['in_footer' => true],
            ],
        ]);

    }
);

/**
 * Frontend assets for D5 modules — gated, fires only on non-admin requests.
 */
add_action(
    'wp_enqueue_scripts',
    function () {
        if (is_admin()) {
            return;
        }

        $dist_url = DIVI_TORQUE_LITE_DIST_URL . 'divi5/';
        $dist_dir = DIVI_TORQUE_LITE_DIR . 'dist/divi5/';
        $ver      = function ($file) use ($dist_dir) {
            $path = $dist_dir . $file;
            return file_exists($path) ? (string) filemtime($path) : DIVI_TORQUE_LITE_VERSION;
        };

        // Swiper carousel library (front end).
        wp_enqueue_style(
            'divi-torque-lite-swiper',
            DIVI_TORQUE_LITE_ASSETS . 'libs/swiper/swiper-bundle.min.css',
            [],
            DIVI_TORQUE_LITE_VERSION
        );
        wp_enqueue_script(
            'divi-torque-lite-swiper',
            DIVI_TORQUE_LITE_ASSETS . 'libs/swiper/swiper-bundle.min.js',
            [],
            DIVI_TORQUE_LITE_VERSION,
            true
        );

        wp_enqueue_style(
            'divi-torque-lite-d5-frontend',
            $dist_url . 'bundle.css',
            [],
            $ver('bundle.css')
        );

        wp_enqueue_script(
            'divi-torque-lite-d5-frontend',
            $dist_url . 'frontend.js',
            ['jquery', 'divi-torque-lite-swiper'],
            $ver('frontend.js'),
            true
        );
    }
);
