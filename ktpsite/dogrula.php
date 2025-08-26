<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kodu Doğrula</title>
<style>
    :root {
        --main-color: #00174A;
        --light-bg: #f4f6fc;
    }
    body {
        font-family: Arial, sans-serif;
        background: var(--light-bg);
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
    }
    .container {
        background: white;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        width: 100%;
        max-width: 400px;
        text-align: center;
    }
    h2 {
        color: var(--main-color);
        margin-bottom: 20px;
    }
    input {
        width: 100%;
        padding: 12px;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-size: 14px;
        margin-bottom: 15px;
        box-sizing: border-box;
    }
    button {
        width: 100%;
        background: var(--main-color);
        color: white;
        padding: 12px;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        cursor: pointer;
    }
    button:hover {
        background: #003088;
    }
</style>
</head>
<body>
<?php
require_once 'repositories/database_repository.php';
?>
<div class="container">
    <h2>Kodu Doğrula</h2>
    <form action="kod_dogrula.php" method="POST">
        <input type="text" name="token" placeholder="6 haneli kodu girin" required>
        <button type="submit">Doğrula</button>
    </form>
</div>
</body>
</html>
