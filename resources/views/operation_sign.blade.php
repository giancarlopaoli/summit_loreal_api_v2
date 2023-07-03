<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Billex</title>
  <link href='https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap' rel='stylesheet' type='text/css'>
  <style type="text/css">
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
      padding: 30px 0 10px 0;
    }

    .check h1 {
      font-size: 22px;
      color: #001489;
      font-weight: 600;
    }

    .line {
      width: 100%;
      height: 1px;
      margin: 0 auto;
      border-bottom: 1px solid #E6E8F4;
    }

    .line-full {
      width: 100%;
      height: 1px;
      margin: 10px auto 0 auto;
      border-bottom: 1px solid #E6E8F4;
    }

    .information h2 {
      font-size: 20px;
      font-weight: 600;
      margin-bottom: 0px;
      margin-top: 20px;
      color: #2C54DC;
    }
    
    .information p {
      font-size: 12px;
      font-weight: 400;
      margin: 0;
      color: #000541;
      width: 98%;
      margin: 20px 0 5px 0;
    }

    .information_details {
      width: 80%;
      margin: 0 auto 20px auto;
      background-color: #F6F9FF;
      border-radius: 10px;
      padding: 30px 30px;
      border-radius: 10px;
      text-align: center;
    }

    .information_details h4 {
      font-size: 16px;
      margin: 0 0 7px 0;
      color: #000541;
    }

    .information_details h4 span {
      color: #001489;
    }


    .information_details p, .information_details h5 {
      font-size: 12px;
      font-weight: 400;
      margin: 13px 0 0 0;
    }

    .information_details a {
      color: #2C54DC;
    }
  
    .warning {
      border: 1px dashed #2C54DC;
      border-radius: 8px;
      margin: 10px 0 30px 0;
      padding: 0 40px;
    }

  </style>
