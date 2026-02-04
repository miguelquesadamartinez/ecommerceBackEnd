<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <meta name="robots" content="noindex,nofollow" />
        <style>            
            body { background-color: #F9F9F9; font: 14px/1.4 Helvetica, Arial, sans-serif; margin: 0; padding-bottom: 45px; }
            a { cursor: pointer; text-decoration: none; }
            a:hover { text-decoration: underline; }
            abbr[title] { border-bottom: none; cursor: help; text-decoration: none; }
            code, pre { font: 13px/1.5 Consolas, Monaco, Menlo, "Ubuntu Mono", "Liberation Mono", monospace; }
            table, tr, th, td { background: #FFFFFF; border-collapse: collapse; vertical-align: top; }
            table { background: #FFFFFF; border: 1px solid #E0E0E0; box-shadow: 0px 0px 1px rgba(128, 128, 128, .2); margin: 1em 0; width: 100%; }
            table th, table td { border: solid #E0E0E0; border-width: 1px 0; padding: 8px 10px; }
            table th { background-color: #E0E0E0; font-weight: bold; text-align: left; }
            .hidden-xs-down { display: none; }
            .block { display: block; }
            .break-long-words { -ms-word-break: break-all; word-break: break-all; word-break: break-word; -webkit-hyphens: auto; -moz-hyphens: auto; hyphens: auto; }
            .text-muted { color: #999; }
            .container { max-width: 100%; padding: 0 15px; }
            .container::after { content: ""; display: table; clear: both; }
            .exception-summary { background: #B0413E; border-bottom: 2px solid rgba(0, 0, 0, 0.1); border-top: 1px solid rgba(0, 0, 0, .3); flex: 0 0 auto; margin-bottom: 30px; }
            .exception-message-wrapper { display: flex; align-items: center; min-height: 70px; }
            .exception-message { flex-grow: 1; padding: 30px 0; }
            .exception-message, .exception-message a { color: #FFF; font-size: 21px; font-weight: 400; margin: 0; }
            .exception-message.long { font-size: 18px; }
            .exception-message a { border-bottom: 1px solid rgba(255, 255, 255, 0.5); font-size: inherit; text-decoration: none; }
            .exception-message a:hover { border-bottom-color: #ffffff; }
            .exception-illustration { flex-basis: 111px; flex-shrink: 0; height: 66px; margin-left: 15px; opacity: .7; }
            .trace + .trace { margin-top: 30px; }
            .trace-head .trace-class { background-color:rgb(255, 255, 255); color:rgb(0, 0, 0); font-size: 10px; margin: 0; position: relative; }
  
            .trace-message { font-size: 14px; font-weight: normal; margin: .5em 0 0; }
  
            .trace-file-path, .trace-file-path a { margin-top: 3px; font-size: 13px; }
            .trace-type { padding: 0 2px; }
            .trace-arguments { color: #777777; font-weight: normal; padding-left: 2px; }

            .footer { margin: auto; width: 90%; padding: 10px; text-align: center; }
            
            /* Estilos para la tabla de datos */
            .data-table { width: 80%; margin-top: 20px; border-collapse: collapse; }
            .data-table th, .data-table td { border: 1px solid #E0E0E0; padding: 8px; text-align: left; }
            .data-table th { background-color: #D3D3D3; font-weight: bold; }
            .data-table .section-header { background-color: #D3D3D3; font-weight: bold; text-align: center; }
            .data-table .section-header.red { color: #FF0000; }
  
            @media (min-width: 575px) {
                .hidden-xs-down { display: initial; }
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div style="background-color:rgb(255, 255, 255); color:rgb(0, 0, 0); font-size: 12px; margin: 0; position: relative;">
                « Bonjour,&nbsp;
            </div>
            <div style="background-color:rgb(255, 255, 255); color:rgb(0, 0, 0); font-size: 12px; margin: 0; position: relative;">
                &nbsp;
            </div>
            <div style="background-color:rgb(255, 255, 255); color:rgb(0, 0, 0); font-size: 12px; margin: 0; position: relative;">
                {{$emailText}} <span style="color: red;">{{$order_reference}}</span>
                @if($emailText_2)
                    <span style="color: red; display: block; margin-top: 10px;">{{$emailText_2}}</span>
                @endif
            </div>
            <div style="background-color:rgb(255, 255, 255); color:rgb(0, 0, 0); font-size: 12px; margin: 0; position: relative;">
                &nbsp;
            </div>
            @if(isset($excelData) && !empty($excelData))
            <table class="data-table">
                <tr>
                    <th width="35%">INFORMATIONS</th>
                    <th width="65%">Correspondance dans SAP</th>
                </tr>
                
                @foreach($excelData['header'] as $field => $value)
                <tr>
                    <td>{{ $field }}</td>
                    <td>{{ $value }}</td>
                </tr>
                @endforeach
                <tr>
                    <td colspan="2" class="section-header">ADRESSE DE LA PHARMACIE</td>
                </tr>
                
                @foreach($excelData['address'] as $field => $value)
                <tr>
                    <td>{{ $field }}</td>
                    <td>{{ $value }}</td>
                </tr>
                @endforeach
                <tr>
                    <td colspan="2" class="section-header red">
                    <span style="color: red;">ANCIENNE ADRESSE DE LA PHARMACIE UNIQUEMENT SI DEMENAGEMENT</span>
                    </td>
                </tr>
                
                @foreach($excelData['oldAddress'] as $field => $value)
                <tr>
                    <td>{{ $field }}</td>
                    <td>{{ $value }}</td>
                </tr>
                @endforeach
                <tr>
                    <td colspan="2" class="section-header">DONNEES BANCAIRES</td>
                </tr>
                
                <tr>
                    <td>Code banque + code guichet</td>
                    <td>{{ $excelData['bankCodes'] ?? '' }}</td>
                </tr>
                
                @foreach($excelData['bank'] as $field => $value)
                <tr>
                    <td>{{ $field }}</td>
                    <td>{{ $value }}</td>
                </tr>
                @endforeach
            </table>
            @endif
        </div>
        <div style="background-color:rgb(255, 255, 255); color:rgb(0, 0, 0); font-size: 12px; margin: 0; position: relative;">
            &nbsp;
        </div>
        <div style="background-color:rgb(255, 255, 255); color:rgb(0, 0, 0); font-size: 12px; margin: 0; position: relative;">
            Cordialement,
        </div>
        <div style="background-color:rgb(255, 255, 255); color:rgb(0, 0, 0); font-size: 12px; margin: 0; position: relative;">
            &nbsp;
        </div>
        <div style="background-color:rgb(255, 255, 255); color:rgb(0, 0, 0); font-size: 12px; margin: 0; position: relative;">
            L'équipe de CallMediCall »
        </div>
        <div style="background-color:rgb(255, 255, 255); color:rgb(0, 0, 0); font-size: 12px; margin: 0; position: relative;">
            &nbsp;
        </div>
        <div class="footer">
            {{ __('Rapport par courrier électronique généré à') }}: {{ date('d-m-Y H:i:s') }}
        </div>
    </body>
</html>




