<!DOCTYPE html>
<html lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Billex</title>
  <link href='https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap' rel='stylesheet' type='text/css'>
  <style type="text/css">
    html {
      margin: 50pt 15pt;
    }

    body {
      font-family: "Poppins",sans-serif;
      font-size: 16px;
      line-height: 1.5;
      margin: 0;
      padding: 0;
      color: #333333
    }
    * {
      font-family: "Poppins", sans-serif;
    }

    .header {
      background-color: #001489;
    }

    .check {
      padding: 40px 0 20px 0;
    }

    .check h1 {
      font-size: 24px;
      color: #001489;
      font-weight: 600;
    }

    .line {
      width: 90%;
      height: 1px;
      margin: 0 auto;
      border-bottom: 1px solid #E6E8F4;
    }

    .line-full {
      width: 100%;
      height: 1px;
      margin: 0;
      border-bottom: 1px solid #E6E8F4;
    }

    .information h2 {
      font-size: 20px;
      font-weight: 600;
      margin-bottom: 20px;
      margin-top: 20px;
      color: #2C54DC;
    }
    
    .information p {
      font-size: 14px;
      font-weight: 400;
      margin: 20;
      color: #000541;
    }

    .information_details {
      width: 90%;
      margin: 0px auto;
      background-color: #F6F9FF;
      border-radius: 10px;
      padding: 20px 30px;
      border-radius: 10px;
    }

    .information_details h4,.information_details p {
      font-size: 12px;
      font-weight: 400;
      margin: 0;
      align-items: center;
    }

    .information_details h4 {
      font-weight: 600;
      margin-bottom: 0px;
    }

    .information_details span {
      margin-left: 10px;
    }

    .information_details_item {
      margin: 0 auto;
    }

    .information_details_item_line {
      width: 1px;
      margin: 0 20px;
      height: 20px;
      border-right: 1px dashed #C7D1F9;
    }
    .information_details_item.hour {
      display: flex;
      align-items: center;
      margin-top: 22px;
      margin-left: 20px;
    }

    .information_details_item span {
      margin-left: 5px;
      margin-top: 2.5px;
      font-weight: 400;
    }

    .details_item {
      width: 100%;
    }

    .details_line {
      width: 153%;
      height: 1px;
      border-bottom: 1px dashed #D4E1FD;
      margin: 5px 0 10px 0;
    }

    .details_line_deposit {
      width: 100%;
      height: 1px;
      border-bottom: 1px dashed #D4E1FD;
      margin: 15px 0;
    }

    .details_item_left {
      text-align: left;
    }

    .details_item_left.account {
      display: flex;
      text-align: left;
    }

    .details_item_left p {
      font-size: 14px;
      font-weight: 400;
      margin: 0;
      margin-bottom: 5px;
      color: #000541;
    }

    .details_item_left.deposit p {
      font-weight: 600;
    }
    
    .details_item_right {
      text-align: right;
    }

    .details_item_right p {
      font-size: 14px;
      font-weight: 600;
      margin: 0;
      margin-bottom: 5px;
      color: #000541;
    }

    .details_item_right.total p {
      font-size: 18px;
      color: #2C54DC;
    }

    .details_title {
      font-size: 18px;
      font-weight: 600;
      color: #001489;
    }

    .account_info {
      margin-left: 10px;
    }

    .account_info p:first-child {
      font-weight: 600;
    }

    .account_space {
      padding: 10px 0;
    }

    .table_accounts {
      border-radius: 15px;
      box-shadow: 0px 8px 24px rgba(44, 84, 220, 0.12);
    }

    .deposit_document {
      width: 100%;
      background-color: #F6F9FF;
      border-radius: 8px;
      padding: 10px 20px;
      margin: 30px 0;
    }

    .deposit_document_item {
      margin: 0 auto;
    }

    .deposit_document_line {
      width: 1px;
      height: 20px;
      margin: auto 10px;
      border-right: 1px solid #C7D1F9;
    }

    .deposit_document_item p{
      font-size: 12px;
    }

    .deposit_document_item span{
      font-weight: 600;
      color:#001489;
      margin-left: 5px;
    }

    .deposit_warning {
      width: 100%;
      border-radius: 8px;
      border: 1px dashed #2C54DC;
    }

    .deposit_warning p {
      font-size: 12px;
      text-align: center;
    }

    .deposit_warning span {
      font-weight: 600;
      color: #000541;
    }

    .advice {
      text-align: center;
      padding: 10px 40px 0 40px;
    }

    .advice p {
      font-size: 12.5px;
    }

    .buttons{
      margin: 0 auto;
    }

    .buttons button {
      padding: 8px 25px;
      margin: 0 5px;
      border-radius: 8px;
      border: 0;
      cursor: pointer;
      display: flex;
      align-items: center;
    }
    
    .buttons a button{
      background-color: #F7B825;
      color: #fff;
    }

    .buttons a button{
      margin-top: 0;
      padding: 10px 50px;
    }

    .buttons a p {
      margin: 0
    }

    .buttons a {
      text-decoration: none;
      cursor: pointer;
    }

    .buttons a img {
      margin-left: 10px;
    }

  </style>
