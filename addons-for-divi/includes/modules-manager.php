<?php

namespace DiviTorqueLite;

use DiviTorqueLite\AdminHelper;

class ModulesManager
{
    private static $instance;

    private $modules_pro = [
        [
            'name' => 'contact-form7',
            'title' => 'Contact Form 7',
            'icon' => 'contact-form.svg',
            'child_name' => '',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-237',
            'is_pro' => true
        ],

        [
            'name' => 'fluent-forms',
            'title' => 'Fluent Forms',
            'icon' => 'contact-form.svg',
            'child_name' => '',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-348',
            'is_pro' => true
        ],
        [
            'name' => 'gravity-forms',
            'title' => 'Gravity Forms',
            'icon' => 'form.svg',
            'child_name' => '',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-349',
            'is_pro' => true
        ],
        [
            'name' => 'inline-notice',
            'title' => 'Inline Notice',
            'icon' => 'inline-notice.svg',
            'child_name' => '',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-199',
            'is_pro' => true
        ],
        [
            'name' => 'basic-list',
            'title' => 'Basic List',
            'icon' => 'list.svg',
            'child_name' => 'basic-list-child',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-377',
            'is_pro' => true
        ],
        [
            'name' => 'icon-list',
            'title' => 'Icon List',
            'icon' => 'icon-list.svg',
            'child_name' => 'icon-list-child',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-375',
            'is_pro' => true
        ],
        [
            'name' => 'business-hour',
            'title' => 'Opening Hours',
            'icon' => 'business-hours.svg',
            'child_name' => 'business-hour-child',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-378',
            'is_pro' => true
        ],
        [
            'name' => 'carousel',
            'title' => 'Image Carousel',
            'icon' => 'carousel-2.svg',
            'child_name' => 'carousel-child',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-299',
            'is_pro' => true
        ],
        [
            'name' => 'checkmark-list',
            'title' => 'Checkmark List',
            'icon' => 'list.svg',
            'child_name' => 'checkmark-list-child',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-376',
            'is_pro' => true
        ],
        [
            'name' => 'compare-image',
            'title' => 'Before & After Slider',
            'icon' => 'before-after.svg',
            'child_name' => '',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-297',
            'is_pro' => true
        ],
        [
            'name' => 'content-toggle',
            'title' => 'Content Toggle',
            'icon' => 'switch.svg',
            'child_name' => '',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-325',
            'is_pro' => true
        ],
        [
            'name' => 'countdown',
            'title' => 'Countdown',
            'icon' => 'countdown.svg',
            'child_name' => '',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-373',
            'is_pro' => true
        ],
        [
            'name' => 'divider',
            'title' => 'Advanced Divider',
            'icon' => 'divider.svg',
            'child_name' => '',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-352',
            'is_pro' => true
        ],
        [
            'name' => 'filterable-gallery',
            'title' => 'Filterable Gallery',
            'icon' => 'grid.svg',
            'child_name' => '',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-350',
            'is_pro' => true
        ],
        [
            'name' => 'flip-box',
            'title' => 'Flip Box',
            'icon' => 'flip-box.svg',
            'child_name' => '',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-239',
            'is_pro' => true
        ],
        [
            'name' => 'gradient-heading',
            'title' => 'Gradient Heading',
            'icon' => 'heading.svg',
            'child_name' => '',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-211',
            'is_pro' => true
        ],
        [
            'name' => 'heading',
            'title' => 'Heading',
            'icon' => 'heading.svg',
            'child_name' => '',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-400',
            'is_pro' => true
        ],
        [
            'name' => 'horizontal-timeline',
            'title' => 'Horizontal Timeline',
            'icon' => 'timeline.svg',
            'child_name' => 'horizontal-timeline-child',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-301',
            'is_pro' => true
        ],
        [
            'name' => 'hotspots',
            'title' => 'Image Hotspots',
            'icon' => 'image-hotspots.svg',
            'child_name' => 'hotspots-child',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-212',
            'is_pro' => true
        ],
        [
            'name' => 'icon-box',
            'title' => 'Icon Box',
            'icon' => 'icon-box.svg',
            'child_name' => '',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-300',
            'is_pro' => true
        ],

        [
            'name' => 'scroll-image',
            'title' => 'Scroll Image',
            'icon' => 'image.svg',
            'child_name' => '',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-257',
            'is_pro' => true
        ],
        [
            'name' => 'image-zoom',
            'title' => 'Image Magnifier',
            'icon' => 'image.svg',
            'child_name' => '',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-213',
            'is_pro' => true
        ],
        [
            'name' => 'inline-svg',
            'title' => 'Inline SVG',
            'icon' => 'inline-svg.svg',
            'child_name' => '',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-200',
            'is_pro' => true
        ],
        [
            'name' => 'instagram-chat',
            'title' => 'Instagram Chat',
            'icon' => 'instagram.svg',
            'child_name' => '',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-328',
            'is_pro' => true
        ],
        [
            'name' => 'instagram-feed',
            'title' => 'Instagram Feed',
            'icon' => 'instagram.svg',
            'child_name' => '',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-187'
        ],
        [
            'name' => 'logo-carousel',
            'title' => 'Logo Carousel',
            'icon' => 'carousel.svg',
            'child_name' => 'logo-carousel-child',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-399',
            'is_pro' => true
        ],
        [
            'name' => 'logo-list',
            'title' => 'Logo List',
            'icon' => 'logo-grid.svg',
            'child_name' => 'logo-grid-child',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-281',
            'is_pro' => true
        ],
        [
            'name' => 'lottie',
            'title' => 'Lottie',
            'icon' => 'lottie.svg',
            'child_name' => '',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-198',
            'is_pro' => true
        ],
        [
            'name' => 'pricing-table',
            'title' => 'Pricing Table',
            'icon' => 'pricing-table.svg',
            'child_name' => 'pricing-table-child',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-134',
            'is_pro' => true
        ],
        [
            'name' => 'progress-bar',
            'title' => 'Progress Bars',
            'icon' => 'progress-bar.svg',
            'child_name' => 'progress-bar-child',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-256',
            'is_pro' => true
        ],
        [
            'name' => 'price-list',
            'title' => 'Price List',
            'icon' => 'price-list.svg',
            'child_name' => 'price-list-child',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-280',
            'is_pro' => true
        ],
        [
            'name' => 'review-card',
            'title' => 'Review Box',
            'icon' => 'review.svg',
            'child_name' => '',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-279',
            'is_pro' => true
        ],
        [
            'name' => 'info-card',
            'title' => 'Info Card',
            'icon' => 'info-box.svg',
            'child_name' => '',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-324',
            'is_pro' => true
        ],
        [
            'name' => 'social-share',
            'title' => 'Social Share Buttons',
            'icon' => 'social-share.svg',
            'child_name' => 'social-share-child',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-193',
            'is_pro' => true
        ],
        [
            'name' => 'star-rating',
            'title' => 'Star Rating',
            'icon' => 'star-rating.svg',
            'child_name' => '',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-374',
            'is_pro' => true
        ],
        [
            'name' => 'stats-grid',
            'title' => 'Stats Grid',
            'icon' => 'grid.svg',
            'child_name' => 'stats-grid-child',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-351',
            'is_pro' => true
        ],
        [
            'name' => 'team-box',
            'title' => 'Team Box',
            'icon' => 'team.svg',
            'child_name' => '',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-398',
            'is_pro' => true
        ],
        [
            'name' => 'telegram-chat',
            'title' => 'Telegram Chat',
            'icon' => 'telegram.svg',
            'child_name' => '',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-327',
            'is_pro' => true
        ],
        [
            'name' => 'testimonial',
            'title' => 'Advanced Testimonial',
            'icon' => 'testimonial.svg',
            'child_name' => '',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-255',
            'is_pro' => true
        ],
        [
            'name' => 'timeline',
            'title' => 'Timeline',
            'icon' => 'timeline.svg',
            'child_name' => 'timeline-child',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-326',
            'is_pro' => true
        ],
        [
            'name' => 'video-modal',
            'title' => 'Video Modal',
            'icon' => 'video-modal.svg',
            'child_name' => '',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-238',
            'is_pro' => true
        ],

        [
            'name' => 'logo-grid',
            'title' => 'Logo Grid',
            'icon' => 'grid.svg',
            'child_name' => '',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-281',
            'is_pro' => true
        ],
        [
            'name' => 'blurb',
            'title' => 'Info Box',
            'icon' => 'info-box.svg',
            'child_name' => '',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-298',
            'is_pro' => true
        ],
        [
            'name' => 'whatsapp-chat',
            'title' => 'WhatsApp Chat',
            'icon' => 'whatsapp.svg',
            'child_name' => '',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-347',
            'is_pro' => true
        ]
    ];

