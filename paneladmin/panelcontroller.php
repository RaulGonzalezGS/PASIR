<?php

    $datos = file_get_contents('php://input');
    $descodificardatos = json_decode($datos, true);

    $dbconn = new mysqli("localhost", "root", "root", "micms");

    if ($descodificardatos["cont"] == "validarcred") {

        $contrasenaenc = hash("sha3-512", $descodificardatos["contrasena"]);
        $contrasena = $dbconn->query("select contrasena from pasirusuarios where usuario = '{$descodificardatos["usuario"]}'");

        $perfil = $dbconn->query("select perfil from pasirusuarios where usuario = '{$descodificardatos["usuario"]}'");

        if (($contrasena->fetch_array()[0] == $contrasenaenc) && ($perfil->fetch_array()[0] == "Admin")) {
            $descodificardatos["mensaje"] = "Usuario validado";

            session_start();
            $_SESSION['usuariocon'] = $descodificardatos["usuario"];
        }

        $resultado = json_encode($descodificardatos);
        echo $resultado;
    }

    if ($descodificardatos["cont"] == "act") {

        session_start();

        if (isset($_SESSION['usuariocon'])) {
            $descodificardatos["usuariosesion"] = $_SESSION['usuariocon'];
        }
        else {
            $descodificardatos["mensaje"] = "Sesion cerrada";
        }

        $listausu = $dbconn->query("select usuario from pasirusuarios");
        $usuarios = array();
        while ($row = $listausu->fetch_array()[0]) {
            array_push($usuarios, $row);
        }

        $descodificardatos["resultadousu"] = $usuarios;

        $listaper = $dbconn->query("select perfil from pasirperfiles");
        $perfiles = array();
        while ($row = $listaper->fetch_array()[0]) {
            array_push($perfiles, $row);
        }

        $descodificardatos["resultadoper"] = $perfiles;

        $listaacc = $dbconn->query("select accion from pasiracciones");
        $acciones = array();
        while ($row = $listaacc->fetch_array()[0]) {
            array_push($acciones, $row);
        }

        $descodificardatos["resultadoacc"] = $acciones;

        $resultado = json_encode($descodificardatos);
        echo $resultado;
    }

    if ($descodificardatos["cont"] == "cerrarsesion") {

        session_start();
        session_destroy();
    }

    if ($descodificardatos["cont"] == "crearusuario") {
        $contrasenaenc = hash("sha3-512",$descodificardatos["contrasenausuario"]);

        $dbconn->query("insert into pasirusuarios (usuario,contrasena) values ('{$descodificardatos["nombreusuario"]}','{$contrasenaenc}');");

        $descodificardatos["mensaje"] = "El usuario se ha creado correctamente";

        $result = $dbconn->query("select usuario from pasirusuarios");
        $usuarios = array();
        while ($row = $result->fetch_array()[0]) {
            array_push($usuarios, $row);
        }

        $descodificardatos["resultadousu"] = $usuarios;

        $resultado = json_encode($descodificardatos);
        echo $resultado;
    }

    if ($descodificardatos["cont"] == "eliminarusu") {

        $perfil = $dbconn->query("select perfil from pasirusuarios where usuario = '{$descodificardatos["usuarioelim"]}';");

        if ($perfil->fetch_array()[0] == "Admin") {
            $descodificardatos["mensaje"] = "El usuario seleccionado tiene perfil Admin";
        }
        else {
            $dbconn->query("delete pasirusuarios.* from pasirusuarios where usuario = '{$descodificardatos["usuarioelim"]}';");
            $descodificardatos["mensaje"] = "El usuario se ha eliminado correctamente";
        }

        $result = $dbconn->query("select usuario from pasirusuarios");
        $usuarios = array();
        while ($row = $result->fetch_array()[0]) {
            array_push($usuarios, $row);
        }

        $descodificardatos["resultadousu"] = $usuarios;

        $resultado = json_encode($descodificardatos);
        echo $resultado;
    }

    if ($descodificardatos["cont"] == "asignarper") {

        $dbconn->query("update pasirusuarios set perfil = '{$descodificardatos["perfilusu"]}' where usuario = '{$descodificardatos["perfilnomusu"]}';");
        $descodificardatos["mensaje"] = "El perfil se ha asignado correctamente";

        $resultado = json_encode($descodificardatos);
        echo $resultado;

    }

    if ($descodificardatos["cont"] == "asignaracc") {

        $dbconn->query("insert into pasiraccper values ('{$descodificardatos["accionnom"]}','{$descodificardatos["perfilesacc"]}');");
        $descodificardatos["mensaje"] = "La accion se ha asignado correctamente";

        $resultado = json_encode($descodificardatos);
        echo $resultado;

    }