<?php

namespace App\Command;

use App\ResourceSpace\ResourceSpace;
use App\Utils\StringUtil;
use DOMDocument;
use DOMXPath;
use Exception;
use Phpoaipmh\Endpoint;
use Phpoaipmh\Exception\OaipmhException;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CsvDatahubToResourceSpaceCommand extends ContainerAwareCommand
{
    private $resourceSpace;
    private $datahubUrl;
    private $namespace;

    private $datahubEndpoint;
    private $resourceSpaceData;
    private $verbose = true;

    protected function configure()
    {
        $this
            ->setName('app:csv-datahub-to-resourcespace')
            ->addArgument('csv', InputArgument::REQUIRED, 'The CSV file containing filenames and datahub record IDs')
            ->addArgument('url', InputArgument::OPTIONAL, 'The URL of the Datahub')
            ->setDescription('')
            ->setHelp('');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $csvFile = $input->getArgument('csv');
        $this->datahubUrl = $input->getArgument('url');
        if (!$this->datahubUrl) {
            $this->datahubUrl = $this->getContainer()->getParameter('datahub_url');
        }

        $this->datahubLanguage = $this->getContainer()->getParameter('datahub_language');
        $this->namespace = $this->getContainer()->getParameter('datahub_namespace');
        $this->metadataPrefix = $this->getContainer()->getParameter('datahub_metadataprefix');
        $this->dataDefinition = $this->getContainer()->getParameter('datahub_data_definition');

        $this->resourceSpace = new ResourceSpace($this->getContainer());

        $this->resourceSpaceData = $this->resourceSpace->getCurrentResourceSpaceData();
        if ($this->resourceSpaceData === null) {
            $this->logger->error( 'Error: no resourcespace data.');
            return;
        }

        $csvData = $this->readRecordIdsFromCsv($csvFile);

        $resourceSpaceFilenames = $this->resourceSpace->getAllOriginalFilenames();
        foreach($csvData as $csvLine) {

            $filename = $csvLine['filename'];

            if(!array_key_exists($filename, $resourceSpaceFilenames)) {
//                echo 'Error: could not find any resources for file ' . $filename . PHP_EOL;
                continue;
            }

            $newData = $this->getDatahubData($csvLine['datahub_record_id']);
            foreach($resourceSpaceFilenames[$filename] as $ref) {
                $this->updateResourceSpaceFields($ref, $newData);
            }
        }
    }

    private function readRecordIdsFromCsv($csvFile)
    {
        $csvData = array();
        if (($handle = fopen($csvFile, "r")) !== false) {
            $columns = fgetcsv($handle, 1000, ";");
            $i = 0;
            while (($row = fgetcsv($handle, 1000, ";")) !== false) {
                if(count($columns) != count($row)) {
                    echo 'Wrong column count: should be ' . count($columns) . ', is ' . count($row) . ' at row ' . $i;
//                    $this->logger->error('Wrong column count: should be ' . count($columns) . ', is ' . count($row) . ' at row ' . $i);
                }
                //TODO trim headers
                $line = array_combine($columns, $row);

                $csvData[] = $line;
                $i++;
            }
            fclose($handle);
        }

        return $csvData;
    }

    function getDatahubData($recordId)
    {
        $newData = array();
        try {
            if (!$this->datahubEndpoint)
                $this->datahubEndpoint = Endpoint::build($this->datahubUrl . '/oai');

            $record = $this->datahubEndpoint->getRecord($recordId, $this->metadataPrefix);
            $data = $record->GetRecord->record->metadata->children($this->namespace, true);
            $domDoc = new DOMDocument;
            $domDoc->loadXML($data->asXML());
            $xpath = new DOMXPath($domDoc);

            foreach ($this->dataDefinition as $key => $dataDef) {
                if(!array_key_exists('field', $dataDef)) {
                    continue;
                }
                $xpaths = array();
                if(array_key_exists('xpaths', $dataDef)) {
                    $xpaths = $dataDef['xpaths'];
                } else if(array_key_exists('xpath', $dataDef)) {
                    $xpaths[] = $dataDef['xpath'];
                }
                $value = null;
                foreach($xpaths as $xpath_) {
                    $query = $this->buildXpath($xpath_, $this->datahubLanguage);
                    $extracted = $xpath->query($query);
                    if ($extracted) {
                        if (count($extracted) > 0) {
                            foreach ($extracted as $extr) {
                                if ($extr->nodeValue !== 'n/a') {
                                    if($value == null) {
                                        $value = $extr->nodeValue;
                                    }
                                    else if($key != 'keywords' || !in_array($extr->nodeValue, explode(",", $value))) {
                                        $value .= ', ' . $extr->nodeValue;
                                    }
                                }
                            }
                        }
                    }
                }
                if ($value != null) {
                    $newData[$dataDef['field']] = trim($value);
                }
            }
        }
        catch(OaipmhException $e) {
            echo 'Record id ' . $recordId . ' error: ' . $e . PHP_EOL;
//            $this->logger->error('Record id ' . $recordId . ' error: ' . $e);
        }
        catch(HttpException $e) {
            echo 'Record id ' . $recordId . ' error: ' . $e . PHP_EOL;
//            $this->logger->error('Record id ' . $recordId . ' error: ' . $e);
        }

        // Combine earliest and latest date into one
        if(array_key_exists('earliestdate', $newData)) {
            if(array_key_exists('latestdate', $newData)) {
                $newData['datecreatedofartwork'] = StringUtil::getDateRange($newData['earliestdate'], $newData['latestdate']);
                unset($newData['latestdate']);
            } else {
                $newData['datecreatedofartwork'] = StringUtil::getDateRange($newData['earliestdate'], $newData['earliestdate']);
            }
            unset($newData['earliestdate']);
        } else if(array_key_exists('latestdate', $newData)) {
            $newData['datecreatedofartwork'] = StringUtil::getDateRange($newData['latestdate'], $newData['latestdate']);
            unset($newData['latestdate']);
        }
        $newData['pidobject'] = $recordId;

        return $newData;
    }

    function updateResourceSpaceFields($resourceId, $newData)
    {
        if(!array_key_exists($resourceId, $this->resourceSpaceData)) {
            return;
        }

        $oldData = $this->resourceSpaceData[$resourceId];

        $updatedFields = 0;
        foreach($newData as $key => $value) {
            $update = false;
            if(!array_key_exists($key, $oldData)) {
                if($this->verbose) {
                    echo 'Field ' . $key . ' does not exist, should be ' . $value . PHP_EOL;
//                    $this->logger->error('Field ' . $key . ' does not exist, should be ' . $value);
                }
                $update = true;
            } else if($key == 'keywords') {
                $explodeVal = explode(',', $value);
                $explodeRS = explode(',', $oldData[$key]);
                $hasAll = true;
                foreach($explodeVal as $val) {
                    $has = false;
                    foreach($explodeRS as $rs) {
                        if($rs == $val) {
                            $has = true;
                            break;
                        }
                    }
                    if(!$has) {
                        $hasAll = false;
                        break;
                    }
                }
                if(!$hasAll) {
                    if($this->verbose) {
                        echo 'Mismatching field ' . $key . ', should be ' . $value . ', is ' . $oldData[$key] . PHP_EOL;
//                        $this->logger->error('Mismatching field ' . $key . ', should be ' . $value . ', is ' . $oldData[$key]);
                    }
                    $update = true;
                }
            } else {
                if($oldData[$key] != $value) {
                    if($this->verbose) {
                        echo 'Mismatching field ' . $key . ', should be ' . $value . ', is ' . $oldData[$key] . PHP_EOL;
//                        $this->logger->error('Mismatching field ' . $key . ', should be ' . $value . ', is ' . $oldData[$key]);
                    }
                    $update = true;
                }
            }
            if($update) {
                $result = $this->resourceSpace->updateField($resourceId, $key, $value);
                if($result !== 'true') {
                    echo 'Error updating field ' . $key . ' for resource id ' . $resourceId . ':' . PHP_EOL . $result . PHP_EOL;
//                    $this->logger->error('Error updating field ' . $key . ' for resource id ' . $resourceId . ':' . PHP_EOL . $result);
                } else {
                    $updatedFields++;
                }
            }
        }
        if($this->verbose) {
            echo 'Updated ' . $updatedFields . ' fields for resource id ' . $resourceId . PHP_EOL;
//            $this->logger->info('Updated ' . $updatedFields . ' fields for resource id ' . $resourceId);
        }
    }

    // Build the xpath based on the provided namespace
    private function buildXpath($xpath, $language)
    {
        $xpath = str_replace('{language}', $language, $xpath);
        $xpath = str_replace('[@', '[@' . $this->namespace . ':', $xpath);
        $xpath = str_replace('[@' . $this->namespace . ':xml:', '[@xml:', $xpath);
        $xpath = preg_replace('/\[([^@])/', '[' . $this->namespace . ':${1}', $xpath);
        $xpath = preg_replace('/\/([^\/])/', '/' . $this->namespace . ':${1}', $xpath);
        if(strpos($xpath, '/') !== 0) {
            $xpath = $this->namespace . ':' . $xpath;
        }
        $xpath = 'descendant::' . $xpath;
        return $xpath;
    }
}
