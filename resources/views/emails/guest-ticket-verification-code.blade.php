<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Ticket verification code</title>
</head>

<body style="
    margin: 0;
    padding: 0;
    background-color: #09090b;
    font-family: Arial, Helvetica, sans-serif;
    color: #fafafa;
">

<table
    width="100%"
    cellpadding="0"
    cellspacing="0"
    role="presentation"
    style="
        width: 100%;
        background-color: #09090b;
        padding: 40px 20px;
    "
>
    <tr>
        <td align="center">

            <table
                width="100%"
                cellpadding="0"
                cellspacing="0"
                role="presentation"
                style="
                    width: 100%;
                    max-width: 560px;
                    background-color: #111113;
                    border: 1px solid #27272a;
                    border-radius: 14px;
                    overflow: hidden;
                "
            >

                {{-- Content --}}
                <tr>
                    <td style="padding: 40px;">

                        <div style="
                            margin-bottom: 12px;
                            color: #c45ac4;
                            font-size: 11px;
                            font-weight: 700;
                            letter-spacing: 2px;
                        ">
                            MY TICKETS
                        </div>

                        <h1 style="
                            margin: 0 0 16px;
                            color: #ffffff;
                            font-size: 28px;
                            line-height: 1.2;
                            font-weight: 700;
                        ">
                            Verify your email
                        </h1>

                        <p style="
                            margin: 0 0 28px;
                            color: #a1a1aa;
                            font-size: 15px;
                            line-height: 1.7;
                        ">
                            Use the verification code below to securely
                            access your support ticket.
                        </p>

                        {{-- Code --}}
                        <div style="
                            margin-bottom: 28px;
                            padding: 24px;
                            border: 1px solid #3f2445;
                            border-radius: 12px;
                            background-color: #18121b;
                            text-align: center;
                        ">

                            <div style="
                                margin-bottom: 10px;
                                color: #a855a8;
                                font-size: 10px;
                                font-weight: 700;
                                letter-spacing: 1.5px;
                            ">
                                VERIFICATION CODE
                            </div>

                            <div style="
                                color: #ffffff;
                                font-size: 34px;
                                line-height: 1.2;
                                font-weight: 700;
                                letter-spacing: 7px;
                            ">
                                {{ $code }}
                            </div>

                        </div>

                        <p style="
                            margin: 0 0 8px;
                            color: #d4d4d8;
                            font-size: 13px;
                            line-height: 1.6;
                        ">
                            This code expires at
                            <strong style="color: #ffffff;">
                                {{ $expiresAt->format('g:i A') }}
                            </strong>.
                        </p>

                        <p style="
                            margin: 0;
                            color: #71717a;
                            font-size: 12px;
                            line-height: 1.6;
                        ">
                            If you did not request this code, you can
                            safely ignore this email.
                        </p>

                    </td>
                </tr>

                {{-- Footer --}}
                <tr>
                    <td style="
                        padding: 20px 40px;
                        border-top: 1px solid #27272a;
                        background-color: #0d0d0f;
                        color: #71717a;
                        font-size: 11px;
                        line-height: 1.5;
                    ">
                        This is an automated message from
                        <span style="color: #a855a8;">
                            {{ config('app.name') }}
                        </span>.
                        Please do not reply to this email.
                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>

</body>
</html>
