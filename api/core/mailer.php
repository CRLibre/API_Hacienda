<?php
/*
 * Copyright (C) 2017-2020 CRLibre <https://crlibre.org>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Send emails
 * @params info an array with:
 * to=mail@example.com
 * replyTo=replyTo@example.com (I will use the default mail in config if not)
 * from=from@example.com (I will use the default mail in config if not)
 * subject=the subject
 * message=the message
 */
//## to use the funtion mail() in line 32 you need install sendmail in your unix server
//Steps for Ubuntu linux
//1) install sendmail
//  sudo apt-get install sendmail
//2) config sendmail
//  sudo sendmailconfig
//3) Edit hosts file
//  sudo nano /etc/hosts
//      127.0.0.1 localhost su_dominio.com
//4) Restart apache
//  sudo service apache2 restart


function GenerateBody($message)
{
    $msg = '<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8"> <!-- utf-8 works for most cases -->
    <meta name="viewport" content="width=device-width"> <!-- Forcing initial-scale shouldn"t be necessary -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge"> <!-- Use the latest (edge) version of IE rendering engine -->
    <meta name="x-apple-disable-message-reformatting">  <!-- Disable auto-scale in iOS 10 Mail entirely -->
    <title></title> <!-- The title tag shows in email notifications, like Android 4.4. -->

    <!-- Web Font / @font-face : BEGIN -->
    <!-- NOTE: If web fonts are not required, lines 10 - 27 can be safely removed. -->

    <!-- Desktop Outlook chokes on web font references and defaults to Times New Roman, so we force a safe fallback font. -->
    <!--[if mso]>
        <style>
            * {
                font-family: sans-serif !important;
            }
        </style>
    <![endif]-->

    <!-- All other clients get the webfont reference; some will render the font and others will silently fail to the fallbacks. More on that here: http://stylecampaign.com/blog/2015/02/webfont-support-in-email/ -->
    <!--[if !mso]><!-->
    <!-- insert web font reference, eg: <link href="https://fonts.googleapis.com/css?family=Roboto:400,700" rel="stylesheet" type="text/css"> -->
    <!--<![endif]-->

    <!-- Web Font / @font-face : END -->

    <!-- CSS Reset : BEGIN -->
    <style>

        /* What it does: Remove spaces around the email design added by some email clients. */
        /* Beware: It can remove the padding / margin and add a background color to the compose a reply window. */
        html,
        body {
            margin: 0 auto !important;
            padding: 0 !important;
            height: 100% !important;
            width: 100% !important;
        }

        /* What it does: Stops email clients resizing small text. */
        * {
            -ms-text-size-adjust: 100%;
            -webkit-text-size-adjust: 100%;
        }

        /* What it does: Centers email on Android 4.4 */
        div[style*="margin: 16px 0"] {
            margin: 0 !important;
        }

        /* What it does: Stops Outlook from adding extra spacing to tables. */
        table,
        td {
            mso-table-lspace: 0pt !important;
            mso-table-rspace: 0pt !important;
        }

        /* What it does: Fixes webkit padding issue. Fix for Yahoo mail table alignment bug. Applies table-layout to the first 2 tables then removes for anything nested deeper. */
        table {
            border-spacing: 0 !important;
            border-collapse: collapse !important;
            table-layout: fixed !important;
            margin: 0 auto !important;
        }
        table table table {
            table-layout: auto;
        }

        /* What it does: Prevents Windows 10 Mail from underlining links despite inline CSS. Styles for underlined links should be inline. */
        a {
            text-decoration: none;
        }

        /* What it does: Uses a better rendering method when resizing images in IE. */
        img {
            -ms-interpolation-mode:bicubic;
        }

        /* What it does: A work-around for email clients meddling in triggered links. */
        *[x-apple-data-detectors],  /* iOS */
        .unstyle-auto-detected-links *,
        .aBn {
            border-bottom: 0 !important;
            cursor: default !important;
            color: inherit !important;
            text-decoration: none !important;
            font-size: inherit !important;
            font-family: inherit !important;
            font-weight: inherit !important;
            line-height: inherit !important;
        }

        /* What it does: Prevents Gmail from displaying a download button on large, non-linked images. */
        .a6S {
           display: none !important;
           opacity: 0.01 !important;
       }
       /* If the above doesn"t work, add a .g-img class to any image in question. */
       img.g-img + div {
           display: none !important;
       }

        /* What it does: Removes right gutter in Gmail iOS app: https://github.com/TedGoas/Cerberus/issues/89  */
        /* Create one of these media queries for each additional viewport size you"d like to fix */

        /* iPhone 4, 4S, 5, 5S, 5C, and 5SE */
        @media only screen and (min-device-width: 320px) and (max-device-width: 374px) {
            .email-container {
                min-width: 320px !important;
            }
        }
        /* iPhone 6, 6S, 7, 8, and X */
        @media only screen and (min-device-width: 375px) and (max-device-width: 413px) {
            .email-container {
                min-width: 375px !important;
            }
        }
        /* iPhone 6+, 7+, and 8+ */
        @media only screen and (min-device-width: 414px) {
            .email-container {
                min-width: 414px !important;
            }
        }

    </style>
    <!-- CSS Reset : END -->
    <!-- Reset list spacing because Outlook ignores much of our inline CSS. -->
    <!--[if mso]>
    <style type="text/css">
        ul,
        ol {
            margin: 0 !important;
        }
        li {
            margin-left: 30px !important;
        }
        li.list-item-first {
            margin-top: 0 !important;
        }
        li.list-item-last {
            margin-bottom: 10px !important;
        }
    </style>
    <![endif]-->

    <!-- Progressive Enhancements : BEGIN -->
    <style>

        /* What it does: Hover styles for buttons */
        .button-td,
        .button-a {
            transition: all 100ms ease-in;
        }
        .button-td-primary:hover,
        .button-a-primary:hover {
            background: #555555 !important;
            border-color: #555555 !important;
        }

        /* Media Queries */
        @media screen and (max-width: 600px) {

            .email-container {
                width: 100% !important;
                margin: auto !important;
            }

            /* What it does: Forces elements to resize to the full width of their container. Useful for resizing images beyond their max-width. */
            .fluid {
                max-width: 100% !important;
                height: auto !important;
                margin-left: auto !important;
                margin-right: auto !important;
            }

            /* What it does: Forces table cells into full-width rows. */
            .stack-column,
            .stack-column-center {
                display: block !important;
                width: 100% !important;
                max-width: 100% !important;
                direction: ltr !important;
            }
            /* And center justify these ones. */
            .stack-column-center {
                text-align: center !important;
            }

            /* What it does: Generic utility class for centering. Useful for images, buttons, and nested tables. */
            .center-on-narrow {
                text-align: center !important;
                display: block !important;
                margin-left: auto !important;
                margin-right: auto !important;
                float: none !important;
            }
            table.center-on-narrow {
                display: inline-block !important;
            }

            /* What it does: Adjust typography on small screens to improve readability */
            .email-container p {
                font-size: 17px !important;
            }
        }

    </style>
    <!-- Progressive Enhancements : END -->

    <!-- What it does: Makes background images in 72ppi Outlook render at correct size. -->
    <!--[if gte mso 9]>
    <xml>
        <o:OfficeDocumentSettings>
            <o:AllowPNG/>
            <o:PixelsPerInch>96</o:PixelsPerInch>
        </o:OfficeDocumentSettings>
    </xml>
    <![endif]-->

</head>
<!--
    The email background color (#222222) is defined in three places:
    1. body tag: for most email clients
    2. center tag: for Gmail and Inbox mobile apps and web versions of Gmail, GSuite, Inbox, Yahoo, AOL, Libero, Comcast, freenet, Mail.ru, Orange.fr
    3. mso conditional: For Windows 10 Mail
-->
<body width="100%" style="margin: 0; padding: 0 !important; mso-line-height-rule: exactly; background-color: #222222;">
    <center style="width: 100%; background-color: #f1f1f1e8;">
    <!--[if mso | IE]>
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #222222;">
    <tr>
    <td>
    <![endif]-->

        <!-- Visually Hidden Preheader Text : BEGIN -->
        <div style="display: none; font-size: 1px; line-height: 1px; max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden; mso-hide: all; font-family: sans-serif;">
            (Optional) This text will appear in the inbox preview, but not the email body. It can be used to supplement the email subject line or even summarize the email"s contents. Extended text preheaders (~490 characters) seems like a better UX for anyone using a screenreader or voice-command apps like Siri to dictate the contents of an email. If this text is not included, email clients will automatically populate it using the text (including image alt text) at the start of the email"s body.
        </div>
        <!-- Visually Hidden Preheader Text : END -->

        <!-- Create white space after the desired preview text so email clients don’t pull other distracting text into the inbox preview. Extend as necessary. -->
        <!-- Preview Text Spacing Hack : BEGIN -->
        <div style="display: none; font-size: 1px; line-height: 1px; max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden; mso-hide: all; font-family: sans-serif;">
            &zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;
        </div>
        <!-- Preview Text Spacing Hack : END -->

        <!-- Email Body : BEGIN -->
        <table align="center" role="presentation" cellspacing="0" cellpadding="0" border="0" width="600" style="margin: 0 auto;" class="email-container">
            <!-- Email Header : BEGIN -->
     
            <!-- Email Header : END -->

            <!-- Hero Image, Flush : BEGIN -->
              <tr> <td ><br></td> </tr>
            <tr>
                <td style="background-color: #ffffff;">
                    <img src="'.conf_get('compannyIMG', 'core').'" width="600" height="" alt="alt_text" border="0" style="width: 100%; max-width: 600px; height: auto; background: #dddddd; font-family: sans-serif; font-size: 15px; line-height: 15px; color: #555555; margin: auto;" class="g-img">
                </td>
            </tr>
            <!-- Hero Image, Flush : END -->

            <!-- 1 Column Text + Button : BEGIN -->
            <tr>
                <td style="background-color: #ffffff;">
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                        <tr>
                            <td style="padding: 20px; font-family: sans-serif; font-size: 15px; line-height: 20px; color: #555555;">
                                <h1 style="margin: 0 0 10px; font-size: 25px; line-height: 30px; color: #333333; font-weight: normal;">Su contraseña fue restaurada</h1>
                                <p style="margin: 0 0 10px;">Si usted no realizo la restauracion, por favor escribanos al correo de soporte y con gusto le ayudaremos a ubicar la IP de la maquina remota.</p>
                                <p	style="margin: 0 0 10px;">Contraseña nueva:</p>
                                <ul style="padding: 0; margin: 0; list-style-type: disc;">
                                    <li style="margin: 0 0 0 20px;" class="list-item-last">' . $message . '</li>
                                </ul>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 0 20px 20px;">
                                <!-- Button : BEGIN -->
                                <table align="center" role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin: auto;">
                                    <tr>
                                        <td class="button-td button-td-primary" style="border-radius: 4px; background: #222222;">
                                    </tr>
                                    <tr>
                                        <br>
                                        <br>
                                        </td>
                                    </tr>
                                </table>
                                <!-- Button : END -->
                            </td>
                        </tr>

                    </table>
                </td>
            </tr>
            <!-- 1 Column Text + Button : END -->
           
            <!-- Clear Spacer : BEGIN -->
            <tr>
                <td aria-hidden="true" height="40" style="font-size: 0px; line-height: 0px;">
                    &nbsp;
                </td>
            </tr>
            <!-- Clear Spacer : END -->

            <!-- 1 Column Text : BEGIN -->
           
            <!-- 1 Column Text : END -->

        </table>
        <!-- Email Body : END -->

        <!-- Email Footer : BEGIN -->
        <table align="center" role="presentation" cellspacing="0" cellpadding="0" border="0" width="600" style="margin: 0 auto;" class="email-container">
            <tr>
                
            </tr>
        </table>
        <!-- Email Footer : END -->

        <!-- Full Bleed Background Section : BEGIN -->
        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #709f2b;">
            <tr>
                <td valign="top">
                    <div style="max-width: 600px; margin: auto;" class="email-container">
                        
                    </div>
                </td>
            </tr>
        </table>
        <!-- Full Bleed Background Section : END -->

    <!--[if mso | IE]>
    </td>
    </tr>
    </table>
    <![endif]-->
    </center>
</body>
</html>
';
    return $msg;
}

function mailer_sendEmail($info)
{
    $msg = GenerateBody($info['message']);

    if (conf_get('type', 'mail') == 'smtp')
    {
        tools_useTool('phpmailer/vendor/autoload.php');
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        try
        {
            $mail->isSMTP();
            $mail->SMTPAuth     = true;
            $mail->Host         = conf_get('host',        'mail');
            $mail->Username     = conf_get('username',    'mail');
            $mail->Password     = conf_get('password',    'mail');
            $mail->SMTPSecure   = conf_get('secure',      'mail');
            $mail->Port         = conf_get('port',        'mail');

            $mail->setFrom(conf_get('address', 'mail'), conf_get('siteName', 'core'));
            $mail->addAddress($info['to']);

            $noreply = conf_get('noreply', 'mail');
            if (trim($noreply) != '')
                $mail->addReplyTo($noreply);

            $mail->isHTML();
            $mail->Subject  = $info['subject'];
            $mail->Body     = $msg;
            $mail->CharSet  = 'UTF-8';

            return $mail->send();
        }
        catch (Exception $e)
        {
            echo $mail->ErrorInfo;
        }

        return false;
    }
    else
    {
        $headers = sprintf("From: %s \r\n" .
            "Reply-To: %s \r\n" .
            'X-Mailer: PHP/ %s', conf_get('defaultMail', 'core', 'info@crlibre.org'), $info['replyTo'] != '' ? $info['replyTo'] : conf_get('defaultMail', 'core', 'info@crlibre.org'), phpversion());
        $headers .= "\r\nContent-Type: text/html; charset=ISO-8859-1\r\n";
        return mail($info['to'], $info['subject'], $msg, $headers);
    }
}
