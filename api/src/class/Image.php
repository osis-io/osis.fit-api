<?php

class Image extends ApiObject {
    
    /* -------- TABLES (T) AND VIEWS (V) -------- */
    private $t_main = "image";
    private $v_detail = "v_image_detail";

    /* ----------- PUBLIC BASIC PARAMS ---------- */
    protected $keys = ['id', 'name', 'mime'];

    public $id;
    public $name;
    public $mime;
    
    public $upload_stamp;
    public $access_stamp;

    /* ----------------- METHODS ---------------- */
    public function create() {

        $vars = ['user_id', 'name', 'mime'];
        $vals = array_merge(
            [ 'user_id' => $this->user->id ], 
            (array) $this->getObject()
        );

        $this->db->make(
            'insert', $this->t_main, 
            $vars, $vals
        );

        $this->id = $this->db->conn->lastInsertId();        
        return $this;

    }

    public function delete() {

        $stmt = $this->db->conn->prepare("
            DELETE FROM ".$this->t_main." WHERE 
            id = :id AND 
            user_id = :user_id 
        ");
        $this->db->bind($stmt, 
            ['id', 'user_id'],
            [$this->id, $this->user->id]
        )->execute($stmt);

        if($stmt->rowCount() !== 1) throw new Exception('entry_not_found', 404);
        return $this;

    }

    public function read($id = false) {
        if(!$id) $id = $this->id;

        $stmt = $this->db->conn->prepare("
            SELECT * FROM ".$this->t_main . " WHERE 
            user_id = :user_id AND id = :id
        ");
        $this->db->bind($stmt, 
            ['user_id', 'id'],
            [$this->user->id, $id]
        )->execute($stmt);

        if ($stmt->rowCount() !== 1) throw new ApiException(404,'entry_not_found', 'image');

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->set([
            'id' => $row['id'],
            'name' => $row['name'],
            'mime' => $row['mime']
        ]);
        return $this;

    }

    public function getObject($obj = false) {
        
        if (!$obj) $obj = $this;
        else if (!is_object($obj)) $obj = (object) $obj;

        $url = Env::api_static_url."/".Env::api_name;
        $folder = hash('ripemd160', $this->user->id);
        $path = $url."/".$folder;
        $file = $obj->name.".".$obj->mime;

        return (object) [
            "id" => (int) $obj->id,
            "name" => $obj->name,
            "mime" => $obj->mime,
            "fullPath" => $path."/".$file,
            "lazyPath" => $path."/lazy/".$file
        ];
        
    }
    
}