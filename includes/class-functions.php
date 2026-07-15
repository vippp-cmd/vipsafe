<?php
/*
	@package : themeson.com
	author : Themeson
	Don't touch baby!
*/

use ReCaptcha\ReCaptcha;

function newpsafelink_data() {
	global $wpsafelink_core;
	$linktarget = apply_filters( 'wp_safelink_code', '' );


	// Check session for stored target link
	if ( isset( $_SESSION['wpsafelink_target'] ) ) {
		$linktarget = $_SESSION['wpsafelink_target'];
	}

	return $linktarget;
}

/**
 * The function for integrate wp safelink into your theme at header section
 */
function newwpsafelink_top() {
	$wpsaf = wpsafelink_options();
	if ( empty( $wpsaf ) ) {
		return;
	}

	$code = newpsafelink_data();
	if ( $code ) {
		$humanverification = $_COOKIE['enable_human_verification'] ?? false;
        if(isset($_POST['secondverification'])) {
            $humanverification = false;
        }

		if ($wpsaf['timer_style'] == 'text') {
			$wpsaf['time_delay_message'] = str_replace( '{time}', '<span id="wpsafe-time">' . $wpsaf['time_delay'] . '</span>', $wpsaf['time_delay_message'] );
		}
		?>
        <style>
            :root {
                --wpsafe-text: <?php echo esc_html( $wpsaf['style_text_color'] ?? '#111827' ); ?>;
                --wpsafe-muted: <?php echo esc_html( $wpsaf['style_muted_color'] ?? '#6b7280' ); ?>;
                --wpsafe-border: <?php echo esc_html( $wpsaf['style_border_color'] ?? '#e5e7eb' ); ?>;
                --wpsafe-primary: <?php echo esc_html( $wpsaf['style_accent_color'] ?? '#1ABC9C' ); ?>;
                --wpsafe-primary-600: <?php echo esc_html( $wpsaf['style_accent_hover_color'] ?? '#16A085' ); ?>;
                --wpsafe-radius: <?php echo esc_html( $wpsaf['style_radius'] ?? '10px' ); ?>;
                --wpsafe-shadow: 0 6px 10px rgba(0, 0, 0, .06), 0 2px 4px rgba(0, 0, 0, .04);
            }

            .wpsafe-root {
                font-family: <?php echo ( $wpsaf['style_font_family'] ?? 'system-ui' ) === 'system-ui' ? "-apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica, Arial, sans-serif" : esc_html( $wpsaf['style_font_family'] ); ?>;
                color: var(--wpsafe-text);
                font-size: <?php echo esc_html( $wpsaf['style_font_size'] ?? '16px' ); ?>;
                line-height: 1.6;
            }

            .wpsafe-top {
                clear: both;
                width: auto;
                text-align: center;
                margin-bottom: 16px;
            }

            .wpsafe-top img {
                display: block;
                margin: 0 auto;
                height: auto;
                max-width: 100%;
            }

            .wpsafe-bottom {
                clear: both;
                width: auto;
                text-align: center;
                margin-top: 0;
            }

            .wpsafe-bottom img {
                display: block;
                margin: 0 auto;
                height: auto;
                max-width: 100%;
            }

            #wpsafe-generate, #wpsafe-wait2, #wpsafe-link {
                display: none;
            }

            .wpsafe-wait {
                font-weight: 600;
                color: var(--wpsafe-text);
            }

            .wpsafe-wait #wpsafe-time {
                font-variant-numeric: tabular-nums;
            }

            .wpsafe-btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                padding: 10px 16px;
                border-radius: 8px;
                border: 1px solid transparent;
                background: var(--wpsafe-primary);
                color: #fff;
                font-weight: 600;
                cursor: pointer;
                transition: background .2s ease, transform .05s ease;
            }

            .wpsafe-btn:hover {
                background: var(--wpsafe-primary-600);
            }

            .wpsafe-btn:active {
                transform: translateY(1px);
            }

            .adb {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(15, 23, 42, 0.95);
                backdrop-filter: blur(8px);
                -webkit-backdrop-filter: blur(8px);
                z-index: 10000;
                animation: fadeIn 0.3s ease-in-out;
            }

            @keyframes fadeIn {
                from {
                    opacity: 0;
                }
                to {
                    opacity: 1;
                }
            }

            @keyframes slideUp {
                from {
                    transform: translate(-50%, -40%);
                    opacity: 0;
                }
                to {
                    transform: translate(-50%, -50%);
                    opacity: 1;
                }
            }

            .adbs {
                position: fixed;
                left: 50%;
                top: 50%;
                transform: translate(-50%, -50%);
                background: #ffffff;
                border: 1px solid rgba(226, 232, 240, 0.8);
                border-radius: 16px;
                box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
                padding: 32px 28px;
                width: min(420px, calc(100% - 32px));
                text-align: center;
                animation: slideUp 0.4s ease-out;
            }

            .adbs::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(251, 191, 36, 0.05);
                border-radius: 16px;
                pointer-events: none;
            }

            .adb-icon {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 64px;
                height: 64px;
                background: #fbbf24;
                border-radius: 50%;
                margin: 0 auto 20px;
                box-shadow: 0 4px 6px -1px rgba(251, 191, 36, 0.3), 0 2px 4px -1px rgba(251, 191, 36, 0.2);
                position: relative;
                z-index: 1;
            }

            .adb-icon svg {
                width: 32px;
                height: 32px;
                fill: #ffffff;
            }

            .adbs h3 {
                margin: 0 0 12px 0;
                font-size: 24px;
                font-weight: 600;
                color: #0f172a;
                letter-spacing: -0.025em;
                position: relative;
                z-index: 1;
            }

            .adbs p {
                margin: 0;
                font-size: 16x;
                line-height: 1.6;
                color: #475569;
                position: relative;
                z-index: 1;
            }

            .safelink-recatpcha {
                text-align: center;
            }

            .safelink-recatpcha > div {
                display: inline-block;
            }

            /* Countdown timer styles */
            .base-timer {
                margin: 30px auto;
                position: relative;
                width: <?php echo $wpsaf['countdown_size'] ?? '200'; ?>px;
                height: <?php echo $wpsaf['countdown_size'] ?? '200'; ?>px;
            }

            .base-timer__svg {
                transform: scaleX(-1);
            }

            .base-timer__circle {
                fill: none;
                stroke: none;
            }

            .base-timer__path-elapsed {
                stroke-width: <?php echo $wpsaf['countdown_stroke_width'] ?? '2'; ?>px;
                stroke: var(--wpsafe-border);
            }

            .base-timer__path-remaining {
                stroke-width: <?php echo $wpsaf['countdown_stroke_width'] ?? '2'; ?>px;
                stroke-linecap: round;
                transform: rotate(90deg);
                transform-origin: center;
                transition: 1s linear all;
                fill-rule: nonzero;
                stroke: currentColor;
            }

            .base-timer__path-remaining.green {
                color: <?php echo $wpsaf['countdown_color_start'] ?? '#41b883'; ?>;
            }

            .base-timer__path-remaining.orange {
                color: <?php echo $wpsaf['countdown_color_warning'] ?? '#ffa500'; ?>;
            }

            .base-timer__path-remaining.red {
                color: <?php echo $wpsaf['countdown_color_alert'] ?? '#ff0000'; ?>;
            }

            .base-timer__label {
                position: absolute;
                width: <?php echo $wpsaf['countdown_size'] ?? '200'; ?>px;
                height: <?php echo $wpsaf['countdown_size'] ?? '200'; ?>px;
                top: 0;
                display: <?php echo ($wpsaf['countdown_show_text'] == 'yes' ? 'flex' : 'none'); ?>;
                align-items: center;
                justify-content: center;
                font-size: <?php echo round(($wpsaf['countdown_size'] ?? 200) / 5.2); ?>px;
            }

        </style>
        <div class="wpsafe-root wpsafe-top text-center">
            <div><?php echo wp_kses_stripslashes( $wpsaf['advertisement_top_1'] ); ?></div>
	        <?php do_action( 'wpsafelink_top_content', $wpsaf ); ?>
	        <?php if ( $humanverification ) : ?>
		        <?php
		        $posts = array();
		        if ( $wpsaf['content_method'] == 'random' ) {
			        $args     = array(
				        'post_type'      => 'post',
				        'orderby'        => 'rand',
				        'posts_per_page' => 1,
			        );
			        $post_all = get_posts( $args );
			        $posts    = $post_all[0];
		        } else if ( $wpsaf['content_method'] == 'selected' ) {
			        $ID = explode( "\n", $wpsaf['content_ids'] );
			        shuffle( $ID );
			        foreach ( $ID as $id ) {
				        $posts = get_post( $id );
				        break;
			        }
		        }
		        ?>
                <form id="wpsafelink-landing" name="dsb" action="<?php echo get_permalink( $posts->ID ) ?>"
                      method="post">
	                <?php wp_nonce_field( 'wpsafelink_human', 'wpsl_nonce' ); ?>
                    <input type="hidden" name="secondverification" value="1"/>

                    <?php if ( $wpsaf['method_human_verification'] == 'recaptcha'): ?>
                        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
                        <div class="safelink-recatpcha">
                            <div class="g-recaptcha" data-sitekey="<?php echo $wpsaf['recaptcha_site_key']; ?>"
                                data-callback="wpsafelink_recaptcha"></div>
                        </div>

                        <script type="text/javascript">
                            window.CAPTCHA_SAFELINK = 'recaptcha';
                        </script>
                    <?php endif; ?>

                    <?php if ( $wpsaf['method_human_verification'] == 'hcaptcha'): ?>
                        <script src="https://hcaptcha.com/1/api.js" async defer></script>
                        <div class="safelink-recatpcha">
                            <div id="hcaptcha" class="h-captcha"
                                data-sitekey="<?php echo $wpsaf['hcaptcha_site_key']; ?>"></div>
                        </div>

                        <script type="text/javascript">
                            window.CAPTCHA_SAFELINK = 'hcaptcha';
                        </script>
                    <?php endif; ?>

                    <a href="<?php bloginfo( 'url' ); ?>" style="cursor:pointer;" onclick="return wpsafehuman()"
                       id="wpsafelinkhuman">
	                    <?php if ( $wpsaf['action_button'] == 'button' ) : ?>
                            <button type="button"
                                    class="wpsafe-btn"><?php echo $wpsaf['action_button_text_1']; ?></button>
	                    <?php else : ?>
                            <img src="<?php echo $wpsaf['action_button_image_1']; ?>" alt="human verification"/>
	                    <?php endif; ?>
                    </a>
                </form>
	        <?php else: ?>
                <?php if ($wpsaf['timer_style'] == 'text') : ?>
                    <div class="wpsafe-wait" id="wpsafe-wait1"><?php echo wp_kses_stripslashes( $wpsaf['time_delay_message'] ); ?></div>
                <?php else : ?>
                    <div id="wpsafelink-countdown"></div>
                <?php endif; ?>
                <div id="wpsafe-generate">
                    <a style="cursor: pointer;" onclick="wpsafegenerate()">
	                    <?php if ( $wpsaf['action_button'] == 'button' ) : ?>
                            <button type="button"
                                    class="wpsafe-btn"><?php echo $wpsaf['action_button_text_2']; ?></button>
	                    <?php else : ?>
                            <img src="<?php echo $wpsaf['action_button_image_2']; ?>"
                                 alt="<?php echo $wpsaf['action_button_text_2']; ?>"/>
	                    <?php endif; ?>
                    </a>
                </div>
	        <?php endif; ?>

            <div><?php echo wp_kses_stripslashes( $wpsaf['advertisement_top_2'] ); ?></div>
        </div>
        <script type="text/javascript">
            document.addEventListener("DOMContentLoaded", function () {
                if (document.getElementById('wpsafelinkhuman'))
                    document.getElementById('wpsafelinkhuman').style.display = "block";
            });

            function wpsafehuman() {
                if (window.CAPTCHA_SAFELINK && window.CAPTCHA_SAFELINK === 'recaptcha') {
                    const response = grecaptcha.getResponse();
                    if (response.length === 0) {
                        alert("<?php echo ! empty( $wpsaf['recaptcha_label'] ) ? $wpsaf['recaptcha_label'] : "Please complete reCAPTCHA verification"; ?>");
                        return false;
                    }
                }
                if (window.CAPTCHA_SAFELINK && window.CAPTCHA_SAFELINK === 'hcaptcha') {
                    const hcaptchaVal = document.getElementsByName("h-captcha-response")[0].value;
                    if (!hcaptchaVal) {
                        alert("<?php echo ! empty( $wpsaf['hcaptcha_label'] ) ? $wpsaf['hcaptcha_label'] : "Please complete Captcha verification"; ?>");
                        return false;
                    }
                }
                document.getElementById('wpsafelink-landing').submit();
                return false;
            }
        </script>
		<?php
	}
}

