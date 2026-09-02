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
    background-color: #f4f4f5;
    font-family: Arial, Helvetica, sans-serif;
    color: #18181b;
">

<table
    width="100%"
    cellpadding="0"
    cellspacing="0"
    role="presentation"
    style="
        width: 100%;
        background-color: #f4f4f5;
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
                    background-color: #ffffff;
                    border-radius: 14px;
                    overflow: hidden;
                "
            >

                {{-- Content --}}
                <tr>
                    <td style="padding: 40px;">

                        <div style="
                            margin-bottom: 12px;
                            color: #B247B2;
                            font-size: 11px;
                            font-weight: 700;
                            letter-spacing: 2px;
                        ">
                            MY TICKETS
                        </div>

                        <h1 style="
                            margin: 0 0 16px;
                            color: #18181b;
                            font-size: 28px;
                            line-height: 1.2;
                            font-weight: 700;
                        ">
                            Verify your email
                        </h1>

                        <p style="
                            margin: 0 0 28px;
                            color: #71717a;
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
                            border: 1px solid #e4e4e7;
                            border-radius: 12px;
                            background-color: #fafafa;
                            text-align: center;
                        ">

                            <div style="
                                margin-bottom: 10px;
                                color: #71717a;
                                font-size: 10px;
                                font-weight: 700;
                                letter-spacing: 1.5px;
                            ">
                                VERIFICATION CODE
                            </div>

                            <div style="
                                color: #18181b;
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
                            color: #52525b;
                            font-size: 13px;
                            line-height: 1.6;
                        ">
                            This code expires at
                            <strong>
                                {{ $expiresAt->format('g:i A') }}
                            </strong>.
                        </p>

                        <p style="
                            margin: 0;
                            color: #a1a1aa;
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
                        border-top: 1px solid #e4e4e7;
                        color: #a1a1aa;
                        font-size: 11px;
                        line-height: 1.5;
                    ">
                        This is an automated message from
                        {{ config('app.name') }}.
                        Please do not reply to this email.
                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>

</body>
</html>