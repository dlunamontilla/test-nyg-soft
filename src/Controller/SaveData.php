<?php

namespace App\Controller;

use DLTools\Database\DLDatabase;
use DLTools\HttpRequest\DLRequest;

class SaveData {

    /**
     * Base de datos
     *
     * @var DLDatabase
     */
    private DLDatabase $db;

    /**
     * Petición del usuario.
     * 
     * @var DLRequest
     */
    private DLRequest $request;

    /**
     * Datos de la consulta
     *
     * @var array
     */
    private array $values = [];

    public function __construct() {
        $this->db = DLDatabase::getInstance();
        $this->request = DLRequest::getInstance();
    }

    public function saveUser(object $vars, DLRequest $request): array {
        /**
         * @var DLDatabase
         */
        $db = $vars->db;

        $isValid = $request->post([
            "csrf-token" => true,
            "departament" => true,
            "name" => true,
            "city"  => true
        ]);

        if (!$isValid) {
            return [
                "send" => false,
                "message" => "Petición inválida"
            ];
        }

        /**
         * Valores obtenidos del formulario del usuario
         */
        $values = $request->getValues();

        $db->from('dl_users')->insert([
            'user_name' => $values['name'],
            'user_lastname' => '',
            'departaments_id' => $values['departament'],
            'cities_id' => $values['city']
        ]);

        return $values;
    }

    public function form(object $vars): string {
        
        /**
         * Base de datos
         * 
         * @var DLDatabase $db
         */
        $db = $vars->db;

        $departaments = $db->from('dl_departaments')->get();
        $users = $db->from('users_result')->get();

        $vars->departaments = $departaments;
        $vars->users = $users;

        return view('home', $vars);
    }

    public function getCities(object $vars, DLRequest $request): array {
        /**
         * @var DLDatabase
         */
        $db = $vars->db;

        $values = $request->getValues();

        $id = $values['id'] ?? 0;

        $cities = $db->from('dl_cities')->where('departaments_id', $id)->get();
        return $cities;
    }

    public function getUsers(object $vars): array {
        /**
         * @var DLDatabase
         */
        $db = $vars->db;

        $data = $db->from('users_result')->get();
        return $data;
    }
}
