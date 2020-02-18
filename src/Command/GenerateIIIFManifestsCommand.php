<?php

namespace App\Command;

use App\Entity\IIIfManifest;
use App\ResourceSpace\ResourceSpace;
use App\Utils\StringUtil;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class GenerateIIIFManifestsCommand extends Command implements ContainerAwareInterface, LoggerAwareInterface
{
    private $verbose;
    private $cantaloupeUrl;
    private $publicUse;

    private $resourceSpace;
    private $imagehubData;

    private $serviceUrl;
    private $createTopLevelCollection;

    protected function configure()
    {
        $this
            ->setName('app:generate-iiif-manifests')
            ->addArgument('rs_id', InputArgument::OPTIONAL, 'The ID (ref) of the resource in ResourceSpace for which we want to generate a IIIF manifest')
            ->setDescription('')
            ->setHelp('');
    }

    /**
     * Sets the container.
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->verbose = $input->getOption('verbose');

        // Make sure the service URL name ends with a trailing slash
        $this->serviceUrl = rtrim($this->container->getParameter('service_url'), '/') . '/';

        $this->cantaloupeUrl = $this->container->getParameter('cantaloupe_url');
        $this->resourceSpace = new ResourceSpace($this->container);

        $resourceSpaceId = $input->getArgument('rs_id');
        if(!preg_match('/^[0-9]+$/', $resourceSpaceId)) {
            $resourceSpaceId = null;
        }

        $resourceSpaceData = array();

        if($resourceSpaceId != null) {
            $allResourceSpaceData = $this->resourceSpace->getCurrentResourceSpaceData();
            foreach($allResourceSpaceData as $id => $data) {
                $add = false;
                if($id == $resourceSpaceId) {
                    $add = true;
                    // Also regenerate the manifests of all resources that this resource refers to
                    if(array_key_exists('relatedrecords', $data)) {
                        $related = explode(PHP_EOL, $data['relatedrecords']);
                        foreach($related as $relId) {
                            if(array_key_exists($relId, $allResourceSpaceData) && !array_key_exists($relId, $resourceSpaceData)) {
                                $resourceSpaceData[$relId] = $allResourceSpaceData[$relId];
                            }
                        }
                    }
                } else {
                    // Also regenerate manifests of all resources that refer to this resources
                    if(array_key_exists('relatedrecords', $data)) {
                        $related = explode(PHP_EOL, $data['relatedrecords']);
                    }
                    if(in_array($resourceSpaceId, $related)) {
                        $add = true;
                    }
                }
                if($add && !array_key_exists($id, $resourceSpaceData)) {
                    $resourceSpaceData[$id] = $data;
                }
            }
            // Don't create a top-level collection because we're only generating a single manifest (or a few manifests)
            $this->createTopLevelCollection = false;
        } else {
            $resourceSpaceData = $this->resourceSpace->getCurrentResourceSpaceData();
            $this->createTopLevelCollection = true;
        }

        if($resourceSpaceData == null) {
            return;
        }

        $this->imagehubData = array();

        $metadataFields = $this->container->getParameter('iiif_metadata_fields');

        $this->publicUse = $this->container->getParameter('public_use');
        $this->addExtraFields($resourceSpaceData, $metadataFields);

        $em = $this->container->get('doctrine')->getManager();
        $this->generateAndStoreManifests($em);
    }

    private function addExtraFields($resourceSpaceData, $metadataFields)
    {
        foreach($resourceSpaceData as $resourceId => $data) {

            $isPublic = $this->resourceSpace->isPublicUse($data, $this->publicUse);
            if($isPublic) {
                $url = $this->publicUse['public_folder'];
            } else {
                $url = $this->publicUse['private_folder'];
            }
            $url .= $resourceId;

            $imageData = $this->getCantaloupeData($url);
            if($imageData) {
                $imageData['metadata'] = array();
                foreach ($metadataFields as $field => $name) {
                    $imageData['metadata'][$name] = $data[$field];
                }

                $photo = StringUtil::filterPhotographer($data['copyrightnoticeofima']);
                $photographer = StringUtil::filterPhotographer($data['credit']);

                $imageData['metadata']['Copyright Notice'] = 'CC0\nAls je ons beeld gebruikt, stellen wij bronvermelding op prijs.\nCollectie ' . $data['publisher'];
                if($photo != null && $photographer != null) {
                    if($photo == $photographer || $photographer == null) {
                        $imageData['metadata']['Copyright Notice'] .= ', foto: ' . $photo . '.';
                    } else if($photo == null) {
                        $imageData['metadata']['Copyright Notice'] .= ', fotograaf: ' . $photographer . '.';
                    } else {
                        $imageData['metadata']['Copyright Notice'] .= ', foto: ' . $photo . ', fotograaf: ' . $photographer . '.';
                    }
                } else {
                    $imageData['metadata']['Copyright Notice'] .= '.';
                }
                $imageData['metadata']['Copyright Notice'] .= '\nWij ontvangen graag een exemplaar van de publicatie voor onze bibliotheek.';



                $imageData['related_records'] = explode(PHP_EOL, $data['relatedrecords']);
                if(!in_array($resourceId, $imageData['related_records'])) {
                    $imageData['related_records'][] = $resourceId;
                }
                $imageData['canvas_base'] = $this->serviceUrl . $resourceId;
                $imageData['manifest_id'] = $this->serviceUrl . $resourceId . '/manifest.json';
                $imageData['image_url'] = $this->cantaloupeUrl . $url . '.tif/full/full/0/default.jpg';
                $imageData['service_id'] = $this->cantaloupeUrl . $url . '.tif';
                $imageData['public_use'] = $isPublic;


                $this->imagehubData[$resourceId] = $imageData;
            }
        }
    }

    private function getCantaloupeData($resourceId)
    {
        try {
            $jsonData = file_get_contents($this->cantaloupeUrl . $resourceId . '.tif/info.json');
            $data = json_decode($jsonData);
            if($this->verbose) {
//                echo 'Retrieved image ' . $resourceId . ' from Cantaloupe' . PHP_EOL;
                $this->logger->info('Retrieved image ' . $resourceId . ' from Cantaloupe');
            }
            return array('height' => $data->height, 'width' => $data->width);
        } catch(Exception $e) {
//            echo $e->getMessage() . PHP_EOL;
            $this->logger->error($e->getMessage());
        }
        return null;
    }

    private function generateAndStoreManifests(EntityManagerInterface $em)
    {
        $validate = $this->container->getParameter('validate_manifests');
        $validatorUrl = $this->container->getParameter('validator_url');

        // Top-level collection containing a link to all manifests
        $manifests = array();

        if($this->createTopLevelCollection) {
            $this->deleteAllManifests($em);
        }

        foreach($this->imagehubData as $resourceId => $data) {

            $label = null;
            $description = null;
            $attribution = null;

            // Fill in (multilingual) manifest data
            $manifestMetadata = array();
            foreach($data['metadata'] as $key => $metadata) {
/*                $arr = array();
                foreach($metadata as $language => $value) {
                    // Change nl into nl-BE, en into en-GB, etc.
                    if(array_key_exists($language, $this->localisations)) {
                        $language = $this->localisations[$language];
                    }
                    $arr[] = array(
                        '@language' => $language,
                        '@value'    => $value
                    );
                }*/

                // Replace comma by ' - ' for date ranges
                if(preg_match('/^[0-9]{3,4}\-[0-9]{1,2}\-[0-9]{1,2}, *[0-9]{3,4}\-[0-9]{1,2}\-[0-9]{1,2}$/', $metadata)) {
                    $metadata = str_replace(' ', '', $metadata);
                    $metadata = str_replace(',', ' - ', $metadata);

                  // Remove date and month when the exact date is clearly unknown
                  if(preg_match('/^[0-9]{3,4}\-01\-01 \- [0-9]{3,4}\-12\-31$/', $metadata)) {
                      $metadata = str_replace('-01-01', '', $metadata);
                      $metadata = str_replace('-12-31', '', $metadata);
                  }

                  // Remove latest date if it is the same as the earliest date
                  $dashIndex = strpos($metadata, ' - ');
                  $earliestDate = substr($metadata, 0, $dashIndex);
                  $latestDate = substr($metadata, $dashIndex + 3);
                  if($earliestDate === $latestDate) {
                    $metadata = $earliestDate;
                  }
                }

                // Grab the values for the top-level description, label and attribution
                if($key == 'Description') {
                    $description = $metadata;
                    // Description is not included in the metadata field
                    continue;
                } else if($key == 'Title') {
                    $label = $metadata;
                } else if($key == 'Credit Line') {
                    $attribution = $metadata;
                }
                $manifestMetadata[] = array(
                    'label' => $key,
                    'value' => $metadata
                );
            }

            // Generate the canvases
            $canvases = array();
            $index = 0;
            $startCanvas = null;
            $thumbnail = null;
            $isStartCanvas = false;

            // Loop through all works related to this resource (including itself)
            foreach($data['related_records'] as $relatedRef) {

                if(!array_key_exists($relatedRef, $this->imagehubData)) {
                    continue;
                }

                // When the related resource ID is the ID of the resource we're currently processing,
                // we know that this canvas is in fact the main canvas.
                $isStartCanvas = $relatedRef == $resourceId;

                $index++;
                $canvasId = $this->imagehubData[$relatedRef]['canvas_base'] . '/canvas/' . $index . '.json';
//                $serviceId = $this->serviceUrl . $relatedRef;
                $serviceId = $this->imagehubData[$relatedRef]['service_id'];
                $imageUrl = $this->imagehubData[$relatedRef]['image_url'];
                $publicUse = $this->imagehubData[$relatedRef]['public_use'];
                if($isStartCanvas && $startCanvas == null) {
                    $startCanvas = $canvasId;
                    $thumbnail = $serviceId;
                }
                $canvases[] = $this->generateCanvas($serviceId, $relatedRef, $imageUrl, $canvasId, $publicUse);

/*                // Store the canvas in the database
                $canvasDocument = new Canvas();
                $canvasDocument->setCanvasId($canvasId);
                $canvasDocument->setData(json_encode($newCanvas));
                $dm->persist($canvasDocument);
*/
            }

            $manifestId = $data['manifest_id'];
            // Generate the whole manifest
            $manifest = array(
                '@context'         => 'http://iiif.io/api/presentation/2/context.json',
                '@type'            => 'sc:Manifest',
                '@id'              => $manifestId,
                'label'            => $label,
                'attribution'      => $attribution,
                'description'      => empty($description) ? 'n/a' : $description,
                'metadata'         => $manifestMetadata,
                'viewingDirection' => 'left-to-right',
                'viewingHint'      => 'individuals',
                'sequences'        => $this->createSequence($canvases, $startCanvas)
            );

            // This image is not for public use, therefore we also don't want this manifest to be public
            if($isStartCanvas && !$data['public_use']) {
                $manifest['service'] = $this->getAuthenticationService();
            }

            if(!$this->createTopLevelCollection) {
                $this->deleteManifest($em, $manifestId);
            }

            $manifestDocument = $this->storeManifest($em, $manifest, $manifestId);

            // Validate the manifest
            // We can only pass a URL to the validator, so the manifest needs to be stored and served already before validation
            // If it does not pass validation, remove from the database
            $valid = true;
            if($validate) {
                $valid = $this->validateManifest($validatorUrl, $manifestId);
                if (!$valid) {
//                    echo 'Manifest ' . $manifestId . ' is not valid.' . PHP_EOL;
                    $this->logger->error('Manifest ' . $manifestId . ' is not valid.');
                    $em->remove($manifestDocument);
                    $em->flush();
                }
            }
            $em->clear();

            if($valid) {
                if($this->verbose) {
//                    echo 'Generated manifest ' . $manifestId . ' for resource ' . $resourceId . PHP_EOL;
                    $this->logger->info('Generated manifest ' . $manifestId . ' for resource ' . $resourceId);
                }

                // Add to manifests array to add to the top-level collection
                $manifests[] = array(
                    '@id' => $manifestId,
                    '@type' => 'sc:Manifest',
                    'label' => $label,
                    'metadata' => $manifestMetadata
                );

                // Update the LIDO data to include the manifest and thumbnail
//                $this->addManifestAndThumbnailToLido($this->namespace, $dataPid, $manifestId, $thumbnail);
            }
        }

        //TODO do we actually need a top-level manifest?
        // If so, we need to store the 'label' of each manifest separately and then do a SELECT to get all ID's and labels for the top-level manifest

        if($this->createTopLevelCollection && count($manifests) > 0) {
            // Generate the top-level collection and store it in mongoDB
            $collectionId = $this->serviceUrl . 'collection/top';
            $collection = array(
                '@context' => 'http://iiif.io/api/presentation/2/context.json',
                '@id' => $collectionId,
                '@type' => 'sc:Collection',
                'label' => 'Top Level Collection for Imagehub',
                'viewingHint' => 'top',
                'description' => 'This collection lists all the IIIF manifests available in this Imagehub instance',
                'manifests' => $manifests
            );

            $this->deleteManifest($em, $collectionId);

            $manifestDocument = $this->storeManifest($em, $collection, $collectionId);

            $valid = true;
            if ($validate) {
                $valid = $this->validateManifest($validatorUrl, $collectionId);
                if (!$valid) {
//                    echo 'Top-level collection ' . $collectionId . ' is not valid.' . PHP_EOL;
                    $this->logger->error('Top-level collection ' . $collectionId . ' is not valid.');
                    $em->remove($manifestDocument);
                    $em->flush();
                }
            }
            $em->clear();

            if ($this->verbose) {
                if ($valid) {
//                    echo 'Created and stored top-level collection' . PHP_EOL;
                    $this->logger->info('Created and stored top-level collection');
                }
//                echo 'Done, created and stored ' . count($manifests) . ' manifests.' . PHP_EOL;
                $this->logger->info('Done, created and stored ' . count($manifests) . ' manifests.');
            }
        }
    }

    private function generateCanvas($serviceId, $relatedRef, $imageUrl, $canvasId, $publicUse)
    {
        $service = array(
            '@context' => 'http://iiif.io/api/image/2/context.json',
            '@id'      => $serviceId,
            'profile'  => 'http://iiif.io/api/image/2/level2.json'
        );
        $resource = array(
            '@id'     => $imageUrl,
            '@type'   => 'dctypes:Image',
            'format'  => 'image/jpeg',
            'service' => $service,
            'height'  => $this->imagehubData[$relatedRef]['height'],
            'width'   => $this->imagehubData[$relatedRef]['width']
        );
        $image = array(
            '@context'   => 'http://iiif.io/api/presentation/2/context.json',
            '@type'      => 'oa:Annotation',
            '@id'        => $canvasId . '/image',
            'motivation' => 'sc:painting',
            'resource'   => $resource,
            'on'         => $canvasId
        );
        if(!$publicUse) {
            $image['service'] = $this->getAuthenticationService();
        }
        $newCanvas = array(
            '@id'    => $canvasId,
            '@type'  => 'sc:Canvas',
            'label'  => $relatedRef,
            'height' => $this->imagehubData[$relatedRef]['height'],
            'width'  => $this->imagehubData[$relatedRef]['width'],
            'images' => array($image)
        );
        return $newCanvas;
    }

    private function getAuthenticationService()
    {
        $arr = array(
            '@context' => 'http://iiif.io/api/auth/1/context.json',
            '@id'      => $this->container->getParameter('authentication_url'),
        );
        foreach($this->container->getParameter('authentication_service_description') as $key => $value) {
            $arr[$key] = $value;
        }
        return $arr;
    }

    private function createSequence($canvases, $startCanvas)
    {
        // Fill in sequence data
        if($startCanvas == null) {
            $manifestSequence = array(
                '@type'    => 'sc:Sequence',
                '@context' => 'http://iiif.io/api/presentation/2/context.json',
                'canvases' => $canvases
            );
        } else {
            $manifestSequence = array(
                '@type'       => 'sc:Sequence',
                '@context'    => 'http://iiif.io/api/presentation/2/context.json',
                'startCanvas' => $startCanvas,
                'canvases'    => $canvases
            );
        }
        return array($manifestSequence);
    }

    private function deleteAllManifests(EntityManagerInterface $em)
    {
        $qb = $em->createQueryBuilder();
        $query = $qb->delete(IIIfManifest::class, 'manifest')
            ->getQuery();
        $query->execute();
        $em->flush();
    }

    private function deleteManifest(EntityManagerInterface $em, $manifestId)
    {
        $qb = $em->createQueryBuilder();
        $query = $qb->delete(IIIfManifest::class, 'manifest')
                    ->where('manifest.manifestId = :manif_id')
                    ->setParameter('manif_id', $manifestId)
                    ->getQuery();
        $query->execute();
        $em->flush();
    }

    private function storeManifest(EntityManagerInterface $em, $manifest, $manifestId)
    {
        // Store the manifest in mongodb
        $manifestDocument = new IIIFManifest();
        $manifestDocument->setManifestId($manifestId);
        $manifestDocument->setData(json_encode($manifest));
        $em->persist($manifestDocument);
        $em->flush();
        return $manifestDocument;
    }

    private function validateManifest($validatorUrl, $manifestId)
    {
        $valid = true;
        try {
            $validatorJsonResult = file_get_contents($validatorUrl . $manifestId);
            $validatorResult = json_decode($validatorJsonResult);
            $valid = $validatorResult->okay == 1;
            if (!empty($validatorResult->warnings)) {
                foreach ($validatorResult->warnings as $warning) {
//                    echo 'Manifest ' . $manifestId . ' warning: ' . $warning . PHP_EOL;
                    $this->logger->warning('Manifest ' . $manifestId . ' warning: ' . $warning);
                }
            }
            if (!empty($validatorResult->error)) {
                if ($validatorResult->error != 'None') {
                    $valid = false;
//                    echo 'Manifest ' . $manifestId . ' error: ' . $validatorResult->error . PHP_EOL;
                    $this->logger->error('Manifest ' . $manifestId . ' error: ' . $validatorResult->error);
                }
            }
        } catch (Exception $e) {
            if($this->verbose) {
//                echo 'Error validating manifest ' . $manifestId . ': ' . $e . PHP_EOL;
                $this->logger->error('Error validating manifest ' . $manifestId . ': ' . $e);
            } else {
//                echo 'Error validating manifest ' . $manifestId . ': ' . $e->getMessage() . PHP_EOL;
                $this->logger->error('Error validating manifest ' . $manifestId . ': ' . $e->getMessage());
            }
        }
        return $valid;
    }
}
