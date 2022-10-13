<?php

namespace App\Http\Controllers\Register;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\Province;
use App\Models\District;
use App\Models\DocumentType;
use App\Models\EconomicActivity;

class FicharucController extends Controller
{
    //
    public function ficha_ruc(Request $request) {
        $val = Validator::make($request->all(), [
            'file' => 'required|file'
        ]);
        if($val->fails()) return response()->json($val->messages());

        if($request->hasFile('file')){
            $file = $request->file('file');

            $parser = new \Smalot\PdfParser\Parser();
            $pdf    = $parser->parseFile($file);
            
            $pages  = $pdf->getPages();

            $text = "";
            foreach ($pages as $page) {
                $text .= $page->getText();
            }

            $details  = $pdf->getDetails();

            $resultado = explode("\t\n", $text);
            
            $tipo = "";
            
            if (strrpos($text, "FICHA RUC : ") || strrpos($text, "FICHA RUC : ") === 0) $tipo = 1;
            if (strrpos($text, "Reporte de Ficha RUC") || strrpos($text, "Reporte de Ficha RUC") === 0) $tipo = 2;


            $ficha = [];
            if($tipo == 1){
                $ficha = FicharucController::parseFicharuc($text);
            }
            elseif ($tipo == 2) {
                $ficha = FicharucController::parseReporteFicharuc($text);
            }

            if($tipo == ""){
                return response()->json([
                    'success' => false,
                    'tipo' => $tipo,
                    'mensaje' => ($tipo != "") ? $ficha->getData() : null
                ]);
            }
            return response()->json([
                'success' => true,
                'tipo' => $tipo,
                'mensaje' => ($tipo != "") ? $ficha->getData() : null
            ]);


        }else{
            return response()->json([
                'success' => false,
                'error' => 'Error en el archivo adjunto',
            ]);
        }
    }


    #########################################################################
    #########################################################################
    ###########################  FICHA RUC ##################################
    #########################################################################
    #########################################################################

