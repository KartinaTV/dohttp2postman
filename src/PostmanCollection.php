<?php

/**
 * Created by PhpStorm.
 * User: sergei
 * Date: 18.04.16
 * Time: 14:28
 */

class PostmanCollection
{
    protected $data;
    protected $folders;
    protected $requests;

    /**
     * PostmanCollection constructor.
     * @param $id
     * @return PostmanCollection
     */
    public function __construct($id)
    {
        $this->data = [];
        $this->data['id'] = $id;
        $this->data['name'] = '';
        $this->data['description'] = '';
        $this->data['order'] = [];
        $this->folders = $this->data['folders'] = [];
        $this->data['timestamp'] = 0;
        $this->data['owner'] = '';
        $this->data['remoteLink'] = '';
        $this->data['public'] = false;
        $this->requests = $this->data['requests'] = [];
        return $this;
    }

    /**
     * @param $name
     */
    public function setName($name)
    {
        $this->data['name'] = $name;
    }

    /**
     * @param $id
     * @param $name
     * @param string $description
     * @throws Exception
     */
    public function addFolder($id, $name, $description = '')
    {
        $folderId = strtolower($id);
        if ($this->isFolderExists($folderId)) {
            throw new \Exception("Folder with ID {$id} exists!");
        }
        $folder = [
            'id' => $folderId,
            'name' => $name,
            'description' => $description,
            'order' => [],
        ];
        $this->folders[$folderId] = $folder;
    }

    /**
     * @param $id
     * @return bool
     */
    public function isFolderExists($id)
    {
        return (array_key_exists(strtolower($id), $this->folders));
    }

    /**
     * @param $id
     * @param $name
     * @param $url
     * @param $method
     * @param array $headers
     * @param null $folderId
     * @param string $dataMode
     * @throws Exception
     */
    public function addRequest($id, $name, $url, $method, array $headers, $folderId = null, $dataMode = 'urlencoded')
    {
        if (!is_null($folderId) && !$this->isFolderExists($folderId)) {
            throw new \Exception("Folder with ID {$folderId} doesn't exist!");
        }
        $supportedMethods = [ "GET", "PUT", "POST", "PATCH", "DELETE", "COPY", "HEAD", "OPTIONS", "LINK", "UNLINK",
            "PURGE", "LOCK", "UNLOCK", "PROPFIND", "VIEW"];
        if (!in_array($method, $supportedMethods)) {
            throw new \Exception("Method is invalid: {$method}");
        }
        if (!in_array($dataMode, ["raw", "urlencoded", "params"])) {
            throw new \Exception("Data mode is invalid: {$dataMode}");
        }
        $requestId = strtolower($id);
        $request = [
            'id' => $requestId,
            'name' => $name,
            'method' => $method,
            'url' => $url,
            'dataMode' => $dataMode,
            'headers' => join("\r\n", $headers),
            'collectionId' => $this->data['id'],
            'folder' => $folderId,
        ];
        // Save order in folder:
        if ($folderId) {
            $this->folders[$folderId]['order'][] = $requestId;
        }
        $this->requests[$requestId] = $request;
    }

    /**
     * @param $requestId
     * @param $type
     * @param $key
     * @param $value
     * @param bool $enabled
     * @throws Exception
     */
    public function addRequestPayload($requestId, $type, $key, $value, $enabled = true)
    {
        $requestId = strtolower($requestId);
        if (!$this->isRequestExists($requestId)) {
            throw new \Exception("Request with ID {$requestId} is not found!");
        }
        if (!in_array($type, ["file", "text",])) {
            throw new \Exception("Type is invalid: {$type}");
        }
        if (in_array($this->requests[$requestId]['method'], ['GET', 'COPY', 'HEAD', 'PURGE', 'UNLOCK'])) {
            $this->addGetParameter($this->requests[$requestId]['url'], $key, $value);
        } else {
            $parameter = ['key' => $key, 'value' => $value, 'enabled' => $enabled, 'type' => 'text',];
            $this->requests[$requestId]['data'][] = $parameter;
        }
    }

    /**
     * @param $id
     * @return bool
     */
    public function isRequestExists($id)
    {
        $requestId = strtolower($id);
        return (array_key_exists($requestId, $this->requests));
    }

    /**
     * @param $url
     * @param $key
     * @param $value
     */
    protected function addGetParameter(&$url, $key, $value)
    {
        $query = parse_url($url, PHP_URL_QUERY); // Get query string
        parse_str($query, $params);
        if ($query) { // if query is not null - remove query from URL
            $url = str_replace($query, '', $url);
        }
        $params[$key] = $value;
        $url .= (substr($url, -1) != '?' ? '?' : '') . http_build_query($params); // Attach query string
    }

    /**
     * @return string
     */
    public function save()
    {
        $this->data['requests'] = [];
        foreach ($this->requests as $request) {
            $this->data['requests'][] = $request;
        }
        foreach ($this->folders as $folder) {
            $this->data['folders'][] = $folder;
        }
        return json_encode($this->data, JSON_PRETTY_PRINT);
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function getName()
    {
        if (empty($this->data['name'])) {
            throw new \Exception("Name is not set");
        }
        return $this->data['name'];
    }
}