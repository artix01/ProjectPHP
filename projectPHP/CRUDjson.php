<?php
// Для использования этого класса require_once 'CRUDjson.php';
class CRUDjson
{
    private $filename;
    private $data;

    public function __construct($filename)
    {
        $this->filename = $filename;

        if (!file_exists($filename)) {
            file_put_contents($filename, "[]");
        }

        $this->data = json_decode(file_get_contents($filename), true);
    }

    public function save()
    {
        file_put_contents($this->filename, json_encode($this->data));
    }

    public function create($item)
    {
        $this->data[] = $item;
        $this->save();
    }

    public function read($id = null)
    {
        if ($id === null) {
            return $this->data;
        }

        foreach ($this->data as $item) {
            if ($item['id'] == $id) {
                return $item;
            }
        }

        return null;
    }

    public function update($id, $fields)
    {
        foreach ($this->data as &$item) {
            if ($item['id'] == $id) {
                foreach ($fields as $key => $value) {
                    $item[$key] = $value;
                }
                $this->save();
                return true;
            }
        }

        return false;
    }

    public function delete($id)
    {
        foreach ($this->data as $index => $item) {
            if ($item['id'] == $id) {
                unset($this->data[$index]);
                $this->save();
                return true;
            }
        }

        return false;
    }
}