    public function parseFicharuc($ficha) {
        $ficha = str_replace("\t  \n \t\n", "\t\n", $ficha);
        $ficha = str_replace("\t \t   \t", "\t\n", $ficha);
        $ficha = str_replace("\t \t: ", "\t:\t", $ficha);
        $ficha = str_replace("\t \t\n", "\t\n", $ficha);
        $ficha = str_replace("\t \t", "\t\n", $ficha);

        $resultado = explode("\t\n", $ficha);

        $res_arr = [];
        foreach ($resultado as $property => $value) {
            $tmp = explode("\t", $value);

            $size = sizeof($tmp)-1;

            $tmp2 = explode(" : ", $value);
            $size2 = sizeof($tmp2)-1;

            $res_arr[$tmp[0]] = $tmp[$size];
            $res_arr2[$tmp2[0]] = $tmp2[$size2];

            // Razón Social
            $tmp3 = explode("\n", $value);

            if(sizeof($tmp3) > 1){
                if(Str::startsWith($value, ["FICHA RUC"])){

                    $nro_ruc = explode(" : ", $tmp3[0])[1];
                    $rason = $tmp3[1];
                }
            }
            else{
                if(Str::startsWith($value, ["FICHA RUC"])){
                    $rason = (sizeof(explode("\n", $resultado[$property+1])) > 1 ) ? explode("\n", $resultado[$property+1])[0] : $resultado[$property+1];
                }
            }
        }

        // Nro de RUC
        isset($nro_ruc) ? $nro_ruc : ($nro_ruc = isset($res_arr2["FICHA RUC"]) ? $res_arr2["FICHA RUC"] : null);
        $nro_ruc = str_replace("\t", "", $nro_ruc);

        $data = [
            "general" => array(
                "razon_social" => isset($rason) ? $rason :(isset($res_arr["Apellidos y Nombres ó Razón Social"]) ? $res_arr["Apellidos y Nombres ó Razón Social"] : null),
                "ruc" => $nro_ruc,
                //"tipo_contribuyente" => isset($res_arr["Tipo de Contribuyente"]) ? $res_arr["Tipo de Contribuyente"] : null,
                "fecha_inscripcion" => isset($res_arr["Fecha de Inscripción"]) ? $res_arr["Fecha de Inscripción"] : null,
                //"estado_contribuyente" => isset($res_arr["Estado del Contribuyente"]) ? $res_arr["Estado del Contribuyente"] : null,
                //"dependencia_sunat" => isset($res_arr["Dependencia SUNAT"]) ? $res_arr["Dependencia SUNAT"] : null,
                //"condicion_domicilio" => isset($res_arr["Condición del Domicilio Fiscal"]) ? $res_arr["Condición del Domicilio Fiscal"] : null,
                "emisor_desde" => isset($res_arr["Emisor electrónico desde"]) ? $res_arr["Emisor electrónico desde"] : null,

                "nombre_comercial" => isset($res_arr["Nombre Comercial"]) ? $res_arr["Nombre Comercial"] : null,
                "actividad_economica" => isset($res_arr["Actividad Económica Principal"]) ? $res_arr["Actividad Económica Principal"] : null,
                "telefono" => isset($res_arr["Teléfono Fijo 1"]) ? $res_arr["Teléfono Fijo 1"] : null,
                "email" => isset($res_arr["Correo Electrónico 1"]) ? $res_arr["Correo Electrónico 1"] : null,

                "domicilio" => array(
                    "departamento" => isset($res_arr["Departamento"]) ? $res_arr["Departamento"] : null,
                    "provincia" => isset($res_arr["Provincia"]) ? $res_arr["Provincia"] : null,
                    "distrito" => isset($res_arr["Distrito"]) ? $res_arr["Distrito"] : null,
                    "zona" => isset($res_arr["Tipo y Nombre Zona"]) ? $res_arr["Tipo y Nombre Zona"] : null,
                    "via" => isset($res_arr["Tipo y Nombre Vía"]) ? $res_arr["Tipo y Nombre Vía"] : null,
                    "nro" => isset($res_arr["Nro"]) ? $res_arr["Nro"] : null,
                    "km" => isset($res_arr["Km"]) ? $res_arr["Km"] : null,
                    "mz" => isset($res_arr["Mz"]) ? $res_arr["Mz"] : null,
                    "lote" => isset($res_arr["Lote"]) ? $res_arr["Lote"] : null,
                    "dpto" => isset($res_arr["Dpto"]) ? $res_arr["Dpto"] : null,
                    "interior" => isset($res_arr["Interior"]) ? $res_arr["Interior"] : null,
                    "otros" => isset($res_arr["Otras Referencias"]) ? $res_arr["Otras Referencias"] : null,
                ),
            ),

        ];

        


        // correcciones de Data
        if($data['general']['domicilio']['distrito'] == "MIRAFL ORES") $data['general']['domicilio']['distrito'] = "MIRAFLORES";
        if($data['general']['domicilio']['distrito'] == "SAN BORJ A") $data['general']['domicilio']['distrito'] = "SAN BORJA";
        if($data['general']['domicilio']['distrito'] == "SURQUILL O") $data['general']['domicilio']['distrito'] = "SURQUILLO";
        if(Str::startsWith($data['general']['domicilio']['distrito'], ["PUEBLO LIBRE"])) $data['general']['domicilio']['distrito'] = "PUEBLO LIBRE";
        
        //Dirección
        $data['general']['domicilio']['direccion'] = "";
        foreach ($data['general']['domicilio'] as $key => $value) {
            if($key != "departamento" && $key != "provincia" && $key != "distrito" && $key != "zona" && $key != "direccion" && $value != "-"  && $value != null){
                if($key == "via" || $key == "nro" || $key == "otros"){
                    $data['general']['domicilio']['direccion'] .= $value ." ";
                }
                else{
                    $data['general']['domicilio']['direccion'] .= $key ." ". $value ." ";
                }
            }
        }
        $data['general']['domicilio']['direccion'] = substr($data['general']['domicilio']['direccion'], 0,  Str::length($data['general']['domicilio']['direccion']) - 1);

        //ubigeo
        $provincia = Province::where('name', $data['general']['domicilio']['provincia'])->first();
        
        $provinciaId = ($provincia) ? $provincia->id : null;
        $departamentoId = ($provincia) ? $provincia->department_id : null;

        $distrito = (!is_null($provinciaId)) ? District::where('province_id', $provinciaId)->where('name', $data['general']['domicilio']['distrito'])->first() : null;


        $distritoId = ($distrito) ? $distrito->id : null;
        $ubigeo = ($distrito) ? $distrito->ubigeo : null;

        $data['general']['domicilio']['ubigeo'] = array(
            "departamento_id" => $departamentoId,
            "provincia_id" => $provinciaId,
            "distrito_id" => $distritoId,
            "ubigeo" => $ubigeo
        );

        $codigo_act_eco =  (int) Str::substr($data['general']['actividad_economica'], 0, strpos($data['general']['actividad_economica'], " - ") );

        try {
            $data['general']['id_actividad_economica'] = EconomicActivity::where('code', $codigo_act_eco)->first()->id;
        } catch (\Exception $e) {
            logger('Actividad Económica: FicharucController@parseReporteFicharuc', ["error" => $e]);
        }

        
        #### Representantes legales
        ######################################################################

        $index_repleg = strpos($ficha, "Representantes Legales");
        $index_otras_personas = strpos($ficha, "Otras Personas Vinculadas");
        $index_anexos = strpos($ficha, "Establecimientos Anexos");
        $index_importante = strpos($ficha, "Importante");

        $final_repleg = ($index_otras_personas) ? $index_otras_personas : ( ($index_anexos) ? $index_anexos : $index_importante);

        $representantes = substr($ficha, $index_repleg + 22, $final_repleg - $index_repleg -22);

        $final_index_otras = ($index_anexos) ? $index_anexos : $index_importante;

        $otras_personas = null;
        
        if($index_otras_personas){
            $otras_personas = substr($ficha, $index_otras_personas + 25, $final_index_otras - $index_otras_personas -25);
        }


        ############################################################################

        try {
            $lista_representantes = FicharucController::representantesLegales($representantes);
            $data["representantes_legales"] = $lista_representantes;
        } catch (\Exception $e) {
            logger('validación de identidad: FicharucController@representantesLegales', ["error" => $e]);
        }

        try {
            $lista_socios = FicharucController::socios($otras_personas);
            $data["socios"] = $lista_socios;
        } catch (\Exception $e) {
            logger('validación de identidad: FicharucController@socios', ["error" => $e]);
        }


        return response()->json([
            'success' => true,
            /*'ficha' => $lista_representantes,
            'test' => $res_arr,*/
            'data' => $data
        ]);
    }
    