/**
 * The function for integrate wp safelink into your theme at footer section
 */
function newwpsafelink_bottom() {
	$wpsaf = wpsafelink_options();
	if ( empty( $wpsaf ) ) {
		return;
	}

	global $wpsafelink_core;

	$code = newpsafelink_data();
	if ( $code ) {
		$humanverification = $_COOKIE['enable_human_verification'] ?? false;
        if(isset($_POST['secondverification'])) {
            $humanverification = false;
        }

        $button_text       = apply_filters( 'wp_safelink_button_download_text', $wpsaf['action_button_text_4'] );

        ?>
        <div class="wpsafe-root wpsafe-bottom text-center" id="wpsafegenerate">
            <div><?php echo wp_kses_stripslashes( $wpsaf['advertisement_bottom_1'] ); ?></div>
            <div id="wpsafe-wait2">
	            <?php if ( $wpsaf['action_button'] == 'button' ) : ?>
                    <button type="button" class="wpsafe-btn"><?php echo $wpsaf['action_button_text_3']; ?></button>
	            <?php else : ?>
                    <img src="<?php echo $wpsaf['action_button_image_3']; ?>"
                         alt="<?php echo $wpsaf['action_button_text_3'] ?>" id="image2"/>
	            <?php endif; ?>
            </div>
            <div id="wpsafe-link">
	            <?php
	            // Build secure redirect URL (do not expose $code)
	            $payload        = array(
		            'safelink'            => $code,
	            );
	            $payload_json   = wp_json_encode( $payload );
	            $encrypted_link = $wpsafelink_core->encrypt_link( $payload_json );
	            $redirect_url   = home_url() . '?safelink_redirect=' . $encrypted_link;
	            ?>
                <a href="<?php echo esc_url( $redirect_url ); ?>" rel="nofollow noopener"
                   style="cursor:pointer">
	                <?php if ( $wpsaf['action_button'] == 'button' ) : ?>
                        <button type="button" class="wpsafe-btn"><?php echo $button_text; ?></button>
	                <?php else : ?>
                        <img src="<?php echo $wpsaf['action_button_image_4']; ?>"
                             alt="<?php echo $wpsaf['action_button_text_4'] ?>" id="image3"/>
	                <?php endif; ?>
                </a>
            </div>
            <div><?php echo wp_kses_stripslashes( $wpsaf['advertisement_bottom_2'] ); ?></div>
        </div>
        <script type="text/javascript">
            let wpsafelinkCount = <?php echo $wpsaf['time_delay']; ?>;

            <?php if ($wpsaf['anti_adblock'] == 'yes') : ?>
            async function detectAdBlock() {
                let adBlockEnabled = false
                const googleAdUrl = 'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js'
                try {
                    await fetch(new Request(googleAdUrl)).catch(_ => adBlockEnabled = true)
                } catch (e) {
                    adBlockEnabled = true
                } finally {
                    if (adBlockEnabled) adBlockDetected();
                }
            }

            detectAdBlock()

            function adBlockDetected() {
                document.getElementById("adb").setAttribute("style", "display:block");
                wpsafelinkCount = 10000;
            }
            <?php endif; ?>

            <?php
            // Only run timer JS when timer is actually shown (not during human verification)
            $show_timer_js = ! $humanverification;
            ?>
            <?php if( $show_timer_js ) : ?>
            <?php if ($wpsaf['timer_style'] == 'text') : ?>
            let wpsafelinkCounter = setInterval(timer, 1000);
            <?php endif; ?>
            <?php $manual_scroll = $wpsaf['generate_manual_scroll'] ?? 'yes'; ?>

            <?php if ($wpsaf['timer_style'] == 'text') : ?>
            function timer() {
                wpsafelinkCount = wpsafelinkCount - 1;
                if (wpsafelinkCount <= 0) {
                    var wait1El = document.getElementById('wpsafe-wait1');
                    var generateEl = document.getElementById('wpsafe-generate');
                    if (wait1El) wait1El.style.display = 'none';
                    if (generateEl) generateEl.style.display = 'block';
                    clearInterval(wpsafelinkCounter);

	                <?php if ( $manual_scroll === 'yes' ) : ?>
                    // Manual scroll mode: do not show the Generate button; start generation automatically
                    clearInterval(wpsafelinkCounter);
                    // Trigger generation without auto-scrolling; user will scroll manually
                    if (typeof wpsafegenerate === 'function') {
                        wpsafegenerate();
                    }
                    return;
	                <?php endif; ?>
                }
                var timeElement = document.getElementById("wpsafe-time");
                if (timeElement) {
                    timeElement.innerHTML = wpsafelinkCount;
                }
            }
            <?php endif; ?>

            <?php if ($wpsaf['timer_style'] == 'countdown') : ?>
            // Countdown circle timer
            const FULL_DASH_ARRAY = 283;
            const WARNING_THRESHOLD = 10;
            const ALERT_THRESHOLD = 5;

            const COLOR_CODES = {
                info: {
                    color: "green"
                },
                warning: {
                    color: "orange",
                    threshold: WARNING_THRESHOLD
                },
                alert: {
                    color: "red",
                    threshold: ALERT_THRESHOLD
                }
            };

            const TIME_LIMIT = wpsafelinkCount;
            let timePassed = 0;
            let timeLeft = TIME_LIMIT;
            let timerInterval = null;
            let remainingPathColor = COLOR_CODES.info.color;

            document.getElementById("wpsafelink-countdown").innerHTML = `
                <div class="base-timer">
                <svg class="base-timer__svg" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                    <g class="base-timer__circle">
                    <circle class="base-timer__path-elapsed" cx="50" cy="50" r="45"></circle>
                    <path
                        id="base-timer-path-remaining"
                        stroke-dasharray="283"
                        class="base-timer__path-remaining ${remainingPathColor}"
                        d="
                        M 50, 50
                        m -45, 0
                        a 45,45 0 1,0 90,0
                        a 45,45 0 1,0 -90,0
                        "
                    ></path>
                    </g>
                </svg>
                <span id="base-timer-label" class="base-timer__label">${formatTime(
                timeLeft
            )}</span>
                </div>
                `;

            startTimer();

            function onTimesUp() {
                document.getElementById('wpsafelink-countdown').style.display = 'none';
                document.getElementById('wpsafe-generate').style.display = 'block';
                clearInterval(timerInterval);
                <?php if ( $manual_scroll === 'yes' ) : ?>
                // Manual scroll mode: hide generate button and start generation automatically
                if (typeof wpsafegenerate === 'function') {
                    wpsafegenerate();
                }
                <?php endif; ?>
            }

            function startTimer() {
                timerInterval = setInterval(() => {
                    timePassed = timePassed += 1;
                    timeLeft = TIME_LIMIT - timePassed;
                    document.getElementById("base-timer-label").innerHTML = formatTime(
                        timeLeft
                    );
                    setCircleDasharray();
                    setRemainingPathColor(timeLeft);

                    if (timeLeft === 0) {
                        onTimesUp();
                    }
                }, 1000);
            }

            function formatTime(time) {
                const minutes = Math.floor(time / 60);
                let seconds = time % 60;

                if (seconds < 10) {
                    seconds = `0${seconds}`;
                }

                return `${minutes}:${seconds}`;
            }

            function setRemainingPathColor(timeLeft) {
                const {alert, warning, info} = COLOR_CODES;
                if (timeLeft <= alert.threshold) {
                    document
                        .getElementById("base-timer-path-remaining")
                        .classList.remove(warning.color);
                    document
                        .getElementById("base-timer-path-remaining")
                        .classList.add(alert.color);
                } else if (timeLeft <= warning.threshold) {
                    document
                        .getElementById("base-timer-path-remaining")
                        .classList.remove(info.color);
                    document
                        .getElementById("base-timer-path-remaining")
                        .classList.add(warning.color);
                }
            }

            function calculateTimeFraction() {
                const rawTimeFraction = timeLeft / TIME_LIMIT;
                return rawTimeFraction - (1 / TIME_LIMIT) * (1 - rawTimeFraction);
            }

            function setCircleDasharray() {
                const circleDasharray = `${(
                    calculateTimeFraction() * FULL_DASH_ARRAY
                ).toFixed(0)} 283`;
                document
                    .getElementById("base-timer-path-remaining")
                    .setAttribute("stroke-dasharray", circleDasharray);
            }
            <?php endif; ?>

            function wpsafegenerate() {
	            <?php
	            $advertisement_bottom_full_screen = $wpsaf['advertisement_bottom_full_screen'] ?? 'no';
	            if($advertisement_bottom_full_screen == 'yes') :
	            ?>
                document.getElementById('wpsafegenerate').style.height = '1500px';
	            <?php endif; ?>
	            <?php $manual = $wpsaf['generate_manual_scroll'] ?? 'yes'; ?>
	            <?php if ( $manual !== 'yes' ) : ?>
                if (document.getElementById('wpsafegenerate')) {
                    document.getElementById('wpsafegenerate').scrollIntoView({behavior: 'smooth', block: 'start'});
                }
	            <?php endif; ?>

                document.getElementById('wpsafe-link').style.display = 'none';
                document.getElementById('wpsafe-wait2').style.display = 'block';

                setInterval(function () {
                    document.getElementById('wpsafe-wait2').style.display = 'none';
                }, 2000);
                setInterval(function () {
                    document.getElementById('wpsafe-link').style.display = 'block';
                }, 2000);
            }
            <?php endif; ?>
        </script>

		<?php if ( $wpsaf['anti_adblock'] == 'yes' ) : ?>
            <div class="adb" id="adb">
                <div class="adbs">
                    <div class="adb-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                            <path fill-rule="evenodd" d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003zM12 8.25a.75.75 0 01.75.75v3.75a.75.75 0 01-1.5 0V9a.75.75 0 01.75-.75zm0 8.25a.75.75 0 100-1.5.75.75 0 000 1.5z"/>
                        </svg>
                    </div>
                    <h3><?php echo $wpsaf['anti_adblock_header_1']; ?></h3>
                    <p><?php echo $wpsaf['anti_adblock_header_2']; ?></p>
                </div>
            </div>
		<?php endif; ?>

		<?php
	}
}
