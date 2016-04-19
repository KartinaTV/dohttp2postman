<?php

/**
 * Created by PhpStorm.
 * User: sergei
 * Date: 18.04.16
 * Time: 14:27
 */

class Convertor
{
    /**
     * @return static
     */
    public static function factory()
    {
        return new static();
    }

    /**
     * @param $data
     * @param null $collectionName
     * @throws Exception
     */
    public function extract($data, $collectionName = null)
    {
        $postmanCollections = [];
        foreach ($data['p'] as $dCollection) {
            $postmanCollection = new PostmanCollection($this->generateId());
            $postmanCollection->setName($dCollection['name']);
            if ($collectionName && $dCollection['name'] != $collectionName) {
                continue;
            }
            $postmanCollection->setName($dCollection['name']);
            $this->convertCollection($postmanCollection, $dCollection);
            file_put_contents($postmanCollection->getName() . '.json', $postmanCollection->save());
            $postmanCollections[$dCollection['name']] = $postmanCollection;
        }
    }

    /**
     * @return string
     */
    public function generateId()
    {
        $strong = null;
        // example: f695cab7-6878-eb55-7943-ad88e1ccfd65
        $uuid = bin2hex(openssl_random_pseudo_bytes(4, $cstrong)) .
            '-' .
            bin2hex(openssl_random_pseudo_bytes(2, $cstrong)) .
            '-' .
            bin2hex(openssl_random_pseudo_bytes(2, $cstrong)) .
            '-' .
            bin2hex(openssl_random_pseudo_bytes(2, $cstrong)) .
            '-' .
            bin2hex(openssl_random_pseudo_bytes(6, $cstrong)) .
            '';
        return $uuid;
    }

    /**
     * @param PostmanCollection $pCollection
     * @param array $dCollection
     */
    protected function convertCollection(PostmanCollection $pCollection, array $dCollection)
    {
        foreach ($dCollection['items'] as $folder) {
            if (isset($folder['items'])) {
                $this->convertFolder($pCollection, $folder);
            } else { // single request record:
                $this->convertRequest($pCollection, $folder);
            }
        }
    }

    /**
     * @param PostmanCollection $collection
     * @param $folder
     * @throws Exception
     */
    protected function convertFolder(PostmanCollection $collection, $folder)
    {
        $folderId = $this->generateId();
        $collection->addFolder($folderId, $folder['name']);
        foreach ($folder['items'] as $request) {
            if (isset($request['items'])) {
                throw new \Exception("Subfolders are not supported! Folder:'$folder[name]', subfolder: '$request[name]'");
            }
            $this->convertRequest($collection, $request, $folderId);
        }
    }

    /**
     * @param PostmanCollection $collection
     * @param $request
     * @param null $folderId
     * @throws Exception
     */
    protected function convertRequest(PostmanCollection $collection, $request, $folderId = null)
    {
        $headers = [];
        foreach ($request['reqHeader'] as $header) {
            $headers[] = $header['key'] . ": " . $header['value'];
        }
        $requestId = strtolower($request['id']);
        $collection->addRequest($requestId, $request['name'], $request['url'], $request['method'], $headers, $folderId);
        foreach ($request['reqParam'] as $param) {
            $collection->addRequestPayload($requestId, strtolower($param['type']), $param['key'], $param['value']);
        }
    }
}