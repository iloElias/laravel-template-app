<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to AgroFast API</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f4f4f9;
            color: #333;
        }
        .container {
            text-align: center;
        }
        h1 {
            font-size: 2.5rem;
            color: #4CAF50;
        }
        p {
            font-size: 1.2rem;
            margin: 10px 0;
        }
        .version {
            margin-top: 20px;
            font-size: 0.9rem;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome to AgroFast API</h1>
        <p>Your reliable API service for agricultural solutions.</p>
        <p>Start building amazing applications with our API.</p>
        <div class="version">
            Laravel Version: {{ app()->version() }}
        </div>
    </div>
</body>
</html>