</head>
<body style="font-family: 'Poppins';">
  <table style="margin: 0; padding: 20px 40px; width: 800px; margin: 0 auto; background-color: #fff;" cellpadding="0" cellspacing="0">
    <tr>
      <td class="header" align="center">
        <img src="https://bill-upload.s3.amazonaws.com/static/img/logo.png" alt="" />
      </td>
    </tr>
    <tr>
      <td align="center" class="check">
        <img src="https://bill-upload.s3.amazonaws.com/static/img/confirm.png" alt="" width="160">
        <h1>INSTRUCCIÓN DE TRANSFERENCIA</h1>
      </td>
    </tr>
    <tr>
      <td>
        <div class="line"></div>
      </td>
    </tr>
    <tr>
      <td align="center" class="information">
        <h2>Estimado Equipo Corfid,</h2>
      </td>
    </tr>
    <tr>
      <td style="padding-top: 20px;width: 100%;
      background-color: #fff;">
      </td>
    </tr>
    <tr style="width: 650px;">
      <td align="center" class="information">
        <h4 style="margin-bottom: 0px;">Las instrucciones de transferencia siguientes se encuentran cargadas en el Telecrédito, pendiente de firmar:</h4>

        <tr>
          <td style="padding-top: 20px;width: 100%;
          background-color: #fff;">
          </td>
        </tr>

        <table>
            <tr><td><b>Fase de Operación</b></td><td>{{ $phase }}</td></tr>
            <tr><td><b>Tipo de Operación</b></td><td>{{ $type }}</td></tr>
            <tr><td><b>Fecha</b></td><td>{{ $date}}</td></tr>
            <tr><td><b>Cliente</b></td><td>{{ $client_name }}</td></tr>
            <tr><td><b>Importe transferido {{ $sent_currency }}</b></td><td> {{ $sent_amount }} </td></tr>
            <tr><td><b>Importe recibido {{ $received_currency }} </b></td><td> {{ $received_amount }} </td></tr>

            <tr><td><b>Fideicomiso deposito Cliente</b></td><td><table border = 1 style="width: 100%;"><tr><th>Banco</th><th>Cuenta</th><th>Monto</th></tr>
            @foreach ($client_escrow_accounts as $escrow_account)
            <tr>
              <td><img src="{{$escrow_account->bank->image}}" width="30" style="display: {{$show_image_client}}"> {{$escrow_account->bank->shortname}}</td>
              <td>Nro: {{$escrow_account->account_number}} <br>CCI: {{$escrow_account->cci_number}}</td>
              <td>{{ $sent_currency }}{{number_format($escrow_account->pivot->amount,2)}}</td>
            </tr>
            @endforeach
            </table></td></tr>

            <tr><td><b>Banco cuenta Cliente</b></td><td><table border = 1 style="width: 100%;"><tr><th>Banco</th><th>Cuenta</th><th>Monto</th></tr>
            @foreach ($client_account as $bank_account)
            <tr>
              <td><img src="{{$bank_account->bank->image}}" width="30" style="display: {{$show_image_client}}"> {{$bank_account->bank->shortname}}</td>
              <td>Nro: {{$bank_account->account_number}} <br>CCI: {{$bank_account->cci_number}}</td>
              <td>{{ $received_currency }}{{number_format($bank_account->pivot->amount,2)}}</td>
            </tr>
            @endforeach
            </table></td></tr>

            <br>
            <tr><td></td><td></td></tr>
            <tr><td><b>Cliente contraparte</b></td><td> {{ $counterpart_name }} </td></tr>
            <tr><td><b>Importe transferido {{ $received_currency }} </b></td><td>{{ $counterpart_sent_amount }}</td></tr>
            <tr><td><b>Importe recibido {{ $sent_currency }}</b></td><td>{{ $counterpart_received_amount }}</td></tr>

            <tr><td><b>Fideicomiso deposito Contraparte</b></td><td><table border = 1 style="width: 100%;"><tr><th>Banco</th><th>Cuenta</th><th>Monto</th></tr>
            @foreach ($counterpart_escrow_accounts as $escrow_account)
            <tr>
              <td><img src="{{$escrow_account->bank->image}}" width="30" style="display: {{$show_image_counterpart}}"> {{$escrow_account->bank->shortname}}</td>
              <td>Nro: {{$escrow_account->account_number}} <br>CCI: {{$escrow_account->cci_number}}</td>
              <td>{{ $sent_currency }}{{number_format($escrow_account->pivot->amount,2)}}</td>
            </tr>
            @endforeach
            </table></td></tr>

            <tr><td><b>Cuenta destino Contraparte</b></td><td><table border = 1 style="width: 100%;"><tr><th>Banco</th><th>Cuenta</th><th>Monto</th></tr>
            @foreach ($counterpart_bank_account as $bank_account)
            <tr>
              <td><img src="{{$bank_account->bank->image}}" width="30" style="display: {{$show_image_counterpart}}"> {{$bank_account->bank->shortname}}</td>
              <td>Nro: {{$bank_account->account_number}} <br>CCI: {{$bank_account->cci_number}}</td>
              <td>{{ $received_currency }}{{number_format($bank_account->pivot->amount,2)}}</td>
            </tr>
            @endforeach
            </table></td></tr>

            <tr><td><b>T/C pactado</b></td><td>{{$exchange_rate}}</td></tr>
            <tr><td><b>Comisión Cliente {{ $comission_currency }}</b></td><td>{{ $comission_amount }}</td></tr>
            <tr><td><b>Comisión Contraparte {{ $comission_currency }}</b></td><td>{{ $counterpart_comission_amount }}</td></tr>
            <tr><td><b>Spread Comisión Billex Cliente</b></td><td>{{ $client_spread_comission}}</td></tr>
            <tr><td><b>Spread Comisión Contraparte</b></td><td>{{ $counterpart_spread_comission }}</td></tr>

        </table>
        
      </td>
    </tr>
    <tr>
      <td style="padding-top: 30px;width: 100%;
      background-color: #fff;">
      <p>Plataforma Billex.</p>
      </td>
    </tr>
    <tr>
      <td align="center" style="background-color: #001489; padding: 25px 0 0 0;">
        <a href="https://www.facebook.com/BillexDivisas/" target="_blank">
            <img src="https://bill-upload.s3.amazonaws.com/static/img/social1.png" alt="" width="32" height="32" style="margin: 0 15px;">
        </a>
        <a href="https://www.linkedin.com/company/billex-divisas/" target="_blank">
          <img src="https://bill-upload.s3.amazonaws.com/static/img/social2.png" alt="" width="32" height="32" style="margin: 0 15px;">
        </a>
        <a href="https://www.youtube.com/channel/UC0hRfNpJN-1aX_VoaKa-ZVg/featured" target="_blank">
          <img src="https://bill-upload.s3.amazonaws.com/static/img/social3.png" alt="" width="32" height="32" style="margin: 0 15px;">
        </a>
      </td>
    </tr>
    <tr>
      <td style="background-color: #001489; width: 100%; height: 100%; padding: 0 0 10px 0;">
        <p style="font-size: 11px;
        margin: 0; text-align: center; color: #fff; margin: 20px 0;">Este mensaje es solo informativo, favor de no responder a este correo. <br>
          ©2022 Billex. Todos los derechos reservados</p>
        <p style="font-size: 11px;
        margin: 0; text-align: center; color: #fff; margin: 20px 0; margin-top: 30px;">Si tienes alguna duda ó pregunta, encuentra la información en nuestra <a href=""style="color: #fff;">página de ayuda</a>  o <a href=""style="color: #fff;">contáctenos</a></p>
      </td>
    </tr>
  </table>
</body>
</html> 