<?php
/**
 * Created by PhpStorm.
 * User: enriqueohernandez
 * Date: 5/2/15
 * Time: 12:28 PM
 */

include 'SpreadsheetReader.php';
header('Content-Type: text/plain');
require('php-excel-reader/excel_reader2.php');

$nombreDirectorio = "xls/";

$nombreArchivo = $_FILES['file']['name'];

if (is_file($nombreArchivo)){
    $idUnico = time();
    $nombreArchivo = $idUnico . "-" . $nombreArchivo;
}

else if ($_FILES['file']['name'] == "")
    $nombreArchivo = '';

    move_uploaded_file($_FILES['file']['tmp_name'], $nombreDirectorio . $nombreArchivo);
    $Filepath = $nombreDirectorio . $nombreArchivo;

try
{
    $Spreadsheet = new SpreadsheetReader($Filepath);
    $BaseMem = memory_get_usage();

    $Sheets = $Spreadsheet -> Sheets();

    $tableName;
    $nombres = array(); // An array used to store the data type of the column
    $size = sizeof($Sheets);


    $string = "";

    foreach ($Sheets as $Index => $Name) {

        $Spreadsheet -> ChangeSheet($Index);
        $count = 0;
        $aux = 1;

        foreach ($Spreadsheet as $Key => $Row) { // Get the name of the table

            if($count == 0){
                $string.= "CREATE TABLE ".$Row[0]."(";
                $tableName = $Row[0];
            }
            else if ($count == 1){

                foreach ($Row as $Key => $Row1){ // Get the names of the columns
                    $myArray = explode(',', $Row1);
                    $string.= $myArray[0]." ";
                    if($myArray[1]=="varchar"){
                        $string.= "varchar(".$myArray[2].")";
                        array_push($nombres, "varchar");

                    }
                    else if($myArray[1]=="int"){
                        $string.= "int";
                        array_push($nombres, "int");

                    }
                    else if($myArray[1]=="char"){
                        $string.= "char";
                        array_push($nombres, "char");

                    }
                    else if($myArray[1]=="date"){
                        $string.= "date";
                        array_push($nombres, "date");

                    }
                    else if($myArray[1]=="decimal"){
                        $string.= "decimal(".$myArray[2].",".$myArray[3].")";
                        array_push($nombres, "decimal");

                    }
                    $aux++;
                    if($aux <= sizeof($Row)){
                        $string.=", ";
                    }
                    else{
                        $string.=");<br> INSERT INTO ".$tableName." <br> VALUES ";
                    }
                }

            }
            else{

                $i = 0;
                $string.= "(";
                foreach ($Row as $Key => $Row1) { // Get the values of the table

                        if($nombres[$i]=="varchar"){
                            if(sizeof($nombres)>($i+1))
                                $string.= "'$Row1', ";
                            else
                                $string.= "'$Row1'";
                        }

                        else if($nombres[$i]=="int"){
                            if(sizeof($nombres)>($i+1))
                                $string.= "$Row1, ";
                            else
                                $string.= "$Row1";
                        }
                        else if($nombres[$i]=="char"){
                            if(sizeof($nombres)>($i+1))
                                $string.= "'$Row1', ";
                            else
                                $string.= "'$Row1'";
                        }
                        else if($nombres[$i]=="date"){
                            if(sizeof($nombres)>($i+1))
                                $string.= "'$Row1', ";
                            else
                                $string.= "'$Row1'";
                        }
                        else if($nombres[$i]=="decimal"){
                            if(sizeof($nombres)>($i+1))
                                $string.= "$Row1, ";
                            else
                                $string.= "$Row1";
                        }
                    $i++;
                }
                $string.= "), ";

            }
            $count++;

        }

        $string = substr($string, 0, -2).";";
        echo $string; // Print the final string
        unlink($Filepath); // Delete .xls file
    }

}catch (Exception $E)
{
    echo $E -> getMessage();
}



?>