    public function representantesLegales($representantes) {

        $tmp_repleg = explode("\t\n", $representantes);
        $tmp_repleg = str_replace("\n", " ", $tmp_repleg);
        
        $rep = [];

        $rep_raw = [];
        $flag = false;

        // Recorriendo el bloque de representantes y quitando las cabeceras, datos en blanco e innecesarios
        foreach ($tmp_repleg as $property => $value) {
            if($value == "" || Str::startsWith($value, ["Tipo y Número", "Dirección", "https://", "Documento\tApellidos"])) {
                if(substr($value, 0, 14) == "Tipo y Número"){
                    if($flag == false) $flag = true;
                    else{
                        array_push($rep_raw, $rep);
                        $rep = [];
                    }
                }
            }
            else{
                $rep[] = $value;
            }
        }
        if($flag == true) array_push($rep_raw, $rep);
        
        $rep_arr = [];
        $solito = false;

        foreach ($rep_raw as $property => $value) {
            $tmprep = [];
            $flag_nrodocumento = false; // para activar la bíusqueda del nro de documento

            foreach ($value as $property2 => $value2) {
                $tmp = explode("\t", $value2);
                $size = sizeof($tmp);


                if(Str::startsWith($value2, ["DOC. NACIONAL DE IDENTIDAD"])){
                    $tmprep["tipo_documento"] = "DNI";
                    $tmprep["id_tipo_documento"] = DocumentType::where('name', $tmprep["tipo_documento"])->first()->id;

                    if($size == 2) $tmprep["nro_documento"] = substr($tmp[1], 1);

                    if(sizeof(explode("\t", $value[$property2 +1])) == 1 && Str::startsWith($value[$property2 +1], ["-"])) $tmprep["nro_documento"] = substr($value[$property2 +1], 1) ;

                }
                elseif( Str::startsWith($value2, ["DOC. NACIONAL DE"]) && $size == 1 ){

                    if(Str::startsWith($value[$property2 +1], ["IDENTIDAD"])){
                        $tmprep["tipo_documento"] = "DNI";
                        $tmprep["id_tipo_documento"] = DocumentType::where('name', $tmprep["tipo_documento"])->first()->id;

                        $tmprep["nro_documento"] = explode("-", $value[$property2 + 1])[1];
                    }

                }
                elseif (Str::startsWith($value2, ["CARNET DE", "CARNÉ DE"])) {
                    $tmprep["tipo_documento"] = "Carné de extranjería";
                    $tmprep["id_tipo_documento"] = DocumentType::where('name', $tmprep["tipo_documento"])->first()->id;

                    
                    if($size == 2) $tmprep["nro_documento"] = $tmp[1];
                    elseif(sizeof(explode("-", $value2)) == 2){
                        $tmprep["nro_documento"] = explode("-", $value2)[1];
                    }
                }
                elseif($size == 1 && !Str::startsWith($value2, ["-"])){
                    $solito = true;
                }
                
                if ($size == 5 && $solito == true && !isset($tmprep["nombres"])) {
                    if(Str::startsWith($value[$property2 - 1], ["IDENTIDAD"])){
                        $tmprep["nombres"] = $tmp[0];
                    }
                    else{
                        $tmprep["nombres"] = $value[$property2 - 1] . " " . $tmp[0];
                        $solito = false;
                    }
                }
                elseif ($size == 5 && !isset($tmprep["nombres"])) {
                    $tmprep["nombres"] = $tmp[0];
                }
            }

            array_push($rep_arr, $tmprep);
        }

        try {
            
            // Validando datos con servicio
            foreach ($rep_arr as $property => $value) {

                if($value['id_tipo_documento'] == 2 && Str::length($value['nro_documento']) == 8) {
                    
                    $consulta = Http::get(env('APP_URL') . "/api/register/validate-dni", ["dni" => $value['nro_documento']]);
                    
                    $rpta_json = json_decode($consulta);

                    $rep_arr[$property]['validadocumento'] = $rpta_json;

                    if(is_object($rpta_json)){
                        if($rpta_json->success){
                            $rep_arr[$property]['validadocumento'] = $rpta_json->data->dni;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            logger('validación de identidad: FicharucController@representantesLegales', ["error" => $e]);
        }

        return $rep_arr;
    }


    public function socios ($otras_personas) {

        $tmp_socios= explode("\t\n", $otras_personas);
        $tmp_socios = str_replace("\n", " ", $tmp_socios);
        
        $socios = [];

        $socios_raw = [];
        $flag = false;


        // Recorriendo el bloque de Otras Personas Relacionadas y quitamos las cabeceras, datos en blanco e innecesarios
        foreach ($tmp_socios as $property => $value) {
            if($value == "" || Str::startsWith($value, ["Tipo y Nro.", "Dirección", "https://","País de Residencia"])){
                if(substr($value, 0, 11) == "Tipo y Nro."){
                    if($flag == false) $flag = true;
                    else{
                        array_push($socios_raw, $socios);
                        $socios = [];
                    }
                }
            }
            else{
                $socios[] = $value;
                //. " " . sizeof(explode("\t", $value));
            }
        }

        if($flag == true) array_push($socios_raw, $socios);

        //Último parse para obtener las información particular
        $socios_arr = [];
        $nodomicialiado = false;
        //return $socios_raw;

        foreach ($socios_raw as $property => $value) {
            $tmpsocios = [];
            $nombre="";
            $cant_solos = 0; // Flag que cuenta cuantas líneas se encuentran solas después del documento de identidad
            foreach ($value as $property2 => $value2) {
                $tmp = explode("\t", $value2);
                $size = sizeof($tmp);

                // Obteniendo tipos y nros de documento
                if(Str::startsWith($value2, ["DOC. NACIONAL DE IDENTIDAD"]) || ($value2 == "DOC." && $value[$property2 + 1] == "NACIONAL")){
                    $tmpsocios["tipo_documento"] = "DNI";
                    $tmpsocios["id_tipo_documento"] = DocumentType::where('name', $tmpsocios["tipo_documento"])->first()->id;


                    if($value2 == "DOC. NACIONAL DE IDENTIDAD" && substr($value[$property2 + 1], 0,1) == "-") $tmpsocios["nro_documento"] = substr($value[$property2 + 1], 1);
                    if($size == 2) $tmpsocios["nro_documento"] = substr($tmp[1], 1);
                    elseif(Str::startsWith($value[$property2 + 3], ["IDENTIDAD"])) $tmpsocios["nro_documento"] = explode("-", $value[$property2 + 3])[1];
                }
                elseif(Str::startsWith(str_replace(" ", "", $value2), ["PASAPORTE"])){
                    $tmpsocios["tipo_documento"] = "Pasaporte";
                    $tmpsocios["id_tipo_documento"] = DocumentType::where('name', $tmpsocios["tipo_documento"])->first()->id;

                    if($size == 2) $tmpsocios["nro_documento"] = substr($tmp[1], 1);
                    elseif($size == 1 && $value[$property2 + 1] == "-" && sizeof(explode("\t", $value[$property2 + 2])) == 1){
                        $tmpsocios["nro_documento"] = $value[$property2 + 2];
                    }
                }
                elseif(Str::startsWith($value2, ["CARNET DE"]) || Str::startsWith(str_replace(" ", "", $value2), ["CARNETDE"]) ){
                    $tmpsocios["tipo_documento"] = "Carné de extranjería";
                    $tmpsocios["id_tipo_documento"] = DocumentType::where('name', $tmpsocios["tipo_documento"])->first()->id;

                    if(sizeof(explode("-", $value2)) == 2){
                        $tmpsocios["nro_documento"] = explode("-", $value2)[1];
                    }
                }
                elseif(Str::startsWith($value2, ["DOC.TRIB.NO.DOM.SIN.RUC"])){
                    $tmpsocios["tipo_documento"] = "No Domiciliado";
                    $tmpsocios["id_tipo_documento"] = DocumentType::where('name', $tmpsocios["tipo_documento"])->first()->id;

                    if($size == 2){
                        $tmpsocios["nro_documento"] = substr($value2, 1);
                    }
                    else $nodomicialiado = true;
                }
                elseif(Str::startsWith($value2, ["REG. UNICO DE CONTRIBUYENTES"])){
                    $tmpsocios["tipo_documento"] = "RUC";
                    $tmpsocios["id_tipo_documento"] = DocumentType::where('name', $tmpsocios["tipo_documento"])->first()->id;

                    if($size == 2){
                        $tmpsocios["nro_documento"] = substr($value2, 1);
                    }
                    elseif(sizeof(explode("-", $value2)) == 2){
                        $tmpsocios["nro_documento"] = explode("-", $value2)[1];
                    }
                }

                // Obteniendo nro de documento de no domiciliado
                elseif($nodomicialiado){
                    if($size == 1) $tmpsocios["nro_documento"] = substr($value2, 1);
                    $nodomicialiado = false;
                }
                elseif ($size == 1 && $value2 != "DE" && !Str::startsWith($value2, ["NACIONAL", "IDENTIDAD","-"]) && !preg_match('~[0-9]+~', $value2) ) {
                    if($cant_solos == 0) $nombre = $value2 . " ";
                    $cant_solos ++;
                }

                // Obteniendo razon social y participación
                if($size == 6 && !isset($tmpsocios["tipo"])){
                    $tmpsocios["nombres"] = $nombre . $tmp[0];
                    $tmpsocios["tipo"] = $tmp[1];
                    $tmpsocios["participacion"] = $tmp[5];

                }

                // Obteniendo rason social y participación
                if($size == 5 && $property2 > 0){
                    $tmp2 = explode("\t", $value[$property2-1]);
                    $size2 = sizeof($tmp2);

                    //if( $tmpsocios["nro_documento"]== "00994589") return $size2;
                    
                    if($size2 == 2){
                        $value2tmp = $value[$property2-1] . " " .$value2;

                        $tmp = explode("\t", $value2tmp);
                        $size = sizeof($tmp);
                        $tmpsocios["nombres"] = $nombre . $tmp[0];
                        $tmpsocios["tipo"] = $tmp[1];
                        $tmpsocios["participacion"] = $tmp[5];
                    }

                    elseif($size2 == 1 && $cant_solos > 1){
                        
                        if($cant_solos == 1){
                            $value2tmp = $value[$property2-1] . " " .$value2;
                        }
                        elseif ($cant_solos == 2) {
                            $value2tmp = $value[$property2-2] . "\t". $value[$property2-1] . " " .$value2;
                        }
                        elseif ($cant_solos == 3) {
                            $value2tmp = $value[$property2-3] . " ". $value[$property2-2] . "\t". $value[$property2-1] . " " .$value2;
                            $nombre = "";
                        }


                        $tmp = explode("\t", $value2tmp);
                        $size = sizeof($tmp);
                        $tmpsocios["nombres"] = $nombre . $tmp[0];
                        $tmpsocios["tipo"] = $tmp[1];
                        $tmpsocios["participacion"] = $tmp[5];
                    }

                }

            }

            if (isset($tmpsocios["tipo"])){
                if ($tmpsocios["tipo"] == "SOCIO"){
                    if (isset($tmpsocios["participacion"])){
                        if ($tmpsocios["participacion"] >= 20){
                            array_push($socios_arr, $tmpsocios);
                        }
                    }
                    else{
                        array_push($socios_arr, $tmpsocios);
                    }
                }
            }
        }

        // Validando datos con servicio
        try {
            
            foreach ($socios_arr as $property => $value) {

                if($value['id_tipo_documento'] == 2 && Str::length($value['nro_documento']) == 8 && $value['tipo'] == "SOCIO") {
                    
                    $consulta = Http::get(env('APP_URL') . "/api/register/validate-dni", ["dni" => $value['nro_documento']]);
                    
                    $rpta_json = json_decode($consulta);

                    $socios_arr[$property]['validadocumento'] = null;

                    if(is_object($rpta_json)){
                        if($rpta_json->success){
                            $socios_arr[$property]['validadocumento'] = $rpta_json->data;
                        }
                    }
                }
                elseif($value['id_tipo_documento'] == 1 && Str::length($value['nro_documento']) == 11 && $value['tipo'] == "SOCIO") {
                    
                    $consulta = Http::get(env('APP_URL') . "/api/register/validate-ruc", ["ruc" => $value['nro_documento']]);
                    
                    $rpta_json = json_decode($consulta);

                    $socios_arr[$property]['validadocumento'] = null;

                    if(is_object($rpta_json)){
                        if($rpta_json->success){
                            $socios_arr[$property]['validadocumento'] = $rpta_json->data->ruc;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            logger('validación de identidad: FicharucController@socios', ["error" => $e]);
        }

        return $socios_arr;
    }

    #########################################################################
    #########################################################################
    ####################### REPORTE FICHA RUC ###############################
    #########################################################################
    #########################################################################
    

    public function parseReporteFicharuc($ficha) {

        $resultado = explode("\t\n", $ficha);

        $res_arr = [];
        foreach ($resultado as $property => $value) {
            $tmp = explode("\t", $value);
            $size = sizeof($tmp)-1;

            $tmp2 = explode("\n", $value);
            $size2 = sizeof($tmp2)-1;

            $res_arr[$tmp[0]] = $tmp[$size];
            $res_arr2[$tmp2[0]] = $tmp2[$size2];

            // Razón Social
            if(Str::startsWith($value, ["Reporte de Ficha RUC"]) && $size2 == 0){
                if(sizeof(explode("\n", $resultado[$property+1])) == 1 ){
                    $rason = $resultado[$property+1];
                    $nro_ruc = $resultado[$property+2];
                }
                elseif(sizeof(explode("\n", $resultado[$property+1])) == 2){
                    $rason = explode("\n", $resultado[$property+1])[0];
                    $nro_ruc = explode("\n", $resultado[$property+1])[1];
                }

                    
            }
            elseif(Str::startsWith($value, ["Reporte de Ficha RUC"]) && $size2 == 1){
                $rason = $tmp2[1];
                $nro_ruc = $resultado[$property+1];
            }
        }

        
        $data = [
            "general" => array(
                "razon_social" => isset($rason) ? $rason :(isset($res_arr["Apellidos y Nombres ó Razón Social"]) ? $res_arr["Apellidos y Nombres ó Razón Social"] : null),
                "ruc" => isset($nro_ruc) ? $nro_ruc : null,
                //"tipo_contribuyente" => isset($res_arr["Tipo de Contribuyente"]) ? $res_arr["Tipo de Contribuyente"] : null,
                "fecha_inscripcion" => isset($res_arr["Fecha de Inscripción"]) ? $res_arr["Fecha de Inscripción"] : null,
                //"estado_contribuyente" => isset($res_arr["Estado del Contribuyente"]) ? $res_arr["Estado del Contribuyente"] : null,
                //"dependencia_sunat" => isset($res_arr["Dependencia SUNAT"]) ? $res_arr["Dependencia SUNAT"] : null,
                //"condicion_domicilio" => isset($res_arr["Condición del Domicilio Fiscal"]) ? $res_arr["Condición del Domicilio Fiscal"] : null,
                "emisor_desde" => isset($res_arr["Emisor electrónico desde"]) ? $res_arr["Emisor electrónico desde"] : null,
                //"comprobantes" => isset($res_arr["Comprobantes electrónicos"]) ? $res_arr["Comprobantes electrónicos"] : null,

                "nombre_comercial" => isset($res_arr["Nombre Comercial"]) ? $res_arr["Nombre Comercial"] : null,
                "actividad_economica" => isset($res_arr["Actividad Económica Principal"]) ? $res_arr["Actividad Económica Principal"] : null,
                "telefono" => isset($res_arr["Teléfono Fijo 1"]) ? $res_arr["Teléfono Fijo 1"] : null,
                "email" => isset($res_arr["Correo Electrónico 1"]) ? $res_arr["Correo Electrónico 1"] : null,

                "domicilio" => array(
                    "departamento" => isset($res_arr["Departamento"]) ? $res_arr["Departamento"] : null,
                    "provincia" => isset($res_arr["Provincia"]) ? $res_arr["Provincia"] : null,
                    "distrito" => isset($res_arr["Distrito"]) ? $res_arr["Distrito"] : null,
                    "zona" => isset($res_arr["Tipo y Nombre Zona"]) ? $res_arr["Tipo y Nombre Zona"] : null,
                    "via" => isset($res_arr["Tipo y Nombre Vía"]) ? $res_arr["Tipo y Nombre Vía"] : null,
                    "nro" => isset($res_arr["Nro"]) ? $res_arr["Nro"] : null,
                    "km" => isset($res_arr["Km"]) ? $res_arr["Km"] : null,
                    "mz" => isset($res_arr["Mz"]) ? $res_arr["Mz"] : null,
                    "lote" => isset($res_arr["Lote"]) ? $res_arr["Lote"] : null,
                    "dpto" => isset($res_arr["Dpto"]) ? $res_arr["Dpto"] : null,
                    "interior" => isset($res_arr["Interior"]) ? $res_arr["Interior"] : null,
                    "otros" => isset($res_arr["Otras Referencias"]) ? $res_arr["Otras Referencias"] : null,
                ),

                //"arr" => isset($res_arr[""]) ? $res_arr[""] : null,

                

            ),
            //"arr" => $res_arr,
            //"resultado" => $resultado
        ];

        // correcciones de Data
        if($data['general']['domicilio']['distrito'] == "MIRAFL ORES") $data['general']['domicilio']['distrito'] = "MIRAFLORES";
        if($data['general']['domicilio']['distrito'] == "SAN BORJ A") $data['general']['domicilio']['distrito'] = "SAN BORJA";
        if(Str::startsWith($data['general']['domicilio']['distrito'], ["PUEBLO LIBRE"])) $data['general']['domicilio']['distrito'] = "PUEBLO LIBRE";
        
        //Dirección
        $data['general']['domicilio']['direccion'] = "";
        foreach ($data['general']['domicilio'] as $key => $value) {
            if($key != "departamento" && $key != "provincia" && $key != "distrito" && $key != "zona" && $key != "direccion" && $value != "-"  && $value != null){
                if($key == "via" || $key == "nro" || $key == "otros"){
                    $data['general']['domicilio']['direccion'] .= $value ." ";
                }
                else{
                    $data['general']['domicilio']['direccion'] .= $key ." ". $value ." ";
                }
            }
        }
        $data['general']['domicilio']['direccion'] = substr($data['general']['domicilio']['direccion'], 0,  Str::length($data['general']['domicilio']['direccion']) - 1);

        //ubigeo
        $provincia = Province::where('name', $data['general']['domicilio']['provincia'])->first();
        
        $provinciaId = ($provincia) ? $provincia->id : null;
        $departamentoId = ($provincia) ? $provincia->department_id : null;

        $distrito = (!is_null($provinciaId)) ? District::where('province_id', $provinciaId)->where('name', $data['general']['domicilio']['distrito'])->first() : null;

        $distritoId = ($distrito) ? $distrito->id : null;
        $ubigeo = ($distrito) ? $distrito->ubigeo : null;

        $data['general']['domicilio']['ubigeo'] = array(
            "departamento_id" => $departamentoId,
            "provincia_id" => $provinciaId,
            "distrito_id" => $distritoId,
            "ubigeo" => $ubigeo
        );


        $codigo_act_eco =  (int) Str::substr($data['general']['actividad_economica'], 0, strpos($data['general']['actividad_economica'], " - ") );

        try {
            $data['general']['id_actividad_economica'] = EconomicActivity::where('code', $codigo_act_eco)->first()->id;
        } catch (\Exception $e) {
            logger('Actividad Económica: FicharucController@parseReporteFicharuc', ["error" => $e]);
        }


        #### Representantes legales
        ######################################################################

        $index_repleg = strpos($ficha, "Representantes Legales");
        $index_otras_personas = strpos($ficha, "Otras Personas Vinculadas");
        $index_anexos = strpos($ficha, "Establecimientos Anexos");
        $index_importante = strpos($ficha, "La información mostrada corresponde");

        $final_repleg = ($index_otras_personas) ? $index_otras_personas : ( ($index_anexos) ? $index_anexos : $index_importante);

        $representantes = substr($ficha, $index_repleg + 22, $final_repleg - $index_repleg -22);

        $final_index_otras = ($index_anexos) ? $index_anexos : $index_importante;

        $otras_personas = null;
        
        if($index_otras_personas){
            $otras_personas = substr($ficha, $index_otras_personas + 25, $final_index_otras - $index_otras_personas -25);
        }


        ############################################################################

        try {
            $lista_representantes = FicharucController::representantesLegales2($representantes);
            $data["representantes_legales"] = $lista_representantes;
        } catch (\Exception $e) {
            logger('validación de identidad: FicharucController@representantesLegales', ["error" => $e]);
        }

        try {
            $lista_socios = FicharucController::socios2($otras_personas);
            $data["socios"] = $lista_socios;
        } catch (\Exception $e) {
            logger('validación de identidad: FicharucController@socios', ["error" => $e]);
        }


        return response()->json([
            'success' => true,
            //'test' => $otras_personas,
            'data' => $data
        ]);

    }

    public function representantesLegales2($representantes) {

        $tmp_repleg = explode("\t\n", $representantes);
        $tmp_repleg = str_replace("\n", " ", $tmp_repleg);
        
        $rep = [];

        $rep_raw = [];
        $flag = false;

        // Recorriendo el bloque de representantes y quitando las cabeceras, datos en blanco e innecesarios
        foreach ($tmp_repleg as $property => $value) {
            if($flag == false  || Str::startsWith($value, ["DOC. NACIONAL DE", "Datos de la Persona Natural","Dirección","Apellidos y Nombres","Documento\tCargo", "CARNET DEEXTRANJERIA"])) {
                
                if( Str::startsWith($value, ["DOC. NACIONAL DE", "CARNET DEEXTRANJERIA"]) ){
                    $rep[] = $value;

                    if($flag == false) $flag = true;
                    else{
                        array_push($rep_raw, $rep);
                        $rep = [];
                    }
                }
                elseif (Str::startsWith($value, ["Datos de la Persona Natural", "Documento\tCargo"])) {
                    if($flag == true) {
                        array_push($rep_raw, $rep);
                        $rep = [];
                    }
                    
                    $flag = false;
                }
            }
            else{
                $rep[] = $value;
            }
        }
        if($flag == true) array_push($rep_raw, $rep);

        $rep_arr = [];
        $solito = false;

        foreach ($rep_raw as $property => $value) {
            $tmprep = [];
            $flag_nrodocumento = false; // para activar la bíusqueda del nro de documento

            foreach ($value as $property2 => $value2) {
                $tmp = explode("\t", $value2);
                $size = sizeof($tmp);

                if(Str::startsWith($value2, ["DOC. NACIONAL DE"])){
                    $tmprep["tipo_documento"] = "DNI";
                    $tmprep["id_tipo_documento"] = DocumentType::where('name', $tmprep["tipo_documento"])->first()->id;

                    if($size == 2 && Str::startsWith($tmp[1], ["IDENTIDAD/LE"])) {
                        $tmprep["nro_documento"] = substr($tmp[1],  12);
                        $tmprep["nombres"] = str_replace("\t", " ", $value[$property2 + 1]);
                    }
                    elseif($size == 2 && Str::startsWith($tmp[1], ["IDENTIDAD"])) {
                        $tmprep["nro_documento"] = substr($tmp[1],  9);
                        $tmprep["nombres"] = str_replace("\t", " ", $value[$property2 + 1]);
                    }
                }
                elseif (Str::startsWith($value2, ["CARNET DEEXTRANJERIA"])) {
                    $tmprep["tipo_documento"] = "Carné de extranjería";
                    $tmprep["id_tipo_documento"] = DocumentType::where('name', $tmprep["tipo_documento"])->first()->id;

                    $tmprep["nro_documento"] = substr($value2,  20);
                    $tmprep["nombres"] = str_replace("\t", " ", $value[$property2 + 1]);
                }
            }

            array_push($rep_arr, $tmprep);
        }

        // Validando datos con servicio
        foreach ($rep_arr as $property => $value) {

            if($value['id_tipo_documento'] == 2 && Str::length($value['nro_documento']) == 8) {
                
                $consulta = Http::get(env('APP_URL') . "/api/register/validate-dni", ["dni" => $value['nro_documento']]);
                
                $rpta_json = json_decode($consulta);

                $rep_arr[$property]['validadocumento'] = null;

                if(is_object($rpta_json)){
                    if($rpta_json->success){
                        $rep_arr[$property]['validadocumento'] = $rpta_json->data;
                    }
                }
            }
        }

        return $rep_arr;
    }

    public function socios2 ($otras_personas) {

        $tmp_socios= explode("\t\n", $otras_personas);
        $tmp_socios = str_replace("\n", " ", $tmp_socios);
        
        $socios = [];

        $socios_raw = [];
        $flag = false;


        // Recorriendo el bloque de Otras Personas Relacionadas y quitamos las cabeceras, datos en blanco e innecesarios
        foreach ($tmp_socios as $property => $value) {
            if($value == "" || Str::startsWith($value, ["DOC. NACIONALDE IDENTIDAD","Dirección","País de Constitución","www.sunat.gob.pe","Central de","Desde ","Página"," ","PASAPORTE","REG. UNICO DECONTRIBUYENTES"])){
                
                if( Str::startsWith($value, ["DOC. NACIONALDE IDENTIDAD", "CARNET DEEXTRANJERIA","PASAPORTE","REG. UNICO DECONTRIBUYENTES"]) ){

                    if($flag == false) $flag = true;
                    else{
                        array_push($socios_raw, $socios);
                        $socios = [];
                    }
                    $socios[] = $value;
                    // ." - " . sizeof(explode("\t", $value))
                }
            }
            else{
                $socios[] = $value;
            }
        }

        if($flag == true) array_push($socios_raw, $socios);

        //Último parse para obtener las información particular
        $socios_arr = [];
        $nodomicialiado = false;

        foreach ($socios_raw as $property => $value) {
            $tmpsocios = [];
            $nombre="";
            $cant_solos = 0; // Flag que cuenta cuantas líneas se encuentran solas después del documento de identidad
            foreach ($value as $property2 => $value2) {
                $tmp = explode("\t", $value2);
                $size = sizeof($tmp);

                // Obteniendo tipos y nros de documento
                if(Str::startsWith($value2, ["DOC. NACIONALDE IDENTIDAD"])){
                    $tmpsocios["tipo_documento"] = "DNI";
                    $tmpsocios["id_tipo_documento"] = DocumentType::where('name', $tmpsocios["tipo_documento"])->first()->id;

                    $tmpsocios["id_tipo_documento"] = 2;

                    if($size == 2) $tmpsocios["nro_documento"] = substr($tmp[1], -8);
                    $tmpsocios["nombres"] = str_replace("\t", " ", $value[$property2 + 1]);
                }
                elseif(Str::startsWith($value2, ["PASAPORTE"])){
                    $tmpsocios["tipo_documento"] = "Pasaporte";
                    $tmpsocios["id_tipo_documento"] = DocumentType::where('name', $tmpsocios["tipo_documento"])->first()->id;

                    if($size == 1 && sizeof(explode("-", $value2)) == 2) $tmpsocios["nro_documento"] = explode("-", $value2)[1];
                    $tmpsocios["nombres"] = str_replace("\t", " ", $value[$property2 + 1]);
                }
                elseif(Str::startsWith($value2, ["REG. UNICO DECONTRIBUYENTES"])){
                    $tmpsocios["tipo_documento"] = "RUC";
                    $tmpsocios["id_tipo_documento"] = DocumentType::where('name', $tmpsocios["tipo_documento"])->first()->id;

                    if($size == 1 && sizeof(explode("-", $value2)) == 2) $tmpsocios["nro_documento"] = trim(explode("-", $value2)[1]);
                    $tmpsocios["nombres"] = str_replace("\t", " ", $value[$property2 + 1]);
                }
                elseif($size == 4){
                    
                    $tmpsocios["tipo"] = $tmp[0];
                }
                elseif($size == 5){
                    
                    $tmpsocios["tipo"] = $tmp[0] . " " . $tmp[1];
                }
                elseif($size == 7 && Str::startsWith($value2, ["Apellidos y Nombres"])){
                    $tmpporc = explode(" ", $tmp[6]);
                    $sizeporc = sizeof($tmpporc);

                    $tmpsocios["participacion"] = ($sizeporc == 2) ? $tmpporc[1]:null;
                }
            }

            if (isset($tmpsocios["tipo"])){
                if ($tmpsocios["tipo"] == "SOCIO"){
                    if (isset($tmpsocios["participacion"])){
                        if ($tmpsocios["participacion"] >= 20){
                            array_push($socios_arr, $tmpsocios);
                        }
                    }
                    else{
                        array_push($socios_arr, $tmpsocios);
                    }
                }
            }
        }

        // Validando datos con servicio
        foreach ($socios_arr as $property => $value) {

            if($value['id_tipo_documento'] == 2 && Str::length($value['nro_documento']) == 8 && $value['tipo'] == "SOCIO") {
                
                $consulta = Http::get(env('APP_URL') . "/api/register/validate-dni", ["dni" => $value['nro_documento']]);
                
                $rpta_json = json_decode($consulta);

                $socios_arr[$property]['validadocumento'] = null;

                if(is_object($rpta_json)){
                    if($rpta_json->success){
                        $socios_arr[$property]['validadocumento'] = $rpta_json->data;
                    }
                }
            }
            elseif($value['id_tipo_documento'] == 1 && Str::length($value['nro_documento']) == 11 && $value['tipo'] == "SOCIO") {
                
                $consulta = Http::get(env('APP_URL') . "/api/register/validate-ruc", ["ruc" => $value['nro_documento']]);
                
                $rpta_json = json_decode($consulta);

                $socios_arr[$property]['validadocumento'] = null;

                if(is_object($rpta_json)){
                    if($rpta_json->success){
                        $socios_arr[$property]['validadocumento'] = $rpta_json->data;
                    }
                }
            }
        }

        return $socios_arr;
    }
}
