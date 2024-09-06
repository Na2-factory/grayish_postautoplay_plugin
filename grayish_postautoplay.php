<?php
/*
Plugin Name: grayish Post Autoplay Plugin
Description: grayish(Cocoonスキンなしも使用可能) 新着記事・人気記事・ナビカード　簡易オートプレイ プラグイン
Version: 1.0.6
Author: Na2factory
Author URI: https://na2-factory.com/
License: GNU General Public License
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

if (!defined('ABSPATH')) exit;

// 現在のテーマがcocoon-master or cocoon-masterの子テーマである場合のみ処理を行う
$theme = wp_get_theme();
if (!$theme || ('cocoon-master' !== $theme->template && (!$theme->parent() || 'cocoon-master' !== $theme->parent()->template))) {
	return;
}

add_action('after_setup_theme', 'gry_post_autoplay_setup', 20);
function gry_post_autoplay_setup()
{
	if (!defined('GRY_POST_AUTOPLAY_PLUGIN_VERSION')) {
		define('GRY_POST_AUTOPLAY_PLUGIN_VERSION', '1.0.6');
	}

	if (!defined('GRY_POST_AUTOPLAY_PLUGIN_PATH')) {
		define('GRY_POST_AUTOPLAY_PLUGIN_PATH', plugin_dir_path(__FILE__));
	}
	if (!defined('GRY_POST_AUTOPLAY_PLUGIN_URL')) {
		define('GRY_POST_AUTOPLAY_PLUGIN_URL', plugins_url('/', __FILE__));
	}

	add_action('wp_enqueue_scripts', 'gry_post_autoplay_enqueue_scripts');

	// カテゴリラベルの表示はユーザーに任せる
	// 新着記事ウィジェット（ショートコード）カテゴリーラベルの表示
	// if (!has_filter('is_new_entry_card_category_label_visible', '__return_true')) {
	// 	add_filter('is_new_entry_card_category_label_visible', '__return_true');
	// }
	// 人気記事ウィジェット（ショートコード）カテゴリーラベルの表示
	// if (!has_filter('is_popular_entry_card_category_label_visible', '__return_true')) {
	// 	add_filter('is_popular_entry_card_category_label_visible', '__return_true');
	// }

	add_filter("cocoon_part__tmp/footer-javascript", 'gry_post_autoplay_add_swiper_script');
}

function gry_post_autoplay_enqueue_scripts()
{
	/* CSS */
	wp_enqueue_style(
		'grayish_postautoplay-style',
		GRY_POST_AUTOPLAY_PLUGIN_URL . 'assets/grayish_postautoplay_style.css',
		array(),
		GRY_POST_AUTOPLAY_PLUGIN_VERSION
	);
}

function gry_post_autoplay_add_swiper_script($content)
{
	ob_start();

	$output = <<<JS
const cstmSwiper_classChange = (target, swiper_name) => {
	if (!target) return;
	const target_Container = target.querySelector('.is-list-horizontal.swiper');
	target_Container.classList.add(swiper_name);
	target_Container.classList.remove('is-list-horizontal');
	return ;
};

const cstmSwiperShtcode_classChange = (target, swiper_name) => {
	if (!target) return;
	target.classList.add(swiper_name);
	target.classList.remove('is-list-horizontal');
	return ;
};

// Common Swiper Params
const cstm_common_swiper_params = {
	effect: 'slide',
	loop: true,
	loopAdditionalSlides: 1,
	slidesPerView: 'auto',
	spaceBetween: 8,
	grabCursor: true,
	freeMode: {
		enabled: true,
		momentum: false,
	},
}

const infinite_loop_swiper_params = {
	speed: 8000,
	autoplay: false, 
}
// Normal type
const normal_swiper_params = {
	speed: 2000,
	centeredSlides: true,
	autoplay: false, 
	watchSlidesProgress: true,
}


// for init infinite_loop_swiper
const initInfiniteSwiper = (postContainerSelector) => {
    const Post_Container = document.querySelectorAll(postContainerSelector);
    Post_Container.forEach(container => {
        const CstmSwiper = new Swiper(container, {
            ...cstm_common_swiper_params,
            ...infinite_loop_swiper_params,
            on: {
                afterInit: (swiper) => {
                    container.classList.add('is-init-after-post');
                },
                touchEnd: (swiper) => {
                    swiper.slideTo(swiper.activeIndex + 1);
                },
            }
        });

        const ObserverAutoplaySwiper = () => {
            const callback = (entries, obs) => {
                if (entries[0].isIntersecting) {
                    CstmSwiper.params.autoplay = {
                        delay: 0,
                        disableOnInteraction: false,
                    };
                    CstmSwiper.autoplay.start(); // autoplayを開始する
                } else {
                    CstmSwiper.autoplay.stop(); // autoplayを停止する
                }
            };
            const options = {
                root: null,
                rootMargin: "0%",
                threshold: 0
            };

            const observer = new IntersectionObserver(callback, options);
            observer.observe(container);
        };
        ObserverAutoplaySwiper();
    });
};
// for Cocoon block
const cstmInfiniteSwiper_blks = Array.from(document.querySelectorAll('.cstm-infinite-loop-swiper.block-box')).filter(element => {
	return element.querySelector(':scope > .is-list-horizontal.swiper') !== null;
});

if (cstmInfiniteSwiper_blks.length > 0) {
	cstmInfiniteSwiper_blks.forEach((cstmInfiniteSwiper_blk) => {
		cstmSwiper_classChange(cstmInfiniteSwiper_blk, 'cstm-infinite-loop');
	});
	// init
	initInfiniteSwiper('.cstm-infinite-loop-swiper .cstm-infinite-loop.swiper');
}

// for Cocoon shortcode
const cstmInfiniteSwiper_shtcodes = (document.querySelectorAll('.cstm-infinite-loop-swiper:not(.block-box).is-list-horizontal.swiper'));

if (cstmInfiniteSwiper_shtcodes.length > 0) {
	cstmInfiniteSwiper_shtcodes.forEach((cstmInfiniteSwiper_shtcode) => {
		cstmSwiperShtcode_classChange(cstmInfiniteSwiper_shtcode, 'cstm-infinite-loop');
	});
	// init
	initInfiniteSwiper('.cstm-infinite-loop-swiper.cstm-infinite-loop.swiper');
}

const initNormalSwiper = (containerSelector, btnNextSelector, btnPrevSelector, paginationSelector) => {
    const btnNext = document.querySelectorAll(btnNextSelector);
    const btnPrev = document.querySelectorAll(btnPrevSelector);
    const Post_Container = document.querySelectorAll(containerSelector);

    Post_Container.forEach(container => {
        const NormalCstmSwiper = new Swiper(container, {
            ...cstm_common_swiper_params,
            ...normal_swiper_params,
            pagination: {
                el: paginationSelector,
                type: 'progressbar'
            },
            navigation: {
                prevEl: btnPrevSelector,
                nextEl: btnNextSelector,
            },
            on: {
                afterInit: (swiper) => {
                    btnNext.forEach(btn => btn.setAttribute('data-btnon', 'true'));
                    btnPrev.forEach(btn => btn.setAttribute('data-btnon', 'true'));
                    container.classList.add('is-init-after-post');
                },
            }
        });

        const NormalObserverAutoplaySwiper = () => {
            const callback = (entries, obs) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        NormalCstmSwiper.params.autoplay = {
                            delay: 4000,
                            disableOnInteraction: false,
                            waitForTransition: false,
                        };
                        NormalCstmSwiper.autoplay.start(); // autoplayを開始する
                    } else {
                        NormalCstmSwiper.autoplay.stop(); // autoplayを停止する
                    }
                });
            };
            const options = {
                root: null,
								// 少し上から発火させる
                rootMargin: "20% 0px",
                threshold: 0
            };

            const observer = new IntersectionObserver(callback, options);
            observer.observe(container);
        };
        NormalObserverAutoplaySwiper();
    });
};
// for Cocoon block 
const cstmNormalSwiper_blks = Array.from(document.querySelectorAll('.cstm-normal-loop-swiper.block-box')).filter(element => {
	return element.querySelector(':scope > .is-list-horizontal.swiper') !== null;
});

