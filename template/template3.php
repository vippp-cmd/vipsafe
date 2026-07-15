<?php
$wpsaf = wpsafelink_options();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title><?php the_title() ?></title>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <meta name="robots" content="noindex,nofollow">
    <style>
        body {
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            font-size: 14px;
            line-height: 1.42857143;
            color: #333;
            background-color: #fff;
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

        #content-wrapper {
            text-align: center;
            border: 1px solid #ddd;
            padding: 20px;
            max-width: 1000px;
            margin: 0 auto;
        }

        #content-wrapper .btn-primary {
            display: inline-block;
            padding: 6px 12px;
            margin-bottom: 0;
            font-size: 14px;
            font-weight: 400;
            line-height: 1.42857143;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            -ms-touch-action: manipulation;
            touch-action: manipulation;
            cursor: pointer;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            background-image: none;
            border: 1px solid #16A085;
            border-radius: 4px;
            color: #fff;
            background-color: #1ABC9C;
        }

        .safelink-recatpcha {
            text-align: center;
        }

        .safelink-recatpcha > div {
            display: inline-block;
        }
    </style>
</head>

<body>
<section id="content-wrapper" style='margin-top:80px'>
    <div class='container'>
        <div class='row'>
            <div class='col-md-12'>
                <div class='panel panel-default'>
                    <div class='panel-body'>
                        <div class="wpsafe-top text-center">
                            <h3 style="color:red;">Verifing Your Link.. Please wait..</h3>
                            <p>Please click the button below to proceed to the destination page.</p>
                            <form id="wpsafelink-landing" name="dsb" action="<?php the_permalink() ?>" method="post">
                                <input type="hidden" name="newwpsafelink" value="<?php echo $_GET['linkr_encrypt'] ?>">

                                <?php if ($wpsaf['captcha'] == 'recaptcha' && $wpsaf['captcha_enable'] == 'yes'): ?>
                                    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
                                    <div class="safelink-recatpcha">
                                        <div class="g-recaptcha"
                                             data-sitekey="<?php echo $wpsaf['recaptcha_site_key']; ?>"
                                             data-callback="wpsafelink_recaptcha"></div>
                                    </div>

                                    <script type="text/javascript">
                                        window.RECAPTCHA_SAFELINK = 'recaptcha';
                                    </script>
                                <?php endif; ?>

                                <?php if ($wpsaf['captcha'] == 'hcaptcha' && $wpsaf['captcha_enable'] == 'yes'): ?>
                                    <script src="https://hcaptcha.com/1/api.js" async defer></script>
                                    <div class="safelink-recatpcha">
                                        <div id="hcaptcha" class="h-captcha"
                                             data-sitekey="<?php echo $wpsaf['hcaptcha_site_key']; ?>"></div>
                                    </div>

                                    <script type="text/javascript">
                                        window.HCAPTCHA_SAFELINK = 'hcaptcha';
                                    </script>
                                <?php endif; ?>

                                <button class="btn btn-primary" type="button" value="Submit"
                                        onclick="return wpsafehuman()">Im a Human
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script type="text/javascript">
    <?php if($wpsaf['skip_verification'] == 'yes'): ?>
    document.getElementById('wpsafelink-landing').submit();
    <?php endif; ?>

    document.addEventListener("DOMContentLoaded", function () {
        if (document.getElementById('wpsafelinkhuman'))
            document.getElementById('wpsafelinkhuman').style.display = "block";
    });

    function wpsafehuman() {
        if (window.RECAPTCHA_SAFELINK && window.RECAPTCHA_SAFELINK === 'recaptcha') {
            const response = grecaptcha.getResponse();
            if (response.length === 0) {
                alert("<?php echo !empty($wpsaf['recaptcha_label']) ? $wpsaf['recaptcha_label'] : "Please complete reCAPTCHA verification"; ?>");
                return false;
            }
        }
        if (window.HCAPTCHA_SAFELINK && window.HCAPTCHA_SAFELINK === 'hcaptcha') {
            const hcaptchaVal = document.getElementsByName("h-captcha-response")[0].value;
            if (!hcaptchaVal) {
                alert("<?php echo !empty($wpsaf['hcaptcha_label']) ? $wpsaf['hcaptcha_label'] : "Please complete Captcha verification"; ?>");
                return false;
            }
        }
        document.getElementById('wpsafelink-landing').submit();
        return false;
    }
</script>
</body>

</html>