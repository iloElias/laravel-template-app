<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Email Notification</title>
</head>

<body>
    <table style="font-family: Trebuchet MS, Arial, sans-serif; max-width: 600px; width: 100%; border: 1px solid #F9F9F9; border-collapse: collapse;" cellspacing="0" cellpadding="0" border="0" align="center">
        <tbody>
            @include('components.email-logo')
            @yield('content')
        </tbody>
    </table>
</body>

</html>
