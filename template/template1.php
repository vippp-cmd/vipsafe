<?php
$wpsaf = wpsafelink_options();
$button_text = apply_filters( 'wp_safelink_button_download_text', $wpsaf['action_button_text_4'] );
if ($wpsaf['timer_style'] == 'text') {
    $wpsaf['time_delay_message'] = str_replace('{time}', '<span id="wpsafe-time">' . $wpsaf['time_delay'] . '</span>', $wpsaf['time_delay_message']);
}

?><!DOCTYPE html>
<html lang="en">

<head>
    <title><?php the_title() ?></title>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <meta name="robots" content="noindex,nofollow">
    <META NAME="robots" CONTENT="noindex,nofollow">
    <style>
        :root {
            --wpsafe-bg: #ffffff;
            --wpsafe-soft: #f8fafc;
            --wpsafe-text: #111827;
            --wpsafe-muted: #6b7280;
            --wpsafe-border: #e5e7eb;
            --wpsafe-primary: #1ABC9C;
            --wpsafe-primary-600: #16A085;
            --wpsafe-radius: 12px;
            --wpsafe-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -4px rgba(0, 0, 0, 0.06);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
            color: var(--wpsafe-text);
            background: var(--wpsafe-soft);
        }

        img {
            max-width: 100%;
            height: auto;
        }

        a {
            color: var(--wpsafe-primary);
            text-decoration: none;
        }

        .wpsafe-header {
            position: sticky;
            top: 0;
            z-index: 10;
            background: var(--wpsafe-bg);
            border-bottom: 1px solid var(--wpsafe-border);
        }

        .wpsafe-header-inner {
            max-width: 960px;
            margin: 0 auto;
            padding: 12px 16px;
            display: flex;
            align-items: center;
        }

        .wpsafe-logo {
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .wpsafe-logo img {
            height: 28px;
        }

        .wpsafe-container {
            max-width: 960px;
            margin: 32px auto;
            padding: 0 16px;
        }

        .wpsafe-card {
            background: var(--wpsafe-bg);
            border: 1px solid var(--wpsafe-border);
            border-radius: var(--wpsafe-radius);
            box-shadow: var(--wpsafe-shadow);
            overflow: hidden;
        }

        .wpsafe-card-body {
            padding: 20px;
        }

        .wpsafe-title {
            margin: 0 0 8px 0;
            font-size: 24px;
            line-height: 1.25;
        }

        .wpsafe-content {
            color: var(--wpsafe-muted);
            margin-bottom: 16px;
        }

        .wpsafe-top, .wpsafe-bottom {
            text-align: center;
        }

        .wpsafe-top {
            margin-bottom: 16px;
        }

        .wpsafe-divider {
            height: 1px;
            background: var(--wpsafe-border);
            margin: 16px 0;
        }

        #wpsafe-generate, #wpsafe-wait2, #wpsafe-link {
            display: none;
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

        .wpsafe-wait {
            font-weight: 500;
        }

        .wpsafe-wait #wpsafe-time {
            font-variant-numeric: tabular-nums;
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
            font-size: 16px;
            line-height: 1.6;
            color: #475569;
            position: relative;
            z-index: 1;
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
            stroke: #e5e7eb;
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

        @media (max-width: 640px) {
            .wpsafe-title {
                font-size: 20px;
            }

            .wpsafe-header-inner, .wpsafe-card-body {
                padding: 14px;
            }
        }
    </style>
</head>

<body>
<header class="wpsafe-header">
    <div class="wpsafe-header-inner">
        <a class="wpsafe-logo" href="#" aria-label="WP Safelink">
            <img alt="wpsafelink image"
                 src="<?php echo ! empty( $wpsaf['style_logo'] ) ? esc_url( $wpsaf['style_logo'] ) : ( $wpsaf['logo'] ?? wpsafelink_plugin_url() . '/assets/logo.png' ); ?>"/>
        </a>
    </div>
</header>

<main class="wpsafe-container" id="content-wrapper">
    <article class="wpsafe-card">
        <div class="wpsafe-card-body">
            <div class="wpsafe-top">
                <div class="wpsafe-ads-top-1"><?php echo wp_kses_stripslashes( $wpsaf['advertisement_top_1'] ); ?></div>
                <?php if ($wpsaf['timer_style'] == 'text') : ?>
                    <p class="wpsafe-wait" id="wpsafe-wait1"><?php echo $wpsaf['time_delay_message'] ?></p>
                <?php else : ?>
                    <div id="wpsafelink-countdown"></div>
                <?php endif; ?>
                <div id="wpsafe-generate">
                    <a style="cursor:pointer" onclick="wpsafegenerate()">
						<?php if ( $wpsaf['action_button'] == 'button' ) : ?>
                            <button class="wpsafe-btn"
                                    type="button"><?php echo $wpsaf['action_button_text_2']; ?></button>
						<?php else : ?>
                            <img src="<?php echo $wpsaf['action_button_image_2'] ?>"
                                 alt="<?php echo $wpsaf['action_button_text_2']; ?>"/>
						<?php endif; ?>
                    </a>
                </div>
                <div class="wpsafe-ads-top-2"><?php echo wp_kses_stripslashes( $wpsaf['advertisement_top_2'] ); ?></div>
            </div>

            <div class="wpsafe-divider"></div>

            <h1 class="wpsafe-title"><?php the_title() ?></h1>
            <div class="wpsafe-content"><?php the_content() ?></div>

            <div class="wpsafe-bottom" id="wpsafegenerate">
                <div class="wpsafe-footer-space"><?php echo wp_kses_stripslashes( $wpsaf['advertisement_bottom_1'] ); ?></div>
                <div id="wpsafe-wait2">
					<?php if ( $wpsaf['action_button'] == 'button' ) : ?>
                        <button class="wpsafe-btn" type="button"><?php echo $wpsaf['action_button_text_3']; ?></button>
					<?php else : ?>
                        <img src="<?php echo $wpsaf['action_button_image_3']; ?>"
                             alt="<?php echo $wpsaf['action_button_text_3'] ?>" id="image2"/>
					<?php endif; ?>
                </div>
                <div id="wpsafe-link">
                    <a href="<?php echo esc_url( $_GET['linkr'] ); ?>" target="_blank" rel="nofollow noopener">
						<?php if ( $wpsaf['action_button'] == 'button' ) : ?>
                            <button class="wpsafe-btn" type="button"><?php echo esc_html( $button_text ); ?></button>
						<?php else : ?>
                            <img src="<?php echo esc_url( $wpsaf['action_button_image_4'] ); ?>"
                                 alt="<?php echo esc_attr( $button_text ); ?>" id="image3"/>
						<?php endif; ?>
                    </a>
                </div>
                <div class="wpsafe-footer-space"><?php echo wp_kses_stripslashes( $wpsaf['advertisement_bottom_2'] ); ?></div>
            </div>
        </div>
    </article>
</main>

<?php if ($wpsaf['anti_adblock'] == 'yes') : ?>
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

<script type="text/javascript">
    let count = <?php echo $wpsaf['time_delay']; ?>;
    <?php if ($wpsaf['anti_adblock'] == 'yes') : ?>
    function adBlockDetected() {
        document.getElementById("adb").setAttribute("style", "display:block");
    }

    function adBlockNotDetected() {
        count = <?php echo $wpsaf['time_delay']; ?>;
    }

    if (typeof window.adblockDetector === 'undefined') {
        adBlockDetected();
    } else {
        window.adblockDetector.init(
            {
                debug: true,
                found: function () {
                    adBlockDetected();
                },
                notFound: function () {
                    adBlockNotDetected();
                }
            }
        );
    }
    <?php endif; ?>
    const counterWpSafelink = setInterval(timer, 1000);
    <?php $manual_scroll = $wpsaf['generate_manual_scroll'] ?? 'yes'; ?>

    function timer() {
        count = count - 1;
        if (count <= 0) {
            document.getElementById('wpsafe-wait1').style.display = 'none';
	        <?php if ( $manual_scroll === 'yes' ) : ?>
            // Manual scroll mode: hide generate button and start generation automatically
            if (document.getElementById('wpsafe-generate')) {
                document.getElementById('wpsafe-generate').style.display = 'none';
            }
            clearInterval(counterWpSafelink);
            if (typeof wpsafegenerate === 'function') {
                wpsafegenerate();
            }
            return;
	        <?php else: ?>
            document.getElementById('wpsafe-generate').style.display = 'block';
            clearInterval(counterWpSafelink);
            return;
	        <?php endif; ?>
        }
        document.getElementById("wpsafe-time").innerHTML = count;
    }

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
</script>

<?php if ($wpsaf['timer_style'] == 'countdown') : ?>
<script type="text/javascript">
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

    const TIME_LIMIT = <?php echo $wpsaf['time_delay']; ?>;
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
        <?php if ($wpsaf['timer_style'] == 'text') : ?>
        document.getElementById('wpsafe-wait1').style.display = 'none';
        <?php else : ?>
        document.getElementById('wpsafelink-countdown').style.display = 'none';
        <?php endif; ?>
        <?php if ( $manual_scroll === 'yes' ) : ?>
        // Manual scroll mode: hide generate button and start generation automatically
        if (document.getElementById('wpsafe-generate')) {
            document.getElementById('wpsafe-generate').style.display = 'none';
        }
        clearInterval(timerInterval);
        if (typeof wpsafegenerate === 'function') {
            wpsafegenerate();
        }
        <?php else: ?>
        document.getElementById('wpsafe-generate').style.display = 'block';
        clearInterval(timerInterval);
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
</script>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
