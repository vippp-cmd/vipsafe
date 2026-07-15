<?php
$wpsaf = wpsafelink_options();
$button_text = apply_filters( 'wp_safelink_button_download_text', $wpsaf['action_button_text_4'] );
$wpsaf['time_delay_message'] = str_replace('{time}', '<span id="wpsafe-time">' . $wpsaf['time_delay'] . '</span>', $wpsaf['time_delay_message']);

?><!DOCTYPE html>
<html lang="en">

<head>
    <title><?php the_title() ?></title>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <link rel='stylesheet' type='text/css' href='//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css'/>
    <link rel='stylesheet' type='text/css'
          href='//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css'/>
    <meta name="robots" content="noindex,nofollow">
    <script type='text/javascript' src='https://code.jquery.com/jquery-1.11.2.min.js'></script>
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans&display=swap" rel="stylesheet">

    <style>
        body {
            margin: 20px 0;
        }

        img {
            max-width: 100%;
        }

        .wpsafe-top {
            width: auto;
            text-align: center;
            margin-bottom: 20px;
        }

        .wpsafe-bottom {
            width: auto;
            text-align: center;
            margin-top: 0;
        }

        #wpsafe-generate {
            display: none;
        }

        #wpsafe-wait2 {
            display: none;
        }

        #wpsafe-link {
            display: none;
        }

        .adb {
            display: none;
            position: fixed;
            width: 100%;
            height: 100%;
            left: 0;
            top: 0;
            bottom: 0;
            background: rgba(51, 51, 51, 0.9);
            z-index: 10000;
            text-align: center;
            color: #111;
        }

        .adbs {
            margin: 0 auto;
            width: auto;
            min-width: 400px;
            position: fixed;
            z-index: 99999;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            padding: 20px 30px 30px;
            background: rgba(255, 255, 255, 0.9);
            -webkit-border-radius: 12px;
            -moz-border-radius: 12px;
            border-radius: 12px;
        }

        #wpsafe-wait1 {
            margin: 20px 0 0;
            text-align: center;
            width: 100%;
            font-size: 18px;
        }

        .base-timer {
            margin: 50px auto;
            position: relative;
            width: <?php echo $wpsaf['countdown_size'] ?? '250'; ?>px;
            height: <?php echo $wpsaf['countdown_size'] ?? '250'; ?>px;
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
            width: <?php echo $wpsaf['countdown_size'] ?? '250'; ?>px;
            height: <?php echo $wpsaf['countdown_size'] ?? '250'; ?>px;
            top: 0;
            display: <?php echo ($wpsaf['countdown_show_text'] == 'yes' ? 'flex' : 'none'); ?>;
            align-items: center;
            justify-content: center;
            font-size: <?php echo round(($wpsaf['countdown_size'] ?? 250) / 5.2); ?>px;
        }
    </style>
</head>

<body>
<div class="wpsafe-top text-center">
    <div><?php echo stripslashes($wpsaf['advertisement_top_1']); ?></div>
    <?php if ($wpsaf['timer_style'] == 'text') : ?>
        <div id="wpsafe-wait1">
            <?php echo $wpsaf['time_delay_message'] ?>
        </div>
    <?php else : ?>
        <div id="wpsafelink-countdown"></div>
    <?php endif; ?>

    <div id="wpsafe-generate"><a href="#wpsafegenerate" onclick="wpsafegenerate()">
            <img alt="wpsafelink image" src="<?php echo $wpsaf['action_button_image_1']; ?>" /></a>
    </div>
</div>

<div class="wpsafe-bottom text-center" id="wpsafegenerate">
    <div id="wpsafe-wait2"><img alt="wpsafelink image" src="<?php echo esc_url( $wpsaf['action_button_image_2'] ); ?>"/>
    </div>
    <div id="wpsafe-link"><a href="<?php echo esc_url( $_GET['linkr'] ); ?>" target="_blank" rel="nofollow noopener">
            <img alt="<?php echo esc_attr( $button_text ); ?>"
                 src="<?php echo esc_url( $wpsaf['action_button_image_4'] ); ?>"/></a></div>
    <div><?php echo stripslashes($wpsaf['advertisement_top_2']); ?></div>
</div>

<?php if ($wpsaf['anti_adblock'] == 'yes') : ?>
    <div class="adb" id="adb">
        <div class="adbs">
            <h3><?php echo $wpsaf['anti_adblock_header_1']; ?></h3>
            <p><?php echo $wpsaf['anti_adblock_header_2']; ?></p>
        </div>
    </div>
<?php endif; ?>

<script type="text/javascript">
    let count = <?php echo $wpsaf['time_delay']; ?>;
    let timerStyle = '<?php echo $wpsaf['timer_style'] ?? 'text'; ?>';
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

    detectAdBlock();

    function adBlockDetected() {
        document.getElementById("adb").setAttribute("style", "display:block");
        wpsafelinkCount = 10000;
    }
    <?php endif; ?>
    function timer() {
        count = count - 1;
        if (count <= 0) {
            document.getElementById('wpsafe-wait1').style.display = 'none';
            document.getElementById('wpsafe-generate').style.display = 'block';
            clearInterval(counter);
            return;
        }
        <?php if ($wpsaf['timer_style'] == 'text') : ?>
        var timeElement = document.getElementById("wpsafe-time");
        if (timeElement) {
            timeElement.innerHTML = count;
        }
        <?php endif; ?>
    }
    
    <?php if ($wpsaf['timer_style'] == 'text') : ?>
    var counter = setInterval(timer, 1000);
    <?php endif; ?>

    function wpsafegenerate() {
        document.getElementById('wpsafegenerate').focus();
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
    // Credit: Mateusz Rybczonec

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

    <?php if ($wpsaf['timer_style'] == 'countdown') : ?>
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
    <?php endif; ?>

    function onTimesUp() {
        <?php if ($wpsaf['timer_style'] == 'text') : ?>
        document.getElementById('wpsafe-wait1').style.display = 'none';
        <?php else : ?>
        document.getElementById('wpsafelink-countdown').style.display = 'none';
        clearInterval(timerInterval);
        <?php endif; ?>
        document.getElementById('wpsafe-generate').style.display = 'block';
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
</body>

</html>
