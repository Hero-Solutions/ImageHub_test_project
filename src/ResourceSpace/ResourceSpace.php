<?php

namespace App\ResourceSpace;

use App\Utils\StringUtil;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ResourceSpace
{
    private $apiUrl;
    private $apiUsername;
    private $apiKey;

    public function __construct(ContainerInterface $container)
    {
        // Make sure the API URL does not end with a '?' character
        $this->apiUrl = rtrim($container->getParameter('resourcespace_api_url'), '?');
        $this->apiUsername = $container->getParameter('resourcespace_api_username');
        $this->apiKey = $container->getParameter('resourcespace_api_key');
    }

    public function getCurrentResourceSpaceData()
    {
        $resources = $this->getAllResources();
        $data = array();
        foreach($resources as $resource) {
            $data[$resource['ref']] = $this->getResourceSpaceData($resource['ref']);
        }

        return $data;
    }

    public function getResourceSpaceData($ref)
    {
        $extracted = array();
        $currentData = $this->getResourceInfo($ref);
        if($currentData != null) {
            if(!empty($currentData)) {
                foreach($currentData as $field) {
                    $extracted[$field['name']] = $field['value'];
                }
            }
        }
        return $extracted;
    }

    public function getAllOriginalFilenames()
    {
        $resources = $this->getAllResources();
        $resourceIds = array();
        foreach($resources as $resource) {
            $filename = $this->getOriginalFilenameForId($resource['ref']);
            if($filename != null) {
                $resourceIds[$filename] = $resource['ref'];
            }
        }

        return $resourceIds;
    }

    public function getOriginalFilenameForId($id)
    {
        $currentData = $this->getResourceInfo($id);
        if($currentData == null) {
            return null;
        }
        if(empty($currentData)) {
            return null;
        }
        return $this->getOriginalFilename($currentData);
    }

    public function getOriginalFilename($data)
    {
        $filename = null;
        foreach($data as $field) {
            if($field['name'] == 'originalfilename') {
                $filename = $field['value'];
                break;
            }
        }
        return $filename;
    }

    public function getAllResources()
    {
        # We need to supply something to param1, otherwise ResourceSpace returns a 500 (it's become a mandatory argument)
        $allResources = $this->doApiCall('do_search&param1=\'\'');

        if ($allResources == 'Invalid signature') {
            echo 'Error: invalid ResourceSpace API key. Please paste the key found in the ResourceSpace user management into app/config/parameters.yml.' . PHP_EOL;
//            $this->logger->error('Error: invalid ResourceSpace API key. Please paste the key found in the ResourceSpace user management into app/config/parameters.yml.');
            return NULL;
        }

        $resources = json_decode($allResources, true);
        return $resources;
    }

    public function isPublicUse($data, $publicUse)
    {
        $public = false;
        if(!empty($publicUse)) {
            if (array_key_exists($publicUse['key'], $data)) {
                $expl = explode(',', $data[$publicUse['key']]);
                foreach ($expl as $val) {
                    if ($val == $publicUse['value']) {
                        $public = true;
                        break;
                    }
                }
            }
        }
        return $public;
    }

    public function isRecommendedForPublication($data, $recommendedForPublication)
    {
        $forPublication = false;
        if(!empty($recommendedForPublication)) {
            if (array_key_exists($recommendedForPublication['key'], $data)) {
                if(!empty($data[$recommendedForPublication['key']])) {
                    $forPublication = true;
                }
            }
        }
        return $forPublication;
    }

    private function getResourceInfo($id)
    {
        $data = $this->doApiCall('get_resource_field_data&param1=' . $id);
        return json_decode($data, true);
    }

    public function updateField($id, $key, $value)
    {
        return $this->doApiCall('update_field&param1=' . $id . '&param2=' . $key . '&param3=' . urlencode($value));
    }

    private function doApiCall($query)
    {
        $query = 'user=' . $this->apiUsername . '&function=' . $query;
        $url = $this->apiUrl . '?' . $query . '&sign=' . $this->getSign($query);
        $data = file_get_contents($url);
        return $data;
    }

    private function getSign($query)
    {
        return hash('sha256', $this->apiKey . $query);
    }
}
