<!DOCTYPE html>
<html lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Loreal Summit</title>
  <style>
    @import url('https://fonts.cdnfonts.com/css/dinpro-bold');
    @import url('https://fonts.cdnfonts.com/css/dinpro-medium');
    @import url('https://fonts.cdnfonts.com/css/dinpro-light');
    
    body {
      font-family: 'DINPro-Medium', sans-serif;
      font-size: 10px;
      line-height: 1.5;
      margin: 0;
      padding: 0;
      color: #333333;
    }
    * {
      font-family: 'DINPro-Light', sans-serif !important;
      font-weight: 400;
      font-size: 1.1rem;
    }

    span, p {
      font-family: 'DINPro-Medium', sans-serif !important;
    }

    table {
      background-image: url('https://signme4.s3.amazonaws.com/public/loreal/images/background.jpg');
      background-repeat: no-repeat;
      background-size: 100% 100%;
      min-height: 120vh;
    }
    table tr, td {
    }
    .logo, .logo-top, .text1, .tira, .text2 {
      height: 220px;
    }
    .logo td {
    }
    .tira {
      height: 60px;
    }

    .text1, .text2 {
      height: 150px;
    }
    .text3 {
      height: 1px;
    }
    .text3 td {
      padding-bottom: 80px;

    }
    .text3 p {
      font-weight: 700;
      font-size: .9rem;
    }
    .text4 p {
      margin: 0;
    }
    .logo-top {
      height: 1px;
    }
    .logo {
      height: 250px;
    }
    .information_details {
      width: 30%;
      display: flex;
      margin: 10px auto;
      background-color: #F6F9FF;
      border-radius: 10px;
      padding: 10px 30px;
      border-radius: 10px;
    }

    .information_details h4,.information_details p {
      font-size: 12px;
      font-weight: 400;
      margin: 0 auto;
      display: flex;
      align-items: center;
      text-align: center;
    }

    .information_details p {
      font-weight: 500;
      margin-bottom: 5px;
      text-align: center;
      font-size: 22px;
    }

    .information_details {
      vertical-align: middle;
      color: #001489;
      font-size: 22px;
      font-weight: 500;
      text-align: center;
    }
  </style>
</head>
<body>
  <table style="margin: 0; padding: 20px 40px; width: 650px; margin: 0 auto; background-color: #fff;" cellpadding="0" cellspacing="0">
    <tr class="logo-top">
      <td align="center" >
        <img src="https://signme4.s3.amazonaws.com/public/loreal/images/logo-top.png" alt="" width="200px"/>
      </td>
    </tr>
    <tr class="logo">
      <td align="center">
        <img src="https://signme4.s3.amazonaws.com/public/loreal/images/logo.png" alt="" width="400px"/>
      </td>
    </tr>
    <tr class="text1">
      <td align="center">
        <h2>Hola, estás recibiendo este correo porque<br>hiciste una solicitud de recuperación de<br>contraseña para tu cuenta.</h2>
      </td>
    </tr>

    <tr class="text2">
      <td align="center">
        <h2>Te enviamos una contraseña temporal<br>para que puedas generar una nueva<br>dentro de la página web.</h2>
      </td>
    </tr>
    <tr style="width: 650px;">

      <td class="information_details" align="center">
        <p>{{ $new_password }}</p> 
      </td>
    </tr>
  </table>
</body>
</html>