if (cstmNormalSwiper_blks.length > 0) {
	cstmNormalSwiper_blks.forEach((cstmNormalSwiper_blk) => {
		cstmSwiper_classChange(cstmNormalSwiper_blk, 'cstm-normal-loop');

		const cstmNormalSwiper_blk_Container = cstmNormalSwiper_blk.querySelector('.cstm-normal-loop.swiper');
		const cstmNormalSwiper_blk_pagination = document.createElement('div');
		cstmNormalSwiper_blk_pagination.classList.add('swiper-pagination');
		cstmNormalSwiper_blk_Container.appendChild(cstmNormalSwiper_blk_pagination);
	});

	// init
	initNormalSwiper(
    '.cstm-normal-loop-swiper .cstm-normal-loop.swiper',
    '.cstm-normal-loop.swiper .swiper-button-next',
    '.cstm-normal-loop.swiper .swiper-button-prev',
    '.cstm-normal-loop-swiper .cstm-normal-loop .swiper-pagination'
);

}

// for Cocoon shortcode
const cstmNormalSwiper_shtcodes = (document.querySelectorAll('.cstm-normal-loop-swiper:not(.block-box).is-list-horizontal.swiper'));

if (cstmNormalSwiper_shtcodes.length > 0) {
	cstmNormalSwiper_shtcodes.forEach((cstmNormalSwiper_shtcode) => {
		cstmSwiperShtcode_classChange(cstmNormalSwiper_shtcode, 'cstm-normal-loop');

		const cstmNormalSwiper_shtcode_pagination = document.createElement('div');
		cstmNormalSwiper_shtcode_pagination.classList.add('swiper-pagination');
		cstmNormalSwiper_shtcode.appendChild(cstmNormalSwiper_shtcode_pagination);
	});

	// init
	initNormalSwiper(
    '.cstm-normal-loop-swiper.cstm-normal-loop.swiper',
    '.cstm-normal-loop-swiper.cstm-normal-loop.swiper .swiper-button-next',
    '.cstm-normal-loop-swiper.cstm-normal-loop.swiper .swiper-button-prev',
    '.cstm-normal-loop-swiper.cstm-normal-loop .swiper-pagination'
);
}

// mySwiper btn init
const mySwiperBtnOn = document.querySelectorAll('.is-list-horizontal.swiper .swiper-button-next');

const add_myswiper_params = {
	on: {
				afterInit: (swiper) => {
					mySwiperBtnOn.forEach(btn => btn.setAttribute('data-btnon', 'true'));
				},
		},
}

JS;
	$pattern_aft = '/<script>(.*?)const mySwiper = new Swiper\(\'\.is-list-horizontal\.swiper\', {(.*?)<\/script>/s';
	$replacement_aft = '<script>$1' . $output . 'const mySwiper = new Swiper(".is-list-horizontal.swiper", {' . '...add_myswiper_params,' . '$2</script>';

	$content_buf = preg_replace($pattern_aft, $replacement_aft, $content);
	echo $content_buf;
	$content = ob_get_clean();
	return $content;
}