    private $modules_lite = [
        [
            'name' => 'icon-box',
            'title' => 'Icon Box',
            'icon' => 'icon-box.svg',
            'child_name' => '',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-375',
        ],
        [
            'name' => 'contact-form7',
            'title' => 'Contact Form 7',
            'icon' => 'contact-form.svg',
            'child_name' => '',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-237'
        ],
        [
            'name' => 'divider',
            'title' => 'Separator',
            'icon' => 'divider.svg',
            'child_name' => '',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-352'
        ],
        [
            'name' => 'skill-bar',
            'title' => 'Skill Bar',
            'icon' => 'progress-bar.svg',
            'child_name' => 'skill-bar-child',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-256'
        ],
        [
            'name' => 'logo-grid',
            'title' => 'Logo Grid',
            'icon' => 'grid.svg',
            'child_name' => 'logo-grid-child',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-281'
        ],
        [
            'name' => 'team-box',
            'title' => 'Person',
            'icon' => 'team.svg',
            'child_name' => '',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-398'
        ],
        [
            'name' => 'testimonial',
            'title' => 'Testimonial',
            'icon' => 'testimonial.svg',
            'child_name' => '',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-255'
        ],
        [
            'name' => 'info-box',
            'title' => 'Info Box',
            'icon' => 'info-box.svg',
            'child_name' => '',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-324'
        ],
        [
            'name' => 'info-card',
            'title' => 'Info Card',
            'icon' => 'info-box.svg',
            'child_name' => '',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-324',
        ],
        [
            'name' => 'dual-button',
            'title' => 'Dual Button',
            'icon' => 'buttons.svg',
            'child_name' => '',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-325'
        ],
        [
            'name' => 'compare-image',
            'title' => 'Before & After Slider',
            'icon' => 'before-after.svg',
            'child_name' => '',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-297',
        ],
        [
            'name' => 'image-carousel',
            'title' => 'Image Carousel',
            'icon' => 'carousel-2.svg',
            'child_name' => 'image-carousel-child',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-299'
        ],
        [
            'name' => 'logo-carousel',
            'title' => 'Logo Carousel',
            'icon' => 'carousel.svg',
            'child_name' => 'logo-carousel-child',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-399'
        ],
        [
            'name' => 'twitter-feed-carousel',
            'title' => 'Twitter Feed Carousel',
            'icon' => 'twitter.svg',
            'child_name' => '',
            'demo_link' => '#'
        ],
        [
            'name' => 'twitter-feed',
            'title' => 'Twitter Feed',
            'icon' => 'twitter.svg',
            'child_name' => '',
            'demo_link' => '#'
        ],
        [
            'name' => 'number',
            'title' => 'Number',
            'icon' => 'number.svg',
            'child_name' => '',
            'demo_link' => '#'
        ],
        [
            'name' => 'video-modal',
            'title' => 'Video Modal',
            'icon' => 'video-modal.svg',
            'child_name' => '',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-238'
        ],
        [
            'name' => 'scroll-image',
            'title' => 'Scroll Image',
            'icon' => 'image.svg',
            'child_name' => '',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-257'
        ],
        [
            'name' => 'news-ticker',
            'title' => 'News Ticker',
            'icon' => 'ticker.svg',
            'child_name' => '',
            'demo_link' => '#'
        ],
        [
            'name' => 'post-list',
            'title' => 'Post List',
            'icon' => 'post-list.svg',
            'child_name' => '',
            'demo_link' => '#'
        ],
        [
            'name' => 'review',
            'title' => 'Review Box',
            'icon' => 'review.svg',
            'child_name' => '',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-279'
        ],
        [
            'name' => 'flip-box',
            'title' => 'Flip Card',
            'icon' => 'flip-box.svg',
            'child_name' => '',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-239'
        ],
        [
            'name' => 'animated-text',
            'title' => 'Animated Text',
            'icon' => 'text.svg',
            'child_name' => '',
            'demo_link' => '#'
        ],
        [
            'name' => 'business-hour',
            'title' => 'Business Hours',
            'icon' => 'business-hours.svg',
            'child_name' => 'business-hour-child',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-378'
        ],
        [
            'name' => 'gradient-heading',
            'title' => 'Gradient Heading',
            'icon' => 'heading.svg',
            'child_name' => '',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-211'
        ],
        [
            'name' => 'inline-notice',
            'title' => 'Inline Notice',
            'icon' => 'inline-notice.svg',
            'child_name' => '',
            'demo_link' => 'https://diviepic.com/torque-pro/#live-preview-199'
        ]
    ];

    public static function get_instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function __construct()
    {
        add_action('et_builder_ready', [$this, 'load_modules'], 9);
    }

    public static function get_all_pro_modules()
    {
        return self::get_instance()->modules_pro;
    }

    public static function get_all_modules()
    {
        return self::get_instance()->modules_lite;
    }

    public function load_modules()
    {
        if (!class_exists(\ET_Builder_Element::class)) {
            return;
        }

        $active_modules = $this->active_modules();

        foreach ($active_modules as $module_name => $module) {
            $module_path = sprintf('%1$s/modules/divi-4/%2$s/%2$s.php', __DIR__, str_replace('-', '', ucwords($module_name, '-')));

            if (file_exists($module_path)) {
                require_once $module_path;
            }
        }
    }

    public function active_modules()
    {
        $all_modules = self::get_all_modules();
        $saved_modules = AdminHelper::get_modules();
        $active_modules = [];

        foreach ($all_modules as $module) {
            if (in_array($module['name'], $saved_modules)) {
                $active_modules[$module['name']] = $module;
                if (!empty($module['child_name'])) {
                    $active_modules[$module['child_name']] = $module;
                }
            }
        }

        return $active_modules;
    }
}
