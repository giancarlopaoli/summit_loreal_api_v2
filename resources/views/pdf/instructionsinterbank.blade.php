<!DOCTYPE html>
<html lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Billex</title>
  <link href='https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap' rel='stylesheet' type='text/css'>
  <style type="text/css">
    html {
      margin: 20pt 15pt;
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
      margin: 0;
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
      margin-bottom: 0px;
      margin-top: 0px;
      color: #2C54DC;
    }
    
    .information p {
      font-size: 14px;
      font-weight: 400;
      margin: 0;
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
        <img src="https://bill-upload.s3.amazonaws.com/static/img/check.png" alt="">
        <h1 style="font-family: 'Poppins';">Tu operación fue registrada con éxito.</h1>
      </td>
    </tr>
    <tr>
      <td>
        <div class="line"></div>
      </td>
    </tr>
    <tr>
      <td align="center" class="information">
        <h2>¡Hola! {{$username}}</h2>
        <p>Aquí te compartimos el detalle de tu operación realizada con el cliente:</p>
        <h2>{{$client_name}}</h2>
      </td>
    </tr>
    <tr>
      <td style="height: 20px;"></td>
    </tr>
    <tr style="width: 650px; display: table-row;">
      <td class="information_details">
        <table>
          <tr>
            <th>
              <div class="information_details_item">
                <h4>Código de operación:</h4>
                <p>{{$code}}</p>
              </div>
            </th>
            <th>
              <div class="information_details_item_line"></div>
            </th>
            <th>
              <div class="information_details_item" style="text-align: left;">
                <h4>Fecha de operación</h4>
                <p style="text-align: center;"> <img src="https://bill-upload.s3.amazonaws.com/static/img/calendar.png" alt="" style="display: inline-block; vertical-align: middle;"> <span style="display: inline-block; vertical-align: middle;">{{$operation_date}}</span></p>
              </div>
            </th>
            <th>
              <div class="information_details_item hour" style="text-align: left; margin-left: 10px; width: 100%;">
                <p style="text-align: center;"> <img src="https://bill-upload.s3.amazonaws.com/static/img/clock.png" alt="" style="display: inline-block; vertical-align: middle;"> <span style="display: inline-block; vertical-align: middle;">{{$operation_time}}</span></p>
              </div>
            </th>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
      <td style="height: 30px;"></td>
    </tr>
    <tr>
      <td>
        <div class="line-full"></div>
      </td>
    </tr>
    <tr>
      <td>
        <table style="width: 100%; padding: 20px 20px;">
          <tr>
            <td>
              <h2 class="details_title">Detalle de Operación</h2>
            </td>
          </tr>
          <tr class="details_item">
            <td class="details_item_left">
              <p>Vas a transferir</p>
            </td>
            <td class="details_item_right">
              <p>{{$currency_sign}} {{$amount}}</p>
            </td>
          </tr>
          <tr>
            <td>
              <div class="details_line"></div>
            </td>
          </tr>


          <tr class="details_item">
            <td class="details_item_left">
              <p>Importe a recibir</p>
            </td>
            <td class="details_item_right">
              <p>{{$currency_sign}} {{$amount}}</p>
            </td>
          </tr>
          <tr class="details_item">
            <td class="details_item_left">
              <p>Tipo de cambio Compra</p>
            </td>
            <td class="details_item_right">
              <p>{{$exchange_rate}}</p>
            </td>
          </tr>
          <tr class="details_item">
            <td class="details_item_left">
              <p>Tipo de cambio Venta</p>
            </td>
            <td class="details_item_right">
              <p>{{$exchange_rate_selling}}</p>
            </td>
          </tr>
          <tr class="details_item">
            <td class="details_item_left">
              <p>Contravalor</p>
            </td>
            <td class="details_item_right">
              <p>{{$currency_sign}} {{$counter_value}}</p>
            </td>
          </tr>
          <tr class="details_item">
            <td class="details_item_left">
              <p>Comisión</p>
            </td>
            <td class="details_item_right">
              <p>{{$currency_sign}} {{$comission_amount}}</p>
            </td>
          </tr>
          <tr class="details_item">
            <td class="details_item_left">
              <p>IGV</p>
            </td>
            <td class="details_item_right">
              <p>{{$currency_sign}} {{$igv}}</p>
            </td>
          </tr>
          <tr>
            <td>
              <div class="details_line"></div>
            </td>
          </tr>
          <tr class="details_item">
            <td class="details_item_left deposit">
              <p>Vas a depositar</p>
            </td>
            <td class="details_item_right total">
              <p>{{$currency_sign}} {{$deposit_amount}}</p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
      <td>
        <div class="line-full"></div>
      </td>
    </tr>
    <tr>
      <td>
        <table style="width: 100%; margin-top: 20px; margin-bottom: 0px; padding: 20px;" class="table_accounts">
          <tr class="details_item">
            <td class="details_item_left">
              <p style="font-size: 18px; font-weight: 600; color: #001489;">Depositarás</p>
            </td>
            <td class="details_item_right">
              <p style="font-size: 18px; color: #2C54DC;">{{$currency_sign}} {{$deposit_amount}}</p>
            </td>
          </tr>
          <tr>
            <td colspan="2">
              <div class="details_line_deposit"></div>
            </td>
            <td></td>
          </tr>

          @if ( $use_escrow_account == 1)
            <tr class="details_item">
              <td class="details_item_left" colspan="2">
                <p><b>Beneficiario:</b> Intercambio Corfid - Fideicomiso Bill</p>
              </td>
            </tr>
            <tr>
              <td colspan="2">
                <div class="details_line_deposit"></div>
              </td>
            </tr>

            @foreach ($escrow_accounts as $escrow_account)

            <tr class="details_item account">
              <td class="details_item_left account" style="display: inline-block;" colspan="1">
                <img style="display: inline-block;vertical-align: middle;" src="{{ $escrow_account->bank->image }}" alt="">
                <div class="account_info" style="display: inline-block;vertical-align: middle;">
                  <p>{{ $escrow_account->bank->shortname }}</p>
                  <p>{{ $escrow_account->account_number }}</p>
                </div>
              </td>
              <td class="details_item_right" colspan="1">
                <p style="width: 200px; display: inline-block; text-align: right;"> {{ $escrow_account->currency->sign }} {{ number_format($escrow_account->pivot->amount,2)}} </p>
              </td>
            </tr>

            @endforeach
          @else
            <tr class="details_item">
              <td class="details_item_left" colspan="2">
                <p><b>Beneficiario:</b> {{ $vendor_bank_accounts[0]->client->name }}</p>
              </td>
            </tr>
            <tr>
              <td colspan="2">
                <div class="details_line_deposit"></div>
              </td>
            </tr>

            @foreach ($vendor_bank_accounts as $vendor_bank_account)

            <tr class="details_item account">
              <td class="details_item_left account" style="display: inline-block;" colspan="1">
                <img style="display: inline-block;vertical-align: middle;" src="{{ $vendor_bank_account->bank->image }}" alt="">
                <div class="account_info" style="display: inline-block;vertical-align: middle;">
                  <p>{{ $vendor_bank_account->bank->shortname }}</p>
                  <p>{{ $vendor_bank_account->account_number }}</p>
                </div>
              </td>
              <td class="details_item_right" colspan="1">
                <p style="width: 200px; display: inline-block; text-align: right;"> {{ $vendor_bank_account->currency->sign }} {{ number_format($vendor_bank_account->pivot->amount,2)}} </p>
              </td>
            </tr>

            @endforeach
          @endif

          <tr>
            <td colspan="2">
              <div class="details_line_deposit"></div>
            </td>
          </tr>
          <tr><th style="height: 10px;"></th></tr>
          <tr>
            <td colspan="2" class="deposit_warning">
              <p><span>RECUERDA: </span>NO se aceptarán depósitos EN EFECTIVO.</p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
      <td>
        <table style="width: 100%; margin: 0 auto; padding: 30px;" class="table_accounts">
          <tr class="details_item">
            <td class="details_item_left">
              <p style="font-size: 18px; font-weight: 600; color: #001489;">Recibirás</p>
            </td>
            <td class="details_item_right">
              <p style="font-size: 18px; color: #2C54DC;">{{$currency_sign}} {{$receive_amount}}</p>
            </td>
          </tr>
          <tr>
            <td colspan="2">
              <div class="details_line_deposit"></div>
            </td>
            <td></td>
          </tr>
          <tr class="details_item">
            <td class="details_item_left" colspan="2">
              <p><b>Cliente:</b> {{ $client_name }} </p>
            </td>
          </tr>
          <tr>
            <td colspan="2">
              <div class="details_line_deposit"></div>
            </td>
          </tr>

          @foreach ($bank_accounts as $bank_account)

          <tr class="details_item account">
            <td class="details_item_left account" style="display: inline-block;" colspan="1">
              <img style="display: inline-block;vertical-align: middle;" src="{{ $bank_account->bank->image }}" alt="">
              <div class="account_info" style="display: inline-block;vertical-align: middle;">
                <p>{{ $bank_account->bank->shortname }}</p>
                <p>{{ $bank_account->account_number }}</p>
              </div>
            </td>
            <td class="details_item_right" colspan="1">
              <p style="width: 200px; display: inline-block; text-align: right;"> {{ $bank_account->currency->sign }} {{ number_format($bank_account->pivot->amount,2)}} </p>
            </td>
          </tr>



          @endforeach
        </table>
      </td>
    </tr>
    <tr>
      <td>
        <div class="line-full"></div>
      </td>
    </tr>
    <tr style="margin: 0 auto;">
      <td class="information_details" style="display: inline-block;">
        <table style="width: 100%;">
          <tr style="width: 100%;">
            <td style="text-align: center; width: 100%; display: inline-block; justify-content: center; margin: 0 auto;">
              <img src="https://bill-upload.s3.amazonaws.com/static/img/check-circle.png" alt="" style="display: inline-block; vertical-align: middle;">
              <div style="margin-left: 10px; text-align: center; display: inline-block; vertical-align: middle;">
                <h4 style="font-size: 16px; color:#001489; margin: 0; display: inline-block; vertical-align: middle;">¡Gracias por confiar en nosotros!</h4>
                <p style="text-align: left;">Gracias por usar Billex.</p>
              </div>
            </td>
          </tr>
        </table>
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
        margin: 0; text-align: center; color: #fff; margin: 20px 0;">©2022 Billex. Todos los derechos reservados</p>
        <p style="font-size: 11px;
        margin: 0; text-align: center; color: #fff; margin: 20px 0; margin-top: 30px;">Si tienes alguna duda ó pregunta, encuentra la información en nuestra <a href=""style="color: #fff;">página de ayuda</a>  o <a href=""style="color: #fff;">contáctenos</a></p>
      </td>
    </tr>
  </table>
</body>
</html>