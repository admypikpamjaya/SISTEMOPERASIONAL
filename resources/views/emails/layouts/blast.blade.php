<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Email Blast</title>
</head>
<body style="margin:0;padding:0;background-color:#f3f4f6;">

<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f4f6;">
<tr>
<td align="center" style="padding:30px 15px;">

    <!-- MAIN CONTAINER -->
    <table width="600" cellpadding="0" cellspacing="0" style="
        background-color:#ffffff;
        border-radius:12px;
        overflow:hidden;
        font-family:Arial, Helvetica, sans-serif;
        box-shadow:0 10px 30px rgba(0,0,0,0.08);
    ">

        <!-- HEADER -->
        <tr>
            <td style="
                background:linear-gradient(90deg,#4F46E5,#9333EA);
                padding:20px 24px;
                text-align:left;
            ">
                <table width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td width="50" valign="middle">
                           <img src="{{ asset('images/logo_ypik.webp') }}" alt="logo_ypik" height="100" />
                        </td>
                        <td valign="middle" style="padding-left:12px;">
                            <div style="font-size:16px;font-weight:bold;color:#ffffff;">
                                SOY YPIK PAM JAYA
                            </div>
                            <div style="font-size:12px;color:#e0e7ff;">
                                Sistem Operasional Yayasan
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- CONTENT -->
        <tr>
            <td style="padding:28px 24px;color:#1f2937;font-size:14px;line-height:1.6;">
                @yield('content')
            </td>
        </tr>

        <!-- FOOTER -->
        <tr>
            <td style="background-color:#f9fafb;padding:18px 24px;">
                <table width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="font-size:12px;color:#6b7280;">
                            Email ini dikirim secara otomatis oleh
                            <strong>Sistem Operasional Yayasan YPIK</strong>.
                            <br>
                            Mohon tidak membalas email ini.
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-top:10px;font-size:11px;color:#9ca3af;">
                            © {{ date('Y') }} Yayasan YPIK PAM JAYA. All rights reserved.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

    </table>
    <!-- END MAIN CONTAINER -->

</td>
</tr>
</table>

</body>
</html>