</head>
<body>
  <table style="margin: 0; padding: 20px 20px; width: 650px; margin: 0 auto; background-color: #fff;" cellpadding="0" cellspacing="0">
    <tr>
      <td class="header" align="center">
        <img src="https://bill-upload.s3.amazonaws.com/static/img/logo.png" alt="" />
      </td>
    </tr>
    <tr>
      <td align="center" class="check">
        <img src="./images/win.png" alt="">
        <h1>FACTURA ELECTRÓNICA</h1>
      </td>
    </tr>
    <tr>
      <td>
        <div class="line"></div>
      </td>
    </tr>
    <tr>
      <td align="center" class="information">
        <h2>¡Hola!</h2>
        <h2>{{ $client_name}}</h2>
        <p>Te hacemos llegar la factura electrónica {{ $invoice_serie}}-{{ $invoice_number}}</p>
      </td>
    </tr>
    <tr>
      <td>
        <div class="line" style="margin-top: 30px;"></div>
      </td>
    </tr>
    <tr>
      <td class="buttons" align="center">
        <p style="font-size: 13px;">En el siguiente Botón podrás descargar tu comprobante electrónico:</p>
        <a href="{{ $invoice_url }}" style="cursor:pointer !important;" target="_blank"><button style="padding-top: 20px;align-items:center !important;margin: 20px 0 0 0;cursor:pointer;"> <p>Descargar Comprobante </p><img src="https://bill-upload.s3.amazonaws.com/static/img/download.png" alt=""></button></a>

      </td>
    </tr>

    <tr>
      <td align="center">
        <p style="font-size: 14px; margin: 30px 0 20px 0;">
          Gracias por utilizar Billex
        </p>
      </td>
    </tr>
    <tr>
      <td style="padding-top: 10px;width: 100%;
      background-color: #fff;">
      </td>
    </tr>
    <tr>
      <td style="width: 100%;
      background-color: #F7B825; padding: 0; padding-top: 5px;">
      </td>
    </tr>
    <tr>
      <td align="center" style="background-color: #001489; padding: 30px 0 0 0;">
        <a href=""><img src="https://bill-upload.s3.amazonaws.com/static/img/social1.png" alt="" style="margin: 0 15px;" width="30"></a>
        <a href=""><img src="https://bill-upload.s3.amazonaws.com/static/img/social2.png" alt="" style="margin: 0 15px;" width="30"></a>
        <a href=""><img src="https://bill-upload.s3.amazonaws.com/static/img/social3.png" alt="" style="margin: 0 15px;" width="30"></a>
      </td>
    </tr>
    <tr>
      <td style="background-color: #001489; width: 100%; padding: 0 0 10px 0;">
        <p style="font-size: 11px;
        margin: 0; text-align: center; color: #fff; margin: 20px 0;">©2023 Billex. Todos los derechos reservados</p>
        <p style="font-size: 11px;
        margin: 0; text-align: center; color: #fff; margin: 20px 0; margin-top: 30px;">Si tienes alguna duda ó pregunta, encuentra la información en nuestra <a href=""style="color: #fff;">página de ayuda</a>  o <a href=""style="color: #fff;">contáctenos</a></p>
      </td>
    </tr>
  </table>
</body>
</html>